<?php

require_once ROOT . '/app/Core/Controller.php';

class AuthController extends Controller {

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
            }

            $role = Auth::role();
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

    public function forgotPassword(): void {
        $this->requireGuest();
        $this->render('auth/forgot-password', ['title' => 'Forgot Password'], 'auth');
    }

    public function processForgotPassword(): void {
        $email = $this->post('email', '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('error', 'Please enter a valid email address.');
            $this->redirect('forgot-password');
            return;
        }

        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $token    = bin2hex(random_bytes(32));
                $expires  = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt2    = $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?,?,?)");
                $stmt2->execute([$email, $token, $expires]);
                // In production, send email with reset link
            }
        } catch (Exception $e) {}

        Flash::set('success', 'If that email is registered, a password reset link has been sent.');
        $this->redirect('login');
    }

    public function resetPassword(): void {
        $token = $this->get('token', '');
        $this->render('auth/reset-password', ['title' => 'Reset Password', 'token' => $token], 'auth');
    }

    public function processReset(): void {
        $token    = $this->post('token', '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 8) {
            Flash::set('error', 'Password must be at least 8 characters.');
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirm) {
            Flash::set('error', 'Passwords do not match.');
            $this->redirect('reset-password?token=' . urlencode($token));
            return;
        }

        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0 LIMIT 1");
            $stmt->execute([$token]);
            $reset = $stmt->fetch();

            if (!$reset) {
                Flash::set('error', 'Invalid or expired reset token.');
                $this->redirect('forgot-password');
                return;
            }

            $hash  = password_hash($password, PASSWORD_BCRYPT);
            $stmt2 = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt2->execute([$hash, $reset['email']]);

            $stmt3 = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt3->execute([$token]);

            Flash::set('success', 'Password reset successfully. Please login.');
            $this->redirect('login');
        } catch (Exception $e) {
            Flash::set('error', 'An error occurred. Please try again.');
            $this->redirect('forgot-password');
        }
    }
}
