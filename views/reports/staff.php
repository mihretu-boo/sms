<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-users text-primary me-2"></i>Staff Report</h4></div>
  <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print"><i class="fas fa-print me-1"></i>Print</button>
</div>

<div class="row g-4 mb-4">
  <!-- By Department -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-building text-primary me-2"></i>Staff by Department</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Department</th><th class="text-center">Total</th><th class="text-center">Male</th><th class="text-center">Female</th><th class="text-end">Avg Salary</th></tr></thead>
          <tbody>
            <?php if (empty($by_dept)): ?>
            <tr><td colspan="5" class="text-center py-3 text-muted">No data</td></tr>
            <?php else: foreach ($by_dept as $d): ?>
            <tr>
              <td class="fw-semibold"><?= e($d['name']) ?></td>
              <td class="text-center fw-bold text-primary"><?= $d['total'] ?></td>
              <td class="text-center text-info"><?= $d['male'] ?></td>
              <td class="text-center text-danger"><?= $d['female'] ?></td>
              <td class="text-end text-muted"><?= $d['avg_salary'] ? formatMoney($d['avg_salary']) : '—' ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- By Position -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-briefcase text-success me-2"></i>Staff by Position</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Position</th><th class="text-center">Count</th></tr></thead>
          <tbody>
            <?php if (empty($by_position)): ?>
            <tr><td colspan="2" class="text-center py-3 text-muted">No data</td></tr>
            <?php else: foreach ($by_position as $p): ?>
            <tr>
              <td><?= e($p['position']) ?></td>
              <td class="text-center"><span class="badge bg-primary"><?= $p['count'] ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Leave Statistics -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-minus text-warning me-2"></i>Leave Statistics (Current Year)</h6></div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 small">
      <thead class="table-light"><tr><th>Leave Type</th><th class="text-center">Requests</th><th class="text-center">Total Days</th></tr></thead>
      <tbody>
        <?php if (empty($leave_stats)): ?>
        <tr><td colspan="3" class="text-center py-3 text-muted">No approved leaves this year</td></tr>
        <?php else: foreach ($leave_stats as $l): ?>
        <tr>
          <td><?= ucfirst(str_replace('_',' ',$l['leave_type'])) ?></td>
          <td class="text-center"><?= $l['count'] ?></td>
          <td class="text-center fw-semibold"><?= $l['total_days'] ?> days</td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
