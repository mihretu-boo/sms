<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i>Annual Report</h4></div>
  <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print"><i class="fas fa-print me-1"></i>Print Report</button>
</div>

<!-- Print Header -->
<div class="text-center mb-4 pb-3 border-bottom no-screen">
  <h5 class="fw-bold text-primary"><?= e(getSetting('school_name')) ?></h5>
  <div class="text-muted small"><?= e(getSetting('school_address')) ?></div>
  <h5 class="fw-bold mt-2">ANNUAL SCHOOL REPORT — <?= e($ay['name'] ?? date('Y')) ?></h5>
</div>

<!-- Stats Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-2"><i class="fas fa-users fa-lg"></i></div>
      <div class="fw-bold fs-4 text-primary"><?= ($students['total'] ?? 0) ?></div>
      <div class="text-muted small">Total Students</div>
      <div class="text-muted" style="font-size:11px"><?= $students['male']??0 ?> Male | <?= $students['female']??0 ?> Female</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="stat-icon bg-success-light text-success rounded-3 mx-auto mb-2"><i class="fas fa-chalkboard-teacher fa-lg"></i></div>
      <div class="fw-bold fs-4 text-success"><?= ($staff['total'] ?? 0) ?></div>
      <div class="text-muted small">Total Staff</div>
      <div class="text-muted" style="font-size:11px"><?= $staff['male']??0 ?> Male | <?= $staff['female']??0 ?> Female</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <?php $attTotal=$attendance['total']??1; $attPresent=$attendance['present']??0; $attRate=round(($attPresent/$attTotal)*100); ?>
      <div class="stat-icon bg-info-light text-info rounded-3 mx-auto mb-2"><i class="fas fa-calendar-check fa-lg"></i></div>
      <div class="fw-bold fs-4 text-info"><?= $attRate ?>%</div>
      <div class="text-muted small">Attendance Rate</div>
      <div class="text-muted" style="font-size:11px"><?= number_format($attPresent) ?> present days</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="stat-icon bg-warning-light text-warning rounded-3 mx-auto mb-2"><i class="fas fa-money-bill fa-lg"></i></div>
      <div class="fw-bold fs-4 text-warning"><?= formatMoney($total_income) ?></div>
      <div class="text-muted small">Total Income</div>
      <div class="text-muted" style="font-size:11px">Expenses: <?= formatMoney($total_expenses) ?></div>
    </div>
  </div>
</div>

<!-- Financial Summary -->
<div class="row g-4 mb-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Financial Summary</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0 small">
          <tr><td class="text-muted">Total Income</td><td class="fw-bold text-success text-end"><?= formatMoney($total_income) ?></td></tr>
          <tr><td class="text-muted">Total Expenses</td><td class="fw-bold text-danger text-end"><?= formatMoney($total_expenses) ?></td></tr>
          <tr class="border-top"><td class="fw-bold">Net Balance</td><td class="fw-bold text-<?= ($total_income-$total_expenses)>=0?'success':'danger' ?> text-end"><?= formatMoney($total_income-$total_expenses) ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Academic Year: <?= e($ay['name']??'') ?></h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0 small">
          <tr><td class="text-muted">Start Date</td><td class="fw-semibold text-end"><?= formatDate($ay['start_date']??'') ?></td></tr>
          <tr><td class="text-muted">End Date</td><td class="fw-semibold text-end"><?= formatDate($ay['end_date']??'') ?></td></tr>
          <tr><td class="text-muted">Status</td><td class="text-end"><?= getStatusBadge($ay['status']??'') ?></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Attendance Details -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Attendance Overview</h6></div>
  <div class="card-body">
    <div class="row g-3 text-center">
      <div class="col-3"><div class="fw-bold fs-5"><?= number_format($attendance['total']??0) ?></div><div class="text-muted small">Total Records</div></div>
      <div class="col-3"><div class="fw-bold fs-5 text-success"><?= number_format($attendance['present']??0) ?></div><div class="text-muted small">Present</div></div>
      <div class="col-3"><div class="fw-bold fs-5 text-danger"><?= number_format($attendance['absent']??0) ?></div><div class="text-muted small">Absent</div></div>
      <div class="col-3"><div class="fw-bold fs-5 <?= $attRate>=80?'text-success':'text-warning' ?>"><?= $attRate ?>%</div><div class="text-muted small">Attendance Rate</div></div>
    </div>
  </div>
</div>

<div class="text-center mt-4 text-muted small no-print">Report generated on <?= date('d M Y H:i') ?> — <?= e(getSetting('school_name')) ?></div>
