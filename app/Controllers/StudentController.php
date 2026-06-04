<?php

require_once ROOT . '/app/Core/Controller.php';

class StudentController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);

        $db     = getDB();
        $ayId   = (int)getSetting('academic_year_id', 1);
        $search = $this->get('search', '');
        $classId= $this->get('class_id', '');
        $status = $this->get('status', 'active');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ["s.academic_year_id = ?"];
        $params = [$ayId];

        if ($search) {
            $where[]  = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.admission_no LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like, $like);
        }
        if ($classId) {
            $where[]  = "s.class_id = ?";
            $params[] = $classId;
        }
        if ($status) {
            $where[]  = "s.status = ?";
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM students s WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit;
        $params[] = $offset;
        $stmt = $db->prepare("SELECT s.*, c.grade, c.section, CONCAT(c.grade,'-',c.section) as class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE $whereStr ORDER BY s.first_name ASC LIMIT ? OFFSET ?");
        $stmt->execute($params);
        $students = $stmt->fetchAll();

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $this->render('students/index', [
            'title'    => 'Students',
            'students' => $students,
            'classes'  => $classes->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'pages'    => ceil($total / $limit),
            'search'   => $search,
            'classId'  => $classId,
            'status'   => $status,
        ]);
    }

    public function create(): void {
        $this->requireAuth(['super_admin','principal','registrar']);

        $db = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $this->render('students/create', [
            'title'   => 'Add Student',
            'classes' => $classes->fetchAll(),
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        $classId     = $this->post('class_id', '');
        $classStream = 'general';
        if ($classId) {
            // Inherit stream from class for Grade 11/12
            $clsQ = getDB()->prepare("SELECT grade, stream FROM classes WHERE id=?");
            $clsQ->execute([$classId]);
            $clsData = $clsQ->fetch();
            if ($clsData && in_array($clsData['grade'], ['11','12'])) {
                $classStream = $this->post('stream', $clsData['stream']);
            }
        }

        $data = [
            'first_name'             => $this->post('first_name', ''),
            'last_name'              => $this->post('last_name', ''),
            'gender'                 => $this->post('gender', 'male'),
            'dob'                    => $this->post('dob', ''),
            'blood_type'             => $this->post('blood_type', ''),
            'nationality'            => $this->post('nationality', 'Ethiopian'),
            'religion'               => $this->post('religion', ''),
            'class_id'               => $classId,
            'stream'                 => $classStream,
            'admission_date'         => $this->post('admission_date', date('Y-m-d')),
            'status'                 => 'active',
            'address'                => $this->post('address', ''),
            'city'                   => $this->post('city', ''),
            'phone'                  => $this->post('phone', ''),
            'email'                  => $this->post('email', ''),
            'previous_school'        => $this->post('previous_school', ''),
            'medical_info'           => $this->post('medical_info', ''),
            'emergency_contact_name' => $this->post('emergency_contact_name', ''),
            'emergency_contact_phone'=> $this->post('emergency_contact_phone', ''),
        ];

        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['dob'])) {
            Flash::set('error', 'First name, last name, and date of birth are required.');
            setOld($data);
            $this->redirect('students/create');
            return;
        }

        // Generate student ID and admission number
        $seqStmt = $db->query("SELECT COUNT(*) + 1 FROM students");
        $seq     = (int)$seqStmt->fetchColumn();
        $stuId   = generateStudentId(date('Y'), $seq);
        $admNo   = 'ADM-' . date('Y') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

        $data['student_id']       = $stuId;
        $data['admission_no']     = $admNo;
        $data['academic_year_id'] = $ayId;

        // Handle photo upload
        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->uploadFile('photo', 'students', ALLOWED_IMAGE_TYPES);
            if ($photo) $data['photo'] = $photo;
        }

        // ── Student login credentials ──────────────────────────────
        $stuUsername = strtolower(
            preg_replace('/[^a-z0-9]/', '', $data['first_name']) . '.' .
            preg_replace('/[^a-z0-9]/', '', $data['last_name'])  . rand(100, 999)
        );
        // Make username unique
        while ($db->prepare("SELECT 1 FROM users WHERE username=?")->execute([$stuUsername]) &&
               $db->query("SELECT COUNT(*) FROM users WHERE username='$stuUsername'")->fetchColumn() > 0) {
            $stuUsername = strtolower(
                preg_replace('/[^a-z0-9]/', '', $data['first_name']) . '.' .
                preg_replace('/[^a-z0-9]/', '', $data['last_name'])  . rand(100, 999)
            );
        }
        $stuEmail    = $data['email'] ?: ($stuUsername . '@sjassms.edu.et');
        $stuPassword = 'Student@123';

        // ── Parent info ─────────────────────────────────────────────
        $parentFirst = trim($this->post('parent_first_name', ''));
        $parentLast  = trim($this->post('parent_last_name', ''));
        $parentPhone = trim($this->post('parent_phone', ''));
        $parentEmail = trim($this->post('parent_email', ''));
        $parentRel   = $this->post('parent_relation', 'father');
        $parentOccup = $this->post('parent_occupation', '');
        $hasParent   = !empty($parentFirst) && !empty($parentPhone);

        // ── Parent login credentials ────────────────────────────────
        $parUsername    = null;
        $parPassword    = null;
        $parUserId      = null;
        $parAccountNew  = false;

        if ($hasParent) {
            // If parent already has a user account (sibling scenario), reuse it
            if ($parentEmail) {
                $existStmt = $db->prepare("SELECT id, username FROM users WHERE email=? AND role='parent' LIMIT 1");
                $existStmt->execute([$parentEmail]);
                $existUser = $existStmt->fetch();
                if ($existUser) {
                    $parUserId   = $existUser['id'];
                    $parUsername = $existUser['username'];
                    $parPassword = '(existing account)';
                }
            }

            // No existing account → create one
            if (!$parUserId) {
                $parPassword = 'Parent@123';
                $parUsername = strtolower(
                    preg_replace('/[^a-z0-9]/', '', $parentFirst) . '.' .
                    preg_replace('/[^a-z0-9]/', '', $parentLast ?: $data['last_name']) . rand(100, 999)
                );
                // Ensure unique username
                while ($db->query("SELECT COUNT(*) FROM users WHERE username='$parUsername'")->fetchColumn() > 0) {
                    $parUsername = strtolower(
                        preg_replace('/[^a-z0-9]/', '', $parentFirst) . '.' .
                        preg_replace('/[^a-z0-9]/', '', $parentLast ?: $data['last_name']) . rand(100, 999)
                    );
                }
                // Use parent email or generate one
                $parEmail = $parentEmail ?: ($parUsername . '@sjassms.edu.et');
                // Ensure email is unique
                $emailChk = $db->prepare("SELECT COUNT(*) FROM users WHERE email=?");
                $emailChk->execute([$parEmail]);
                if ($emailChk->fetchColumn() > 0) {
                    $parEmail = $parUsername . rand(10,99) . '@sjassms.edu.et';
                }
                $parAccountNew = true;
            }
        }

        try {
            $db->beginTransaction();

            // ── 1. Create student user account ──────────────────────
            $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,'student')")
               ->execute([$stuUsername, $stuEmail, password_hash($stuPassword, PASSWORD_BCRYPT)]);
            $stuUserId       = $db->lastInsertId();
            $data['user_id'] = $stuUserId;

            // ── 2. Insert student ────────────────────────────────────
            $cols         = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO students ($cols) VALUES ($placeholders)")->execute(array_values($data));
            $studentId = $db->lastInsertId();

            // ── 3. Create / reuse parent user account ───────────────
            if ($hasParent) {
                if ($parAccountNew) {
                    $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,'parent')")
                       ->execute([$parUsername, $parEmail, password_hash($parPassword, PASSWORD_BCRYPT)]);
                    $parUserId = $db->lastInsertId();
                }

                // ── 4. Insert parent record linked to user + student ─
                $db->prepare(
                    "INSERT INTO parents
                     (user_id, student_id, relation, first_name, last_name, phone, email, occupation, is_primary)
                     VALUES (?,?,?,?,?,?,?,?,1)"
                )->execute([
                    $parUserId,
                    $studentId,
                    $parentRel,
                    $parentFirst,
                    $parentLast,
                    $parentPhone,
                    $parentEmail,
                    $parentOccup,
                ]);
            }

            $db->commit();
            Auth::audit('create', 'students', $studentId);

            // ── Build success message with both accounts ─────────────
            $msg  = "Student <strong>{$data['first_name']} {$data['last_name']}</strong> registered.";
            $msg .= "<br><i class='fas fa-user-graduate me-1'></i> Student login: <code>$stuUsername</code> / <code>$stuPassword</code>";
            if ($hasParent && $parUsername) {
                $accountTag = $parAccountNew ? '(new account created)' : '(existing account linked)';
                $msg .= "<br><i class='fas fa-users me-1'></i> Parent login: <code>$parUsername</code> / <code>" .
                        ($parAccountNew ? $parPassword : 'existing password') . "</code> $accountTag";
            }
            Flash::set('success', $msg);
            $this->redirect('students/view/' . $studentId);

        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed to add student: ' . $e->getMessage());
            setOld($data);
            $this->redirect('students/create');
        }
    }

    public function view(string $id): void {
        $this->requireAuth();
        $db      = getDB();
        $student = $this->findStudentOrFail($db, (int)$id);
        $semId   = (int)getSetting('semester_id', 1);

        $parent = $db->prepare("SELECT * FROM parents WHERE student_id = ? LIMIT 1");
        $parent->execute([$student['id']]);

        $marks = $db->prepare("SELECT m.*, e.title, e.type, e.total_marks, e.weight_percent, s.name as subject FROM marks m JOIN exams e ON m.exam_id = e.id JOIN subjects s ON e.subject_id = s.id WHERE m.student_id = ? ORDER BY s.name, e.type");
        $marks->execute([$student['id']]);

        $attendance = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent, SUM(status='late') as late FROM student_attendance WHERE student_id = ?");
        $attendance->execute([$student['id']]);

        $fees = $db->prepare("SELECT sf.*, fc.name as fee_name FROM student_fees sf JOIN fee_categories fc ON sf.fee_category_id = fc.id WHERE sf.student_id = ? ORDER BY sf.created_at DESC");
        $fees->execute([$student['id']]);

        $discipline = $db->prepare("SELECT di.*, u.username as reported_by_name FROM discipline_incidents di LEFT JOIN users u ON di.reported_by = u.id WHERE di.student_id = ? ORDER BY di.incident_date DESC LIMIT 10");
        $discipline->execute([$student['id']]);

        $timetable = $db->prepare("SELECT tt.*, s.name as subject_name, st.first_name, st.last_name FROM timetable tt JOIN subjects s ON tt.subject_id = s.id JOIN staff st ON tt.teacher_id = st.id WHERE tt.class_id = ? AND tt.semester_id = ? ORDER BY FIELD(tt.day,'Monday','Tuesday','Wednesday','Thursday','Friday'), tt.period");
        $timetable->execute([$student['class_id'], $semId]);

        $user = null;
        if ($student['user_id']) {
            $uStmt = $db->prepare("SELECT username, email FROM users WHERE id = ?");
            $uStmt->execute([$student['user_id']]);
            $user = $uStmt->fetch();
        }

        $this->render('students/view', [
            'title'      => $student['first_name'] . ' ' . $student['last_name'],
            'student'    => $student,
            'parent'     => $parent->fetch(),
            'marks'      => $marks->fetchAll(),
            'attendance' => $attendance->fetch(),
            'fees'       => $fees->fetchAll(),
            'discipline' => $discipline->fetchAll(),
            'timetable'  => $timetable->fetchAll(),
            'user'       => $user,
        ]);
    }

    public function edit(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db      = getDB();
        $student = $this->findStudentOrFail($db, (int)$id);
        $ayId    = (int)getSetting('academic_year_id', 1);
        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $parent = $db->prepare("SELECT * FROM parents WHERE student_id = ? LIMIT 1");
        $parent->execute([$id]);

        $this->render('students/edit', [
            'title'   => 'Edit Student',
            'student' => $student,
            'classes' => $classes->fetchAll(),
            'parent'  => $parent->fetch(),
        ]);
    }

    public function update(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db      = getDB();
        $student = $this->findStudentOrFail($db, (int)$id);

        // Determine stream for Grade 11/12
        $newClassId = $this->post('class_id', $student['class_id']);
        $newStream  = $student['stream'] ?? 'general';
        if ($newClassId) {
            $clsQ = $db->prepare("SELECT grade FROM classes WHERE id=?");
            $clsQ->execute([$newClassId]);
            $clsData = $clsQ->fetch();
            if ($clsData && in_array($clsData['grade'], ['11','12'])) {
                $newStream = $this->post('stream', $newStream);
            }
        }

        $data = [
            'first_name'              => $this->post('first_name', $student['first_name']),
            'last_name'               => $this->post('last_name', $student['last_name']),
            'gender'                  => $this->post('gender', $student['gender']),
            'dob'                     => $this->post('dob', $student['dob']),
            'blood_type'              => $this->post('blood_type', ''),
            'nationality'             => $this->post('nationality', 'Ethiopian'),
            'religion'                => $this->post('religion', ''),
            'class_id'                => $newClassId,
            'stream'                  => $newStream,
            'status'                  => $this->post('status', $student['status']),
            'address'                 => $this->post('address', ''),
            'city'                    => $this->post('city', ''),
            'phone'                   => $this->post('phone', ''),
            'email'                   => $this->post('email', ''),
            'previous_school'         => $this->post('previous_school', ''),
            'medical_info'            => $this->post('medical_info', ''),
            'emergency_contact_name'  => $this->post('emergency_contact_name', ''),
            'emergency_contact_phone' => $this->post('emergency_contact_phone', ''),
        ];

        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->uploadFile('photo', 'students', ALLOWED_IMAGE_TYPES);
            if ($photo) $data['photo'] = $photo;
        }

        try {
            $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE students SET $sets WHERE id = ?")->execute($vals);

            Auth::audit('update', 'students', (int)$id);
            Flash::set('success', 'Student updated successfully.');
            $this->redirect('students/view/' . $id);
        } catch (Exception $e) {
            Flash::set('error', 'Failed to update student: ' . $e->getMessage());
            $this->redirect('students/edit/' . $id);
        }
    }

    public function delete(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db = getDB();
        try {
            $stmt = $db->prepare("UPDATE students SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            Auth::audit('delete', 'students', (int)$id);
            Flash::set('success', 'Student deactivated successfully.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed to delete student.');
        }
        $this->redirect('students');
    }

    public function admissions(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db   = getDB();
        $year = $this->get('year', date('Y'));

        $stmt = $db->prepare("SELECT s.*, c.grade, c.section FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE YEAR(s.admission_date) = ? ORDER BY s.admission_date DESC");
        $stmt->execute([$year]);

        $this->render('students/admissions', [
            'title'    => 'Admissions',
            'students' => $stmt->fetchAll(),
            'year'     => $year,
        ]);
    }

    public function promotions(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        $classes = $db->prepare("SELECT c.*, COUNT(s.id) as student_count FROM classes c LEFT JOIN students s ON s.class_id = c.id WHERE c.academic_year_id = ? GROUP BY c.id ORDER BY c.grade, c.section");
        $classes->execute([$ayId]);

        $ay = $db->query("SELECT * FROM academic_years ORDER BY id DESC LIMIT 5")->fetchAll();

        $this->render('students/promotions', [
            'title'   => 'Student Promotions',
            'classes' => $classes->fetchAll(),
            'years'   => $ay,
        ]);
    }

    public function promote(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();
        Flash::set('success', 'Promotion processed successfully.');
        $this->redirect('students/promotions');
    }

    public function transfers(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db = getDB();

        // Handle POST (record new transfer)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = [
                'student_id'    => $this->post('student_id', ''),
                'transfer_type' => $this->post('transfer_type', 'out'),
                'from_school'   => $this->post('from_school', ''),
                'to_school'     => $this->post('to_school', ''),
                'transfer_date' => $this->post('transfer_date', date('Y-m-d')),
                'reason'        => $this->post('reason', ''),
                'certificate_no'=> $this->post('certificate_no', ''),
                'approved_by'   => Auth::id(),
                'status'        => 'approved',
            ];
            try {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO transfers ($cols) VALUES ($ph)")->execute(array_values($data));
                // Update student status for transfer-out
                if ($data['transfer_type'] === 'out' && $data['student_id']) {
                    $db->prepare("UPDATE students SET status='transferred' WHERE id=?")->execute([$data['student_id']]);
                }
                Flash::set('success', 'Transfer recorded successfully.');
            } catch (\Exception $e) {
                Flash::set('error', 'Failed: ' . $e->getMessage());
            }
            $this->redirect('students/transfers');
            return;
        }

        $stmt = $db->query("SELECT t.*, s.first_name, s.last_name, s.student_id FROM transfers t JOIN students s ON t.student_id = s.id ORDER BY t.created_at DESC");

        $this->render('students/transfers', [
            'title'     => 'Transfers',
            'transfers' => $stmt->fetchAll(),
        ]);
    }

    public function idCard(string $id): void {
        $this->requireAuth();
        $db      = getDB();
        $student = $this->findStudentOrFail($db, (int)$id);

        $this->render('students/id-card', [
            'title'   => 'Student ID Card',
            'student' => $student,
        ], 'print');
    }

    /**
     * Create a parent login account for an existing student whose parent
     * has no user account yet (e.g. students added before this feature).
     */
    public function createParentAccount(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db      = getDB();
        $student = $this->findStudentOrFail($db, (int)$id);

        // Get existing parent record
        $pStmt = $db->prepare("SELECT * FROM parents WHERE student_id=? LIMIT 1");
        $pStmt->execute([$id]);
        $parent = $pStmt->fetch();

        if (!$parent) {
            Flash::set('error', 'No parent record found for this student. Add parent info first.');
            $this->redirect('students/view/' . $id);
            return;
        }

        if (!empty($parent['user_id'])) {
            Flash::set('info', 'This parent already has a login account.');
            $this->redirect('students/view/' . $id);
            return;
        }

        // Generate parent username
        $parFirst    = $parent['first_name'];
        $parLast     = $parent['last_name'] ?: $student['last_name'];
        $parEmail    = $parent['email'] ?: '';
        $parPassword = 'Parent@123';

        // Check if email already belongs to a parent user
        if ($parEmail) {
            $existStmt = $db->prepare("SELECT id, username FROM users WHERE email=? AND role='parent' LIMIT 1");
            $existStmt->execute([$parEmail]);
            $existUser = $existStmt->fetch();
            if ($existUser) {
                // Link existing parent user to this parent record
                $db->prepare("UPDATE parents SET user_id=? WHERE id=?")->execute([$existUser['id'], $parent['id']]);
                Flash::set('success', "Existing parent account <strong>{$existUser['username']}</strong> linked to this student.");
                $this->redirect('students/view/' . $id);
                return;
            }
        }

        // Build unique username
        $parUsername = strtolower(
            preg_replace('/[^a-z0-9]/', '', $parFirst) . '.' .
            preg_replace('/[^a-z0-9]/', '', $parLast) . rand(100, 999)
        );
        while ($db->query("SELECT COUNT(*) FROM users WHERE username='$parUsername'")->fetchColumn() > 0) {
            $parUsername = strtolower(
                preg_replace('/[^a-z0-9]/', '', $parFirst) . '.' .
                preg_replace('/[^a-z0-9]/', '', $parLast) . rand(100, 999)
            );
        }

        $parLoginEmail = $parEmail ?: ($parUsername . '@sjassms.edu.et');
        // Ensure email unique
        $emailChk = $db->prepare("SELECT COUNT(*) FROM users WHERE email=?");
        $emailChk->execute([$parLoginEmail]);
        if ($emailChk->fetchColumn() > 0) {
            $parLoginEmail = $parUsername . rand(10,99) . '@sjassms.edu.et';
        }

        try {
            $db->beginTransaction();
            $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,'parent')")
               ->execute([$parUsername, $parLoginEmail, password_hash($parPassword, PASSWORD_BCRYPT)]);
            $newUserId = $db->lastInsertId();

            $db->prepare("UPDATE parents SET user_id=? WHERE id=?")->execute([$newUserId, $parent['id']]);
            $db->commit();

            Auth::audit('create_parent_account', 'students', (int)$id);
            Flash::set('success',
                "Parent account created. Login: <strong>$parUsername</strong> / <strong>$parPassword</strong>. " .
                "Please give these credentials to the parent."
            );
        } catch (\Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed to create parent account: ' . $e->getMessage());
        }

        $this->redirect('students/view/' . $id);
    }

    private function findStudentOrFail(PDO $db, int $id): array {
        $stmt = $db->prepare("SELECT s.*, c.grade, c.section, CONCAT(c.grade,'-',c.section) as class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?");
        $stmt->execute([$id]);
        $student = $stmt->fetch();
        if (!$student) {
            Flash::set('error', 'Student not found.');
            $this->redirect('students');
            exit;
        }
        return $student;
    }
}
