<?php

require_once ROOT . '/app/Core/Controller.php';

class TimetableController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $db      = getDB();
        $ayId    = (int)getSetting('academic_year_id', 1);
        $semId   = (int)getSetting('semester_id', 1);
        $role    = Auth::role();
        $classId = $this->get('class_id', '');

        // Determine class for students/teachers
        if ($role === 'student' && !$classId) {
            $stuStmt = $db->prepare("SELECT class_id FROM students WHERE user_id=? LIMIT 1");
            $stuStmt->execute([Auth::id()]);
            $stu = $stuStmt->fetch();
            $classId = $stu ? $stu['class_id'] : '';
        } elseif ($role === 'teacher' && !$classId) {
            $stfStmt = $db->prepare("SELECT id FROM staff WHERE user_id=? LIMIT 1");
            $stfStmt->execute([Auth::id()]);
            $stf = $stfStmt->fetch();
            if ($stf) {
                $clsStmt = $db->prepare("SELECT DISTINCT class_id FROM class_subjects WHERE teacher_id=? AND semester_id=? LIMIT 1");
                $clsStmt->execute([$stf['id'], $semId]);
                $cls = $clsStmt->fetch();
                $classId = $cls ? $cls['class_id'] : '';
            }
        }

        $classes = $db->prepare("SELECT * FROM classes WHERE academic_year_id=? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $timetable = [];
        if ($classId) {
            $stmt = $db->prepare("SELECT tt.*, s.name as subject_name, s.code, st.first_name, st.last_name FROM timetable tt JOIN subjects s ON tt.subject_id=s.id JOIN staff st ON tt.teacher_id=st.id WHERE tt.class_id=? AND tt.semester_id=? ORDER BY FIELD(tt.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), tt.period");
            $stmt->execute([$classId, $semId]);
            foreach ($stmt->fetchAll() as $row) {
                $timetable[$row['day']][$row['period']] = $row;
            }
        }

        $periods = [
            1 => ['start' => '08:00', 'end' => '08:45'],
            2 => ['start' => '08:45', 'end' => '09:30'],
            3 => ['start' => '09:45', 'end' => '10:30'],
            4 => ['start' => '10:30', 'end' => '11:15'],
            5 => ['start' => '11:30', 'end' => '12:15'],
            6 => ['start' => '13:00', 'end' => '13:45'],
            7 => ['start' => '13:45', 'end' => '14:30'],
            8 => ['start' => '14:30', 'end' => '15:15'],
        ];

        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];

        $this->render('timetable/index', [
            'title'     => 'Timetable',
            'classes'   => $classes->fetchAll(),
            'timetable' => $timetable,
            'classId'   => $classId,
            'days'      => $days,
            'periods'   => $periods,
        ]);
    }

    public function create(): void {
        $this->requireAuth(['super_admin','principal','registrar']);

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $semId= (int)getSetting('semester_id', 1);
        $classId = $this->get('class_id', '');

        $classes  = $db->prepare("SELECT * FROM classes WHERE academic_year_id=? ORDER BY grade, section");
        $classes->execute([$ayId]);

        $subjects = [];
        $teachers = [];
        if ($classId) {
            $subStmt = $db->prepare("SELECT cs.*, s.name as subject_name, s.code, st.first_name, st.last_name FROM class_subjects cs JOIN subjects s ON cs.subject_id=s.id LEFT JOIN staff st ON cs.teacher_id=st.id WHERE cs.class_id=? AND cs.semester_id=?");
            $subStmt->execute([$classId, $semId]);
            $subjects = $subStmt->fetchAll();
            $teachers = $db->query("SELECT id, first_name, last_name FROM staff WHERE status='active' ORDER BY first_name")->fetchAll();
        }

        $this->render('timetable/create', [
            'title'    => 'Manage Timetable',
            'classes'  => $classes->fetchAll(),
            'subjects' => $subjects,
            'teachers' => $teachers,
            'classId'  => $classId,
            'semId'    => $semId,
        ]);
    }

    public function save(): void {
        $this->requireAuth(['super_admin','principal','registrar']);
        $this->validateCsrf();

        $db      = getDB();
        $classId = $this->post('class_id', '');
        $semId   = $this->post('semester_id', (int)getSetting('semester_id', 1));
        $entries = $_POST['timetable'] ?? [];

        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM timetable WHERE class_id=? AND semester_id=?")->execute([$classId, $semId]);

            $stmt = $db->prepare("INSERT INTO timetable (class_id, subject_id, teacher_id, day, period, start_time, end_time, room, semester_id) VALUES (?,?,?,?,?,?,?,?,?)");
            foreach ($entries as $day => $periods) {
                foreach ($periods as $period => $entry) {
                    if (empty($entry['subject_id'])) continue;
                    $stmt->execute([$classId, $entry['subject_id'], $entry['teacher_id'], $day, $period, $entry['start_time'], $entry['end_time'], $entry['room'] ?? '', $semId]);
                }
            }
            $db->commit();
            Flash::set('success', 'Timetable saved.');
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('timetable?class_id=' . $classId);
    }

    public function delete(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM timetable WHERE id=?")->execute([$id]);
        Flash::set('success', 'Slot removed.');
        $this->redirectBack();
    }
}
