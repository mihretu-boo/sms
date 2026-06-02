<?php

require_once ROOT . '/app/Core/Controller.php';

class FinanceController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);
        $this->redirect('finance/payments');
    }

    public function fees(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);

        $db   = getDB();
        $cats = $db->query("SELECT * FROM fee_categories ORDER BY type, name")->fetchAll();

        $this->render('finance/fees', [
            'title'      => 'Fee Management',
            'categories' => $cats,
        ]);
    }

    public function saveFeeCategory(): void {
        $this->requireAuth(['super_admin','finance_officer']);
        $this->validateCsrf();

        $db   = getDB();
        $id   = $this->post('id', '');

        $data = [
            'name'         => $this->post('name', ''),
            'amount'       => $this->post('amount', 0),
            'type'         => $this->post('type', 'other'),
            'frequency'    => $this->post('frequency', 'annual'),
            'description'  => $this->post('description', ''),
            'is_mandatory' => $this->post('is_mandatory', 0),
        ];

        try {
            if ($id) {
                $sets = implode('=?,', array_keys($data)) . '=?';
                $vals = array_values($data);
                $vals[] = $id;
                $db->prepare("UPDATE fee_categories SET $sets WHERE id=?")->execute($vals);
                Flash::set('success', 'Fee category updated.');
            } else {
                $cols = implode(',', array_keys($data));
                $ph   = implode(',', array_fill(0, count($data), '?'));
                $db->prepare("INSERT INTO fee_categories ($cols) VALUES ($ph)")->execute(array_values($data));
                Flash::set('success', 'Fee category added.');
            }
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }

        $this->redirect('finance/fees');
    }

    public function assignFees(): void {
        $this->requireAuth(['super_admin','finance_officer']);
        $this->validateCsrf();

        $db   = getDB();
        $ayId = (int)getSetting('academic_year_id', 1);
        $catId= $this->post('fee_category_id', '');
        $dueDate = $this->post('due_date', '');

        if (!$catId || !$dueDate) {
            Flash::set('error', 'Fee category and due date are required.');
            $this->redirect('finance/fees');
            return;
        }

        $cat    = $db->prepare("SELECT * FROM fee_categories WHERE id = ?");
        $cat->execute([$catId]);
        $feeData = $cat->fetch();

        $students = $db->prepare("SELECT id FROM students WHERE status = 'active' AND academic_year_id = ?");
        $students->execute([$ayId]);
        $allStudents = $students->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        $stmt  = $db->prepare("INSERT IGNORE INTO student_fees (student_id, fee_category_id, academic_year_id, amount, due_date) VALUES (?,?,?,?,?)");
        foreach ($allStudents as $stuId) {
            $stmt->execute([$stuId, $catId, $ayId, $feeData['amount'], $dueDate]);
            $count++;
        }

        Flash::set('success', "Fee assigned to $count students.");
        $this->redirect('finance/fees');
    }

    public function payments(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);

        $db     = getDB();
        $search = $this->get('search', '');
        $date   = $this->get('date', '');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($search) {
            $where[]  = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR p.receipt_no LIKE ?)";
            $like = "%$search%";
            array_push($params, $like, $like, $like, $like);
        }
        if ($date) {
            $where[] = "p.payment_date = ?";
            $params[] = $date;
        }

        $whereStr  = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM payments p JOIN students s ON p.student_id = s.id WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit;
        $params[] = $offset;
        $stmt = $db->prepare("SELECT p.*, s.first_name, s.last_name, s.student_id as stud_no, fc.name as fee_name FROM payments p JOIN students s ON p.student_id = s.id LEFT JOIN student_fees sf ON p.student_fee_id = sf.id LEFT JOIN fee_categories fc ON sf.fee_category_id = fc.id WHERE $whereStr ORDER BY p.payment_date DESC, p.id DESC LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $monthTotal = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())");
        $monthTotal->execute();
        $todayTotal = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date = CURDATE()");
        $todayTotal->execute();

        $this->render('finance/payments', [
            'title'       => 'Payments',
            'payments'    => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'pages'       => ceil($total / $limit),
            'search'      => $search,
            'date'        => $date,
            'month_total' => (float)$monthTotal->fetchColumn(),
            'today_total' => (float)$todayTotal->fetchColumn(),
        ]);
    }

    public function createPayment(): void {
        $this->requireAuth(['super_admin','finance_officer']);

        $db      = getDB();
        $stuId   = $this->get('student_id', '');
        $student = null;
        $fees    = [];

        if ($stuId) {
            $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$stuId]);
            $student = $stmt->fetch();

            $fStmt = $db->prepare("SELECT sf.*, fc.name as fee_name FROM student_fees sf JOIN fee_categories fc ON sf.fee_category_id = fc.id WHERE sf.student_id = ? AND sf.status IN ('unpaid','partial') ORDER BY sf.due_date ASC");
            $fStmt->execute([$stuId]);
            $fees = $fStmt->fetchAll();
        }

        $this->render('finance/create-payment', [
            'title'   => 'Record Payment',
            'student' => $student,
            'fees'    => $fees,
            'stuId'   => $stuId,
        ]);
    }

    public function storePayment(): void {
        $this->requireAuth(['super_admin','finance_officer']);
        $this->validateCsrf();

        $db = getDB();
        $data = [
            'student_id'      => $this->post('student_id', ''),
            'student_fee_id'  => $this->post('student_fee_id', '') ?: null,
            'amount'          => (float)$this->post('amount', 0),
            'payment_date'    => $this->post('payment_date', date('Y-m-d')),
            'payment_method'  => $this->post('payment_method', 'cash'),
            'receipt_no'      => generateReceiptNo(),
            'transaction_ref' => $this->post('transaction_ref', ''),
            'notes'           => $this->post('notes', ''),
            'recorded_by'     => Auth::id(),
        ];

        if (!$data['student_id'] || $data['amount'] <= 0) {
            Flash::set('error', 'Student and valid amount are required.');
            $this->redirect('finance/payments/create?student_id=' . $data['student_id']);
            return;
        }

        try {
            $db->beginTransaction();

            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO payments ($cols) VALUES ($ph)")->execute(array_values($data));
            $payId = $db->lastInsertId();

            // Update fee status
            if ($data['student_fee_id']) {
                $fStmt = $db->prepare("SELECT amount FROM student_fees WHERE id = ?");
                $fStmt->execute([$data['student_fee_id']]);
                $feeAmount = (float)$fStmt->fetchColumn();

                $totalPaidStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE student_fee_id = ?");
                $totalPaidStmt->execute([$data['student_fee_id']]);
                $totalPaid = (float)$totalPaidStmt->fetchColumn();

                $status = $totalPaid >= $feeAmount ? 'paid' : 'partial';
                $db->prepare("UPDATE student_fees SET status = ? WHERE id = ?")->execute([$status, $data['student_fee_id']]);
            }

            $db->commit();
            Auth::audit('create_payment', 'finance', $payId);
            Flash::set('success', 'Payment recorded. Receipt: <strong>' . $data['receipt_no'] . '</strong>');
            $this->redirect('finance/payments/receipt/' . $payId);
        } catch (Exception $e) {
            $db->rollBack();
            Flash::set('error', 'Failed: ' . $e->getMessage());
            $this->redirect('finance/payments/create');
        }
    }

    public function receipt(string $id): void {
        $this->requireAuth(['super_admin','finance_officer','principal']);
        $db   = getDB();
        $stmt = $db->prepare("SELECT p.*, s.first_name, s.last_name, s.student_id as stud_no, s.class_id, c.grade, c.section, fc.name as fee_name, u.username as recorded_by_name FROM payments p JOIN students s ON p.student_id = s.id LEFT JOIN student_fees sf ON p.student_fee_id = sf.id LEFT JOIN fee_categories fc ON sf.fee_category_id = fc.id LEFT JOIN classes c ON s.class_id = c.id LEFT JOIN users u ON p.recorded_by = u.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $payment = $stmt->fetch();
        if (!$payment) { Flash::set('error', 'Receipt not found.'); $this->redirect('finance/payments'); return; }

        $this->render('finance/receipt', ['title' => 'Receipt', 'payment' => $payment], 'print');
    }

    public function expenses(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);

        $db     = getDB();
        $month  = $this->get('month', date('Y-m'));
        [$year, $mon] = explode('-', $month . '-01');

        $stmt = $db->prepare("SELECT e.*, u.username as recorded_by_name FROM expenses e LEFT JOIN users u ON e.recorded_by = u.id WHERE YEAR(e.expense_date) = ? AND MONTH(e.expense_date) = ? ORDER BY e.expense_date DESC");
        $stmt->execute([$year, $mon]);

        $totalStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE YEAR(expense_date) = ? AND MONTH(expense_date) = ? AND status = 'approved'");
        $totalStmt->execute([$year, $mon]);

        $cats = $db->query("SELECT DISTINCT category FROM expenses ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

        $this->render('finance/expenses', [
            'title'    => 'Expenses',
            'expenses' => $stmt->fetchAll(),
            'total'    => (float)$totalStmt->fetchColumn(),
            'month'    => $month,
            'cats'     => $cats,
        ]);
    }

    public function saveExpense(): void {
        $this->requireAuth(['super_admin','finance_officer']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'title'        => $this->post('title', ''),
            'category'     => $this->post('category', ''),
            'amount'       => (float)$this->post('amount', 0),
            'expense_date' => $this->post('expense_date', date('Y-m-d')),
            'description'  => $this->post('description', ''),
            'recorded_by'  => Auth::id(),
            'status'       => 'approved',
        ];

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO expenses ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Expense recorded.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }

        $this->redirect('finance/expenses');
    }

    public function payroll(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);

        $db    = getDB();
        $month = $this->get('month', date('m'));
        $year  = $this->get('year', date('Y'));

        $stmt = $db->prepare("SELECT pr.*, s.first_name, s.last_name, s.employee_id, s.position FROM payroll pr JOIN staff s ON pr.staff_id = s.id WHERE pr.month = ? AND pr.year = ? ORDER BY s.first_name");
        $stmt->execute([$month, $year]);

        $allStaff = $db->query("SELECT * FROM staff WHERE status = 'active' ORDER BY first_name")->fetchAll();

        $this->render('finance/payroll', [
            'title'    => 'Payroll',
            'payroll'  => $stmt->fetchAll(),
            'staff'    => $allStaff,
            'month'    => $month,
            'year'     => $year,
        ]);
    }

    public function processPayroll(): void {
        $this->requireAuth(['super_admin','finance_officer']);
        $this->validateCsrf();

        $db    = getDB();
        $month = $this->post('month', date('m'));
        $year  = $this->post('year', date('Y'));
        $staffIds = $_POST['staff_ids'] ?? [];

        if (empty($staffIds)) {
            Flash::set('error', 'Select at least one staff member.');
            $this->redirect('finance/payroll');
            return;
        }

        $count = 0;
        $stmt  = $db->prepare("INSERT IGNORE INTO payroll (staff_id, month, year, basic_salary, allowances, deductions, income_tax, pension, net_salary, status) VALUES (?,?,?,?,?,?,?,?,?,?)");

        foreach ($staffIds as $staffId) {
            $sStmt = $db->prepare("SELECT basic_salary FROM staff WHERE id = ?");
            $sStmt->execute([$staffId]);
            $staffData = $sStmt->fetch();
            if (!$staffData) continue;

            $basic     = (float)$staffData['basic_salary'];
            $allowance = $basic * 0.1;
            $pension   = $basic * 0.07;
            $tax       = $this->calcIncomeTax($basic);
            $net       = $basic + $allowance - $pension - $tax;

            $stmt->execute([$staffId, $month, $year, $basic, $allowance, 0, $tax, $pension, $net, 'pending']);
            $count++;
        }

        Flash::set('success', "Payroll generated for $count staff members.");
        $this->redirect('finance/payroll?month=' . $month . '&year=' . $year);
    }

    private function calcIncomeTax(float $salary): float {
        // Ethiopian income tax brackets
        if ($salary <= 600)  return 0;
        if ($salary <= 1650) return ($salary - 600) * 0.10;
        if ($salary <= 3200) return 105 + ($salary - 1650) * 0.15;
        if ($salary <= 5250) return 337.50 + ($salary - 3200) * 0.20;
        if ($salary <= 7800) return 747.50 + ($salary - 5250) * 0.25;
        if ($salary <= 10900)return 1385 + ($salary - 7800) * 0.30;
        return 2315 + ($salary - 10900) * 0.35;
    }

    public function reports(): void {
        $this->requireAuth(['super_admin','principal','finance_officer']);

        $db   = getDB();
        $year = $this->get('year', date('Y'));

        $monthlyChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $mS = "$year-" . str_pad($m, 2, '0', STR_PAD_LEFT) . '-01';
            $mE = date('Y-m-t', strtotime($mS));
            $cStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_date BETWEEN ? AND ?");
            $cStmt->execute([$mS, $mE]);
            $eStmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE expense_date BETWEEN ? AND ? AND status='approved'");
            $eStmt->execute([$mS, $mE]);
            $monthlyChart[] = [
                'month'    => date('M', strtotime($mS)),
                'income'   => (float)$cStmt->fetchColumn(),
                'expenses' => (float)$eStmt->fetchColumn(),
            ];
        }

        $feeByCategory = $db->query("SELECT fc.name, SUM(p.amount) as total FROM payments p LEFT JOIN student_fees sf ON p.student_fee_id = sf.id LEFT JOIN fee_categories fc ON sf.fee_category_id = fc.id GROUP BY fc.id ORDER BY total DESC")->fetchAll();

        $this->render('finance/reports', [
            'title'          => 'Financial Reports',
            'monthly_chart'  => $monthlyChart,
            'fee_by_category'=> $feeByCategory,
            'year'           => $year,
        ]);
    }
}
