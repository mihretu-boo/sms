<?php

class Auth {

    public static function check(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function user(): array|null {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int|null {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): string|null {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function login(int $userId, array $userData): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user']    = $userData;
        $_SESSION['logged_at'] = time();
        self::generateCsrf();

        // Update last login in DB
        try {
            $db = getDB();
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
        } catch (Exception $e) {
            // ignore
        }
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function attempt(string $usernameOrEmail, string $password): bool {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1");
            $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
            $user = $stmt->fetch();

            if (!$user) return false;
            if (!password_verify($password, $user['password'])) return false;

            self::login($user['id'], [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
                'photo'    => $user['photo'],
                'phone'    => $user['phone'],
                'lang'     => $user['lang'] ?? 'en',
            ]);

            // Log login
            self::audit('login', 'auth', $user['id']);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function can(string $permission): bool {
        $role = self::role();
        if (!$role) return false;

        $permissions = ROLE_PERMISSIONS[$role] ?? [];

        if (in_array('*', $permissions)) return true;
        if (in_array($permission, $permissions)) return true;

        // Check parent permission (e.g. 'students' covers 'students.view')
        $parts = explode('.', $permission);
        if (count($parts) > 1 && in_array($parts[0], $permissions)) return true;

        return false;
    }

    public static function hasRole(string|array $roles): bool {
        $role = self::role();
        if (is_array($roles)) return in_array($role, $roles);
        return $role === $roles;
    }

    public static function isAdmin(): bool {
        return self::hasRole('super_admin');
    }

    public static function generateCsrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrf(): string {
        return $_SESSION['csrf_token'] ?? self::generateCsrf();
    }

    public static function audit(string $action, string $module, int|null $recordId = null, string $details = ''): void {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, module, record_id, new_data, ip_address, user_agent) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([
                self::id(),
                $action,
                $module,
                $recordId,
                $details ?: null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (Exception $e) {
            // ignore
        }
    }

    public static function checkSession(): void {
        if (!self::check()) return;

        // Session timeout
        $loggedAt = $_SESSION['logged_at'] ?? 0;
        if (time() - $loggedAt > SESSION_LIFETIME) {
            self::logout();
        }
    }

    // Get linked student/staff record for current user
    public static function getLinkedRecord(): array|null {
        $userId = self::id();
        $role   = self::role();
        if (!$userId) return null;

        try {
            $db = getDB();
            if (in_array($role, ['student'])) {
                $stmt = $db->prepare("SELECT * FROM students WHERE user_id = ? LIMIT 1");
                $stmt->execute([$userId]);
                return $stmt->fetch() ?: null;
            } elseif (in_array($role, ['teacher','principal','vice_principal','registrar','dept_head','finance_officer'])) {
                $stmt = $db->prepare("SELECT * FROM staff WHERE user_id = ? LIMIT 1");
                $stmt->execute([$userId]);
                return $stmt->fetch() ?: null;
            } elseif ($role === 'parent') {
                $stmt = $db->prepare("SELECT p.*, s.first_name AS student_first, s.last_name AS student_last, s.student_id AS stud_no FROM parents p JOIN students s ON p.student_id = s.id WHERE p.user_id = ? LIMIT 1");
                $stmt->execute([$userId]);
                return $stmt->fetch() ?: null;
            }
        } catch (Exception $e) {}

        return null;
    }
}
