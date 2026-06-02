<?php

require_once ROOT . '/app/Core/Controller.php';

class ClubController extends Controller {

    public function index(): void {
        $this->requireAuth();
        $db   = getDB();
        $stmt = $db->query("SELECT cl.*, st.first_name, st.last_name, COUNT(cm.id) as member_count FROM clubs cl LEFT JOIN staff st ON cl.supervisor_id=st.id LEFT JOIN club_members cm ON cm.club_id=cl.id AND cm.status='active' GROUP BY cl.id ORDER BY cl.name");

        $supervisors = $db->query("SELECT id, first_name, last_name FROM staff WHERE status='active' ORDER BY first_name")->fetchAll();
        $students    = $db->query("SELECT id, first_name, last_name, student_id FROM students WHERE status='active' ORDER BY first_name")->fetchAll();

        $this->render('clubs/index', [
            'title'       => 'Clubs',
            'clubs'       => $stmt->fetchAll(),
            'supervisors' => $supervisors,
            'students'    => $students,
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal','vice_principal']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'name'             => $this->post('name', ''),
            'code'             => $this->post('code', ''),
            'description'      => $this->post('description', ''),
            'supervisor_id'    => $this->post('supervisor_id', '') ?: null,
            'meeting_schedule' => $this->post('meeting_schedule', ''),
            'status'           => 'active',
        ];

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO clubs ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Club created.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('clubs');
    }

    public function view(string $id): void {
        $this->requireAuth();
        $db   = getDB();
        $stmt = $db->prepare("SELECT cl.*, st.first_name as sup_first, st.last_name as sup_last FROM clubs cl LEFT JOIN staff st ON cl.supervisor_id=st.id WHERE cl.id=?");
        $stmt->execute([$id]);
        $club = $stmt->fetch();
        if (!$club) { Flash::set('error', 'Club not found.'); $this->redirect('clubs'); return; }

        $members = $db->prepare("SELECT cm.*, s.first_name, s.last_name, s.student_id FROM club_members cm JOIN students s ON cm.student_id=s.id WHERE cm.club_id=? AND cm.status='active' ORDER BY cm.role, s.first_name");
        $members->execute([$id]);

        $this->render('clubs/view', ['title' => $club['name'], 'club' => $club, 'members' => $members->fetchAll()]);
    }

    public function enroll(): void {
        $this->requireAuth(['super_admin','principal','teacher']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'club_id'    => $this->post('club_id', ''),
            'student_id' => $this->post('student_id', ''),
            'role'       => 'member',
            'joined_date'=> date('Y-m-d'),
            'status'     => 'active',
        ];

        try {
            $db->prepare("INSERT IGNORE INTO club_members (club_id, student_id, role, joined_date, status) VALUES (?,?,?,?,?)")->execute(array_values($data));
            Flash::set('success', 'Student enrolled in club.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed.');
        }
        $this->redirect('clubs/view/' . $data['club_id']);
    }
}
