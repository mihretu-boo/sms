<?php

require_once ROOT . '/app/Core/Controller.php';

class DisciplineController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','teacher','registrar']);

        $db     = getDB();
        $search = $this->get('search', '');
        $status = $this->get('status', '');
        $severity = $this->get('severity', '');

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR di.incident_type LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like);
        }
        if ($status)   { $where[] = "di.status=?"; $params[] = $status; }
        if ($severity) { $where[] = "di.severity=?"; $params[] = $severity; }

        $whereStr = implode(' AND ', $where);
        $stmt = $db->prepare("SELECT di.*, s.first_name, s.last_name, s.student_id as stud_no, c.grade, c.section, u.username as reporter FROM discipline_incidents di JOIN students s ON di.student_id=s.id LEFT JOIN classes c ON s.class_id=c.id LEFT JOIN users u ON di.reported_by=u.id WHERE $whereStr ORDER BY di.incident_date DESC");
        $stmt->execute($params);

        $students = $db->query("SELECT id, first_name, last_name, student_id FROM students WHERE status='active' ORDER BY first_name")->fetchAll();

        $summary = $db->query("SELECT severity, COUNT(*) as count FROM discipline_incidents GROUP BY severity")->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->render('discipline/index', [
            'title'     => 'Discipline Management',
            'incidents' => $stmt->fetchAll(),
            'students'  => $students,
            'status'    => $status,
            'severity'  => $severity,
            'summary'   => $summary,
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','teacher','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'student_id'     => $this->post('student_id', ''),
            'reported_by'    => Auth::id(),
            'incident_date'  => $this->post('incident_date', date('Y-m-d')),
            'incident_type'  => $this->post('incident_type', ''),
            'description'    => $this->post('description', ''),
            'action_taken'   => $this->post('action_taken', ''),
            'parent_notified'=> $this->post('parent_notified', 0),
            'severity'       => $this->post('severity', 'minor'),
            'status'         => 'open',
        ];

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO discipline_incidents ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Incident recorded.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('discipline');
    }

    public function view(string $id): void {
        $this->requireAuth(['super_admin','principal','vice_principal','teacher']);
        $db   = getDB();
        $stmt = $db->prepare("SELECT di.*, s.first_name, s.last_name, s.student_id as stud_no, c.grade, c.section, u.username as reporter FROM discipline_incidents di JOIN students s ON di.student_id=s.id LEFT JOIN classes c ON s.class_id=c.id LEFT JOIN users u ON di.reported_by=u.id WHERE di.id=?");
        $stmt->execute([$id]);
        $incident = $stmt->fetch();
        if (!$incident) { Flash::set('error', 'Not found.'); $this->redirect('discipline'); return; }
        $this->render('discipline/view', ['title' => 'Incident Detail', 'incident' => $incident]);
    }

    public function resolve(string $id): void {
        $this->requireAuth(['super_admin','principal','vice_principal']);
        $this->validateCsrf();

        $db = getDB();
        $resolution = $this->post('resolution', '');
        $db->prepare("UPDATE discipline_incidents SET status='resolved', action_taken=CONCAT(COALESCE(action_taken,''), '\nResolution: ', ?) WHERE id=?")->execute([$resolution, $id]);
        Flash::set('success', 'Incident resolved.');
        $this->redirect('discipline');
    }
}
