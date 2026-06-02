<?php

require_once ROOT . '/app/Core/Controller.php';

class AcademicController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head']);
        $this->redirect('academics/classes');
    }

    public function years(): void {
        $this->requireAuth(['super_admin','principal']);
        $db   = getDB();
        $stmt = $db->query("SELECT ay.*, COUNT(c.id) as class_count, (SELECT COUNT(*) FROM students s WHERE s.academic_year_id = ay.id) as student_count FROM academic_years ay LEFT JOIN classes c ON c.academic_year_id = ay.id GROUP BY ay.id ORDER BY ay.start_date DESC");
        $this->render('academics/years', ['title' => 'Academic Years', 'years' => $stmt->fetchAll()]);
    }

    public function saveYear(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $data = [
            'name'       => $this->post('name', ''),
            'start_date' => $this->post('start_date', ''),
            'end_date'   => $this->post('end_date', ''),
            'status'     => $this->post('status', 'inactive'),
        ];

        // If setting active, deactivate others
        if ($data['status'] === 'active') {
            $db->query("UPDATE academic_years SET status = 'inactive'");
        }

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE academic_years SET $sets WHERE id=?")->execute($vals);
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO academic_years ($cols) VALUES ($ph)")->execute(array_values($data));
                $yearId = $db->lastInsertId();
                // Create 2 semesters for this year
                $db->prepare("INSERT INTO semesters (academic_year_id, name, start_date, end_date, status) VALUES (?,?,?,?,?),(?,?,?,?,?)")->execute([
                    $yearId, 'Semester 1', $data['start_date'], date('Y-01-31', strtotime($data['end_date'])), 'active',
                    $yearId, 'Semester 2', date('Y-02-01', strtotime($data['end_date'])), $data['end_date'], 'inactive',
                ]);
            }
            Flash::set('success', 'Academic year saved.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('academics/years');
    }

    public function classes(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        $stmt = $db->prepare("SELECT c.*, COUNT(s.id) as student_count, st.first_name as teacher_first, st.last_name as teacher_last FROM classes c LEFT JOIN students s ON s.class_id = c.id AND s.status='active' LEFT JOIN staff st ON c.class_teacher_id = st.id WHERE c.academic_year_id = ? GROUP BY c.id ORDER BY c.grade, c.section");
        $stmt->execute([$ayId]);

        $teachers = $db->query("SELECT s.id, s.first_name, s.last_name FROM staff s WHERE s.status='active' AND EXISTS(SELECT 1 FROM users u WHERE u.id=s.user_id AND u.role IN ('teacher','dept_head')) ORDER BY s.first_name")->fetchAll();

        $this->render('academics/classes', [
            'title'    => 'Classes',
            'classes'  => $stmt->fetchAll(),
            'teachers' => $teachers,
            'ayId'     => $ayId,
        ]);
    }

    public function saveClass(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $ayId = (int)getSetting('academic_year_id', 1);

        $data = [
            'grade'           => $this->post('grade', '9'),
            'section'         => $this->post('section', 'A'),
            'class_teacher_id'=> $this->post('class_teacher_id', '') ?: null,
            'room_no'         => $this->post('room_no', ''),
            'max_students'    => $this->post('max_students', 50),
            'stream'          => $this->post('stream', 'general'),
            'academic_year_id'=> $ayId,
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE classes SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Class updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO classes ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Class added.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('academics/classes');
    }

    public function deleteClass(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();
        $db = getDB();
        try {
            $db->prepare("DELETE FROM classes WHERE id=?")->execute([$id]);
            Flash::set('success', 'Class deleted.');
        } catch (Exception $e) {
            Flash::set('error', 'Cannot delete class with students.');
        }
        $this->redirect('academics/classes');
    }

    public function subjects(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head']);
        $db    = getDB();
        $grade = $this->get('grade', '');
        $deptId = $this->get('dept_id', '');

        $where  = ['1=1'];
        $params = [];
        if ($grade)  { $where[] = "(s.grade = ? OR s.grade = 'all')"; $params[] = $grade; }
        if ($deptId) { $where[] = "s.department_id = ?"; $params[] = $deptId; }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT s.*, d.name as dept_name FROM subjects s LEFT JOIN departments d ON s.department_id = d.id WHERE $whereStr ORDER BY s.grade, s.name");
        $stmt->execute($params);

        $depts = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();

        $this->render('academics/subjects', [
            'title'    => 'Subjects',
            'subjects' => $stmt->fetchAll(),
            'depts'    => $depts,
            'grade'    => $grade,
            'deptId'   => $deptId,
        ]);
    }

    public function saveSubject(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $data = [
            'code'          => $this->post('code', ''),
            'name'          => $this->post('name', ''),
            'department_id' => $this->post('department_id', '') ?: null,
            'grade'         => $this->post('grade', '9'),
            'credit_hours'  => $this->post('credit_hours', 3),
            'type'          => $this->post('type', 'core'),
            'description'   => $this->post('description', ''),
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE subjects SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Subject updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO subjects ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Subject added.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('academics/subjects');
    }

    public function deleteSubject(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM subjects WHERE id=?")->execute([$id]);
        Flash::set('success', 'Subject deleted.');
        $this->redirect('academics/subjects');
    }

    public function departments(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','dept_head']);
        $db   = getDB();
        $stmt = $db->query("SELECT d.*, COUNT(s.id) as staff_count, h.first_name as head_first, h.last_name as head_last FROM departments d LEFT JOIN staff s ON s.department_id = d.id AND s.status='active' LEFT JOIN staff h ON d.head_id = h.id GROUP BY d.id ORDER BY d.name");
        $teachers = $db->query("SELECT id, first_name, last_name FROM staff WHERE status='active' ORDER BY first_name")->fetchAll();
        $this->render('academics/departments', ['title' => 'Departments', 'departments' => $stmt->fetchAll(), 'teachers' => $teachers]);
    }

    public function saveDepartment(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $data = [
            'name'        => $this->post('name', ''),
            'code'        => $this->post('code', ''),
            'head_id'     => $this->post('head_id', '') ?: null,
            'description' => $this->post('description', ''),
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE departments SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Department updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO departments ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Department added.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('academics/departments');
    }

    public function assignSubjects(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId= (int)getSetting('semester_id', 1);
        $classId = $this->get('class_id', '');

        $classes  = $db->prepare("SELECT * FROM classes WHERE academic_year_id=? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $assigned = [];
        $subjects = [];
        if ($classId) {
            $cls = $db->prepare("SELECT grade FROM classes WHERE id=?");
            $cls->execute([$classId]);
            $cls = $cls->fetch();
            $grade = $cls['grade'] ?? '9';

            $subStmt = $db->prepare("SELECT * FROM subjects WHERE grade=? OR grade='all' ORDER BY name");
            $subStmt->execute([$grade]);
            $subjects = $subStmt->fetchAll();

            $asgStmt = $db->prepare("SELECT cs.*, s.first_name as teacher_first, s.last_name as teacher_last FROM class_subjects cs LEFT JOIN staff s ON cs.teacher_id = s.id WHERE cs.class_id=? AND cs.semester_id=?");
            $asgStmt->execute([$classId, $semId]);
            foreach ($asgStmt->fetchAll() as $row) {
                $assigned[$row['subject_id']] = $row;
            }
        }

        $teachers = $db->query("SELECT s.id, s.first_name, s.last_name FROM staff s WHERE s.status='active' ORDER BY s.first_name")->fetchAll();

        $this->render('academics/assign-subjects', [
            'title'    => 'Assign Subjects',
            'classes'  => $classes->fetchAll(),
            'subjects' => $subjects,
            'assigned' => $assigned,
            'teachers' => $teachers,
            'classId'  => $classId,
            'semId'    => $semId,
        ]);
    }

    public function saveAssignments(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db      = getDB();
        $classId = $this->post('class_id', '');
        $semId   = $this->post('semester_id', (int)getSetting('semester_id', 1));
        $subjects = $_POST['subject_ids'] ?? [];
        $teachers = $_POST['teacher_id'] ?? [];

        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM class_subjects WHERE class_id=? AND semester_id=?")->execute([$classId, $semId]);
            $stmt = $db->prepare("INSERT INTO class_subjects (class_id, subject_id, teacher_id, semester_id, periods_per_week) VALUES (?,?,?,?,?)");
            foreach ($subjects as $subId) {
                $stmt->execute([$classId, $subId, $teachers[$subId] ?? null, $semId, $_POST['periods'][$subId] ?? 3]);
            }
            $db->commit();
            Flash::set('success', 'Subject assignments saved.');
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('academics/assign-subjects?class_id=' . $classId);
    }
}
