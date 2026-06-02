<?php

require_once ROOT . '/app/Core/Controller.php';

class ReportController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head','finance_officer']);
        $this->render('reports/index', ['title' => 'Reports']);
    }

    public function academic(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId= (int)getSetting('semester_id', 1);
        $classId = $this->get('class_id', '');

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id=? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $performance = [];
        if ($classId) {
            $stmt = $db->prepare("SELECT s.first_name, s.last_name, s.student_id, AVG(m.grade_point) as gpa, COUNT(DISTINCT e.subject_id) as subjects, SUM(sa.status='absent') as absences FROM students s LEFT JOIN marks m ON m.student_id=s.id LEFT JOIN exams e ON m.exam_id=e.id AND e.semester_id=? LEFT JOIN student_attendance sa ON sa.student_id=s.id WHERE s.class_id=? AND s.status='active' GROUP BY s.id ORDER BY gpa DESC");
            $stmt->execute([$semId, $classId]);
            $rows = $stmt->fetchAll();
            foreach ($rows as $i => &$r) {
                $r['rank'] = $i + 1;
                $r['gpa']  = round($r['gpa'] ?? 0, 2);
            }
            $performance = $rows;
        }

        // Subject-wise average
        $subjectAvg = [];
        if ($classId) {
            $stmt = $db->prepare("SELECT sub.name, AVG(m.marks_obtained/e.total_marks*100) as avg_pct, MIN(m.marks_obtained/e.total_marks*100) as min_pct, MAX(m.marks_obtained/e.total_marks*100) as max_pct FROM marks m JOIN exams e ON m.exam_id=e.id JOIN subjects sub ON e.subject_id=sub.id JOIN students s ON m.student_id=s.id WHERE s.class_id=? AND e.semester_id=? GROUP BY sub.id ORDER BY avg_pct DESC");
            $stmt->execute([$classId, $semId]);
            $subjectAvg = $stmt->fetchAll();
        }

        $this->render('reports/academic', [
            'title'       => 'Academic Report',
            'classes'     => $classes->fetchAll(),
            'classId'     => $classId,
            'performance' => $performance,
            'subject_avg' => $subjectAvg,
        ]);
    }

    public function attendance(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $month= $this->get('month', date('Y-m'));

        [$year, $mon] = explode('-', $month . '-01');

        $classStats = $db->prepare("SELECT c.grade, c.section, COUNT(DISTINCT sa.student_id) as students, COUNT(sa.id) as total_records, SUM(sa.status='present') as present, SUM(sa.status='absent') as absent, SUM(sa.status='late') as late FROM classes c LEFT JOIN students s ON s.class_id=c.id LEFT JOIN student_attendance sa ON sa.student_id=s.id AND YEAR(sa.date)=? AND MONTH(sa.date)=? WHERE c.academic_year_id=? GROUP BY c.id ORDER BY c.grade, c.section");
        $classStats->execute([$year, $mon, $ayId]);

        $dailyStats = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$mon, (int)$year);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt = sprintf('%04d-%02d-%02d', $year, $mon, $d);
            $dow = (int)date('N', strtotime($dt));
            if ($dow >= 6) continue;
            $pStmt = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date=? AND status='present'");
            $pStmt->execute([$dt]);
            $aStmt = $db->prepare("SELECT COUNT(*) FROM student_attendance WHERE date=?");
            $aStmt->execute([$dt]);
            $p = (int)$pStmt->fetchColumn();
            $a = (int)$aStmt->fetchColumn();
            if ($a > 0) $dailyStats[] = ['date' => $dt, 'day' => date('d', strtotime($dt)), 'rate' => round(($p/$a)*100,1)];
        }

        $this->render('reports/attendance', [
            'title'       => 'Attendance Report',
            'class_stats' => $classStats->fetchAll(),
            'daily_stats' => $dailyStats,
            'month'       => $month,
        ]);
    }

    public function financial(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);
        $db   = getDB();
        $year = $this->get('year', date('Y'));

        $income = $db->prepare("SELECT MONTH(payment_date) as month, SUM(amount) as total FROM payments WHERE YEAR(payment_date)=? GROUP BY MONTH(payment_date) ORDER BY month");
        $income->execute([$year]);

        $expenses = $db->prepare("SELECT MONTH(expense_date) as month, SUM(amount) as total FROM expenses WHERE YEAR(expense_date)=? AND status='approved' GROUP BY MONTH(expense_date) ORDER BY month");
        $expenses->execute([$year]);

        $unpaid = $db->query("SELECT c.grade, c.section, COUNT(sf.id) as count, SUM(sf.amount) as total FROM student_fees sf JOIN students s ON sf.student_id=s.id JOIN classes c ON s.class_id=c.id WHERE sf.status IN ('unpaid','partial') GROUP BY c.id ORDER BY c.grade, c.section")->fetchAll();

        $paymentMethods = $db->prepare("SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM payments WHERE YEAR(payment_date)=? GROUP BY payment_method");
        $paymentMethods->execute([$year]);

        $this->render('reports/financial', [
            'title'           => 'Financial Report',
            'income'          => $income->fetchAll(),
            'expenses'        => $expenses->fetchAll(),
            'unpaid'          => $unpaid,
            'payment_methods' => $paymentMethods->fetchAll(),
            'year'            => $year,
        ]);
    }

    public function staff(): void {
        $this->requireAuth(['super_admin','principal']);
        $db = getDB();

        $byDept = $db->query("SELECT d.name, COUNT(s.id) as total, SUM(s.gender='male') as male, SUM(s.gender='female') as female, AVG(s.basic_salary) as avg_salary FROM departments d LEFT JOIN staff s ON s.department_id=d.id AND s.status='active' GROUP BY d.id ORDER BY d.name")->fetchAll();

        $byPosition = $db->query("SELECT position, COUNT(*) as count FROM staff WHERE status='active' GROUP BY position ORDER BY count DESC")->fetchAll();

        $leaveStats = $db->query("SELECT leave_type, COUNT(*) as count, SUM(days) as total_days FROM leave_requests WHERE status='approved' AND YEAR(start_date)=YEAR(CURDATE()) GROUP BY leave_type")->fetchAll();

        $this->render('reports/staff', [
            'title'        => 'Staff Report',
            'by_dept'      => $byDept,
            'by_position'  => $byPosition,
            'leave_stats'  => $leaveStats,
        ]);
    }

    public function annual(): void {
        $this->requireAuth(['super_admin','principal']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        $ay = $db->prepare("SELECT * FROM academic_years WHERE id=?");
        $ay->execute([$ayId]);

        $students = $db->prepare("SELECT COUNT(*) as total, SUM(gender='male') as male, SUM(gender='female') as female FROM students WHERE academic_year_id=? AND status='active'");
        $students->execute([$ayId]);

        $staff = $db->query("SELECT COUNT(*) as total, SUM(gender='male') as male, SUM(gender='female') as female FROM staff WHERE status='active'")->fetch();

        $attendance = $db->query("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent FROM student_attendance WHERE YEAR(date)=YEAR(CURDATE())")->fetch();

        $totalIncome = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE YEAR(payment_date)=?");
        $totalIncome->execute([date('Y')]);

        $totalExpenses = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE YEAR(expense_date)=? AND status='approved'");
        $totalExpenses->execute([date('Y')]);

        $this->render('reports/annual', [
            'title'          => 'Annual Report',
            'ay'             => $ay->fetch(),
            'students'       => $students->fetch(),
            'staff'          => $staff,
            'attendance'     => $attendance,
            'total_income'   => (float)$totalIncome->fetchColumn(),
            'total_expenses' => (float)$totalExpenses->fetchColumn(),
        ]);
    }

    public function export(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);
        $type   = $this->get('type', '');
        $format = $this->get('format', 'csv');

        // Simple CSV export
        $db = getDB();
        $data = [];
        $headers = [];
        $filename = 'report_' . date('Y-m-d');

        switch ($type) {
            case 'students':
                $stmt = $db->query("SELECT s.student_id, s.first_name, s.last_name, s.gender, s.dob, c.grade, c.section, s.status, s.phone FROM students s LEFT JOIN classes c ON s.class_id=c.id ORDER BY s.first_name");
                $headers = ['Student ID','First Name','Last Name','Gender','DOB','Grade','Section','Status','Phone'];
                $data = $stmt->fetchAll(PDO::FETCH_NUM);
                $filename = 'students_' . date('Y-m-d');
                break;
            case 'staff':
                $stmt = $db->query("SELECT s.employee_id, s.first_name, s.last_name, s.gender, d.name, s.position, s.hire_date, s.basic_salary FROM staff s LEFT JOIN departments d ON s.department_id=d.id ORDER BY s.first_name");
                $headers = ['Employee ID','First Name','Last Name','Gender','Department','Position','Hire Date','Salary'];
                $data = $stmt->fetchAll(PDO::FETCH_NUM);
                $filename = 'staff_' . date('Y-m-d');
                break;
            case 'payments':
                $stmt = $db->query("SELECT p.receipt_no, s.first_name, s.last_name, s.student_id, p.amount, p.payment_date, p.payment_method FROM payments p JOIN students s ON p.student_id=s.id ORDER BY p.payment_date DESC");
                $headers = ['Receipt','First Name','Last Name','Student ID','Amount','Date','Method'];
                $data = $stmt->fetchAll(PDO::FETCH_NUM);
                $filename = 'payments_' . date('Y-m-d');
                break;
        }

        if ($format === 'csv' && !empty($data)) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($data as $row) fputcsv($out, $row);
            fclose($out);
            exit;
        }

        Flash::set('error', 'Export type not supported.');
        $this->redirect('reports');
    }
}
