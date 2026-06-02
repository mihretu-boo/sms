<?php

require_once ROOT . '/app/Core/Controller.php';

class TransportController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $db = getDB();

        $vehicles = $db->query("SELECT * FROM vehicles ORDER BY plate_no")->fetchAll();
        $routes   = $db->query("SELECT r.*, v.plate_no, v.driver_name FROM transport_routes r LEFT JOIN vehicles v ON r.vehicle_id=v.id ORDER BY r.name")->fetchAll();

        $this->render('transport/index', [
            'title'    => 'Transport Management',
            'vehicles' => $vehicles,
            'routes'   => $routes,
        ]);
    }

    public function saveVehicle(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $data = [
            'plate_no'     => $this->post('plate_no', ''),
            'type'         => $this->post('type', 'bus'),
            'capacity'     => $this->post('capacity', 40),
            'driver_name'  => $this->post('driver_name', ''),
            'driver_phone' => $this->post('driver_phone', ''),
            'model'        => $this->post('model', ''),
            'status'       => $this->post('status', 'active'),
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE vehicles SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Vehicle updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO vehicles ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Vehicle added.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('transport');
    }

    public function saveRoute(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');
        $data = [
            'name'         => $this->post('name', ''),
            'stops'        => $this->post('stops', ''),
            'vehicle_id'   => $this->post('vehicle_id', '') ?: null,
            'morning_time' => $this->post('morning_time', ''),
            'evening_time' => $this->post('evening_time', ''),
            'monthly_fee'  => (float)$this->post('monthly_fee', 0),
            'status'       => $this->post('status', 'active'),
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data); $vals[] = $id;
                $db->prepare("UPDATE transport_routes SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Route updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO transport_routes ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Route added.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('transport');
    }
}
