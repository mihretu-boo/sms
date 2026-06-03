<?php

require_once ROOT . '/app/Core/Controller.php';

class ExamRepositoryController extends Controller {

    private const ALLOWED_MIME = [
        'application/pdf'  => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/zip'  => 'zip',
        'application/x-zip-compressed' => 'zip',
        'application/octet-stream' => 'zip',
    ];

    private const MAX_FILE_MB = 50;

    public function index(): void {
        $this->requireAuth();
        $db   = getDB();
        $role = Auth::role();
        $ayId = (int)getSetting('academic_year_id', 1);

        // Dashboard stats
        $stats = [];
        $statsQ = $db->query("SELECT status, COUNT(*) as cnt FROM exam_repository GROUP BY status");
        foreach ($statsQ->fetchAll() as $row) {
            $stats[$row['status']] = $row['cnt'];
        }

        $totalDownloads = $db->query("SELECT COALESCE(SUM(download_count),0) FROM exam_repository")->fetchColumn();
        $totalQuestions = $db->query("SELECT COUNT(*) FROM question_bank WHERE status='active'")->fetchColumn();

        // Recent uploads (role-filtered)
        $where  = ['1=1'];
        $params = [];
        if ($role === 'teacher') {
            $where[] = '(er.uploaded_by = ? OR er.status = \'approved\')';
            $params[] = Auth::id();
        } elseif ($role === 'student') {
            $where[] = "er.status = 'approved' AND er.is_public = 1";
        } elseif ($role === 'dept_head') {
            $stfStmt = $db->prepare("SELECT department_id FROM staff WHERE user_id=? LIMIT 1");
            $stfStmt->execute([Auth::id()]);
            $stf = $stfStmt->fetch();
            if ($stf && $stf['department_id']) {
                $where[] = '(er.department_id = ? OR er.uploaded_by = ?)';
                $params[] = $stf['department_id'];
                $params[] = Auth::id();
            }
        }

        $whereStr = implode(' AND ', $where);
        $recentStmt = $db->prepare("SELECT er.*, s.name as subject_name, u.username as uploader, d.name as dept_name FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id LEFT JOIN users u ON er.uploaded_by=u.id LEFT JOIN departments d ON er.department_id=d.id WHERE $whereStr ORDER BY er.created_at DESC LIMIT 10");
        $recentStmt->execute($params);
        $recent = $recentStmt->fetchAll();

        // Pending approvals
        $pending = [];
        if (in_array($role, ['super_admin','principal','vice_principal','dept_head'])) {
            $targetStatus = in_array($role, ['super_admin','principal','vice_principal']) ? 'under_review' : 'submitted';
            $pStmt = $db->prepare("SELECT er.*, s.name as subject_name, u.username as uploader FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id LEFT JOIN users u ON er.uploaded_by=u.id WHERE er.status=? ORDER BY er.created_at DESC LIMIT 5");
            $pStmt->execute([$targetStatus]);
            $pending = $pStmt->fetchAll();
        }

        // My uploads (for teachers)
        $myUploads = [];
        if (in_array($role, ['teacher','registrar','dept_head'])) {
            $myStmt = $db->prepare("SELECT er.*, s.name as subject_name FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id WHERE er.uploaded_by=? ORDER BY er.created_at DESC LIMIT 5");
            $myStmt->execute([Auth::id()]);
            $myUploads = $myStmt->fetchAll();
        }

        $this->render('exam-repository/index', [
            'title'          => 'Exam Repository',
            'stats'          => $stats,
            'total_downloads'=> (int)$totalDownloads,
            'total_questions'=> (int)$totalQuestions,
            'recent'         => $recent,
            'pending'        => $pending,
            'my_uploads'     => $myUploads,
        ]);
    }

    public function upload(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        $subjects  = $db->query("SELECT * FROM subjects ORDER BY grade, name")->fetchAll();
        $depts     = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        $semesters = $db->query("SELECT * FROM semesters ORDER BY id")->fetchAll();
        $years     = $db->query("SELECT * FROM academic_years ORDER BY start_date DESC")->fetchAll();

        $this->render('exam-repository/upload', [
            'title'     => 'Upload Examination',
            'subjects'  => $subjects,
            'depts'     => $depts,
            'semesters' => $semesters,
            'years'     => $years,
            'ayId'      => $ayId,
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);
        $this->validateCsrf();

        $db = getDB();

        // Handle multiple files
        $files  = $_FILES['exam_files'] ?? null;
        $titles = $_POST['title'] ?? [];

        if (empty($files) || empty($files['name'][0])) {
            Flash::set('error', 'Please select at least one file to upload.');
            $this->redirect('exam-repository/upload');
            return;
        }

        $role     = Auth::role();
        $autoStatus = in_array($role, ['super_admin','principal']) ? 'approved' : 'draft';
        $success  = 0;
        $errors   = [];

        $uploadDir = UPLOADS_PATH . '/exam-repository';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $count = is_array($files['name']) ? count($files['name']) : 1;

        try {
            $db->beginTransaction();

            for ($i = 0; $i < $count; $i++) {
                $tmpFile  = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $origName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
                $errCode  = is_array($files['error']) ? $files['error'][$i] : $files['error'];

                if ($errCode !== UPLOAD_ERR_OK) {
                    $errors[] = "File '$origName' upload error (code: $errCode)";
                    continue;
                }

                // Validate size (50 MB)
                if ($fileSize > self::MAX_FILE_MB * 1024 * 1024) {
                    $errors[] = "File '$origName' exceeds " . self::MAX_FILE_MB . "MB limit.";
                    continue;
                }

                // Validate MIME type
                $mime = mime_content_type($tmpFile);
                if (!isset(self::ALLOWED_MIME[$mime])) {
                    // Try by extension
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','zip'];
                    if (!in_array($ext, $allowed)) {
                        $errors[] = "File '$origName' — unsupported type ($ext).";
                        continue;
                    }
                    $ext = $ext;
                } else {
                    $ext = self::ALLOWED_MIME[$mime];
                }

                // Generate unique filename
                $safeName  = uniqid('exam_', true) . '.' . $ext;
                $destPath  = $uploadDir . '/' . $safeName;
                $relPath   = 'uploads/exam-repository/' . $safeName;

                if (!move_uploaded_file($tmpFile, $destPath)) {
                    $errors[] = "Failed to save '$origName'.";
                    continue;
                }

                // Determine title
                $fileTitle = isset($titles[$i]) && !empty($titles[$i])
                    ? $titles[$i]
                    : pathinfo($origName, PATHINFO_FILENAME);

                $data = [
                    'title'            => $fileTitle,
                    'subject_id'       => $this->post('subject_id', '') ?: null,
                    'grade'            => $this->post('grade', 'all'),
                    'semester_id'      => $this->post('semester_id', '') ?: null,
                    'academic_year_id' => $this->post('academic_year_id', getSetting('academic_year_id',1)) ?: null,
                    'department_id'    => $this->post('department_id', '') ?: null,
                    'exam_type'        => $this->post('exam_type', 'test'),
                    'category_type'    => $this->post('category_type', 'internal'),
                    'difficulty'       => $this->post('difficulty', 'medium'),
                    'description'      => $this->post('description', ''),
                    'instructions'     => $this->post('instructions', ''),
                    'file_path'        => $relPath,
                    'file_original_name' => $origName,
                    'file_size'        => $fileSize,
                    'file_mime'        => $mime,
                    'status'           => $autoStatus,
                    'is_public'        => (int)$this->post('is_public', 0),
                    'watermark'        => (int)$this->post('watermark', 0),
                    'tags'             => $this->post('tags', ''),
                    'uploaded_by'      => Auth::id(),
                ];

                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO exam_repository ($cols) VALUES ($ph)")->execute(array_values($data));
                $examId = $db->lastInsertId();

                // Record initial version
                $db->prepare("INSERT INTO exam_versions (exam_repo_id, version, file_path, file_original_name, file_size, change_notes, uploaded_by) VALUES (?,1,?,?,?,'Initial upload',?)")->execute([$examId, $relPath, $origName, $fileSize, Auth::id()]);

                // Log approval workflow entry
                $db->prepare("INSERT INTO exam_approvals (exam_repo_id, reviewer_id, reviewer_role, action, comments) VALUES (?,?,?,?,?)")->execute([$examId, Auth::id(), $role, 'submitted', 'File uploaded']);

                // Notify dept heads / principals
                $this->notifyReviewers($db, $examId, $fileTitle);

                Auth::audit('upload_exam', 'exam_repository', $examId, $origName);
                $success++;
            }

            $db->commit();

            if ($success > 0) {
                Flash::set('success', "$success file(s) uploaded successfully." . ($autoStatus === 'draft' ? ' Status: Draft — submit for review when ready.' : ' Status: Approved.'));
            }
            if (!empty($errors)) {
                Flash::set('error', implode('<br>', $errors));
            }

            $this->redirect('exam-repository');
        } catch (\Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Upload failed: ' . $e->getMessage());
            $this->redirect('exam-repository/upload');
        }
    }

    public function view(string $id): void {
        $this->requireAuth();
        $db   = getDB();
        $exam = $this->findOrFail($db, (int)$id);

        // Check access
        $role = Auth::role();
        if ($role === 'student' && ($exam['status'] !== 'approved' || !$exam['is_public'])) {
            Flash::set('error', 'This exam is not available.');
            $this->redirect('exam-repository');
            return;
        }

        // Approval history
        $approvals = $db->prepare("SELECT ea.*, u.username, u.role FROM exam_approvals ea JOIN users u ON ea.reviewer_id=u.id WHERE ea.exam_repo_id=? ORDER BY ea.created_at ASC");
        $approvals->execute([$id]);

        // Version history
        $versions = $db->prepare("SELECT ev.*, u.username FROM exam_versions ev LEFT JOIN users u ON ev.uploaded_by=u.id WHERE ev.exam_repo_id=? ORDER BY ev.version DESC");
        $versions->execute([$id]);

        // Download history (admins only)
        $downloads = [];
        if (in_array($role, ['super_admin','principal','vice_principal'])) {
            $dlStmt = $db->prepare("SELECT ed.*, u.username FROM exam_downloads ed JOIN users u ON ed.user_id=u.id WHERE ed.exam_repo_id=? ORDER BY ed.downloaded_at DESC LIMIT 20");
            $dlStmt->execute([$id]);
            $downloads = $dlStmt->fetchAll();
        }

        $this->render('exam-repository/view', [
            'title'     => e($exam['title']),
            'exam'      => $exam,
            'approvals' => $approvals->fetchAll(),
            'versions'  => $versions->fetchAll(),
            'downloads' => $downloads,
        ]);
    }

    public function download(string $id): void {
        $this->requireAuth();
        $db   = getDB();
        $exam = $this->findOrFail($db, (int)$id);
        $role = Auth::role();

        // Permission check
        if ($role === 'student' && ($exam['status'] !== 'approved' || !$exam['is_public'])) {
            Flash::set('error', 'Download not permitted.');
            $this->redirect('exam-repository');
            return;
        }

        $filePath = ROOT . '/' . $exam['file_path'];
        if (!file_exists($filePath)) {
            Flash::set('error', 'File not found on server. Please contact admin.');
            $this->redirect('exam-repository/view/' . $id);
            return;
        }

        // Log download
        $db->prepare("INSERT INTO exam_downloads (exam_repo_id, user_id, ip_address) VALUES (?,?,?)")->execute([$id, Auth::id(), $_SERVER['REMOTE_ADDR'] ?? null]);
        $db->prepare("UPDATE exam_repository SET download_count = download_count + 1 WHERE id=?")->execute([$id]);
        Auth::audit('download_exam', 'exam_repository', (int)$id);

        // Stream file
        $mime = $exam['file_mime'] ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $exam['file_original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($filePath);
        exit;
    }

    public function edit(string $id): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);
        $db   = getDB();
        $exam = $this->findOrFail($db, (int)$id);

        // Only uploader or admin can edit
        if ($exam['uploaded_by'] != Auth::id() && !in_array(Auth::role(), ['super_admin','principal'])) {
            Flash::set('error', 'Permission denied.');
            $this->redirect('exam-repository');
            return;
        }

        $subjects  = $db->query("SELECT * FROM subjects ORDER BY grade, name")->fetchAll();
        $depts     = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        $semesters = $db->query("SELECT * FROM semesters ORDER BY id")->fetchAll();
        $years     = $db->query("SELECT * FROM academic_years ORDER BY start_date DESC")->fetchAll();

        $this->render('exam-repository/edit', [
            'title'     => 'Edit Exam',
            'exam'      => $exam,
            'subjects'  => $subjects,
            'depts'     => $depts,
            'semesters' => $semesters,
            'years'     => $years,
        ]);
    }

    public function update(string $id): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','teacher','dept_head']);
        $this->validateCsrf();

        $db   = getDB();
        $exam = $this->findOrFail($db, (int)$id);

        if ($exam['uploaded_by'] != Auth::id() && !in_array(Auth::role(), ['super_admin','principal'])) {
            Flash::set('error', 'Permission denied.');
            $this->redirect('exam-repository');
            return;
        }

        $data = [
            'title'        => $this->post('title', $exam['title']),
            'subject_id'   => $this->post('subject_id', '') ?: null,
            'grade'        => $this->post('grade', $exam['grade']),
            'semester_id'  => $this->post('semester_id', '') ?: null,
            'department_id'=> $this->post('department_id', '') ?: null,
            'exam_type'    => $this->post('exam_type', $exam['exam_type']),
            'category_type'=> $this->post('category_type', $exam['category_type']),
            'difficulty'   => $this->post('difficulty', $exam['difficulty']),
            'description'  => $this->post('description', ''),
            'instructions' => $this->post('instructions', ''),
            'is_public'    => (int)$this->post('is_public', 0),
            'tags'         => $this->post('tags', ''),
        ];

        // Handle file replacement (new version)
        if (!empty($_FILES['exam_file']['name'])) {
            $uploadDir = UPLOADS_PATH . '/exam-repository';
            $tmpFile  = $_FILES['exam_file']['tmp_name'];
            $origName = $_FILES['exam_file']['name'];
            $fileSize = $_FILES['exam_file']['size'];
            $mime     = mime_content_type($tmpFile);
            $ext      = self::ALLOWED_MIME[$mime] ?? strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $safeName = uniqid('exam_v', true) . '.' . $ext;
            $destPath = $uploadDir . '/' . $safeName;

            if (move_uploaded_file($tmpFile, $destPath)) {
                $newVersion = (int)$exam['version'] + 1;
                $relPath    = 'uploads/exam-repository/' . $safeName;

                // Archive current version
                $db->prepare("INSERT INTO exam_versions (exam_repo_id, version, file_path, file_original_name, file_size, change_notes, uploaded_by) VALUES (?,?,?,?,?,?,?)")->execute([$id, $exam['version'], $exam['file_path'], $exam['file_original_name'], $exam['file_size'], $this->post('change_notes',''), Auth::id()]);

                $data['file_path']          = $relPath;
                $data['file_original_name'] = $origName;
                $data['file_size']          = $fileSize;
                $data['file_mime']          = $mime;
                $data['version']            = $newVersion;
                $data['status']             = 'draft'; // Reset to draft on new version
            }
        }

        try {
            $sets = implode('=?,', array_keys($data)) . '=?';
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE exam_repository SET $sets WHERE id=?")->execute($vals);
            Auth::audit('update_exam', 'exam_repository', (int)$id);
            Flash::set('success', 'Exam updated successfully.');
            $this->redirect('exam-repository/view/' . $id);
        } catch (\Exception $e) {
            Flash::set('error', 'Update failed: ' . $e->getMessage());
            $this->redirect('exam-repository/edit/' . $id);
        }
    }

    public function submit(string $id): void {
        $this->requireAuth(['teacher','registrar','dept_head','super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $exam = $this->findOrFail($db, (int)$id);

        if ($exam['uploaded_by'] != Auth::id() && !in_array(Auth::role(), ['super_admin','principal'])) {
            Flash::set('error', 'Permission denied.');
            $this->redirect('exam-repository');
            return;
        }

        if (!in_array($exam['status'], ['draft','rejected'])) {
            Flash::set('error', 'Only draft or rejected exams can be submitted.');
            $this->redirect('exam-repository/view/' . $id);
            return;
        }

        $db->prepare("UPDATE exam_repository SET status='submitted' WHERE id=?")->execute([$id]);
        $db->prepare("INSERT INTO exam_approvals (exam_repo_id, reviewer_id, reviewer_role, action, comments) VALUES (?,?,?,?,?)")->execute([$id, Auth::id(), Auth::role(), 'submitted', 'Submitted for review']);

        $this->notifyReviewers($db, (int)$id, $exam['title']);
        Auth::audit('submit_exam', 'exam_repository', (int)$id);
        Flash::set('success', 'Exam submitted for review.');
        $this->redirect('exam-repository/view/' . $id);
    }

    public function approve(string $id): void {
        $this->requireAuth(['super_admin','principal','vice_principal','dept_head']);
        $this->validateCsrf();

        $db     = getDB();
        $exam   = $this->findOrFail($db, (int)$id);
        $role   = Auth::role();
        $action = $this->post('action', 'approve');
        $comments = $this->post('comments', '');

        $allowedStatuses = $role === 'dept_head'
            ? ['submitted']
            : ['submitted', 'under_review'];

        if (!in_array($exam['status'], $allowedStatuses)) {
            Flash::set('error', 'This exam cannot be actioned at this stage.');
            $this->redirect('exam-repository/view/' . $id);
            return;
        }

        if ($action === 'approve') {
            // Dept head → moves to under_review; principal/VP → fully approved
            $newStatus = in_array($role, ['principal','super_admin','vice_principal'])
                ? 'approved'
                : 'under_review';

            $approvedBy = in_array($newStatus, ['approved']) ? Auth::id() : null;
            $approvedAt = $newStatus === 'approved' ? date('Y-m-d H:i:s') : null;

            $db->prepare("UPDATE exam_repository SET status=?, approved_by=?, approved_at=? WHERE id=?")->execute([$newStatus, $approvedBy, $approvedAt, $id]);
            $db->prepare("INSERT INTO exam_approvals (exam_repo_id, reviewer_id, reviewer_role, action, comments) VALUES (?,?,?,?,?)")->execute([$id, Auth::id(), $role, 'approved', $comments]);

            // Notify uploader
            $db->prepare("INSERT INTO notifications (user_id, title, message, type, icon, link) VALUES (?,?,?,?,?,?)")->execute([$exam['uploaded_by'], 'Exam Approved', "Your exam '{$exam['title']}' has been " . ($newStatus === 'approved' ? 'fully approved' : 'forwarded for final approval') . ".", 'success', 'check-circle', '/exam-repository/view/' . $id]);

            Flash::set('success', $newStatus === 'approved' ? 'Exam fully approved and published.' : 'Exam forwarded for final approval.');

        } elseif ($action === 'reject') {
            $db->prepare("UPDATE exam_repository SET status='rejected', rejection_reason=? WHERE id=?")->execute([$comments, $id]);
            $db->prepare("INSERT INTO exam_approvals (exam_repo_id, reviewer_id, reviewer_role, action, comments) VALUES (?,?,?,?,?)")->execute([$id, Auth::id(), $role, 'rejected', $comments]);

            $db->prepare("INSERT INTO notifications (user_id, title, message, type, icon, link) VALUES (?,?,?,?,?,?)")->execute([$exam['uploaded_by'], 'Exam Rejected', "Your exam '{$exam['title']}' was rejected. Reason: $comments", 'danger', 'times-circle', '/exam-repository/view/' . $id]);

            Flash::set('warning', 'Exam rejected and uploader notified.');
        }

        Auth::audit('review_exam', 'exam_repository', (int)$id, "Action: $action");
        $this->redirect('exam-repository/view/' . $id);
    }

    public function archive(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db = getDB();
        $db->prepare("UPDATE exam_repository SET status='archived' WHERE id=?")->execute([$id]);
        Flash::set('success', 'Exam archived.');
        $this->redirect('exam-repository/manage');
    }

    public function delete(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db   = getDB();
        $exam = $this->findOrFail($db, (int)$id);

        // Delete physical file
        $filePath = ROOT . '/' . $exam['file_path'];
        if (file_exists($filePath) && strpos($filePath, 'placeholder') === false) {
            @unlink($filePath);
        }

        $db->prepare("DELETE FROM exam_repository WHERE id=?")->execute([$id]);
        Auth::audit('delete_exam', 'exam_repository', (int)$id);
        Flash::set('success', 'Exam deleted permanently.');
        $this->redirect('exam-repository/manage');
    }

    public function browse(): void {
        $this->requireAuth();
        $db    = getDB();
        $role  = Auth::role();
        $grade = $this->get('grade', '');
        $subId = $this->get('subject_id', '');
        $type  = $this->get('type', '');
        $ayId  = $this->get('ay_id', (int)getSetting('academic_year_id', 1));
        $page  = max(1, (int)$this->get('page', 1));
        $limit = 24;
        $offset= ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        // Students only see approved public exams
        if ($role === 'student') {
            $where[] = "er.status = 'approved' AND er.is_public = 1";
        } elseif ($role === 'teacher') {
            $where[] = "(er.status = 'approved' OR er.uploaded_by = ?)";
            $params[] = Auth::id();
        } elseif (!in_array($role, ['super_admin','principal','vice_principal','registrar'])) {
            $where[] = "er.status IN ('approved','under_review','submitted')";
        }

        if ($grade) { $where[] = "(er.grade = ? OR er.grade = 'all')"; $params[] = $grade; }
        if ($subId) { $where[] = "er.subject_id = ?"; $params[] = $subId; }
        if ($type)  { $where[] = "er.exam_type = ?";  $params[] = $type; }
        if ($ayId)  { $where[] = "(er.academic_year_id = ? OR er.academic_year_id IS NULL)"; $params[] = $ayId; }

        // Search
        $search = $this->get('search', '');
        if ($search) {
            $where[] = "(er.title LIKE ? OR er.description LIKE ? OR er.tags LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like);
        }

        $whereStr  = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM exam_repository er WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT er.*, s.name as subject_name, u.username as uploader, d.name as dept_name FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id LEFT JOIN users u ON er.uploaded_by=u.id LEFT JOIN departments d ON er.department_id=d.id WHERE $whereStr ORDER BY er.grade, er.exam_type, er.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $subjects = $db->query("SELECT id, name, grade FROM subjects ORDER BY grade, name")->fetchAll();
        $years    = $db->query("SELECT * FROM academic_years ORDER BY start_date DESC")->fetchAll();

        $this->render('exam-repository/browse', [
            'title'    => 'Browse Examinations',
            'exams'    => $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'pages'    => ceil($total / $limit),
            'subjects' => $subjects,
            'years'    => $years,
            'grade'    => $grade,
            'subId'    => $subId,
            'type'     => $type,
            'ayId'     => $ayId,
            'search'   => $search,
        ]);
    }

    public function manage(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head']);

        $db     = getDB();
        $role   = Auth::role();
        $status = $this->get('status', '');
        $grade  = $this->get('grade', '');
        $search = $this->get('search', '');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($role === 'dept_head') {
            $stfStmt = $db->prepare("SELECT department_id FROM staff WHERE user_id=? LIMIT 1");
            $stfStmt->execute([Auth::id()]);
            $stf = $stfStmt->fetch();
            if ($stf && $stf['department_id']) {
                $where[] = 'er.department_id = ?';
                $params[] = $stf['department_id'];
            }
        }

        if ($status) { $where[] = "er.status = ?"; $params[] = $status; }
        if ($grade)  { $where[] = "er.grade = ?";  $params[] = $grade; }
        if ($search) {
            $where[] = "(er.title LIKE ? OR u.username LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        $whereStr  = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM exam_repository er LEFT JOIN users u ON er.uploaded_by=u.id WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT er.*, s.name as subject_name, u.username as uploader, d.name as dept_name FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id LEFT JOIN users u ON er.uploaded_by=u.id LEFT JOIN departments d ON er.department_id=d.id WHERE $whereStr ORDER BY er.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $this->render('exam-repository/manage', [
            'title'  => 'Manage Examinations',
            'exams'  => $stmt->fetchAll(),
            'total'  => $total,
            'page'   => $page,
            'pages'  => ceil($total / $limit),
            'status' => $status,
            'grade'  => $grade,
            'search' => $search,
        ]);
    }

    // ===== QUESTION BANK =====

    public function questionBank(): void {
        $this->requireAuth();
        $db     = getDB();
        $role   = Auth::role();
        $subId  = $this->get('subject_id', '');
        $grade  = $this->get('grade', '');
        $diff   = $this->get('difficulty', '');
        $type   = $this->get('type', '');
        $search = $this->get('search', '');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ["qb.status = 'active'"];
        $params = [];

        if ($subId) { $where[] = "qb.subject_id = ?"; $params[] = $subId; }
        if ($grade) { $where[] = "(qb.grade = ? OR qb.grade = 'all')"; $params[] = $grade; }
        if ($diff)  { $where[] = "qb.difficulty = ?"; $params[] = $diff; }
        if ($type)  { $where[] = "qb.question_type = ?"; $params[] = $type; }
        if ($search){ $where[] = "qb.question_text LIKE ?"; $params[] = "%$search%"; }

        $whereStr = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM question_bank qb WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT qb.*, s.name as subject_name, u.username as creator FROM question_bank qb LEFT JOIN subjects s ON qb.subject_id=s.id LEFT JOIN users u ON qb.created_by=u.id WHERE $whereStr ORDER BY qb.difficulty, qb.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $subjects = $db->query("SELECT id, name, grade FROM subjects ORDER BY grade, name")->fetchAll();

        $this->render('exam-repository/question-bank', [
            'title'    => 'Question Bank',
            'questions'=> $stmt->fetchAll(),
            'total'    => $total,
            'page'     => $page,
            'pages'    => ceil($total / $limit),
            'subjects' => $subjects,
            'subId'    => $subId,
            'grade'    => $grade,
            'diff'     => $diff,
            'type'     => $type,
            'search'   => $search,
        ]);
    }

    public function storeQuestion(): void {
        $this->requireAuth(['super_admin','principal','teacher','dept_head','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'subject_id'       => $this->post('subject_id','') ?: null,
            'grade'            => $this->post('grade','all'),
            'chapter'          => $this->post('chapter',''),
            'question_text'    => $this->post('question_text',''),
            'question_type'    => $this->post('question_type','mcq'),
            'option_a'         => $this->post('option_a','') ?: null,
            'option_b'         => $this->post('option_b','') ?: null,
            'option_c'         => $this->post('option_c','') ?: null,
            'option_d'         => $this->post('option_d','') ?: null,
            'correct_answer'   => $this->post('correct_answer','') ?: null,
            'explanation'      => $this->post('explanation','') ?: null,
            'difficulty'       => $this->post('difficulty','medium'),
            'marks'            => (int)$this->post('marks',1),
            'learning_outcome' => $this->post('learning_outcome','') ?: null,
            'tags'             => $this->post('tags','') ?: null,
            'created_by'       => Auth::id(),
            'status'           => 'active',
        ];

        if (empty($data['question_text'])) {
            Flash::set('error', 'Question text is required.');
            $this->redirect('exam-repository/question-bank');
            return;
        }

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO question_bank ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Question added to bank.');
        } catch (\Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('exam-repository/question-bank');
    }

    public function deleteQuestion(string $id): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $this->validateCsrf();

        $db = getDB();
        $db->prepare("UPDATE question_bank SET status='inactive' WHERE id=?")->execute([$id]);
        Flash::set('success', 'Question removed.');
        $this->redirect('exam-repository/question-bank');
    }

    // ===== REPORTS =====

    public function reports(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head']);

        $db = getDB();

        $byStatus = $db->query("SELECT status, COUNT(*) as cnt FROM exam_repository GROUP BY status ORDER BY cnt DESC")->fetchAll();
        $byGrade  = $db->query("SELECT grade, COUNT(*) as cnt FROM exam_repository GROUP BY grade ORDER BY grade")->fetchAll();
        $byType   = $db->query("SELECT exam_type, COUNT(*) as cnt FROM exam_repository GROUP BY exam_type ORDER BY cnt DESC")->fetchAll();
        $byDept   = $db->query("SELECT d.name, COUNT(er.id) as cnt FROM departments d LEFT JOIN exam_repository er ON er.department_id=d.id GROUP BY d.id ORDER BY cnt DESC")->fetchAll();
        $topDownloads = $db->query("SELECT er.*, s.name as subject_name FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id ORDER BY er.download_count DESC LIMIT 10")->fetchAll();
        $recentActivity = $db->query("SELECT ed.*, er.title, u.username FROM exam_downloads ed JOIN exam_repository er ON ed.exam_repo_id=er.id JOIN users u ON ed.user_id=u.id ORDER BY ed.downloaded_at DESC LIMIT 20")->fetchAll();

        $monthly = $db->query("SELECT DATE_FORMAT(created_at,'%Y-%m') as month, COUNT(*) as cnt FROM exam_repository WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month")->fetchAll();

        $this->render('exam-repository/reports', [
            'title'           => 'Repository Reports',
            'by_status'       => $byStatus,
            'by_grade'        => $byGrade,
            'by_type'         => $byType,
            'by_dept'         => $byDept,
            'top_downloads'   => $topDownloads,
            'recent_activity' => $recentActivity,
            'monthly'         => $monthly,
        ]);
    }

    // ===== HELPERS =====

    private function findOrFail(PDO $db, int $id): array {
        $stmt = $db->prepare("SELECT er.*, s.name as subject_name, d.name as dept_name, u.username as uploader, ay.name as year_name FROM exam_repository er LEFT JOIN subjects s ON er.subject_id=s.id LEFT JOIN departments d ON er.department_id=d.id LEFT JOIN users u ON er.uploaded_by=u.id LEFT JOIN academic_years ay ON er.academic_year_id=ay.id WHERE er.id=?");
        $stmt->execute([$id]);
        $exam = $stmt->fetch();
        if (!$exam) {
            Flash::set('error', 'Exam not found.');
            $this->redirect('exam-repository');
            exit;
        }
        return $exam;
    }

    private function notifyReviewers(PDO $db, int $examId, string $title): void {
        try {
            // Notify department heads and principals
            $receivers = $db->query("SELECT id FROM users WHERE role IN ('dept_head','vice_principal','principal','super_admin') AND status='active'")->fetchAll(PDO::FETCH_COLUMN);
            $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, icon, link) VALUES (?,?,?,?,?,?)");
            foreach ($receivers as $uid) {
                if ($uid != Auth::id()) {
                    $stmt->execute([$uid, 'New Exam Uploaded', "A new exam '$title' has been uploaded and requires review.", 'info', 'file-alt', '/exam-repository/view/' . $examId]);
                }
            }
        } catch (\Exception $e) {
            // Notification failure is non-fatal
        }
    }

    public static function formatFileSize(int $bytes): string {
        if ($bytes < 1024)       return $bytes . ' B';
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public static function fileIcon(string $mime): string {
        return match(true) {
            str_contains($mime, 'pdf')         => 'fa-file-pdf text-danger',
            str_contains($mime, 'word')        => 'fa-file-word text-primary',
            str_contains($mime, 'excel') || str_contains($mime, 'spreadsheet') => 'fa-file-excel text-success',
            str_contains($mime, 'powerpoint') || str_contains($mime, 'presentation') => 'fa-file-powerpoint text-warning',
            str_contains($mime, 'zip')         => 'fa-file-archive text-secondary',
            default                            => 'fa-file text-muted',
        };
    }
}
