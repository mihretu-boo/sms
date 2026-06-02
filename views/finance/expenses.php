<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-receipt text-primary me-2"></i>Expenses</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('finance') ?>">Finance</a></li><li class="breadcrumb-item active">Expenses</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#expModal"><i class="fas fa-plus me-1"></i>Record Expense</button>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/fees') ?>">Fee Categories</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payments') ?>">Payments</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('finance/expenses') ?>">Expenses</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payroll') ?>">Payroll</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/reports') ?>">Reports</a></li>
</ul>

<!-- Month filter + total -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3"><input type="month" name="month" class="form-control" value="<?= e($month) ?>"></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary">Filter</button></div>
      <div class="col-md-4 ms-auto text-end"><span class="fw-bold">Month Total:</span> <span class="text-danger fw-bold fs-5"><?= formatMoney($total) ?></span></div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light"><tr><th>Date</th><th>Title</th><th>Category</th><th>Amount</th><th>Recorded By</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($expenses)): ?>
          <tr><td colspan="6" class="text-center py-5 text-muted">No expenses recorded</td></tr>
          <?php else: foreach ($expenses as $e): ?>
          <tr>
            <td><?= formatDate($e['expense_date']) ?></td>
            <td class="fw-semibold"><?= e($e['title']) ?></td>
            <td><span class="badge bg-light text-dark"><?= e($e['category']) ?></span></td>
            <td class="fw-bold text-danger"><?= formatMoney($e['amount']) ?></td>
            <td class="text-muted small"><?= e($e['recorded_by_name']??'—') ?></td>
            <td><?= getStatusBadge($e['status']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Record Expense Modal -->
<div class="modal fade" id="expModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white"><h6 class="modal-title"><i class="fas fa-receipt me-2"></i>Record Expense</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('finance/expenses') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <input type="text" name="category" class="form-control" placeholder="e.g. Office Supplies" list="catList">
              <datalist id="catList"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
            </div>
            <div class="col-md-6"><label class="form-label">Amount (ETB) <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" min="0.01" step="0.01" required></div>
            <div class="col-md-6"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control flatpickr" value="<?= date('Y-m-d') ?>"></div>
          </div>
          <div class="mt-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>Record</button></div>
      </form>
    </div>
  </div>
</div>
