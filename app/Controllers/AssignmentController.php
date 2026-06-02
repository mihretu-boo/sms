<?php

require_once ROOT . '/app/Core/Controller.php';

class AssignmentController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $db    = getDB();
        $role  = Auth::role();
        $semId = (int)getSetting('semester_id', 1);

        if ($role === 'student') {
            $stuStmt = $db->prepare("SELECT class_id, id FROM students WHERE user_id=? LIMIT 1");
            $stuStmt->execute([Auth::id()]);
            $stu = $stuStmt->fetch();
            $stmt = $db->prepare("SELECT a.*, s.name as subject, st.first_name, st.last_name, sub.id as submission_id, sub.status as sub_status, sub.marks FROM assignments a JOIN subjects s ON a.subject_id=s.id JOIN staff st ON a.teacher_id=st.id LEFT JOIN submissions sub ON sub.assignment_id=a.id AND sub.student_id=? WHERE a.class_id=? AND a.semester_id=? ORDER BY a.due_date ASC");
            $stmt->execute([$stu['id'] ?? 0, $stu['class_id'] ?? 0, $semId]);
        } elseif ($role === 'teacher') {
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id=? LIMIT 1");
            $stfStmt->execute([Auth::id()]);
            $stf = $stfStmt->fetch();
            $stmt = $db->prepare("SELECT a.*, s.name as subject, c.grade, c.section, COUNT(sub.id) as submission_count FROM assignments a JOIN subjects s ON a.subject_id=s.id JOIN classes c ON a.class_id=c.id LEFT JOIN submissions sub ON sub.assignment_id=a.id WHERE a.teacher_id=? AND a.semester_id=? GROUP BY a.id ORDER BY a.created_at DESC");
            $stmt->execute([$stf['id'] ?? 0, $semId]);
        } else {
            $stmt = $db->prepare("SELECT a.*, s.name as subject, c.grade, c.section, st.first_name, st.last_name FROM assignments a JOIN subjects s ON a.subject_id=s.id JOIN classes c ON a.class_id=c.id JOIN staff st ON a.teacher_id=st.id WHERE a.semester_id=? ORDER BY a.created_at DESC");
            $stmt->execute([$semId]);
        }

        $this->render('assignments/index', [
            'title'       => 'Assignments',
            'assignments' => $stmt->fetchAll(),
        ]);
    }

    public function create(): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId= (int)getSetting('semester_id', 1);

        $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id=? LIMIT 1");
        $stfStmt->execute([Auth::id()]);
        $stf = $stfStmt->fetch();
        $staffId = $stf ? $stf['id'] : null;

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id=? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $this->render('assignments/create', [
            'title'   => 'Create Assignment',
            'classes' => $classes->fetchAll(),
            'staffId' => $staffId,
            'semId'   => $semId,
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $this->validateCsrf();

        $db   = getDB();
        $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id=? LIMIT 1");
        $stfStmt->execute([Auth::id()]);
        $stf = $stfStmt->fetch();

        $data = [
            'title'      => $this->post('title', ''),
            'description'=> $this->post('description', ''),
            'class_id'   => $this->post('class_id', ''),
            'subject_id' => $this->post('subject_id', ''),
            'teacher_id' => $stf ? $stf['id'] : Auth::id(),
            'due_date'   => $this->post('due_date', ''),
            'max_marks'  => $this->post('max_marks', 100),
            'semester_id'=> $this->post('semester_id', (int)getSetting('semester_id', 1)),
            'status'     => 'active',
        ];

        if (!empty($_FILES['file']['name'])) {
            $file = $this->uploadFile('file', 'assignments', array_merge(ALLOWED_DOC_TYPES, ALLOWED_IMAGE_TYPES));
            if ($file) $data['file_path'] = $file;
        }

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO assignments ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Assignment created.');
            $this->redirect('assignments');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
            $this->redirect('assignments/create');
        }
    }

    public function view(string $id): void {
        $this->requireAuth();
        $db   = getDB();
        $stmt = $db->prepare("SELECT a.*, s.name as subject, c.grade, c.section, st.first_name, st.last_name FROM assignments a JOIN subjects s ON a.subject_id=s.id JOIN classes c ON a.class_id=c.id JOIN staff st ON a.teacher_id=st.id WHERE a.id=?");
        $stmt->execute([$id]);
        $assignment = $stmt->fetch();
        if (!$assignment) { Flash::set('error', 'Not found.'); $this->redirect('assignments'); return; }

        $submissions = $db->prepare("SELECT sub.*, s.first_name, s.last_name, s.student_id FROM submissions sub JOIN students s ON sub.student_id=s.id WHERE sub.assignment_id=? ORDER BY sub.submitted_at");
        $submissions->execute([$id]);

        $mySubmission = null;
        if (Auth::role() === 'student') {
            $stuStmt = $db->prepare("SELECT id FROM students WHERE user_id=?");
            $stuStmt->execute([Auth::id()]);
            $stu = $stuStmt->fetch();
            if ($stu) {
                $subStmt = $db->prepare("SELECT * FROM submissions WHERE assignment_id=? AND student_id=?");
                $subStmt->execute([$id, $stu['id']]);
                $mySubmission = $subStmt->fetch();
            }
        }

        $this->render('assignments/view', [
            'title'        => $assignment['title'],
            'assignment'   => $assignment,
            'submissions'  => $submissions->fetchAll(),
            'my_submission'=> $mySubmission,
        ]);
    }

    public function submit(string $id): void {
        $this->requireAuth(['student']);
        $this->validateCsrf();

        $db  = getDB();
        $stuStmt = $db->prepare("SELECT id FROM students WHERE user_id=?");
        $stuStmt->execute([Auth::id()]);
        $stu = $stuStmt->fetch();
        if (!$stu) { Flash::set('error', 'Student record not found.'); $this->redirect('assignments'); return; }

        $data = [
            'assignment_id' => $id,
            'student_id'    => $stu['id'],
            'text_content'  => $this->post('text_content', ''),
            'status'        => strtotime('now') > strtotime($this->post('due_date','')) ? 'late' : 'submitted',
        ];

        if (!empty($_FILES['file']['name'])) {
            $file = $this->uploadFile('file', 'submissions', array_merge(ALLOWED_DOC_TYPES, ALLOWED_IMAGE_TYPES));
            if ($file) $data['file_path'] = $file;
        }

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO submissions ($cols) VALUES ($ph) ON DUPLICATE KEY UPDATE text_content=VALUES(text_content), file_path=COALESCE(VALUES(file_path),file_path), submitted_at=NOW()")->execute(array_values($data));
            Flash::set('success', 'Assignment submitted.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('assignments/view/' . $id);
    }

    public function grade(string $id): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $db   = getDB();
        $stmt = $db->prepare("SELECT sub.*, s.first_name, s.last_name, s.student_id, a.title, a.max_marks FROM submissions sub JOIN students s ON sub.student_id=s.id JOIN assignments a ON sub.assignment_id=a.id WHERE sub.id=?");
        $stmt->execute([$id]);
        $submission = $stmt->fetch();
        if (!$submission) { Flash::set('error', 'Not found.'); $this->redirect('assignments'); return; }
        $this->render('assignments/grade', ['title' => 'Grade Submission', 'submission' => $submission]);
    }

    public function saveGrade(string $id): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $this->validateCsrf();

        $db = getDB();
        $db->prepare("UPDATE submissions SET marks=?, feedback=?, status='graded' WHERE id=?")->execute([$this->post('marks', 0), $this->post('feedback', ''), $id]);
        Flash::set('success', 'Grade saved.');
        $this->redirectBack();
    }

    public function materials(): void {
        $this->requireAuth();
        $db    = getDB();
        $semId = (int)getSetting('semester_id', 1);
        $role  = Auth::role();

        if ($role === 'student') {
            $stuStmt = $db->prepare("SELECT class_id FROM students WHERE user_id=? LIMIT 1");
            $stuStmt->execute([Auth::id()]);
            $stu = $stuStmt->fetch();
            $stmt = $db->prepare("SELECT m.*, s.name as subject, st.first_name, st.last_name FROM materials m JOIN subjects s ON m.subject_id=s.id JOIN staff st ON m.teacher_id=st.id WHERE m.class_id=? AND m.semester_id=? ORDER BY m.created_at DESC");
            $stmt->execute([$stu['class_id'] ?? 0, $semId]);
        } elseif ($role === 'teacher') {
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id=? LIMIT 1");
            $stfStmt->execute([Auth::id()]);
            $stf = $stfStmt->fetch();
            $stmt = $db->prepare("SELECT m.*, s.name as subject, c.grade, c.section FROM materials m JOIN subjects s ON m.subject_id=s.id JOIN classes c ON m.class_id=c.id WHERE m.teacher_id=? AND m.semester_id=? ORDER BY m.created_at DESC");
            $stmt->execute([$stf['id'] ?? 0, $semId]);
        } else {
            $stmt = $db->prepare("SELECT m.*, s.name as subject, c.grade, c.section FROM materials m JOIN subjects s ON m.subject_id=s.id JOIN classes c ON m.class_id=c.id WHERE m.semester_id=? ORDER BY m.created_at DESC");
            $stmt->execute([$semId]);
        }

        $this->render('assignments/materials', ['title' => 'Learning Materials', 'materials' => $stmt->fetchAll()]);
    }

    public function uploadMaterial(): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $this->validateCsrf();

        $db  = getDB();
        $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id=? LIMIT 1");
        $stfStmt->execute([Auth::id()]);
        $stf = $stfStmt->fetch();

        $data = [
            'title'       => $this->post('title', ''),
            'description' => $this->post('description', ''),
            'class_id'    => $this->post('class_id', ''),
            'subject_id'  => $this->post('subject_id', ''),
            'teacher_id'  => $stf ? $stf['id'] : Auth::id(),
            'file_type'   => $this->post('file_type', 'pdf'),
            'external_url'=> $this->post('external_url', ''),
            'semester_id' => (int)getSetting('semester_id', 1),
        ];

        if (!empty($_FILES['file']['name'])) {
            $file = $this->uploadFile('file', 'materials', array_merge(ALLOWED_DOC_TYPES, ALLOWED_IMAGE_TYPES));
            if ($file) $data['file_path'] = $file;
        }

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO materials ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Material uploaded.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('materials');
    }
}
