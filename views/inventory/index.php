<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-warehouse text-primary me-2"></i>Inventory Management</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Inventory</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal">
    <i class="fas fa-plus me-1"></i>Add Item
  </button>
</div>

<!-- Category Summary -->
<div class="row g-3 mb-4">
  <?php foreach (array_slice($summary, 0, 4) as $s): ?>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fw-bold text-primary"><?= e($s['name']) ?></div>
      <div class="text-muted small"><?= $s['items'] ?> items | <?= $s['qty'] ?> units</div>
      <div class="text-success small">Value: <?= formatMoney($s['value']) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search item name, code...">
      </div>
      <div class="col-md-3">
        <select name="category_id" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= selected($catId,$c['id']) ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('inventory') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Items Table -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr><th>Item</th><th>Code</th><th>Category</th><th>Qty</th><th>Unit</th><th>Condition</th><th>Location</th><th>Value</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
          <tr><td colspan="9" class="text-center py-5 text-muted">No inventory items found</td></tr>
          <?php else: foreach ($items as $item): ?>
          <tr>
            <td class="fw-semibold"><?= e($item['name']) ?></td>
            <td class="font-monospace text-muted small"><?= e($item['item_code']??'—') ?></td>
            <td><span class="badge bg-light text-dark"><?= e($item['category']??'—') ?></span></td>
            <td class="fw-bold <?= ($item['quantity']??0)<5 ? 'text-danger' : '' ?>"><?= $item['quantity'] ?></td>
            <td class="text-muted"><?= e($item['unit']) ?></td>
            <td><?= getStatusBadge($item['condition_status']) ?></td>
            <td class="text-muted"><?= e($item['location']??'—') ?></td>
            <td><?= $item['cost'] > 0 ? formatMoney($item['cost']) : '—' ?></td>
            <td>
              <button class="btn btn-xs btn-outline-primary" onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)"><i class="fas fa-edit"></i></button>
              <form action="<?= url('inventory/delete/'.$item['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                <?= csrfField() ?>
                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between">
    <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
    <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
  </div>
  <?php endif; ?>
</div>

<!-- Add/Edit Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="itemModalTitle">Add Inventory Item</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form id="itemForm" action="<?= url('inventory/create') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Item Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="itemName" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Item Code</label>
              <input type="text" name="item_code" id="itemCode" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">Category</label>
              <select name="category_id" id="itemCat" class="form-select">
                <option value="">Uncategorized</option>
                <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" id="itemQty" class="form-control" value="1" min="0">
            </div>
            <div class="col-md-3">
              <label class="form-label">Unit</label>
              <input type="text" name="unit" id="itemUnit" class="form-control" value="pcs">
            </div>
            <div class="col-md-3">
              <label class="form-label">Condition</label>
              <select name="condition_status" id="itemCond" class="form-select">
                <?php foreach (['excellent','good','fair','poor','damaged'] as $c): ?><option value="<?= $c ?>"><?= ucfirst($c) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Unit Cost (ETB)</label>
              <input type="number" name="cost" id="itemCost" class="form-control" value="0" step="0.01" min="0">
            </div>
            <div class="col-md-6">
              <label class="form-label">Location</label>
              <input type="text" name="location" id="itemLoc" class="form-control" placeholder="e.g. Computer Lab, Room 101">
            </div>
            <div class="col-md-3">
              <label class="form-label">Purchase Date</label>
              <input type="date" name="purchase_date" id="itemDate" class="form-control flatpickr">
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <input type="text" name="notes" id="itemNotes" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editItem(item) {
  document.getElementById('itemModalTitle').textContent = 'Edit Item';
  document.getElementById('itemForm').action = BASE_URL + '/inventory/edit/' + item.id;
  document.getElementById('itemName').value  = item.name;
  document.getElementById('itemCode').value  = item.item_code || '';
  document.getElementById('itemCat').value   = item.category_id || '';
  document.getElementById('itemQty').value   = item.quantity;
  document.getElementById('itemUnit').value  = item.unit;
  document.getElementById('itemCond').value  = item.condition_status;
  document.getElementById('itemCost').value  = item.cost;
  document.getElementById('itemLoc').value   = item.location || '';
  document.getElementById('itemNotes').value = item.notes || '';
  new bootstrap.Modal(document.getElementById('itemModal')).show();
}
</script>
