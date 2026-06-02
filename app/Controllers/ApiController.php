<?php

require_once ROOT . '/app/Core/Controller.php';

class ApiController extends Controller {

    public function students(): void {
        $this->requireAuth();
        $db     = getDB();
        $search = $this->get('q', '');
        $classId= $this->get('class_id', '');
        $params = [];

        $where = ["s.status='active'"];
        if ($search) {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like);
        }
        if ($classId) { $where[] = "s.class_id=?"; $params[] = $classId; }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT s.id, s.student_id, s.first_name, s.last_name, s.photo, c.grade, c.section FROM students s LEFT JOIN classes c ON s.class_id=c.id WHERE $whereStr LIMIT 30");
        $stmt->execute($params);
        $this->json($stmt->fetchAll());
    }

    public function staff(): void {
        $this->requireAuth();
        $db     = getDB();
        $search = $this->get('q', '');
        $params = ["status='active'"];

        $where = ["s.status='active'"];
        if ($search) {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.employee_id LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like);
            $params = array_slice($params, 1);
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT s.id, s.employee_id, s.first_name, s.last_name, s.position, s.photo FROM staff s WHERE $whereStr LIMIT 30");
        $stmt->execute(empty($search) ? [] : [$like, $like, $like]);
        $this->json($stmt->fetchAll());
    }

    public function classes(): void {
        $this->requireAuth();
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $stmt = $db->prepare("SELECT id, grade, section, CONCAT('Grade ',grade,'-',section) as name FROM classes WHERE academic_year_id=? ORDER BY grade, section");
        $stmt->execute([$ayId]);
        $this->json($stmt->fetchAll());
    }

    public function subjects(): void {
        $this->requireAuth();
        $db      = getDB();
        $classId = $this->get('class_id', '');
        $semId   = $this->get('semester_id', getSetting('semester_id', 1));

        if ($classId) {
            $stmt = $db->prepare("SELECT s.id, s.code, s.name FROM class_subjects cs JOIN subjects s ON cs.subject_id=s.id WHERE cs.class_id=? AND cs.semester_id=? ORDER BY s.name");
            $stmt->execute([$classId, $semId]);
        } else {
            $stmt = $db->query("SELECT id, code, name FROM subjects ORDER BY name");
        }
        $this->json($stmt->fetchAll());
    }

    public function notifications(): void {
        $this->requireAuth();
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, title, message, type, is_read, created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([Auth::id()]);
        $this->json(['notifications' => $stmt->fetchAll(), 'unread' => count(getUnreadNotifications())]);
    }

    public function markNotifRead(): void {
        $this->requireAuth();
        $id = $this->post('id', '');
        if ($id) {
            $db = getDB();
            $db->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$id, Auth::id()]);
        }
        $this->json(['success' => true]);
    }
}
