<?php

require_once ROOT . '/app/Core/Controller.php';

class CommunicationController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $this->redirect('communication/announcements');
    }

    public function announcements(): void {
        $this->requireAuth();
        $db   = getDB();
        $role = Auth::role();

        $where  = ["a.status = 'active'"];
        $params = [];

        if (!in_array($role, ['super_admin','principal','vice_principal'])) {
            $where[] = "(a.target_role = 'all' OR a.target_role = ?)";
            $params[] = $role;
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT a.*, u.username as author_name FROM announcements a JOIN users u ON a.author_id = u.id WHERE $whereStr ORDER BY a.priority DESC, a.created_at DESC");
        $stmt->execute($params);

        $canCreate = in_array($role, ['super_admin','principal','vice_principal','teacher']);

        $this->render('communication/announcements', [
            'title'         => 'Announcements',
            'announcements' => $stmt->fetchAll(),
            'canCreate'     => $canCreate,
        ]);
    }

    public function saveAnnouncement(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','teacher']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $data = [
            'title'       => $this->post('title', ''),
            'content'     => $this->post('content', ''),
            'target_role' => $this->post('target_role', 'all'),
            'author_id'   => Auth::id(),
            'start_date'  => $this->post('start_date', date('Y-m-d')),
            'end_date'    => $this->post('end_date', date('Y-m-d', strtotime('+30 days'))),
            'priority'    => $this->post('priority', 'normal'),
            'status'      => 'active',
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE announcements SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Announcement updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO announcements ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Announcement published.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('communication/announcements');
    }

    public function deleteAnnouncement(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("UPDATE announcements SET status='inactive' WHERE id=?")->execute([$id]);
        Flash::set('success', 'Announcement removed.');
        $this->redirect('communication/announcements');
    }

    public function messages(): void {
        $this->requireAuth();
        $db   = getDB();
        $userId = Auth::id();
        $folder = $this->get('folder', 'inbox');

        if ($folder === 'sent') {
            $stmt = $db->prepare("SELECT m.*, u.username as receiver_name FROM messages m JOIN users u ON m.receiver_id = u.id WHERE m.sender_id=? AND m.is_deleted_sender=0 ORDER BY m.created_at DESC");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT m.*, u.username as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id=? AND m.is_deleted_receiver=0 ORDER BY m.created_at DESC");
            $stmt->execute([$userId]);
        }

        $users = $db->query("SELECT id, username, role FROM users WHERE status='active' AND id != " . (int)$userId . " ORDER BY username")->fetchAll();

        $this->render('communication/messages', [
            'title'    => 'Messages',
            'messages' => $stmt->fetchAll(),
            'folder'   => $folder,
            'users'    => $users,
        ]);
    }

    public function sendMessage(): void {
        $this->requireAuth();
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'sender_id'   => Auth::id(),
            'receiver_id' => $this->post('receiver_id', ''),
            'subject'     => $this->post('subject', ''),
            'content'     => $this->post('content', ''),
        ];

        if (!$data['receiver_id'] || !$data['content']) {
            Flash::set('error', 'Recipient and message content are required.');
            $this->redirect('communication/messages');
            return;
        }

        try {
            $db->prepare("INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?,?,?,?)")->execute(array_values($data));

            // Send notification to receiver
            $db->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,?,?)")->execute([
                $data['receiver_id'],
                'New Message',
                'You have a new message from ' . (Auth::user()['username'] ?? 'someone'),
                'info',
                '/communication/messages',
            ]);

            Flash::set('success', 'Message sent.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed.');
        }
        $this->redirect('communication/messages');
    }

    public function viewMessage(string $id): void {
        $this->requireAuth();
        $db     = getDB();
        $userId = Auth::id();

        $stmt = $db->prepare("SELECT m.*, us.username as sender_name, ur.username as receiver_name FROM messages m JOIN users us ON m.sender_id = us.id JOIN users ur ON m.receiver_id = ur.id WHERE m.id=? AND (m.sender_id=? OR m.receiver_id=?)");
        $stmt->execute([$id, $userId, $userId]);
        $message = $stmt->fetch();

        if (!$message) { Flash::set('error', 'Message not found.'); $this->redirect('communication/messages'); return; }

        // Mark as read
        if ($message['receiver_id'] == $userId && !$message['read_at']) {
            $db->prepare("UPDATE messages SET read_at=NOW() WHERE id=?")->execute([$id]);
        }

        // Thread
        $thread = [];
        if ($message['parent_id']) {
            $thStmt = $db->prepare("SELECT m.*, u.username as sender_name FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.id=?");
            $thStmt->execute([$message['parent_id']]);
            $thread = [$thStmt->fetch()];
        }

        $this->render('communication/view-message', ['title' => $message['subject'] ?: 'Message', 'message' => $message, 'thread' => $thread]);
    }

    public function notifications(): void {
        $this->requireAuth();
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
        $stmt->execute([Auth::id()]);
        $this->render('communication/notifications', ['title' => 'Notifications', 'notifications' => $stmt->fetchAll()]);
    }

    public function markRead(string $id): void {
        $this->requireAuth();
        $db = getDB();
        $db->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$id, Auth::id()]);
        $this->json(['success' => true]);
    }

    public function markAllRead(): void {
        $this->requireAuth();
        $db = getDB();
        $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([Auth::id()]);
        Flash::set('success', 'All notifications marked as read.');
        $this->redirect('communication/notifications');
    }
}
