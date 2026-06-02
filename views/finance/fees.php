<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Fee Management</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Fees</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
      <i class="fas fa-users me-1"></i>Assign to All
    </button>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#feeModal">
      <i class="fas fa-plus me-1"></i>Add Fee Category
    </button>
  </div>
</div>

<!-- Finance Nav Tabs -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link active" href="<?= url('finance/fees') ?>">Fee Categories</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payments') ?>">Payments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/expenses') ?>">Expenses</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payroll') ?>">Payroll</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/reports') ?>">Reports</a></li>
</ul>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr><th>#</th><th>Fee Name</th><th>Type</th><th>Frequency</th><th>Amount (ETB)</th><th>Mandatory</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php $n=1; foreach ($categories as $c): ?>
          <tr>
            <td><?= $n++ ?></td>
            <td class="fw-semibold"><?= e($c['name']) ?></td>
            <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$c['type'])) ?></span></td>
            <td><?= ucfirst(str_replace('_',' ',$c['frequency'])) ?></td>
            <td class="fw-bold"><?= $c['amount'] > 0 ? formatMoney($c['amount']) : '<span class="text-muted">Free</span>' ?></td>
            <td><?= $c['is_mandatory'] ? '<span class="badge bg-danger">Mandatory</span>' : '<span class="badge bg-secondary">Optional</span>' ?></td>
            <td>
              <button class="btn btn-xs btn-outline-primary" onclick="editFee(<?= htmlspecialchars(json_encode($c)) ?>)"><i class="fas fa-edit"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add/Edit Fee Modal -->
<div class="modal fade" id="feeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="feeModalTitle">Add Fee Category</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('finance/fees') ?>" method="POST" id="feeForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="feeId">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Fee Name <span class="text-danger">*</span></label><input type="text" name="name" id="feeName" class="form-control" required></div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Type</label><select name="type" id="feeType" class="form-select"><?php foreach (['tuition','registration','exam','library','hostel','transport','uniform','other'] as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">Frequency</label><select name="frequency" id="feeFreq" class="form-select"><?php foreach (['one_time','monthly','semester','annual'] as $f): ?><option value="<?= $f ?>"><?= ucfirst(str_replace('_',' ',$f)) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">Amount (ETB)</label><input type="number" name="amount" id="feeAmount" class="form-control" min="0" step="0.01" value="0"></div>
            <div class="col-md-6"><label class="form-label">Mandatory?</label><select name="is_mandatory" id="feeMandatory" class="form-select"><option value="1">Yes</option><option value="0">No</option></select></div>
          </div>
          <div class="mt-3"><label class="form-label">Description</label><textarea name="description" id="feeDesc" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Assign Fees Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white"><h6 class="modal-title">Assign Fee to All Active Students</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('finance/fees/assign') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Fee Category <span class="text-danger">*</span></label><select name="fee_category_id" class="form-select" required><option value="">Select fee category</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= formatMoney($c['amount']) ?>)</option><?php endforeach; ?></select></div>
          <div class="mb-3"><label class="form-label">Due Date <span class="text-danger">*</span></label><input type="date" name="due_date" class="form-control flatpickr" required></div>
          <div class="alert alert-warning small"><i class="fas fa-exclamation-triangle me-1"></i>This will assign the fee to ALL currently active students.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Assign Fee</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function editFee(c) {
  document.getElementById('feeModalTitle').textContent = 'Edit Fee Category';
  document.getElementById('feeId').value = c.id;
  document.getElementById('feeName').value = c.name;
  document.getElementById('feeType').value = c.type;
  document.getElementById('feeFreq').value = c.frequency;
  document.getElementById('feeAmount').value = c.amount;
  document.getElementById('feeMandatory').value = c.is_mandatory;
  document.getElementById('feeDesc').value = c.description || '';
  new bootstrap.Modal(document.getElementById('feeModal')).show();
}
</script>
