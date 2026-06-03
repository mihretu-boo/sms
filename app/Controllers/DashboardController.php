<?php

require_once ROOT . '/app/Core/Controller.php';

class DashboardController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $role  = Auth::role();
        $db    = getDB();
        $ayId  = (int)getSetting('academic_year_id', 1);
        $semId = (int)getSetting('semester_id', 1);

        $base = [
            'title'         => 'Dashboard',
            'role'          => $role,
            'announcements' => getActiveAnnouncements(),
            'notifications' => getUnreadNotifications(),
            'unread_msgs'   => getUnreadMessages(),
            'today'         => date('l, d F Y'),
            'time_greeting' => $this->greeting(),
            'username_short'=> ucfirst(explode('.', Auth::user()['username'] ?? 'user')[0]),
        ];

        $data = match(true) {
            in_array($role, ['super_admin','principal','vice_principal','registrar'])
                => array_merge($base, $this->adminData($db, $ayId, $semId)),
            $role === 'teacher'
                => array_merge($base, $this->teacherData($db, $semId)),
            $role === 'dept_head'
                => array_merge($base, $this->deptHeadData($db, $ayId, $semId)),
            $role === 'student'
                => array_merge($base, $this->studentData($db, $semId)),
            $role === 'parent'
                => array_merge($base, $this->parentData($db, $semId)),
            $role === 'finance_officer'
                => array_merge($base, $this->financeData($db)),
            default
                => array_merge($base, $this->adminData($db, $ayId, $semId)),
        };

        $this->render('dashboard/index', $data);
    }

    /* ─── Helpers ───────────────────────────────────────── */

    private function greeting(): string {
        $h = (int)date('H');
        if ($h < 12) return 'Good Morning';
        if ($h < 17) return 'Good Afternoon';
        return 'Good Evening';
    }

    private function safeQuery(PDO $db, string $sql): mixed {
        try {
            return $db->query($sql);
        } catch (PDOException $e) {
            error_log("Dashboard query error: " . $e->getMessage() . " SQL: " . substr($sql, 0, 200));
            return false;
        }
    }

    private function count(PDO $db, string $sql): int {
        $r = $this->safeQuery($db, $sql);
        return $r ? (int)$r->fetchColumn() : 0;
    }

    private function sum(PDO $db, string $sql): float {
        $r = $this->safeQuery($db, $sql);
        return $r ? (float)$r->fetchColumn() : 0.0;
    }

    /* ─── Admin / Principal ─────────────────────────────── */
    private function adminData(PDO $db, int $ayId, int $semId): array {
        $today      = date('Y-m-d');
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $monthStart = date('Y-m-01');
        $prevMonth  = date('Y-m-01', strtotime('-1 month'));

        // Core counts — students joined via classes (grade is on classes, not students)
        $totalStudents = $this->count($db,
            "SELECT COUNT(*) FROM students s
             JOIN classes c ON s.class_id = c.id
             WHERE s.status='active' AND c.academic_year_id=$ayId");

        $totalStaff   = $this->count($db, "SELECT COUNT(*) FROM staff WHERE status='active'");
        $totalClasses = $this->count($db, "SELECT COUNT(*) FROM classes WHERE academic_year_id=$ayId");

        // Attendance today vs yesterday
        $att     = $db->query("SELECT COUNT(*) as t, COALESCE(SUM(status='present'),0) as p FROM student_attendance WHERE date='$today'")->fetch();
        $attYest = $db->query("SELECT COUNT(*) as t, COALESCE(SUM(status='present'),0) as p FROM student_attendance WHERE date='$yesterday'")->fetch();
        $attRate     = ($att['t']     > 0) ? round(($att['p']     / $att['t'])     * 100, 1) : 0;
        $attYestRate = ($attYest['t'] > 0) ? round(($attYest['p'] / $attYest['t']) * 100, 1) : 0;
        $attTrend    = round($attRate - $attYestRate, 1);

        // Finance
        $monthIncome  = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date>='$monthStart'");
        $prevIncome   = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date>='$prevMonth' AND payment_date<'$monthStart'");
        $incomeTrend  = $prevIncome > 0 ? round((($monthIncome - $prevIncome) / $prevIncome) * 100, 1) : 0;
        $pendingFees  = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE status IN ('unpaid','partial')");

        $openIncidents = $this->count($db, "SELECT COUNT(*) FROM discipline_incidents WHERE status='open'");
        $overdueBooks  = $this->count($db, "SELECT COUNT(*) FROM book_borrowings WHERE status='borrowed' AND due_date < CURDATE()");
        $pendingExams  = $this->count($db, "SELECT COUNT(*) FROM exam_repository WHERE status IN ('submitted','under_review')");

        // Attendance 14-day chart
        $attChart = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $r = $db->query("SELECT COUNT(*) as t, COALESCE(SUM(status='present'),0) as p FROM student_attendance WHERE date='$d'")->fetch();
            $t = max(1, (int)($r['t'] ?? 1));
            $p = (int)($r['p'] ?? 0);
            $attChart[] = [
                'date'    => date('D d', strtotime($d)),
                'rate'    => round(($p / $t) * 100, 1),
                'present' => $p,
                'absent'  => max(0, (int)($r['t'] ?? 0) - $p),
            ];
        }

        // Finance 6-month chart
        $finChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $ms  = date('Y-m-01', strtotime("-$i months"));
            $me  = date('Y-m-t', strtotime($ms));
            $finChart[] = [
                'month'    => date('M', strtotime($ms)),
                'income'   => $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN '$ms' AND '$me'"),
                'expenses' => $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN '$ms' AND '$me' AND status='approved'"),
            ];
        }

        // Students by grade — JOIN classes because grade is on classes, not students
        $gradeChart = $db->query(
            "SELECT c.grade, COUNT(s.id) as cnt
             FROM classes c
             LEFT JOIN students s ON s.class_id = c.id AND s.status = 'active'
             WHERE c.academic_year_id = $ayId
             GROUP BY c.grade
             ORDER BY c.grade"
        )->fetchAll();

        // Staff by department
        $staffChart = $db->query(
            "SELECT d.name, COUNT(s.id) as cnt
             FROM departments d
             LEFT JOIN staff s ON s.department_id = d.id AND s.status = 'active'
             GROUP BY d.id
             ORDER BY cnt DESC
             LIMIT 5"
        )->fetchAll();

        // Recent payments (LEFT JOIN so missing students don't hide rows)
        $recentPayments = $db->query(
            "SELECT p.*, s.first_name, s.last_name, s.student_id
             FROM payments p
             LEFT JOIN students s ON p.student_id = s.id
             ORDER BY p.created_at DESC
             LIMIT 8"
        )->fetchAll();

        // Recent students (JOIN classes for grade/section)
        $recentStudents = $db->query(
            "SELECT s.*, c.grade, c.section
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE c.academic_year_id = $ayId OR s.academic_year_id = $ayId
             ORDER BY s.created_at DESC
             LIMIT 6"
        )->fetchAll();

        // Upcoming exams (next 14 days)
        $upcomingExams = $db->query(
            "SELECT e.*, sub.name as subject_name, c.grade, c.section
             FROM exams e
             LEFT JOIN subjects sub ON e.subject_id = sub.id
             LEFT JOIN classes  c   ON e.class_id   = c.id
             WHERE e.exam_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
             ORDER BY e.exam_date ASC
             LIMIT 6"
        )->fetchAll();

        // Live activity feed
        $activity = $this->buildActivityFeed($db);

        return compact(
            'totalStudents','totalStaff','totalClasses',
            'attRate','attTrend','monthIncome','incomeTrend',
            'pendingFees','openIncidents','overdueBooks',
            'attChart','finChart','gradeChart','staffChart',
            'recentPayments','recentStudents',
            'pendingExams','activity','upcomingExams'
        );
    }

    /* ─── Teacher ───────────────────────────────────────── */
    private function teacherData(PDO $db, int $semId): array {
        $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id = ? LIMIT 1");
        $stfStmt->execute([Auth::id()]);
        $stf     = $stfStmt->fetch();
        $staffId = (int)($stf['id'] ?? 0);

        if ($staffId === 0) {
            return ['myClasses'=>[],'todayTT'=>[],'pendingGrading'=>0,'attToday'=>[],'recentMarks'=>[],'upcomingDue'=>[],'monthAtt'=>[]];
        }

        // My classes with student count
        $myClasses = $db->query(
            "SELECT c.*, COUNT(DISTINCT s.id) as student_count
             FROM class_subjects cs
             JOIN   classes  c ON cs.class_id = c.id
             LEFT JOIN students s ON s.class_id = c.id AND s.status = 'active'
             WHERE cs.teacher_id = $staffId AND cs.semester_id = $semId
             GROUP BY c.id
             ORDER BY c.grade, c.section"
        )->fetchAll();

        // Today's timetable
        $day     = date('l');
        $todayTT = $db->query(
            "SELECT tt.*, sub.name as subject_name, c.grade, c.section
             FROM timetable tt
             JOIN subjects sub ON tt.subject_id = sub.id
             JOIN classes  c   ON tt.class_id   = c.id
             WHERE tt.teacher_id = $staffId AND tt.day = '$day' AND tt.semester_id = $semId
             ORDER BY tt.period"
        )->fetchAll();

        // Pending grading
        $pendingGrading = $this->count($db,
            "SELECT COUNT(*) FROM submissions sub
             JOIN assignments a ON sub.assignment_id = a.id
             WHERE a.teacher_id = $staffId AND sub.status = 'submitted'");

        // Per-class attendance today
        $attToday = [];
        foreach ($myClasses as $cls) {
            $cid = (int)$cls['id'];
            $r   = $db->query("SELECT COUNT(*) as t, COALESCE(SUM(status='present'),0) as p FROM student_attendance WHERE class_id=$cid AND date=CURDATE()")->fetch();
            $attToday[$cid] = ['total' => (int)($r['t'] ?? 0), 'present' => (int)($r['p'] ?? 0)];
        }

        // Recent marks entered by this teacher
        $uid         = (int)Auth::id();
        $recentMarks = $db->query(
            "SELECT m.*, e.title, sub.name as subject_name, s.first_name, s.last_name
             FROM marks m
             JOIN exams   e   ON m.exam_id    = e.id
             JOIN subjects sub ON e.subject_id = sub.id
             JOIN students s   ON m.student_id = s.id
             WHERE m.recorded_by = $uid
             ORDER BY m.updated_at DESC
             LIMIT 5"
        )->fetchAll();

        // Upcoming assignments due
        $upcomingDue = $db->query(
            "SELECT a.*, sub.name as subject_name, c.grade, c.section,
                    COUNT(sub2.id) as submissions
             FROM assignments a
             JOIN subjects sub ON a.subject_id = sub.id
             JOIN classes  c   ON a.class_id   = c.id
             LEFT JOIN submissions sub2 ON sub2.assignment_id = a.id
             WHERE a.teacher_id = $staffId AND a.status = 'active' AND a.due_date >= CURDATE()
             GROUP BY a.id
             ORDER BY a.due_date ASC
             LIMIT 4"
        )->fetchAll();

        // Monthly attendance for my classes (for possible chart)
        $monthAtt = $db->query(
            "SELECT DATE(sa.date) as d, COUNT(*) as t, COALESCE(SUM(sa.status='present'),0) as p
             FROM student_attendance sa
             JOIN class_subjects cs ON sa.class_id = cs.class_id
             WHERE cs.teacher_id = $staffId AND cs.semester_id = $semId
               AND sa.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(sa.date)
             ORDER BY sa.date"
        )->fetchAll();

        return compact('myClasses','todayTT','pendingGrading','attToday','recentMarks','upcomingDue','monthAtt');
    }

    /* ─── Department Head ───────────────────────────────── */
    private function deptHeadData(PDO $db, int $ayId, int $semId): array {
        $stfStmt = $db->prepare("SELECT id, department_id FROM staff WHERE user_id = ? LIMIT 1");
        $stfStmt->execute([Auth::id()]);
        $stf    = $stfStmt->fetch();
        $deptId = (int)($stf['department_id'] ?? 0);

        $deptStaff    = $deptId ? $db->query(
            "SELECT s.*, u.username FROM staff s LEFT JOIN users u ON s.user_id=u.id
             WHERE s.department_id=$deptId AND s.status='active'")->fetchAll() : [];

        $deptSubjects = $deptId ? $db->query(
            "SELECT * FROM subjects WHERE department_id=$deptId ORDER BY grade, name")->fetchAll() : [];

        $pendingExams = $db->query(
            "SELECT er.*, sub.name as subject_name FROM exam_repository er
             LEFT JOIN subjects sub ON er.subject_id = sub.id
             WHERE er.status = 'submitted'
             LIMIT 5"
        )->fetchAll();

        return array_merge(
            $this->teacherData($db, $semId),
            compact('deptStaff','deptSubjects','pendingExams')
        );
    }

    /* ─── Student ───────────────────────────────────────── */
    private function studentData(PDO $db, int $semId): array {
        $stmt = $db->prepare(
            "SELECT s.*, c.grade, c.section, c.stream as class_stream
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.user_id = ? LIMIT 1"
        );
        $stmt->execute([Auth::id()]);
        $student = $stmt->fetch();

        if (!$student) return ['student' => null];

        $stuId = (int)$student['id'];
        $clsId = (int)($student['class_id'] ?? 0);

        // GPA this semester
        $gpaRow = $db->query(
            "SELECT COALESCE(AVG(m.grade_point), 0) as gpa,
                    COUNT(DISTINCT e.subject_id) as subjects
             FROM marks m
             JOIN exams e ON m.exam_id = e.id
             WHERE m.student_id = $stuId AND e.semester_id = $semId"
        )->fetch();
        $gpa = round((float)($gpaRow['gpa'] ?? 0), 2);

        // Class rank
        $rank = 1;
        if ($clsId && $gpa > 0) {
            $rank = $this->count($db,
                "SELECT COUNT(*) + 1
                 FROM students s2
                 WHERE s2.class_id = $clsId AND s2.id != $stuId
                   AND (SELECT COALESCE(AVG(m2.grade_point),0)
                        FROM marks m2 JOIN exams e2 ON m2.exam_id=e2.id
                        WHERE m2.student_id=s2.id AND e2.semester_id=$semId) > $gpa"
            );
        }

        // Monthly attendance
        $attRow = $db->query(
            "SELECT COUNT(*) as total,
                    COALESCE(SUM(status='present'),0) as p,
                    COALESCE(SUM(status='absent'),0)  as a,
                    COALESCE(SUM(status='late'),0)    as l
             FROM student_attendance
             WHERE student_id = $stuId
               AND MONTH(date) = MONTH(CURDATE())
               AND YEAR(date)  = YEAR(CURDATE())"
        )->fetch();
        $attPct = ($attRow['total'] ?? 0) > 0
            ? round(($attRow['p'] / $attRow['total']) * 100)
            : 0;

        // 14-day sparkline
        $attSpark = [];
        for ($i = 13; $i >= 0; $i--) {
            $d  = date('Y-m-d', strtotime("-$i days"));
            $st = $db->query("SELECT status FROM student_attendance WHERE student_id=$stuId AND date='$d'")->fetchColumn();
            $attSpark[] = ['date' => date('d', strtotime($d)), 'status' => $st ?: 'no-data'];
        }

        // Today's timetable
        $day     = date('l');
        $todayTT = $clsId ? $db->query(
            "SELECT tt.*, sub.name as subject_name, st.first_name, st.last_name
             FROM timetable tt
             JOIN subjects sub ON tt.subject_id = sub.id
             JOIN staff    st  ON tt.teacher_id  = st.id
             WHERE tt.class_id = $clsId AND tt.day = '$day' AND tt.semester_id = $semId
             ORDER BY tt.period"
        )->fetchAll() : [];

        // Recent marks
        $recentMarks = $db->query(
            "SELECT m.*, e.title, e.type, e.total_marks, sub.name as subject_name
             FROM marks m
             JOIN exams   e   ON m.exam_id    = e.id
             JOIN subjects sub ON e.subject_id = sub.id
             WHERE m.student_id = $stuId
             ORDER BY m.created_at DESC
             LIMIT 6"
        )->fetchAll();

        // Subject performance
        $subjectPerf = $clsId ? $db->query(
            "SELECT sub.name,
                    COALESCE(AVG(m.marks_obtained / NULLIF(e.total_marks,0) * 100), 0) as avg_pct
             FROM subjects sub
             JOIN class_subjects cs ON cs.subject_id = sub.id AND cs.semester_id = $semId
             LEFT JOIN exams  e  ON e.subject_id = sub.id AND e.class_id = $clsId AND e.semester_id = $semId
             LEFT JOIN marks  m  ON m.exam_id    = e.id   AND m.student_id = $stuId
             WHERE cs.class_id = $clsId
             GROUP BY sub.id
             ORDER BY avg_pct DESC"
        )->fetchAll() : [];

        // Pending assignments
        $pendingAsgn = $clsId ? $db->query(
            "SELECT a.*, sub.name as subject_name
             FROM assignments a
             JOIN subjects sub ON a.subject_id = sub.id
             LEFT JOIN submissions sub2 ON sub2.assignment_id = a.id AND sub2.student_id = $stuId
             WHERE a.class_id = $clsId AND a.status = 'active' AND sub2.id IS NULL
             ORDER BY a.due_date ASC
             LIMIT 4"
        )->fetchAll() : [];

        // Fee status
        $feeRow = $db->query(
            "SELECT COUNT(*) as total,
                    COALESCE(SUM(status='paid'),0) as paid,
                    COALESCE(SUM(status IN ('unpaid','partial')),0) as unpaid,
                    COALESCE(SUM(CASE WHEN status IN ('unpaid','partial') THEN amount ELSE 0 END),0) as amount_due
             FROM student_fees
             WHERE student_id = $stuId"
        )->fetch();

        // Upcoming exams
        $upcomingExams = $clsId ? $db->query(
            "SELECT e.*, sub.name as subject_name
             FROM exams e
             JOIN subjects sub ON e.subject_id = sub.id
             WHERE e.class_id = $clsId
               AND e.exam_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
             ORDER BY e.exam_date ASC
             LIMIT 4"
        )->fetchAll() : [];

        return compact(
            'student','gpa','rank',
            'attRow','attPct','attSpark',
            'todayTT','recentMarks','subjectPerf',
            'pendingAsgn','feeRow','upcomingExams'
        );
    }

    /* ─── Parent ────────────────────────────────────────── */
    private function parentData(PDO $db, int $semId): array {
        $stmt = $db->prepare(
            "SELECT p.*, s.id as stu_id, s.first_name, s.last_name,
                    s.student_id as stud_no, s.class_id, s.photo,
                    c.grade, c.section
             FROM parents p
             JOIN   students s ON p.student_id = s.id
             LEFT JOIN classes c ON s.class_id  = c.id
             WHERE p.user_id = ? LIMIT 1"
        );
        $stmt->execute([Auth::id()]);
        $linked = $stmt->fetch();

        if (!$linked) return ['linked' => null];

        $stuId = (int)$linked['stu_id'];
        $clsId = (int)($linked['class_id'] ?? 0);

        $attRow = $db->query(
            "SELECT COUNT(*) as total,
                    COALESCE(SUM(status='present'),0) as p,
                    COALESCE(SUM(status='absent'),0)  as a
             FROM student_attendance
             WHERE student_id = $stuId
               AND MONTH(date) = MONTH(CURDATE())
               AND YEAR(date)  = YEAR(CURDATE())"
        )->fetch();
        $attPct = ($attRow['total'] ?? 0) > 0 ? round(($attRow['p'] / $attRow['total']) * 100) : 0;

        $gpa = round((float)$db->query(
            "SELECT COALESCE(AVG(m.grade_point),0)
             FROM marks m JOIN exams e ON m.exam_id=e.id
             WHERE m.student_id=$stuId AND e.semester_id=$semId"
        )->fetchColumn(), 2);

        $recentMarks = $db->query(
            "SELECT m.*, e.title, e.type, e.total_marks, sub.name as subject_name
             FROM marks m
             JOIN exams   e   ON m.exam_id    = e.id
             JOIN subjects sub ON e.subject_id = sub.id
             WHERE m.student_id = $stuId
             ORDER BY m.created_at DESC
             LIMIT 5"
        )->fetchAll();

        $pendingFees = $db->query(
            "SELECT sf.*, fc.name as fee_name
             FROM student_fees sf
             JOIN fee_categories fc ON sf.fee_category_id = fc.id
             WHERE sf.student_id = $stuId AND sf.status IN ('unpaid','partial')
             ORDER BY sf.due_date ASC"
        )->fetchAll();

        $upcomingExams = $clsId ? $db->query(
            "SELECT e.*, sub.name as subject_name
             FROM exams e
             JOIN subjects sub ON e.subject_id = sub.id
             WHERE e.class_id = $clsId
               AND e.exam_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
             ORDER BY e.exam_date ASC
             LIMIT 4"
        )->fetchAll() : [];

        return compact('linked','attRow','attPct','gpa','recentMarks','pendingFees','upcomingExams');
    }

    /* ─── Finance ───────────────────────────────────────── */
    private function financeData(PDO $db): array {
        $today      = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $yearStart  = date('Y-01-01');

        $monthIncome    = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date>='$monthStart'");
        $monthExpenses  = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date>='$monthStart' AND status='approved'");
        $yearIncome     = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date>='$yearStart'");
        $pendingFees    = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE status IN ('unpaid','partial')");
        $todayCollected = $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date='$today'");
        $pendingPayroll = $this->count($db, "SELECT COUNT(*) FROM payroll WHERE month=".date('m')." AND year=".date('Y')." AND status='pending'");

        $finChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $ms = date('Y-m-01', strtotime("-$i months"));
            $me = date('Y-m-t', strtotime($ms));
            $finChart[] = [
                'month'    => date('M Y', strtotime($ms)),
                'income'   => $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN '$ms' AND '$me'"),
                'expenses' => $this->sum($db, "SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN '$ms' AND '$me' AND status='approved'"),
            ];
        }

        $recentPayments = $db->query(
            "SELECT p.*, s.first_name, s.last_name, s.student_id
             FROM payments p
             LEFT JOIN students s ON p.student_id = s.id
             ORDER BY p.created_at DESC
             LIMIT 8"
        )->fetchAll();

        $feeByCategory = $db->query(
            "SELECT fc.name, COALESCE(SUM(p.amount),0) as total
             FROM payments p
             LEFT JOIN student_fees sf ON p.student_fee_id = sf.id
             LEFT JOIN fee_categories fc ON sf.fee_category_id = fc.id
             GROUP BY fc.id
             ORDER BY total DESC
             LIMIT 6"
        )->fetchAll();

        return compact(
            'monthIncome','monthExpenses','yearIncome',
            'pendingFees','todayCollected','pendingPayroll',
            'finChart','recentPayments','feeByCategory'
        );
    }

    /* ─── Activity Feed ─────────────────────────────────── */
    private function buildActivityFeed(PDO $db): array {
        $items = [];

        // Recent payments
        $rows = $db->query(
            "SELECT CONCAT(s.first_name,' ',s.last_name) as actor,
                    CONCAT('Paid ETB ',FORMAT(p.amount,0)) as action,
                    p.created_at as ts
             FROM payments p
             LEFT JOIN students s ON p.student_id = s.id
             ORDER BY p.created_at DESC LIMIT 3"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['icon'=>'money-bill','color'=>'success','text'=>($r['actor']??'Student').' — '.($r['action']??''),'time'=>$r['ts']];
        }

        // Recent admissions
        $rows = $db->query(
            "SELECT CONCAT(first_name,' ',last_name) as actor, admission_no, created_at as ts
             FROM students ORDER BY created_at DESC LIMIT 3"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['icon'=>'user-graduate','color'=>'primary','text'=>$r['actor'].' enrolled ('.$r['admission_no'].')','time'=>$r['ts']];
        }

        // Recent marks
        $rows = $db->query(
            "SELECT s.first_name, s.last_name, e.title, m.grade_letter, m.created_at as ts
             FROM marks m
             JOIN students s ON m.student_id = s.id
             JOIN exams    e ON m.exam_id    = e.id
             ORDER BY m.created_at DESC LIMIT 3"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['icon'=>'star','color'=>'warning','text'=>$r['first_name'].' '.$r['last_name'].' scored '.$r['grade_letter'].' in '.$r['title'],'time'=>$r['ts']];
        }

        // Recent exam repo uploads
        $rows = $db->query(
            "SELECT er.title, u.username, er.created_at as ts
             FROM exam_repository er
             LEFT JOIN users u ON er.uploaded_by = u.id
             ORDER BY er.created_at DESC LIMIT 2"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['icon'=>'file-upload','color'=>'info','text'=>($r['username']??'Admin').' uploaded "'.$r['title'].'"','time'=>$r['ts']];
        }

        // Recent announcements
        $rows = $db->query(
            "SELECT a.title, u.username, a.created_at as ts
             FROM announcements a
             LEFT JOIN users u ON a.author_id = u.id
             WHERE a.status = 'active'
             ORDER BY a.created_at DESC LIMIT 2"
        )->fetchAll();
        foreach ($rows as $r) {
            $items[] = ['icon'=>'bullhorn','color'=>'danger','text'=>($r['username']??'Admin').' posted "'.$r['title'].'"','time'=>$r['ts']];
        }

        usort($items, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));
        return array_slice($items, 0, 10);
    }
}
