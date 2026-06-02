<?php

require_once ROOT . '/app/Core/Controller.php';

class SettingsController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal']);
        $db   = getDB();
        $stmt = $db->query("SELECT * FROM settings ORDER BY group_name, setting_key");
        $all  = $stmt->fetchAll();
        $grouped = [];
        foreach ($all as $s) {
            $grouped[$s['group_name']][] = $s;
        }
        $this->render('settings/index', ['title' => 'Settings', 'groups' => $grouped]);
    }

    public function save(): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db   = getDB();
        $data = $_POST['settings'] ?? [];

        try {
            $stmt = $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?");
            foreach ($data as $key => $value) {
                $stmt->execute([$value, $key]);
            }
            Flash::set('success', 'Settings saved.');
            Auth::audit('update_settings', 'settings');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('settings');
    }

    public function users(): void {
        $this->requireAuth(['super_admin','principal']);
        $db     = getDB();
        $search = $this->get('search', '');
        $role   = $this->get('role', '');

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(username LIKE ? OR email LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like);
        }
        if ($role) { $where[] = "role=?"; $params[] = $role; }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT u.*, (SELECT COUNT(*) FROM audit_logs WHERE user_id=u.id) as log_count FROM users u WHERE $whereStr ORDER BY role, username");
        $stmt->execute($params);

        $this->render('settings/users', [
            'title'  => 'User Management',
            'users'  => $stmt->fetchAll(),
            'search' => $search,
            'role'   => $role,
        ]);
    }

    public function createUser(): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'username' => $this->post('username', ''),
            'email'    => $this->post('email', ''),
            'password' => password_hash($this->post('password', 'Admin@123') ?: 'Admin@123', PASSWORD_BCRYPT),
            'role'     => $this->post('role', 'teacher'),
            'phone'    => $this->post('phone', ''),
            'status'   => 'active',
        ];

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO users ($cols) VALUES ($ph)")->execute(array_values($data));
            Auth::audit('create_user', 'settings');
            Flash::set('success', 'User created: <strong>' . e($data['username']) . '</strong>');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('settings/users');
    }

    public function editUser(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'username' => $this->post('username', ''),
            'email'    => $this->post('email', ''),
            'role'     => $this->post('role', 'teacher'),
            'phone'    => $this->post('phone', ''),
            'status'   => $this->post('status', 'active'),
        ];

        $newPass = $_POST['password'] ?? '';
        if (!empty($newPass)) {
            $data['password'] = password_hash($newPass, PASSWORD_BCRYPT);
        }

        try {
            $sets = implode('=?,', array_keys($data)) . '=?';
            $vals = array_values($data); $vals[] = $id;
            $db->prepare("UPDATE users SET $sets WHERE id=?")->execute($vals);
            Auth::audit('update_user', 'settings', (int)$id);
            Flash::set('success', 'User updated.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('settings/users');
    }

    public function toggleUser(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db   = getDB();
        $stmt = $db->prepare("SELECT status FROM users WHERE id=?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        $newStatus = ($user['status'] === 'active') ? 'suspended' : 'active';
        $db->prepare("UPDATE users SET status=? WHERE id=?")->execute([$newStatus, $id]);
        Flash::set('success', 'User status updated.');
        $this->redirect('settings/users');
    }

    public function resetUserPassword(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $db      = getDB();
        $newPass = password_hash('Admin@123', PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newPass, $id]);
        Auth::audit('reset_password', 'settings', (int)$id);
        Flash::set('success', 'Password reset to <strong>Admin@123</strong>.');
        $this->redirect('settings/users');
    }

    public function roles(): void {
        $this->requireAuth(['super_admin']);
        $this->render('settings/roles', ['title' => 'Roles & Permissions', 'permissions' => ROLE_PERMISSIONS]);
    }

    public function audit(): void {
        $this->requireAuth(['super_admin','principal']);
        $db     = getDB();
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = 50;
        $offset = ($page - 1) * $limit;
        $module = $this->get('module', '');
        $userId = $this->get('user_id', '');

        $where  = ['1=1'];
        $params = [];
        if ($module) { $where[] = "al.module=?"; $params[] = $module; }
        if ($userId) { $where[] = "al.user_id=?"; $params[] = $userId; }

        $whereStr = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM audit_logs al WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT al.*, u.username FROM audit_logs al LEFT JOIN users u ON al.user_id=u.id WHERE $whereStr ORDER BY al.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $modules = $db->query("SELECT DISTINCT module FROM audit_logs ORDER BY module")->fetchAll(PDO::FETCH_COLUMN);
        $users   = $db->query("SELECT id, username FROM users ORDER BY username")->fetchAll();

        $this->render('settings/audit', [
            'title'   => 'Audit Logs',
            'logs'    => $stmt->fetchAll(),
            'total'   => $total,
            'page'    => $page,
            'pages'   => ceil($total / $limit),
            'modules' => $modules,
            'users'   => $users,
            'module'  => $module,
            'userId'  => $userId,
        ]);
    }

    public function backup(): void {
        $this->requireAuth(['super_admin']);
        $backupDir = ROOT . '/backups';
        $backups = [];
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            foreach ($files as $file) {
                $backups[] = ['name' => basename($file), 'size' => filesize($file), 'date' => filemtime($file)];
            }
            usort($backups, fn($a,$b) => $b['date'] - $a['date']);
        }
        $this->render('settings/backup', ['title' => 'Backup & Restore', 'backups' => $backups]);
    }

    public function createBackup(): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $backupDir = ROOT . '/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

        $filename = 'sjassms_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $cmd = sprintf(
            'c:\\xampp\\mysql\\bin\\mysqldump.exe --user=%s --password=%s --host=%s %s > %s 2>&1',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_NAME),
            escapeshellarg($filepath)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($filepath)) {
            Auth::audit('backup', 'settings', null, $filename);
            Flash::set('success', "Backup created: <strong>$filename</strong>");
        } else {
            Flash::set('error', 'Backup failed. Check MySQL access.');
        }
        $this->redirect('settings/backup');
    }
}
