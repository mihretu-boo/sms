<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i>Financial Reports</h4></div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/fees') ?>">Fee Categories</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payments') ?>">Payments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/expenses') ?>">Expenses</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('finance/payroll') ?>">Payroll</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('finance/reports') ?>">Reports</a></li>
</ul>

<!-- Year filter -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-2"><select name="year" class="form-select" onchange="this.form.submit()"><?php for ($y=date('Y');$y>=2022;$y--): ?><option value="<?= $y ?>" <?= selected($year,$y) ?>><?= $y ?></option><?php endfor; ?></select></div>
      <div class="col-auto"><a href="<?= url('reports/export?type=payments') ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a></div>
    </form>
  </div>
</div>

<!-- Monthly Chart -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Monthly Income vs Expenses — <?= $year ?></h6></div>
  <div class="card-body"><canvas id="finReportChart" height="90"></canvas></div>
</div>

<!-- Fee by Category -->
<div class="row g-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Collections by Fee Type</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover small mb-0">
          <thead class="table-light"><tr><th>Fee Type</th><th class="text-end">Total Collected</th></tr></thead>
          <tbody>
            <?php if (empty($fee_by_category)): ?>
            <tr><td colspan="2" class="text-center py-3 text-muted">No data</td></tr>
            <?php else: foreach ($fee_by_category as $f): ?>
            <tr><td><?= e($f['name']??'General') ?></td><td class="text-end fw-semibold text-success"><?= formatMoney($f['total']) ?></td></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Monthly Summary</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover small mb-0">
          <thead class="table-light"><tr><th>Month</th><th class="text-end text-success">Income</th><th class="text-end text-danger">Expenses</th><th class="text-end">Balance</th></tr></thead>
          <tbody>
            <?php foreach ($monthly_chart as $m): $bal=$m['income']-$m['expenses']; ?>
            <tr>
              <td><?= e($m['month']) ?></td>
              <td class="text-end text-success"><?= formatMoney($m['income']) ?></td>
              <td class="text-end text-danger"><?= formatMoney($m['expenses']) ?></td>
              <td class="text-end fw-bold <?= $bal>=0?'text-success':'text-danger' ?>"><?= formatMoney($bal) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $mc = $monthly_chart ?? []; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  new Chart(document.getElementById('finReportChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_column($mc,'month')) ?>,
      datasets: [
        { label:'Income', data:<?= json_encode(array_column($mc,'income')) ?>, backgroundColor:'rgba(46,125,50,0.7)', borderRadius:4 },
        { label:'Expenses', data:<?= json_encode(array_column($mc,'expenses')) ?>, backgroundColor:'rgba(198,40,40,0.7)', borderRadius:4 }
      ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } } }
  });
});
</script>
