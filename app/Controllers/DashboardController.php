<?php

require_once ROOT . '/app/Core/Controller.php';

class DashboardController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $role = Auth::role();
        $db   = getDB();
        $data = ['title' => 'Dashboard', 'role' => $role];

        switch ($role) {
            case 'super_admin':
            case 'principal':
            case 'vice_principal':
                $data = array_merge($data, $this->getAdminStats($db));
                break;
            case 'teacher':
            case 'dept_head':
                $data = array_merge($data, $this->getTeacherStats($db));
                break;
            case 'student':
                $data = array_merge($data, $this->getStudentStats($db));
                break;
            case 'parent':
                $data = array_merge($data, $this->getParentStats($db));
                break;
            case 'finance_officer':
                $data = array_merge($data, $this->getFinanceStats($db));
                break;
            default:
                $data = array_merge($data, $this->getAdminStats($db));
        }

        $data['announcements'] = getActiveAnnouncements();
        $data['notifications'] = getUnreadNotifications();

        $this->render('dashboard/index', $data);
    }

    private function getAdminStats(PDO $db): array {
        $ayId = (int)getSetting('academic_year_id', 1);

        $totalStudents = $db->prepare("SELECT COUNT(*) FROM students WHERE status = 'active' AND academic_year_id = ?");
        $totalStudents->execute([$ayId]);

        $totalStaff = $db->query("SELECT COUNT(*) FROM staff WHERE status = 'active'")->fetchColumn();
        $totalClasses = $db->prepare("SELECT COUNT(*) FROM classes WHERE academic_year_id = ?");
        $totalClasses->execute([$ayId]);

        $todayDate = date('Y-m-d');
        $presentToday = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date = ? AND status = 'present'");
        $presentToday->execute([$todayDate]);
        $totalToday = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date = ?");
        $totalToday->execute([$todayDate]);

        $ptVal  = (int)$presentToday->fetchColumn();
        $ttVal  = (int)$totalToday->fetchColumn();
        $attRate = $ttVal > 0 ? round(($ptVal / $ttVal) * 100, 1) : 0;

        $monthStart = date('Y-m-01');
        $feeCollected = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date >= ?");
        $feeCollected->execute([$monthStart]);

        $pendingFees = $db->query("SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE status IN ('unpaid','partial')")->fetchColumn();
        $openIncidents = $db->query("SELECT COUNT(*) FROM discipline_incidents WHERE status = 'open'")->fetchColumn();
        $overdueBooks = $db->query("SELECT COUNT(*) FROM book_borrowings WHERE status = 'borrowed' AND due_date < CURDATE()")->fetchColumn();

        // Grade distribution for chart
        $gradeDistrib = $db->prepare("SELECT grade, COUNT(*) as count FROM classes WHERE academic_year_id = ? GROUP BY grade ORDER BY grade");
        $gradeDistrib->execute([$ayId]);
        $gradeDistribData = $gradeDistrib->fetchAll();

        // Recent payments
        $recentPayments = $db->query("SELECT p.*, s.first_name, s.last_name, s.student_id FROM payments p JOIN students s ON p.student_id = s.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

        // Recent enrollments
        $recentStudents = $db->prepare("SELECT * FROM students WHERE academic_year_id = ? ORDER BY created_at DESC LIMIT 5");
        $recentStudents->execute([$ayId]);

        // Monthly attendance for chart (last 7 days)
        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $pStmt = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date = ? AND status = 'present'");
            $pStmt->execute([$d]);
            $aStmt = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date = ?");
            $aStmt->execute([$d]);
            $p = (int)$pStmt->fetchColumn();
            $a = (int)$aStmt->fetchColumn();
            $attendanceChart[] = [
                'date'    => date('D d', strtotime($d)),
                'present' => $p,
                'absent'  => $a - $p,
                'rate'    => $a > 0 ? round(($p/$a)*100,1) : 0,
            ];
        }

        return [
            'total_students'   => (int)$totalStudents->fetchColumn(),
            'total_staff'      => (int)$totalStaff,
            'total_classes'    => (int)$totalClasses->fetchColumn(),
            'att_rate'         => $attRate,
            'fee_collected'    => (float)$feeCollected->fetchColumn(),
            'pending_fees'     => (float)$pendingFees,
            'open_incidents'   => (int)$openIncidents,
            'overdue_books'    => (int)$overdueBooks,
            'grade_distrib'    => $gradeDistribData,
            'recent_payments'  => $recentPayments,
            'recent_students'  => $recentStudents->fetchAll(),
            'attendance_chart' => $attendanceChart,
        ];
    }

    private function getTeacherStats(PDO $db): array {
        $userId  = Auth::id();
        $semId   = (int)getSetting('semester_id', 1);

        $staffStmt = $db->prepare("SELECT id FROM staff WHERE user_id = ? LIMIT 1");
        $staffStmt->execute([$userId]);
        $staff = $staffStmt->fetch();
        $staffId = $staff ? $staff['id'] : 0;

        $myClasses = $db->prepare("SELECT DISTINCT c.* FROM class_subjects cs JOIN classes c ON cs.class_id = c.id WHERE cs.teacher_id = ? AND cs.semester_id = ?");
        $myClasses->execute([$staffId, $semId]);
        $classes = $myClasses->fetchAll();

        $mySubjects = $db->prepare("SELECT DISTINCT s.name FROM class_subjects cs JOIN subjects s ON cs.subject_id = s.id WHERE cs.teacher_id = ? AND cs.semester_id = ?");
        $mySubjects->execute([$staffId, $semId]);

        $pendingAssignments = $db->prepare("SELECT COUNT(*) FROM assignments a LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.status != 'graded' WHERE a.teacher_id = ? AND a.status = 'active'");
        $pendingAssignments->execute([$staffId]);

        $todayTimetable = $db->prepare("SELECT tt.*, s.name as subject_name, c.grade, c.section FROM timetable tt JOIN subjects s ON tt.subject_id = s.id JOIN classes c ON tt.class_id = c.id WHERE tt.teacher_id = ? AND tt.day = ? AND tt.semester_id = ? ORDER BY tt.period");
        $todayTimetable->execute([$staffId, date('l'), $semId]);

        $attendanceStats = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent FROM staff_attendance WHERE staff_id = ? AND MONTH(date) = ? AND YEAR(date) = ?");
        $attendanceStats->execute([$staffId, date('m'), date('Y')]);

        return [
            'my_classes'          => $classes,
            'class_count'         => count($classes),
            'subject_count'       => $mySubjects->rowCount(),
            'pending_assignments' => (int)$pendingAssignments->fetchColumn(),
            'today_timetable'     => $todayTimetable->fetchAll(),
            'attendance_stats'    => $attendanceStats->fetch(),
        ];
    }

    private function getStudentStats(PDO $db): array {
        $userId  = Auth::id();
        $semId   = (int)getSetting('semester_id', 1);

        $stuStmt = $db->prepare("SELECT s.*, c.grade, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.user_id = ? LIMIT 1");
        $stuStmt->execute([$userId]);
        $student = $stuStmt->fetch();

        if (!$student) return ['student' => null];

        $stuId   = $student['id'];
        $classId = $student['class_id'];

        // Attendance summary this month
        $attStmt = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent, SUM(status='late') as late FROM student_attendance WHERE student_id = ? AND MONTH(date) = ? AND YEAR(date) = ?");
        $attStmt->execute([$stuId, date('m'), date('Y')]);
        $attendance = $attStmt->fetch();

        // Recent marks
        $marksStmt = $db->prepare("SELECT m.*, e.title, e.type, e.total_marks, s.name as subject_name FROM marks m JOIN exams e ON m.exam_id = e.id JOIN subjects s ON e.subject_id = s.id WHERE m.student_id = ? ORDER BY m.created_at DESC LIMIT 5");
        $marksStmt->execute([$stuId]);

        // Today's timetable
        $ttStmt = $db->prepare("SELECT tt.*, s.name as subject_name, st.first_name, st.last_name FROM timetable tt JOIN subjects s ON tt.subject_id = s.id JOIN staff st ON tt.teacher_id = st.id WHERE tt.class_id = ? AND tt.day = ? AND tt.semester_id = ? ORDER BY tt.period");
        $ttStmt->execute([$classId, date('l'), $semId]);

        // Pending assignments
        $asgStmt = $db->prepare("SELECT a.*, s.name as subject_name FROM assignments a JOIN subjects s ON a.subject_id = s.id LEFT JOIN submissions sub ON sub.assignment_id = a.id AND sub.student_id = ? WHERE a.class_id = ? AND a.status = 'active' AND sub.id IS NULL ORDER BY a.due_date ASC");
        $asgStmt->execute([$stuId, $classId]);

        // Fee status
        $feeStmt = $db->prepare("SELECT COUNT(*) as total, SUM(status='paid') as paid, SUM(status IN ('unpaid','partial')) as unpaid FROM student_fees WHERE student_id = ?");
        $feeStmt->execute([$stuId]);

        return [
            'student'             => $student,
            'attendance'          => $attendance,
            'recent_marks'        => $marksStmt->fetchAll(),
            'today_timetable'     => $ttStmt->fetchAll(),
            'pending_assignments' => $asgStmt->fetchAll(),
            'fee_status'          => $feeStmt->fetch(),
        ];
    }

    private function getParentStats(PDO $db): array {
        $userId  = Auth::id();

        $parentStmt = $db->prepare("SELECT p.*, s.* FROM parents p JOIN students s ON p.student_id = s.id WHERE p.user_id = ? LIMIT 1");
        $parentStmt->execute([$userId]);
        $linked = $parentStmt->fetch();

        if (!$linked) return ['linked_student' => null];

        $stuId = $linked['student_id'];

        $attStmt = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent FROM student_attendance WHERE student_id = ? AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())");
        $attStmt->execute([$stuId]);

        $recentMarks = $db->prepare("SELECT m.*, e.title, e.type, e.total_marks, s.name as subject FROM marks m JOIN exams e ON m.exam_id = e.id JOIN subjects s ON e.subject_id = s.id WHERE m.student_id = ? ORDER BY m.created_at DESC LIMIT 5");
        $recentMarks->execute([$stuId]);

        $feeStmt = $db->prepare("SELECT sf.*, fc.name as fee_name FROM student_fees sf JOIN fee_categories fc ON sf.fee_category_id = fc.id WHERE sf.student_id = ? AND sf.status IN ('unpaid','partial') ORDER BY sf.due_date ASC");
        $feeStmt->execute([$stuId]);

        return [
            'linked_student' => $linked,
            'attendance'     => $attStmt->fetch(),
            'recent_marks'   => $recentMarks->fetchAll(),
            'pending_fees'   => $feeStmt->fetchAll(),
        ];
    }

    private function getFinanceStats(PDO $db): array {
        $month      = date('m');
        $year       = date('Y');
        $monthStart = date('Y-m-01');
        $monthEnd   = date('Y-m-t');

        $collected = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN ? AND ?");
        $collected->execute([$monthStart, $monthEnd]);

        $expenses = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ? AND status = 'approved'");
        $expenses->execute([$monthStart, $monthEnd]);

        $pendingFees = $db->query("SELECT COALESCE(SUM(amount - discount),0) FROM student_fees WHERE status IN ('unpaid','partial')")->fetchColumn();

        $recentPayments = $db->query("SELECT p.*, s.first_name, s.last_name, s.student_id FROM payments p JOIN students s ON p.student_id = s.id ORDER BY p.created_at DESC LIMIT 10")->fetchAll();

        $pendingPayroll = $db->prepare("SELECT COUNT(*) FROM payroll WHERE month = ? AND year = ? AND status = 'pending'");
        $pendingPayroll->execute([$month, $year]);

        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $m    = date('Y-m', strtotime("-$i months"));
            $mS   = $m . '-01';
            $mE   = date('Y-m-t', strtotime($mS));
            $colStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN ? AND ?");
            $colStmt->execute([$mS, $mE]);
            $expStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ? AND status='approved'");
            $expStmt->execute([$mS, $mE]);
            $monthlyChart[] = [
                'month'     => date('M Y', strtotime($mS)),
                'collected' => (float)$colStmt->fetchColumn(),
                'expenses'  => (float)$expStmt->fetchColumn(),
            ];
        }

        return [
            'month_collected'  => (float)$collected->fetchColumn(),
            'month_expenses'   => (float)$expenses->fetchColumn(),
            'pending_fees'     => (float)$pendingFees,
            'recent_payments'  => $recentPayments,
            'pending_payroll'  => (int)$pendingPayroll->fetchColumn(),
            'monthly_chart'    => $monthlyChart,
        ];
    }
}
