<?php

require_once ROOT . '/app/Core/Controller.php';

class AttendanceController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $db    = getDB();
        $ayId  = (int)getSetting('academic_year_id', 1);
        $today = date('Y-m-d');

        // Today's summary per class
        $stmt = $db->prepare("SELECT c.grade, c.section, COUNT(s.id) as enrolled, SUM(sa.status='present') as present, SUM(sa.status='absent') as absent, SUM(sa.status='late') as late FROM classes c LEFT JOIN students s ON s.class_id = c.id AND s.status = 'active' LEFT JOIN student_attendance sa ON sa.student_id = s.id AND sa.date = ? WHERE c.academic_year_id = ? GROUP BY c.id ORDER BY c.grade, c.section");
        $stmt->execute([$today, $ayId]);

        $this->render('attendance/index', [
            'title'   => 'Attendance',
            'classes' => $stmt->fetchAll(),
            'today'   => $today,
        ]);
    }

    public function take(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','teacher','registrar']);

        $db      = getDB();
        $ayId    = (int)getSetting('academic_year_id', 1);
        $classId = $this->get('class_id', '');
        $date    = $this->get('date', date('Y-m-d'));

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $students = [];
        $existing = [];

        if ($classId) {
            $stmt = $db->prepare("SELECT s.*, COALESCE(sa.status,'') as att_status, sa.remarks FROM students s LEFT JOIN student_attendance sa ON sa.student_id = s.id AND sa.date = ? WHERE s.class_id = ? AND s.status = 'active' ORDER BY s.first_name");
            $stmt->execute([$date, $classId]);
            $students = $stmt->fetchAll();

            $exStmt = $db->prepare("SELECT student_id FROM student_attendance WHERE class_id = ? AND date = ?");
            $exStmt->execute([$classId, $date]);
            $existing = $exStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $this->render('attendance/take', [
            'title'    => 'Take Attendance',
            'classes'  => $classes->fetchAll(),
            'students' => $students,
            'classId'  => $classId,
            'date'     => $date,
            'existing' => $existing,
        ]);
    }

    public function save(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','teacher','registrar']);
        $this->validateCsrf();

        $db      = getDB();
        $classId = $this->post('class_id', '');
        $date    = $this->post('date', date('Y-m-d'));
        $statuses= $_POST['status'] ?? [];
        $remarks = $_POST['remarks'] ?? [];

        if (empty($classId) || empty($statuses)) {
            Flash::set('error', 'Invalid attendance data.');
            $this->redirect('attendance/take?class_id=' . $classId . '&date=' . $date);
            return;
        }

        try {
            $db->beginTransaction();

            // Delete existing and re-insert for idempotency
            $delStmt = $db->prepare("DELETE FROM student_attendance WHERE class_id = ? AND date = ?");
            $delStmt->execute([$classId, $date]);

            $insStmt = $db->prepare("INSERT INTO student_attendance (student_id, class_id, date, status, remarks, recorded_by) VALUES (?,?,?,?,?,?)");

            foreach ($statuses as $studentId => $status) {
                $insStmt->execute([
                    $studentId,
                    $classId,
                    $date,
                    $status,
                    $remarks[$studentId] ?? null,
                    Auth::id(),
                ]);
            }

            $db->commit();
            Auth::audit('save_attendance', 'attendance', (int)$classId, "Date: $date");
            Flash::set('success', 'Attendance saved successfully for ' . date('D, d M Y', strtotime($date)) . '.');
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed to save attendance: ' . $e->getMessage());
        }

        $this->redirect('attendance/take?class_id=' . $classId . '&date=' . $date);
    }

    public function report(): void {
        $this->requireAuth();

        $db      = getDB();
        $ayId    = (int)getSetting('academic_year_id', 1);
        $classId = $this->get('class_id', '');
        $month   = $this->get('month', date('Y-m'));
        $type    = $this->get('type', 'monthly');

        [$year, $mon] = explode('-', $month . '-01');

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $reportData = [];
        if ($classId) {
            $stuStmt = $db->prepare("SELECT s.id, s.first_name, s.last_name, s.student_id FROM students s WHERE s.class_id = ? AND s.status = 'active' ORDER BY s.first_name");
            $stuStmt->execute([$classId]);
            $students = $stuStmt->fetchAll();

            foreach ($students as &$stu) {
                $attStmt = $db->prepare("SELECT date, status, remarks FROM student_attendance WHERE student_id = ? AND YEAR(date) = ? AND MONTH(date) = ? ORDER BY date");
                $attStmt->execute([$stu['id'], $year, $mon]);
                $stu['attendance'] = $attStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $sumStmt = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent, SUM(status='late') as late, SUM(status='excused') as excused FROM student_attendance WHERE student_id = ? AND YEAR(date) = ? AND MONTH(date) = ?");
                $sumStmt->execute([$stu['id'], $year, $mon]);
                $stu['summary'] = $sumStmt->fetch();
            }
            $reportData = $students;
        }

        // Days in month
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$mon, (int)$year);
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt = sprintf('%04d-%02d-%02d', $year, $mon, $d);
            $dow = date('N', strtotime($dt));
            if ($dow < 6) $dates[] = $dt; // Mon-Fri only
        }

        $this->render('attendance/report', [
            'title'    => 'Attendance Report',
            'classes'  => $classes->fetchAll(),
            'classId'  => $classId,
            'month'    => $month,
            'dates'    => $dates,
            'students' => $reportData,
        ]);
    }

    public function analytics(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar']);

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        // Last 30 days daily rate
        $dailyChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $d    = date('Y-m-d', strtotime("-$i days"));
            $pStmt = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date = ? AND status = 'present'");
            $pStmt->execute([$d]);
            $aStmt = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date = ?");
            $aStmt->execute([$d]);
            $p = (int)$pStmt->fetchColumn();
            $a = (int)$aStmt->fetchColumn();
            $dailyChart[] = ['date' => date('M d', strtotime($d)), 'rate' => $a > 0 ? round(($p/$a)*100,1) : 0];
        }

        // Most absent students
        $mostAbsent = $db->query("SELECT s.first_name, s.last_name, s.student_id, COUNT(*) as absences, c.grade, c.section FROM student_attendance sa JOIN students s ON sa.student_id = s.id JOIN classes c ON s.class_id = c.id WHERE sa.status = 'absent' GROUP BY sa.student_id ORDER BY absences DESC LIMIT 10")->fetchAll();

        // Per class rate
        $classRate = $db->prepare("SELECT c.grade, c.section, COUNT(sa.id) as total, SUM(sa.status='present') as present FROM classes c LEFT JOIN students s ON s.class_id = c.id LEFT JOIN student_attendance sa ON sa.student_id = s.id WHERE c.academic_year_id = ? GROUP BY c.id ORDER BY c.grade, c.section");
        $classRate->execute([$ayId]);

        $this->render('attendance/analytics', [
            'title'       => 'Attendance Analytics',
            'daily_chart' => $dailyChart,
            'most_absent' => $mostAbsent,
            'class_rate'  => $classRate->fetchAll(),
        ]);
    }
}
