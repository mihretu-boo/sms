<?php

require_once ROOT . '/app/Core/Controller.php';

class InventoryController extends Controller {

    public function index(): void {
        $this->requireAuth(['super_admin','principal','vice_principal','registrar']);

        $db     = getDB();
        $search = $this->get('search', '');
        $catId  = $this->get('category_id', '');
        $page   = max(1, (int)$this->get('page', 1));
        $limit  = PER_PAGE;
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];
        if ($search) { $where[] = "(ii.name LIKE ? OR ii.item_code LIKE ?)"; $like = "%$search%"; array_push($params, $like, $like); }
        if ($catId)  { $where[] = "ii.category_id=?"; $params[] = $catId; }

        $whereStr  = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM inventory_items ii WHERE $whereStr");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $params[] = $limit; $params[] = $offset;
        $stmt = $db->prepare("SELECT ii.*, ic.name as category FROM inventory_items ii LEFT JOIN inventory_categories ic ON ii.category_id=ic.id WHERE $whereStr ORDER BY ii.name LIMIT ? OFFSET ?");
        $stmt->execute($params);

        $cats = $db->query("SELECT * FROM inventory_categories ORDER BY name")->fetchAll();
        $summary = $db->query("SELECT ic.name, COUNT(ii.id) as items, SUM(ii.quantity) as qty, SUM(ii.quantity*ii.cost) as value FROM inventory_categories ic LEFT JOIN inventory_items ii ON ii.category_id=ic.id GROUP BY ic.id")->fetchAll();

        $this->render('inventory/index', [
            'title'   => 'Inventory',
            'items'   => $stmt->fetchAll(),
            'cats'    => $cats,
            'summary' => $summary,
            'total'   => $total,
            'page'    => $page,
            'pages'   => ceil($total / $limit),
            'catId'   => $catId,
            'search'  => $search,
        ]);
    }

    public function store(): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'category_id'      => $this->post('category_id', '') ?: null,
            'name'             => $this->post('name', ''),
            'item_code'        => $this->post('item_code', '') ?: null,
            'quantity'         => (int)$this->post('quantity', 0),
            'unit'             => $this->post('unit', 'pcs'),
            'condition_status' => $this->post('condition_status', 'good'),
            'location'         => $this->post('location', ''),
            'purchase_date'    => $this->post('purchase_date', '') ?: null,
            'cost'             => (float)$this->post('cost', 0),
            'supplier'         => $this->post('supplier', ''),
            'notes'            => $this->post('notes', ''),
        ];

        try {
            $cols = implode(',', array_keys($data));
            $ph   = implode(',', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO inventory_items ($cols) VALUES ($ph)")->execute(array_values($data));
            Flash::set('success', 'Item added.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('inventory');
    }

    public function update(string $id): void {
        $this->requireAuth(['super_admin','principal']);
        $this->validateCsrf();

        $db   = getDB();
        $data = [
            'name'             => $this->post('name', ''),
            'quantity'         => (int)$this->post('quantity', 0),
            'condition_status' => $this->post('condition_status', 'good'),
            'location'         => $this->post('location', ''),
            'notes'            => $this->post('notes', ''),
        ];

        try {
            $sets = implode('=?,', array_keys($data)) . '=?';
            $vals = array_values($data); $vals[] = $id;
            $db->prepare("UPDATE inventory_items SET $sets WHERE id=?")->execute($vals);
            Flash::set('success', 'Item updated.');
        } catch (Exception $e) {
            Flash::set('error', 'Failed.');
        }
        $this->redirect('inventory');
    }

    public function delete(string $id): void {
        $this->requireAuth(['super_admin']);
        $this->validateCsrf();
        $db = getDB();
        $db->prepare("DELETE FROM inventory_items WHERE id=?")->execute([$id]);
        Flash::set('success', 'Item deleted.');
        $this->redirect('inventory');
    }

    public function categories(): void {
        $this->requireAuth(['super_admin','principal']);
        $db = getDB();

        // Handle POST (add category)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = [
                'name'        => $this->post('name', ''),
                'description' => $this->post('description', ''),
            ];
            if (!empty($data['name'])) {
                try {
                    $db->prepare("INSERT INTO inventory_categories (name, description) VALUES (?,?)")->execute(array_values($data));
                    Flash::set('success', 'Category added.');
                } catch (\Exception $e) {
                    Flash::set('error', 'Failed: ' . $e->getMessage());
                }
            }
            $this->redirect('inventory/categories');
            return;
        }

        $stmt = $db->query("SELECT ic.*, COUNT(ii.id) as item_count FROM inventory_categories ic LEFT JOIN inventory_items ii ON ii.category_id=ic.id GROUP BY ic.id ORDER BY ic.name");
        $this->render('inventory/categories', ['title' => 'Inventory Categories', 'categories' => $stmt->fetchAll()]);
    }
}
