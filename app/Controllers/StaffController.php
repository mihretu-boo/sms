<?php

require_once ROOT . '/app/Core/Controller.php';

class StaffController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar','dept_head']);

        $db     = getDB();
        $search = $this->get('search', '');
        $deptId = $this->get('dept_id', '');
        $status = $this->get('status', 'active');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.employee_id LIKE ? OR s.position LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like, $like);
        }
        if ($deptId) { $where[] = "s.department_id = ?"; $params[] = $deptId; }
        if ($status)  { $where[] = "s.status = ?"; $params[] = $status; }

        $whereStr = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM staff s WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT s.*, d.name as dept_name FROM staff s LEFT JOIN departments d ON s.department_id = d.id WHERE $whereStr ORDER BY s.first_name ASC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $depts = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();

        $this->render('staff/index', [
            'title'  => 'Staff',
            'staff'  => $stmt->fetchAll(),
            'depts'  => $depts,
            'total'  => $total,
            'page'   => $page,
            'pages'  => ceil($total / $limit),
            'search' => $search,
            'deptId' => $deptId,
            'status' => $status,
        ]);
    }

    public function create(): void {
        $this->requireAuth(['super_admin','principal']);
        $db    = getDB();
        $depts = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        $this->render('staff/create', ['title' => 'Add Staff', 'depts' => $depts]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();

        $data = [
            'first_name'     => $this->post('first_name', ''),
            'last_name'      => $this->post('last_name', ''),
            'gender'         => $this->post('gender', 'male'),
            'dob'            => $this->post('dob', '') ?: null,
            'nationality'    => $this->post('nationality', 'Ethiopian'),
            'department_id'  => $this->post('department_id', '') ?: null,
            'position'       => $this->post('position', ''),
            'qualification'  => $this->post('qualification', ''),
            'specialization' => $this->post('specialization', ''),
            'hire_date'      => $this->post('hire_date', date('Y-m-d')),
            'contract_type'  => $this->post('contract_type', 'permanent'),
            'basic_salary'   => (float)$this->post('basic_salary', 0),
            'phone'          => $this->post('phone', ''),
            'email'          => $this->post('email', ''),
            'address'        => $this->post('address', ''),
            'notes'          => $this->post('notes', ''),
            'status'         => 'active',
        ];

        if (empty($data['first_name']) || empty($data['position'])) {
            Flash::set('error', 'First name and position are required.');
            $this->redirect('staff/create');
            return;
        }

        // Generate employee ID
        $seq    = (int)$db->query("SELECT COUNT(*) + 1 FROM staff")->fetchColumn();
        $empId  = generateEmployeeId($seq);
        $data['employee_id'] = $empId;

        // Create user account
        $role     = $this->post('role', 'teacher');
        $username = strtolower($data['first_name'] . '.' . $data['last_name'] . rand(10, 99));
        $email    = $data['email'] ?: $username . '@sjassms.edu.et';
        $password = password_hash('Staff@123', PASSWORD_BCRYPT);

        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->uploadFile('photo', 'staff', ALLOWED_IMAGE_TYPES);
            if ($photo) $data['photo'] = $photo;
        }

        try {
            $db->beginTransaction();

            $uStmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,?)");
            $uStmt->execute([$username, $email, $password, $role]);
            $userId = $db->lastInsertId();
            $data['user_id'] = $userId;

            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO staff ($cols) VALUES ($ph)")->execute(array_values($data));
            $staffId = $db->lastInsertId();

            $db->commit();
            Auth::audit('create', 'staff', $staffId);
            Flash::set('success', "Staff <strong>$data[first_name] $data[last_name]</strong> added. Employee ID: <strong>$empId</strong>. Login: <strong>$username</strong> / <strong>Staff@123</strong>");
            $this->redirect('staff/view/' . $staffId);
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
            $this->redirect('staff/create');
        }
    }

    public function view(string $id): void {
        $this->requireAuth();
        $db    = getDB();
        $staff = $this->findOrFail($db, (int)$id);

        $leaves = $db->prepare("SELECT * FROM leave_requests WHERE staff_id = ? ORDER BY created_at DESC LIMIT 10");
        $leaves->execute([$id]);

        $payroll = $db->prepare("SELECT * FROM payroll WHERE staff_id = ? ORDER BY year DESC, month DESC LIMIT 12");
        $payroll->execute([$id]);

        $user = null;
        if ($staff['user_id']) {
            $uStmt = $db->prepare("SELECT username, email, role FROM users WHERE id = ?");
            $uStmt->execute([$staff['user_id']]);
            $user = $uStmt->fetch();
        }

        $attStmt = $db->prepare("SELECT COUNT(*) as total, SUM(status='present') as present, SUM(status='absent') as absent FROM staff_attendance WHERE staff_id = ? AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())");
        $attStmt->execute([$id]);

        $this->render('staff/view', [
            'title'      => $staff['first_name'] . ' ' . $staff['last_name'],
            'staff'      => $staff,
            'leaves'     => $leaves->fetchAll(),
            'payroll'    => $payroll->fetchAll(),
            'user'       => $user,
            'attendance' => $attStmt->fetch(),
        ]);
    }

    public function edit(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $db    = getDB();
        $staff = $this->findOrFail($db, (int)$id);
        $depts = $db->query("SELECT * FROM departments ORDER BY name")->fetchAll();
        $this->render('staff/edit', ['title' => 'Edit Staff', 'staff' => $staff, 'depts' => $depts]);
    }

    public function update(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db = getDB();
        $this->findOrFail($db, (int)$id);

        $data = [
            'first_name'    => $this->post('first_name', ''),
            'last_name'     => $this->post('last_name', ''),
            'gender'        => $this->post('gender', 'male'),
            'department_id' => $this->post('department_id', '') ?: null,
            'position'      => $this->post('position', ''),
            'qualification' => $this->post('qualification', ''),
            'hire_date'     => $this->post('hire_date', date('Y-m-d')),
            'basic_salary'  => (float)$this->post('basic_salary', 0),
            'phone'         => $this->post('phone', ''),
            'email'         => $this->post('email', ''),
            'status'        => $this->post('status', 'active'),
            'notes'         => $this->post('notes', ''),
        ];

        if (!empty($_FILES['photo']['name'])) {
            $photo = $this->uploadFile('photo', 'staff', ALLOWED_IMAGE_TYPES);
            if ($photo) $data['photo'] = $photo;
        }

        try {
            $sets = implode('=?,', array_keys($data)) . '=?';
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE staff SET $sets WHERE id=?")->execute($vals);
            Auth::audit('update', 'staff', (int)$id);
            Flash::set('success', 'Staff updated.');
            $this->redirect('staff/view/' . $id);
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
            $this->redirect('staff/edit/' . $id);
        }
    }

    public function delete(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("UPDATE staff SET status = 'terminated' WHERE id = ?")->execute([$id]);
        Flash::set('success', 'Staff terminated.');
        $this->redirect('staff');
    }

    public function attendance(): void {
        $this->requireAuth(['super_admin','principal','vice_principal']);
        $db   = getDB();
        $date = $this->get('date', date('Y-m-d'));

        $stmt = $db->prepare("SELECT s.*, d.name as dept_name, sa.status as att_status, sa.check_in, sa.check_out FROM staff s LEFT JOIN departments d ON s.department_id = d.id LEFT JOIN staff_attendance sa ON sa.staff_id = s.id AND sa.date = ? WHERE s.status = 'active' ORDER BY s.first_name");
        $stmt->execute([$date]);

        $this->render('staff/attendance', ['title' => 'Staff Attendance', 'staff' => $stmt->fetchAll(), 'date' => $date]);
    }

    public function saveAttendance(): void {
        $this->requireAuth(['super_admin','principal','vice_principal']);
        $this->validateCsrf();

        $db   = getDB();
        $date = $this->post('date', date('Y-m-d'));
        $statuses = $_POST['status'] ?? [];

        try {
            $db->beginTransaction();
            $delStmt = $db->prepare("DELETE FROM staff_attendance WHERE date = ?");
            $delStmt->execute([$date]);

            $insStmt = $db->prepare("INSERT INTO staff_attendance (staff_id, date, check_in, check_out, status) VALUES (?,?,?,?,?)");
            foreach ($statuses as $staffId => $status) {
                $insStmt->execute([$staffId, $date, $_POST['check_in'][$staffId] ?? null, $_POST['check_out'][$staffId] ?? null, $status]);
            }
            $db->commit();
            Flash::set('success', 'Attendance saved.');
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('staff/attendance?date=' . $date);
    }

    public function leaves(): void {
        $this->requireAuth();
        $db = getDB();

        $role = Auth::role();
        if (in_array($role, ['super_admin','principal','vice_principal'])) {
            $stmt = $db->query("SELECT lr.*, s.first_name, s.last_name, s.employee_id FROM leave_requests lr JOIN staff s ON lr.staff_id = s.id ORDER BY lr.created_at DESC");
        } else {
            $staffStmt = $db->prepare("SELECT id FROM staff WHERE user_id = ? LIMIT 1");
            $staffStmt->execute([Auth::id()]);
            $staff = $staffStmt->fetch();
            $sid   = $staff ? $staff['id'] : 0;
            $stmt  = $db->prepare("SELECT lr.*, s.first_name, s.last_name FROM leave_requests lr JOIN staff s ON lr.staff_id = s.id WHERE lr.staff_id = ? ORDER BY lr.created_at DESC");
            $stmt->execute([$sid]);
        }

        $this->render('staff/leaves', ['title' => 'Leave Requests', 'leaves' => $stmt->fetchAll()]);
    }

    public function submitLeave(): void {
        $this->requireAuth(['teacher','dept_head','registrar','finance_officer','vice_principal','principal','super_admin']);
        $this->validateCsrf();

        $db = getDB();
        $staffStmt = $db->prepare("SELECT id FROM staff WHERE user_id = ?");
        $staffStmt->execute([Auth::id()]);
        $staff = $staffStmt->fetch();
        if (!$staff) { Flash::set('error', 'Staff record not found.'); $this->redirect('staff/leaves'); return; }

        $start = $this->post('start_date', '');
        $end   = $this->post('end_date', '');
        $days  = $start && $end ? (int)round((strtotime($end) - strtotime($start)) / 86400) + 1 : 1;

        $data = [
            'staff_id'   => $staff['id'],
            'leave_type' => $this->post('leave_type', 'annual'),
            'start_date' => $start,
            'end_date'   => $end,
            'days'       => $days,
            'reason'     => $this->post('reason', ''),
            'status'     => 'pending',
        ];

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO leave_requests ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Leave request submitted.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('staff/leaves');
    }

    public function approveLeave(string $id): void {
        $this->requireAuth(['super_admin','principal','vice_principal']);
        $this->validateCsrf();

        $db     = getDB();
        $action = $this->post('action', 'approve');
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $db->prepare("UPDATE leave_requests SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?")->execute([$status, Auth::id(), $id]);
        Flash::set('success', 'Leave request ' . $status . '.');
        $this->redirect('staff/leaves');
    }

    public function payroll(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);
        $this->redirect('finance/payroll');
    }

    public function processPayroll(): void {
        $this->requireAuth(['super_admin','finance_officer']);
        $this->redirect('finance/payroll');
    }

    private function findOrFail(PDO $db, int $id): array {
        $stmt = $db->prepare("SELECT s.*, d.name as dept_name FROM staff s LEFT JOIN departments d ON s.department_id = d.id WHERE s.id = ?");
        $stmt->execute([$id]);
        $staff = $stmt->fetch();
        if (!$staff) { Flash::set('error', 'Staff not found.'); $this->redirect('staff'); exit; }
        return $staff;
    }
}
