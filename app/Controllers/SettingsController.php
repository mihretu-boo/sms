<?php

require_once ROOT . '/app/Core/Controller.php';
require_once ROOT . '/app/Core/Mailer.php';
require_once ROOT . '/app/Core/MailerProviders.php';

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

        // Email types (graceful if table not yet created)
        $emailTypes = [];
        try {
            $emailTypes = $db->query("SELECT * FROM email_templates ORDER BY id")->fetchAll();
        } catch (\Exception $e) { /* migration not run yet */ }

        // Last 10 sent emails
        $emailLogs = [];
        try {
            $emailLogs = $db->query("SELECT * FROM email_logs ORDER BY created_at DESC LIMIT 10")->fetchAll();
        } catch (\Exception $e) { /* migration not run yet */ }

        $this->render('settings/index', [
            'title'      => 'Settings',
            'groups'     => $grouped,
            'emailTypes' => $emailTypes,
            'emailLogs'  => $emailLogs,
        ]);
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

    // ===== EMAIL PROVIDER SWITCHER =====

    public function switchEmailProvider(): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $provider = $this->post('provider', 'custom');
        $valid    = array_keys(MailerProviders::PRESETS);

        if (!in_array($provider, $valid)) {
            $this->json(['success' => false, 'message' => 'Unknown provider.']);
            return;
        }

        try {
            $db = getDB();
            MailerProviders::applyPreset($db, $provider);
            Auth::audit('switch_email_provider', 'settings', null, "Switched to: $provider");

            $preset = MailerProviders::get($provider);
            $this->json([
                'success'  => true,
                'message'  => "Provider switched to <strong>{$preset['label']}</strong>. SMTP host and port updated.",
                'provider' => $preset,
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ===== SMTP TEST =====

    public function smtpTest(): void {
        $this->requireAuth(['super_admin']);
        $result = Mailer::test();
        $this->json($result);
    }

    // ===== EMAIL TEMPLATES (enable/disable + subject) =====

    public function saveEmailTemplates(): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $subjects = $_POST['subjects'] ?? [];
        $enabled  = $_POST['enabled']  ?? [];

        try {
            $db   = getDB();
            $keys = $db->query("SELECT template_key FROM email_templates")->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $db->prepare(
                "UPDATE email_templates SET subject_template=?, enabled=? WHERE template_key=?"
            );
            foreach ($keys as $key) {
                $stmt->execute([
                    $subjects[$key] ?? '',
                    isset($enabled[$key]) ? 1 : 0,
                    $key,
                ]);
            }
            Auth::audit('update_email_templates', 'settings');
            Flash::set('success', 'Email types saved.');
        } catch (\Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('settings?tab=email');
    }

    // ===== COMPOSE & SEND =====

    public function composeSend(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $toType      = $this->post('to_type', 'custom');
        $customEmail = trim($this->post('custom_email', ''));
        $subject     = trim($this->post('subject', ''));
        $body        = $this->post('body', '');
        $addSig      = (bool)$this->post('add_signature', '0');

        if (empty($subject)) {
            Flash::set('compose_error', 'Subject is required.');
            $this->redirect('settings?tab=email');
            return;
        }

        $schoolName    = getSetting('school_name', 'SJASSMS');
        $schoolAddress = getSetting('school_address', '');
        $fromEmail     = getSetting('smtp_from_email', getSetting('smtp_user', ''));

        // Build HTML body
        $htmlBody = $this->buildComposeHtml($subject, $body, $addSig, $schoolName, $schoolAddress, $fromEmail);

        // Resolve recipients
        $recipients = [];

        if ($toType === 'custom') {
            if (!filter_var($customEmail, FILTER_VALIDATE_EMAIL)) {
                Flash::set('compose_error', 'Please enter a valid email address.');
                $this->redirect('settings?tab=email');
                return;
            }
            $recipients[] = ['email' => $customEmail, 'name' => ''];
        } else {
            $db = getDB();
            if ($toType === 'role:all') {
                $rows = $db->query(
                    "SELECT email, username FROM users WHERE email <> '' AND status='active'"
                )->fetchAll();
            } elseif (str_starts_with($toType, 'role:')) {
                $role = substr($toType, 5);
                if ($role === 'staff') {
                    $rows = $db->prepare(
                        "SELECT email, username FROM users WHERE role NOT IN ('student','parent') AND email <> '' AND status='active'"
                    );
                    $rows->execute();
                    $rows = $rows->fetchAll();
                } else {
                    $rows = $db->prepare(
                        "SELECT email, username FROM users WHERE role=? AND email <> '' AND status='active'"
                    );
                    $rows->execute([$role]);
                    $rows = $rows->fetchAll();
                }
            } else {
                Flash::set('compose_error', 'Invalid recipient type.');
                $this->redirect('settings?tab=email');
                return;
            }
            foreach ($rows as $r) {
                if (!empty($r['email'])) {
                    $recipients[] = ['email' => $r['email'], 'name' => $r['username'] ?? ''];
                }
            }
        }

        if (empty($recipients)) {
            Flash::set('compose_error', 'No recipients found for the selected group.');
            $this->redirect('settings?tab=email');
            return;
        }

        $db      = getDB();
        $mailer  = new Mailer();
        $sent    = 0;
        $failed  = 0;
        $userId  = Auth::id();

        $logStmt = null;
        try {
            $logStmt = $db->prepare(
                "INSERT INTO email_logs (to_email, to_name, subject, template_key, body_html, status, error_message, sent_by)
                 VALUES (?,?,?,'compose',?,?,?,?)"
            );
        } catch (\Exception $e) { /* email_logs table may not exist */ }

        foreach ($recipients as $rec) {
            $status = 'sent';
            $errMsg = '';
            try {
                $mailer->send($rec['email'], $subject, $htmlBody);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $status = 'failed';
                $errMsg = $e->getMessage();
            }
            if ($logStmt) {
                try {
                    $logStmt->execute([
                        $rec['email'], $rec['name'], $subject,
                        $htmlBody, $status, $errMsg, $userId,
                    ]);
                } catch (\Exception $e) { /* ignore log failure */ }
            }
        }

        Auth::audit('compose_send_email', 'settings', null, "Sent to $sent recipients");

        if ($failed === 0) {
            Flash::set('compose_success', "✅ Email sent to <strong>$sent</strong> recipient" . ($sent !== 1 ? 's' : '') . ".");
        } else {
            Flash::set('compose_error', "Sent: $sent | Failed: $failed. Check SMTP settings.");
        }
        $this->redirect('settings?tab=email');
    }

    private function buildComposeHtml(
        string $subject,
        string $body,
        bool   $addSig,
        string $schoolName,
        string $schoolAddress,
        string $fromEmail
    ): string {
        $footerHtml = $addSig ? "
          <div style='margin-top:32px;padding-top:16px;border-top:1px solid #EEE;font-size:12px;color:#9E9E9E;text-align:center'>
            <p style='margin:4px 0'><strong style='color:#666'>{$schoolName}</strong></p>
            <p style='margin:4px 0'>{$schoolAddress}</p>
            <p style='margin:8px 0 0;color:#BDBDBD'>This is an automated message &mdash; please do not reply directly to this email.
              &copy; " . date('Y') . " {$schoolName}</p>
          </div>" : '';

        return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
          <body style='margin:0;padding:0;background:#F0F4F8;font-family:Segoe UI,Arial,sans-serif'>
          <div style='max-width:600px;margin:0 auto;padding:24px 16px'>
            <div style='background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden'>
              <div style='background:linear-gradient(135deg,#1B3A6B,#2A5298);padding:28px 40px;text-align:center'>
                <h1 style='color:#fff;margin:0;font-size:20px;font-weight:700'>{$schoolName}</h1>
                <p style='color:rgba(255,255,255,.75);margin:4px 0 0;font-size:13px'>" . date('d M Y') . "</p>
              </div>
              <div style='padding:32px 40px'>
                <h2 style='color:#1B3A6B;font-size:18px;font-weight:700;margin:0 0 20px'>{$subject}</h2>
                <div style='font-size:14px;color:#444;line-height:1.8'>{$body}</div>
                {$footerHtml}
              </div>
            </div>
          </div></body></html>";
    }

    // ===== EMAIL LOG (full list) =====

    public function emailLogs(): void {
        $this->requireAuth(['super_admin','principal']);
        $db    = getDB();
        $page  = max(1, (int)$this->get('page', 1));
        $limit = 50;
        $offset= ($page - 1) * $limit;

        try {
            $total = (int)$db->query("SELECT COUNT(*) FROM email_logs")->fetchColumn();
            $logs  = $db->prepare(
                "SELECT el.*, u.username as sent_by_name FROM email_logs el
                 LEFT JOIN users u ON el.sent_by = u.id
                 ORDER BY el.created_at DESC LIMIT ? OFFSET ?"
            );
            $logs->execute([$limit, $offset]);
            $logs = $logs->fetchAll();
        } catch (\Exception $e) {
            $total = 0;
            $logs  = [];
        }

        $this->render('settings/email-logs', [
            'title'  => 'Email Logs',
            'logs'   => $logs,
            'total'  => $total,
            'page'   => $page,
            'pages'  => ceil($total / $limit),
        ]);
    }

    // ===== EMAIL PREVIEW =====

    public function previewEmail(string $key): void {
        $this->requireAuth(['super_admin','principal']);

        $schoolName    = getSetting('school_name','SJASSMS');
        $schoolAddress = getSetting('school_address','');
        $adminEmail    = getSetting('school_email','admin@school.edu.et');
        $loginUrl      = url('login');

        $previewVars = [
            // universal
            'schoolName'    => $schoolName,
            'schoolAddress' => $schoolAddress,
            'adminEmail'    => $adminEmail,
            'loginUrl'      => $loginUrl,
            'schoolPhone'   => getSetting('school_phone',''),
            // student/staff
            'studentName'   => 'Alemu Bekele',
            'staffName'     => 'Tigist Haile',
            'parentName'    => 'Bekele Alemu',
            'recipientName' => 'Parent / Student',
            'username'      => 'alemu.bekele',
            'password'      => 'Temp@1234',
            'role'          => 'Teacher',
            'department'    => 'Mathematics',
            'grade'         => 'Grade 10A',
            'studentId'     => 'STU-2025-001',
            // fee
            'feeType'       => 'Annual Tuition Fee',
            'amount'        => '4500',
            'currency'      => 'ETB',
            'dueDate'       => date('d M Y', strtotime('+7 days')),
            'academicTerm'  => 'Semester 1, 2024–25',
            // attendance
            'presentDays'   => '42',
            'absentDays'    => '18',
            'attendanceRate'=> '70',
            'threshold'     => '75',
            // exam
            'examName'      => 'Mid-Term Examination',
            'gpa'           => '3.25',
            'subjects'      => [
                ['subject'=>'Mathematics',    'score'=>'85','total'=>'100','grade'=>'A-','remarks'=>'Excellent'],
                ['subject'=>'English',        'score'=>'78','total'=>'100','grade'=>'B+','remarks'=>'Good'],
                ['subject'=>'Physics',        'score'=>'72','total'=>'100','grade'=>'B', 'remarks'=>'Above Average'],
                ['subject'=>'Amharic',        'score'=>'90','total'=>'100','grade'=>'A', 'remarks'=>'Outstanding'],
            ],
            // announcement
            'title'         => 'School Reopening Notice',
            'body'          => "Dear Parents and Students,\n\nWe are pleased to announce that the new semester begins on Monday, 23 September 2024.\nAll students are required to report by 7:30 AM in full school uniform.\n\nThank you for your continued support.",
            'category'      => 'Notice',
            'publishedAt'   => date('d M Y'),
            'audience'      => 'All Students & Parents',
        ];

        try {
            $html = Mailer::renderTemplate($key, $previewVars);
            header('Content-Type: text/html; charset=UTF-8');
            echo $html;
            exit;
        } catch (\Exception $e) {
            http_response_code(404);
            echo '<p>Template not found: ' . e($key) . '</p>';
            exit;
        }
    }

    public function sendTestEmail(): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();

        $to = $this->post('test_email', '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Please enter a valid test email address.');
            $this->redirect('settings?tab=email');
            return;
        }

        try {
            $mailer  = new Mailer();
            $provider = getSetting('smtp_provider', 'custom');
            $preset   = MailerProviders::get($provider);
            $subject  = 'SMTP Test — ' . getSetting('school_name', 'SJASSMS');
            $html     = '<div style="font-family:sans-serif;max-width:520px;margin:0 auto;padding:24px">
                          <h2 style="color:#2E7D32">✅ SMTP Test Successful!</h2>
                          <p>This is a test email from the <strong>' . e(getSetting('school_name','SJASSMS')) . '</strong> Management System.</p>
                          <table style="border-collapse:collapse;width:100%;font-size:13px">
                            <tr><td style="padding:6px;color:#666">Provider</td><td style="padding:6px"><strong>' . e($preset['label']) . '</strong></td></tr>
                            <tr><td style="padding:6px;color:#666">SMTP Server</td><td style="padding:6px"><code>' . e(getSetting('smtp_host')) . ':' . e(getSetting('smtp_port')) . '</code></td></tr>
                            <tr><td style="padding:6px;color:#666">From</td><td style="padding:6px">' . e(getSetting('smtp_from_email', getSetting('smtp_user',''))) . '</td></tr>
                            <tr><td style="padding:6px;color:#666">Sent at</td><td style="padding:6px">' . date('d M Y H:i:s T') . '</td></tr>
                          </table>
                          <p style="color:#888;font-size:12px;margin-top:16px">Email system is working correctly.</p>
                        </div>';

            $mailer->send($to, $subject, $html);
            Auth::audit('smtp_test_email', 'settings', null, "Test email sent to: $to via {$preset['label']}");
            Flash::set('success', "✅ Test email sent to <strong>$to</strong> via <strong>{$preset['label']}</strong>. Check your inbox (and spam folder).");
        } catch (\Exception $e) {
            Flash::set('error', '❌ Failed: ' . $e->getMessage());
        }

        $this->redirect('settings?tab=email');
    }
}
