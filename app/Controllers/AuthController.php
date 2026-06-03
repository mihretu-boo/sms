<?php

require_once ROOT . '/app/Core/Controller.php';
require_once ROOT . '/app/Core/Mailer.php';
require_once ROOT . '/app/Core/RateLimiter.php';

class AuthController extends Controller {

    // ===== LOGIN =====

    public function showLogin(): void {
        $this->requireGuest();
        $this->render('auth/login', ['title' => 'Login'], 'auth');
    }

    public function login(): void {
        $this->requireGuest();

        $credential = $this->post('credential', '');
        $password   = $_POST['password'] ?? '';
        $remember   = $this->post('remember', '0');

        if (empty($credential) || empty($password)) {
            Flash::set('error', 'Username/email and password are required.');
            setOld(['credential' => $credential]);
            $this->redirect('login');
            return;
        }

        if (Auth::attempt($credential, $password)) {
            if ($remember === '1') {
                session_set_cookie_params(86400 * 30);
                session_regenerate_id(true);
            }
            $this->redirect('dashboard');
        } else {
            Flash::set('error', 'Invalid credentials. Please check your username/email and password.');
            setOld(['credential' => $credential]);
            $this->redirect('login');
        }
    }

    public function logout(): void {
        if (Auth::check()) {
            Auth::audit('logout', 'auth');
        }
        Auth::logout();
        Flash::set('success', 'You have been logged out successfully.');
        $this->redirect('login');
    }

    // ===== FORGOT PASSWORD =====

    public function forgotPassword(): void {
        $this->requireGuest();
        $this->render('auth/forgot-password', ['title' => 'Forgot Password'], 'auth');
    }

    public function processForgotPassword(): void {
        $this->requireGuest();

        $email = trim($this->post('email', ''));

        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Please enter a valid email address.');
            $this->render('auth/forgot-password', ['title' => 'Forgot Password'], 'auth');
            return;
        }

        $email     = strtolower($email);
        $ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $maxReqs   = (int)getSetting('reset_rate_limit', '3');
        $limiter   = new RateLimiter();

        // Rate limit by IP
        if (!$limiter->attempt($ip, 'ip', $maxReqs * 2, 60)) {
            $wait = ceil($limiter->retryAfter($ip, 'ip') / 60);
            Flash::set('error', "Too many requests from your location. Please wait {$wait} minute(s) and try again.");
            $this->render('auth/forgot-password', ['title' => 'Forgot Password'], 'auth');
            return;
        }

        // Rate limit by email
        if (!$limiter->attempt($email, 'email', $maxReqs, 60)) {
            $wait = ceil($limiter->retryAfter($email, 'email') / 60);
            Flash::set('error', "Too many reset requests for this email. Please wait {$wait} minute(s) and try again.");
            $this->render('auth/forgot-password', ['title' => 'Forgot Password'], 'auth');
            return;
        }

        $db = getDB();

        // Always show "success" to prevent email enumeration
        $successMsg = "If that email is registered, a password reset link has been sent. Please check your inbox and spam folder.";

        // Look up user
        $stmt = $db->prepare("SELECT id, username, email FROM users WHERE LOWER(email) = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            try {
                $expiryMins = (int)getSetting('reset_token_expiry', '30');

                // Invalidate previous tokens for this email
                $db->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0")->execute([$user['email']]);

                // Generate secure token — stored as plain, sent as-is
                // (the 64-char random token is computationally infeasible to guess)
                $plainToken = bin2hex(random_bytes(32)); // 64 hex chars
                $expiresAt  = date('Y-m-d H:i:s', strtotime("+{$expiryMins} minutes"));

                $db->prepare(
                    "INSERT INTO password_resets (email, token, expires_at, used, ip_address, user_agent)
                     VALUES (?, ?, ?, 0, ?, ?)"
                )->execute([
                    $user['email'],
                    $plainToken,
                    $expiresAt,
                    $ip,
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ]);

                // Build reset URL
                $baseUrl  = getSetting('school_url', BASE_URL);
                $resetUrl = rtrim($baseUrl, '/') . '/reset-password?token=' . urlencode($plainToken);

                // Render email template
                $html = Mailer::renderTemplate('reset-password', [
                    'schoolName'    => getSetting('school_name', 'Shalaka Jatan Ali Secondary School'),
                    'schoolAddress' => getSetting('school_address', 'Yabelo, Borana Zone, Oromia, Ethiopia'),
                    'adminEmail'    => getSetting('school_email', 'admin@sjassms.edu.et'),
                    'username'      => $user['username'],
                    'email'         => $user['email'],
                    'resetUrl'      => $resetUrl,
                    'expiryMinutes' => $expiryMins,
                    'expiresAt'     => date('d M Y, H:i', strtotime($expiresAt)) . ' (EAT)',
                ]);

                $subject = "Reset Your Password – " . getSetting('school_name', 'Shalaka Jatan Ali Secondary School');

                // Send email
                $mailer = new Mailer();
                $mailer->send($user['email'], $subject, $html);

                // Audit log
                Auth::audit('forgot_password_request', 'auth', $user['id'],
                    json_encode(['email' => $user['email'], 'ip' => $ip]));

            } catch (\Exception $e) {
                // Log but don't expose error to user
                error_log("Password reset error for {$email}: " . $e->getMessage());
                // Still log the audit attempt
                Auth::audit('forgot_password_error', 'auth', null,
                    json_encode(['email' => $email, 'error' => $e->getMessage()]));
            }
        } else {
            // Still log the attempt (invalid email)
            Auth::audit('forgot_password_invalid_email', 'auth', null,
                json_encode(['email' => $email, 'ip' => $ip]));
        }

        // Redirect to confirmation page (same response whether email exists or not)
        $_SESSION['reset_email_sent'] = $email;
        $this->redirect('forgot-password/sent');
    }

    public function forgotPasswordSent(): void {
        $this->requireGuest();
        $email = $_SESSION['reset_email_sent'] ?? '';
        unset($_SESSION['reset_email_sent']);
        $this->render('auth/email-sent', [
            'title' => 'Check Your Email',
            'email' => $email,
        ], 'auth');
    }

    // ===== RESET PASSWORD =====

    public function resetPassword(): void {
        $this->requireGuest();

        $token = $this->get('token', '');

        if (empty($token)) {
            Flash::set('error', 'Invalid reset link. Please request a new one.');
            $this->redirect('forgot-password');
            return;
        }

        // Validate token early (show error before user types password)
        $db     = getDB();
        $stmt   = $db->prepare(
            "SELECT * FROM password_resets
             WHERE token = ? AND used = 0 AND expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $this->render('auth/reset-token-invalid', [
                'title' => 'Link Expired or Invalid',
            ], 'auth');
            return;
        }

        $this->render('auth/reset-password', [
            'title' => 'Set New Password',
            'token' => $token,
            'email' => $reset['email'],
        ], 'auth');
    }

    public function processReset(): void {
        $this->requireGuest();

        $token    = $this->post('token', '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (empty($token)) {
            Flash::set('error', 'Invalid reset token.');
            $this->redirect('forgot-password');
            return;
        }

        // Validate password strength
        $strengthError = $this->validatePasswordStrength($password);
        if ($strengthError) {
            Flash::set('error', $strengthError);
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirm) {
            Flash::set('error', 'Passwords do not match. Please try again.');
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        $db   = getDB();

        // Retrieve and validate token (with row locking)
        $stmt = $db->prepare(
            "SELECT pr.*, u.id as user_id, u.username, u.email as user_email
             FROM password_resets pr
             JOIN users u ON u.email = pr.email
             WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $this->render('auth/reset-token-invalid', [
                'title' => 'Link Expired or Invalid',
            ], 'auth');
            return;
        }

        try {
            $db->beginTransaction();

            // Update password
            $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $reset['user_id']]);

            // Invalidate ALL tokens for this email (including this one)
            $db->prepare("UPDATE password_resets SET used = 1 WHERE email = ?")->execute([$reset['email']]);

            $db->commit();

            // Audit log
            Auth::audit('password_reset_success', 'auth', $reset['user_id'],
                json_encode(['email' => $reset['email'], 'ip' => $ip]));

            // Send confirmation email
            try {
                $changedAt = date('d M Y, H:i') . ' (EAT)';
                $html = Mailer::renderTemplate('password-changed', [
                    'schoolName'    => getSetting('school_name', 'Shalaka Jatan Ali Secondary School'),
                    'schoolAddress' => getSetting('school_address', 'Yabelo, Borana Zone, Oromia, Ethiopia'),
                    'adminEmail'    => getSetting('school_email', 'admin@sjassms.edu.et'),
                    'username'      => $reset['username'],
                    'email'         => $reset['email'],
                    'changedAt'     => $changedAt,
                    'ipAddress'     => $ip,
                    'loginUrl'      => rtrim(getSetting('school_url', BASE_URL), '/') . '/login',
                ]);

                $mailer  = new Mailer();
                $subject = "Password Changed – " . getSetting('school_name', 'Shalaka Jatan Ali Secondary School');
                $mailer->send($reset['email'], $subject, $html);

            } catch (\Exception $emailEx) {
                // Non-fatal — password was changed successfully
                error_log("Confirmation email failed: " . $emailEx->getMessage());
            }

            // Store username for success page
            $_SESSION['pwd_reset_success'] = $reset['username'];
            $this->redirect('reset-password/success');

        } catch (\Exception $e) {
            $db->rollBack();
            Auth::audit('password_reset_failed', 'auth', null,
                json_encode(['error' => $e->getMessage(), 'ip' => $ip]));
            Flash::set('error', 'An error occurred. Please try again or contact the administrator.');
            $this->redirect('reset-password?token=' . urlencode($token));
        }
    }

    public function resetPasswordSuccess(): void {
        $this->requireGuest();
        $username = $_SESSION['pwd_reset_success'] ?? '';
        unset($_SESSION['pwd_reset_success']);
        if (empty($username)) {
            $this->redirect('login');
            return;
        }
        $this->render('auth/password-changed', [
            'title'    => 'Password Updated',
            'username' => $username,
        ], 'auth');
    }

    // ===== HELPERS =====

    private function validatePasswordStrength(string $password): ?string {
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter (A-Z).';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter (a-z).';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one number (0-9).';
        }
        if (!preg_match('/[\W_]/', $password)) {
            return 'Password must contain at least one special character (e.g. @, #, !, %).';
        }
        if (strlen($password) > 128) {
            return 'Password is too long (max 128 characters).';
        }
        return null;
    }
}
