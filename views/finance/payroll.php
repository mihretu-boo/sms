<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-money-check text-primary me-2"></i>Payroll</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('finance') ?>">Finance</a></li><li class="breadcrumb-item active">Payroll</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#payrollModal"><i class="fas fa-cog me-1"></i>Process Payroll</button>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/fees') ?>">Fee Categories</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payments') ?>">Payments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/expenses') ?>">Expenses</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('finance/payroll') ?>">Payroll</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/reports') ?>">Reports</a></li>
</ul>

<!-- Month/Year filter -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-2"><select name="month" class="form-select"><?php for ($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= selected($month,$m) ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select></div>
      <div class="col-md-2"><select name="year" class="form-select"><?php for ($y=date('Y');$y>=2022;$y--): ?><option value="<?= $y ?>" <?= selected($year,$y) ?>><?= $y ?></option><?php endfor; ?></select></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary">View</button></div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="payrollTable">
        <thead class="table-light"><tr><th>Employee</th><th>ID</th><th>Position</th><th>Basic</th><th>Allowances</th><th>Tax</th><th>Pension</th><th>Net</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($payroll)): ?>
          <tr><td colspan="9" class="text-center py-5 text-muted">No payroll for selected period. Process payroll first.</td></tr>
          <?php else: foreach ($payroll as $p): ?>
          <tr>
            <td class="fw-semibold"><?= e($p['first_name'].' '.$p['last_name']) ?></td>
            <td class="text-muted font-monospace"><?= e($p['employee_id']) ?></td>
            <td class="small"><?= e($p['position']) ?></td>
            <td><?= formatMoney($p['basic_salary']) ?></td>
            <td class="text-success">+<?= formatMoney($p['allowances']) ?></td>
            <td class="text-danger">-<?= formatMoney($p['income_tax']) ?></td>
            <td class="text-danger">-<?= formatMoney($p['pension']) ?></td>
            <td class="fw-bold text-primary"><?= formatMoney($p['net_salary']) ?></td>
            <td><?= getStatusBadge($p['status']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Process Payroll Modal -->
<div class="modal fade" id="payrollModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title"><i class="fas fa-cog me-2"></i>Process Payroll</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('finance/payroll') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Month</label><select name="month" class="form-select"><?php for ($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= selected(date('m'),$m) ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select></div>
            <div class="col-md-6"><label class="form-label">Year</label><select name="year" class="form-select"><?php for ($y=date('Y');$y>=2022;$y--): ?><option value="<?= $y ?>"><?= $y ?></option><?php endfor; ?></select></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Select Staff</label>
            <div class="form-check mb-2"><input type="checkbox" id="selectAll" class="form-check-input" onchange="document.querySelectorAll('.staff-check').forEach(c=>c.checked=this.checked)"><label class="form-check-label" for="selectAll"><strong>Select All</strong></label></div>
            <div style="max-height:250px;overflow-y:auto">
              <?php foreach ($staff as $s): ?>
              <div class="form-check">
                <input type="checkbox" name="staff_ids[]" value="<?= $s['id'] ?>" class="form-check-input staff-check" id="stf<?= $s['id'] ?>" checked>
                <label class="form-check-label" for="stf<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?> — <?= e($s['position']) ?> (<?= formatMoney($s['basic_salary']) ?>)</label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-play me-1"></i>Generate Payroll</button></div>
      </form>
    </div>
  </div>
</div>
