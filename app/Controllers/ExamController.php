<?php

require_once ROOT . '/app/Core/Controller.php';

class ExamController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);

        $db    = getDB();
        $semId = (int)getSetting('semester_id', 1);
        $ayId  = (int)getSetting('academic_year_id', 1);
        $classId = $this->get('class_id', '');
        $type    = $this->get('type', '');

        $where  = ["e.semester_id = ?"];
        $params = [$semId];

        if ($classId) { $where[] = "e.class_id = ?"; $params[] = $classId; }
        if ($type)    { $where[] = "e.type = ?"; $params[] = $type; }

        $role = Auth::role();
        if ($role === 'teacher') {
            $staffStmt = $db->prepare("SELECT id FROM staff WHERE user_id = ? LIMIT 1");
            $staffStmt->execute([Auth::id()]);
            $staff = $staffStmt->fetch();
            if ($staff) {
                $where[]  = "e.created_by = ?";
                $params[] = Auth::id();
            }
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT e.*, c.grade, c.section, s.name as subject_name, u.username as created_by_name FROM exams e JOIN classes c ON e.class_id = c.id JOIN subjects s ON e.subject_id = s.id JOIN users u ON e.created_by = u.id WHERE $whereStr ORDER BY e.exam_date DESC, e.created_at DESC");
        $stmt->execute($params);

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $this->render('exams/index', [
            'title'   => 'Exams',
            'exams'   => $stmt->fetchAll(),
            'classes' => $classes->fetchAll(),
            'classId' => $classId,
            'type'    => $type,
        ]);
    }

    public function create(): void {
        $this->requireAuth(['super_admin','principal','teacher','registrar']);
        $db  = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId = (int)getSetting('semester_id', 1);

        $classes  = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);
        $sems = $db->query("SELECT * FROM semesters ORDER BY id")->fetchAll();

        $this->render('exams/create', [
            'title'    => 'Create Exam',
            'classes'  => $classes->fetchAll(),
            'semesters'=> $sems,
            'semId'    => $semId,
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal','teacher','registrar']);
        $this->validateCsrf();

        $db = getDB();
        $data = [
            'title'       => $this->post('title', ''),
            'type'        => $this->post('type', 'assignment'),
            'semester_id' => $this->post('semester_id', ''),
            'class_id'    => $this->post('class_id', ''),
            'subject_id'  => $this->post('subject_id', ''),
            'exam_date'   => $this->post('exam_date', ''),
            'total_marks' => $this->post('total_marks', 100),
            'pass_marks'  => $this->post('pass_marks', 50),
            'weight_percent' => $this->post('weight_percent', null),
            'instructions'=> $this->post('instructions', ''),
            'created_by'  => Auth::id(),
        ];

        if (empty($data['title']) || empty($data['class_id']) || empty($data['subject_id'])) {
            Flash::set('error', 'Title, class, and subject are required.');
            $this->redirect('exams/create');
            return;
        }

        try {
            $cols = implode(', ', array_keys($data));
            $ph   = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO exams ($cols) VALUES ($ph)")->execute(array_values($data));
            $examId = $db->lastInsertId();
            Auth::audit('create', 'exams', $examId);
            Flash::set('success', 'Exam created successfully.');
            $this->redirect('exams/marks?exam_id=' . $examId);
        } catch (Exception $e) {
            Flash::set('error', 'Failed to create exam: ' . $e->getMessage());
            $this->redirect('exams/create');
        }
    }

    public function edit(string $id): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $db   = getDB();
        $exam = $this->findExamOrFail($db, (int)$id);
        $ayId = (int)getSetting('academic_year_id', 1);
        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $this->render('exams/edit', [
            'title'  => 'Edit Exam',
            'exam'   => $exam,
            'classes'=> $classes->fetchAll(),
        ]);
    }

    public function update(string $id): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $this->validateCsrf();

        $db   = getDB();
        $exam = $this->findExamOrFail($db, (int)$id);

        $data = [
            'title'        => $this->post('title', $exam['title']),
            'exam_date'    => $this->post('exam_date', ''),
            'total_marks'  => $this->post('total_marks', $exam['total_marks']),
            'pass_marks'   => $this->post('pass_marks', $exam['pass_marks']),
            'instructions' => $this->post('instructions', ''),
        ];

        try {
            $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE exams SET $sets WHERE id = ?")->execute($vals);
            Flash::set('success', 'Exam updated.');
            $this->redirect('exams');
        } catch (Exception $e) {
            Flash::set('error', 'Update failed.');
            $this->redirect('exams/edit/' . $id);
        }
    }

    public function delete(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db = getDB();
        $db->prepare("DELETE FROM exams WHERE id = ?")->execute([$id]);
        Flash::set('success', 'Exam deleted.');
        $this->redirect('exams');
    }

    public function marks(): void {
        $this->requireAuth(['super_admin','principal','teacher','registrar']);

        $db     = getDB();
        $examId = $this->get('exam_id', '');
        $ayId   = (int)getSetting('academic_year_id', 1);
        $semId  = (int)getSetting('semester_id', 1);

        $exams = $db->prepare("SELECT e.*, c.grade, c.section, s.name as subject_name FROM exams e JOIN classes c ON e.class_id = c.id JOIN subjects s ON e.subject_id = s.id WHERE e.semester_id = ? ORDER BY e.exam_date DESC, e.created_at DESC");
        $exams->execute([$semId]);

        $exam = null;
        $students = [];
        if ($examId) {
            $exam = $this->findExamOrFail($db, (int)$examId);

            $stmt = $db->prepare("SELECT s.id, s.first_name, s.last_name, s.student_id, m.marks_obtained, m.grade_letter, m.grade_point, m.remarks FROM students s LEFT JOIN marks m ON m.exam_id = ? AND m.student_id = s.id WHERE s.class_id = ? AND s.status = 'active' ORDER BY s.first_name");
            $stmt->execute([$examId, $exam['class_id']]);
            $students = $stmt->fetchAll();
        }

        $this->render('exams/marks', [
            'title'    => 'Enter Marks',
            'exams'    => $exams->fetchAll(),
            'exam'     => $exam,
            'examId'   => $examId,
            'students' => $students,
        ]);
    }

    public function saveMarks(): void {
        $this->requireAuth(['super_admin','principal','teacher','registrar']);
        $this->validateCsrf();

        $db     = getDB();
        $examId = $this->post('exam_id', '');
        $marksArr = $_POST['marks'] ?? [];

        if (!$examId) {
            Flash::set('error', 'No exam selected.');
            $this->redirect('exams/marks');
            return;
        }

        $exam = $this->findExamOrFail($db, (int)$examId);

        try {
            $db->beginTransaction();

            $upsert = $db->prepare("INSERT INTO marks (exam_id, student_id, marks_obtained, grade_letter, grade_point, remarks, recorded_by) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE marks_obtained=VALUES(marks_obtained), grade_letter=VALUES(grade_letter), grade_point=VALUES(grade_point), remarks=VALUES(remarks), recorded_by=VALUES(recorded_by)");

            foreach ($marksArr as $stuId => $marksObtained) {
                $pct   = $exam['total_marks'] > 0 ? ((float)$marksObtained / $exam['total_marks']) * 100 : 0;
                $grade = calcGrade($pct);
                $upsert->execute([
                    $examId,
                    $stuId,
                    (float)$marksObtained,
                    $grade['letter'],
                    $grade['point'],
                    $_POST['remarks'][$stuId] ?? null,
                    Auth::id(),
                ]);
            }

            $db->commit();
            Auth::audit('save_marks', 'exams', (int)$examId);
            Flash::set('success', 'Marks saved successfully for ' . count($marksArr) . ' students.');
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed to save marks: ' . $e->getMessage());
        }

        $this->redirect('exams/marks?exam_id=' . $examId);
    }

    public function reportCards(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher']);

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId= (int)getSetting('semester_id', 1);
        $classId = $this->get('class_id', '');

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $students = [];
        if ($classId) {
            $stmt = $db->prepare("SELECT s.*, GROUP_CONCAT(DISTINCT CONCAT(sub.name,'|',COALESCE(m.marks_obtained,0),'|',COALESCE(e.total_marks,100)) ORDER BY sub.name SEPARATOR ';;') as grades_raw FROM students s LEFT JOIN marks m ON m.student_id = s.id LEFT JOIN exams e ON m.exam_id = e.id AND e.semester_id = ? LEFT JOIN subjects sub ON e.subject_id = sub.id WHERE s.class_id = ? AND s.status = 'active' GROUP BY s.id ORDER BY s.first_name");
            $stmt->execute([$semId, $classId]);
            $students = $stmt->fetchAll();

            // Calculate GPA for each student
            foreach ($students as &$stu) {
                $gpaStmt = $db->prepare("SELECT AVG(m.grade_point) as gpa, COUNT(DISTINCT e.subject_id) as subjects FROM marks m JOIN exams e ON m.exam_id = e.id WHERE m.student_id = ? AND e.semester_id = ?");
                $gpaStmt->execute([$stu['id'], $semId]);
                $gpaData   = $gpaStmt->fetch();
                $stu['gpa'] = round($gpaData['gpa'] ?? 0, 2);
            }

            // Rank students
            usort($students, fn($a, $b) => $b['gpa'] <=> $a['gpa']);
            foreach ($students as $i => &$stu) {
                $stu['rank'] = $i + 1;
            }
        }

        $this->render('exams/report-cards', [
            'title'    => 'Report Cards',
            'classes'  => $classes->fetchAll(),
            'students' => $students,
            'classId'  => $classId,
            'semId'    => $semId,
        ]);
    }

    public function viewReportCard(string $id): void {
        $this->requireAuth();
        $db    = getDB();
        $semId = (int)getSetting('semester_id', 1);
        $ayId  = (int)getSetting('academic_year_id', 1);

        $stuStmt = $db->prepare("SELECT s.*, c.grade, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?");
        $stuStmt->execute([$id]);
        $student = $stuStmt->fetch();
        if (!$student) { Flash::set('error', 'Student not found.'); $this->redirect('exams/report-cards'); return; }

        $subjectsStmt = $db->prepare("SELECT sub.name as subject, sub.credit_hours, SUM(CASE WHEN e.type='assignment' THEN m.marks_obtained ELSE 0 END) as assignments, SUM(CASE WHEN e.type='quiz' THEN m.marks_obtained ELSE 0 END) as quizzes, SUM(CASE WHEN e.type='mid_exam' THEN m.marks_obtained ELSE 0 END) as mid, SUM(CASE WHEN e.type='final_exam' THEN m.marks_obtained ELSE 0 END) as final, AVG(m.grade_point) as gpa, MAX(m.grade_letter) as grade FROM marks m JOIN exams e ON m.exam_id = e.id JOIN subjects sub ON e.subject_id = sub.id WHERE m.student_id = ? AND e.semester_id = ? GROUP BY sub.id ORDER BY sub.name");
        $subjectsStmt->execute([$id, $semId]);
        $subjects = $subjectsStmt->fetchAll();

        $gpaStmt = $db->prepare("SELECT AVG(m.grade_point) as gpa FROM marks m JOIN exams e ON m.exam_id = e.id WHERE m.student_id = ? AND e.semester_id = ?");
        $gpaStmt->execute([$id, $semId]);
        $overallGpa = round($gpaStmt->fetchColumn() ?? 0, 2);

        $attStmt = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent FROM student_attendance WHERE student_id = ?");
        $attStmt->execute([$id]);
        $attendance = $attStmt->fetch();

        // Rank
        $rankStmt = $db->prepare("SELECT COUNT(*) + 1 as rank FROM students s WHERE s.class_id = ? AND s.id != ? AND (SELECT AVG(m2.grade_point) FROM marks m2 JOIN exams e2 ON m2.exam_id = e2.id WHERE m2.student_id = s.id AND e2.semester_id = ?) > ?");
        $rankStmt->execute([$student['class_id'], $id, $semId, $overallGpa]);
        $rank = (int)$rankStmt->fetchColumn();

        $ay = $db->prepare("SELECT * FROM academic_years WHERE id = ?");
        $ay->execute([$ayId]);
        $semStmt = $db->prepare("SELECT * FROM semesters WHERE id = ?");
        $semStmt->execute([$semId]);

        $this->render('exams/report-card', [
            'title'      => 'Report Card - ' . $student['first_name'],
            'student'    => $student,
            'subjects'   => $subjects,
            'overall_gpa'=> $overallGpa,
            'attendance' => $attendance,
            'rank'       => $rank,
            'ay'         => $ay->fetch(),
            'semester'   => $semStmt->fetch(),
        ], 'print');
    }

    public function gpa(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar']);

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId= (int)getSetting('semester_id', 1);
        $classId = $this->get('class_id', '');

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $gpaData = [];
        if ($classId) {
            $stmt = $db->prepare("SELECT s.id, s.first_name, s.last_name, s.student_id, AVG(m.grade_point) as gpa, COUNT(DISTINCT e.subject_id) as subjects FROM students s LEFT JOIN marks m ON m.student_id = s.id LEFT JOIN exams e ON m.exam_id = e.id AND e.semester_id = ? WHERE s.class_id = ? AND s.status = 'active' GROUP BY s.id ORDER BY gpa DESC");
            $stmt->execute([$semId, $classId]);
            $gpaData = $stmt->fetchAll();
            foreach ($gpaData as $i => &$g) {
                $g['rank'] = $i + 1;
                $g['gpa']  = round($g['gpa'] ?? 0, 2);
            }
        }

        $this->render('exams/gpa', [
            'title'   => 'GPA Calculator',
            'classes' => $classes->fetchAll(),
            'classId' => $classId,
            'gpaData' => $gpaData,
        ]);
    }

    private function findExamOrFail(PDO $db, int $id): array {
        $stmt = $db->prepare("SELECT e.*, c.grade, c.section, s.name as subject_name FROM exams e JOIN classes c ON e.class_id = c.id JOIN subjects s ON e.subject_id = s.id WHERE e.id = ?");
        $stmt->execute([$id]);
        $exam = $stmt->fetch();
        if (!$exam) {
            Flash::set('error', 'Exam not found.');
            $this->redirect('exams');
            exit;
        }
        return $exam;
    }
}
