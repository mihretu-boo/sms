<?php

require_once ROOT . '/app/Core/Controller.php';

class HostelController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar']);
        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);

        $hostels = $db->query("SELECT h.*, COUNT(hr.id) as room_count, SUM(hr.current_occupancy) as current_students, SUM(hr.capacity) as total_capacity FROM hostels h LEFT JOIN hostel_rooms hr ON hr.hostel_id=h.id GROUP BY h.id ORDER BY h.name")->fetchAll();
        $allocations = $db->prepare("SELECT ha.*, s.first_name, s.last_name, s.student_id, hr.room_number, hs.name as hostel_name FROM hostel_allocations ha JOIN students s ON ha.student_id=s.id JOIN hostel_rooms hr ON ha.room_id=hr.id JOIN hostels hs ON hr.hostel_id=hs.id WHERE ha.academic_year_id=? AND ha.status='active' ORDER BY s.first_name");
        $allocations->execute([$ayId]);

        $rooms     = $db->query("SELECT hr.*, h.name as hostel_name FROM hostel_rooms hr JOIN hostels h ON hr.hostel_id=h.id WHERE hr.status='available' ORDER BY h.name, hr.room_number")->fetchAll();
        $students  = $db->prepare("SELECT s.id, s.first_name, s.last_name, s.student_id FROM students s WHERE s.status='active' AND s.academic_year_id=? AND s.id NOT IN (SELECT student_id FROM hostel_allocations WHERE status='active' AND academic_year_id=?) ORDER BY s.first_name");
        $students->execute([$ayId, $ayId]);

        $this->render('hostel/index', [
            'title'       => 'Hostel Management',
            'hostels'     => $hostels,
            'allocations' => $allocations->fetchAll(),
            'rooms'       => $rooms,
            'students'    => $students->fetchAll(),
        ]);
    }

    public function allocate(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $data = [
            'student_id'       => $this->post('student_id', ''),
            'room_id'          => $this->post('room_id', ''),
            'academic_year_id' => $ayId,
            'check_in'         => $this->post('check_in', date('Y-m-d')),
            'fee'              => (float)$this->post('fee', 0),
            'status'           => 'active',
        ];

        try {
            $db->beginTransaction();
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO hostel_allocations ($cols) VALUES ($ph)")->execute(array_values($data));
            $db->prepare("UPDATE hostel_rooms SET current_occupancy = current_occupancy + 1 WHERE id=?")->execute([$data['room_id']]);
            // Update room status if full
            $db->prepare("UPDATE hostel_rooms SET status = CASE WHEN current_occupancy >= capacity THEN 'full' ELSE 'available' END WHERE id=?")->execute([$data['room_id']]);
            $db->commit();
            Flash::set('success', 'Student allocated to hostel.');
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('hostel');
    }

    public function vacate(string $id): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM hostel_allocations WHERE id=?");
        $stmt->execute([$id]);
        $alloc = $stmt->fetch();

        if ($alloc) {
            $db->prepare("UPDATE hostel_allocations SET status='vacated', check_out=CURDATE() WHERE id=?")->execute([$id]);
            $db->prepare("UPDATE hostel_rooms SET current_occupancy = GREATEST(0, current_occupancy - 1), status='available' WHERE id=?")->execute([$alloc['room_id']]);
            Flash::set('success', 'Student vacated.');
        } else {
            Flash::set('error', 'Record not found.');
        }
        $this->redirect('hostel');
    }
}
