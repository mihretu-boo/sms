<?php

require_once ROOT . '/app/Core/Controller.php';

class ProfileController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $db   = getDB();
        $userId = Auth::id();

        $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $linked = Auth::getLinkedRecord();

        $this->render('profile/index', [
            'title'  => 'My Profile',
            'user'   => $user,
            'linked' => $linked,
        ]);
    }

    public function update(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $db     = getDB();
        $userId = Auth::id();

        $data = [
            'phone' => $this->post('phone', ''),
            'lang'  => $this->post('lang', 'en'),
        ];

        try {
            $sets = implode('=?,', array_keys($data)) . '=?';
            $vals = array_values($data); $vals[] = $userId;
            $db->prepare("UPDATE users SET $sets WHERE id=?")->execute($vals);

            // Update session
            $_SESSION['user']['phone'] = $data['phone'];
            $_SESSION['user']['lang']  = $data['lang'];

            Flash::set('success', 'Profile updated.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('profile');
    }

    public function changePassword(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $db      = getDB();
        $userId  = Auth::id();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $db->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!password_verify($current, $user['password'])) {
            Flash::set('error', 'Current password is incorrect.');
            $this->redirect('profile');
            return;
        }

        if (strlen($new) < 8) {
            Flash::set('error', 'New password must be at least 8 characters.');
            $this->redirect('profile');
            return;
        }

        if ($new !== $confirm) {
            Flash::set('error', 'Passwords do not match.');
            $this->redirect('profile');
            return;
        }

        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_BCRYPT), $userId]);
        Auth::audit('change_password', 'profile');
        Flash::set('success', 'Password changed successfully.');
        $this->redirect('profile');
    }

    public function uploadPhoto(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $photo = $this->uploadFile('photo', 'avatars', ALLOWED_IMAGE_TYPES);
        if ($photo) {
            $db = getDB();
            $db->prepare("UPDATE users SET photo=? WHERE id=?")->execute([$photo, Auth::id()]);
            $_SESSION['user']['photo'] = $photo;
            Flash::set('success', 'Photo updated.');
        } else {
            Flash::set('error', 'Invalid photo. Use JPEG, PNG, GIF, or WebP.');
        }
        $this->redirect('profile');
    }
}
