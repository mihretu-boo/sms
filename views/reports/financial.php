<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave text-primary me-2"></i>Financial Report</h4></div>
  <div class="d-flex gap-2">
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print"><i class="fas fa-print me-1"></i>Print</button>
    <a href="<?= url('reports/export?type=payments') ?>" class="btn btn-sm btn-outline-success no-print"><i class="fas fa-download me-1"></i>Export</a>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-2"><select name="year" class="form-select" onchange="this.form.submit()"><?php for ($y=date('Y');$y>=2022;$y--): ?><option value="<?= $y ?>" <?= selected($year,$y) ?>><?= $y ?></option><?php endfor; ?></select></div>
    </form>
  </div>
</div>

<!-- Monthly chart -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Monthly Income vs Expenses — <?= $year ?></h6></div>
  <div class="card-body"><canvas id="finChart" height="90"></canvas></div>
</div>

<div class="row g-4 mb-4">
  <!-- Income by month -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold text-success"><i class="fas fa-arrow-up me-1"></i>Monthly Income</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Month</th><th class="text-end">Amount (ETB)</th></tr></thead>
          <tbody>
            <?php $totalInc=0; foreach ($income as $row): $totalInc+=$row['total']; ?>
            <tr><td><?= date('F', mktime(0,0,0,$row['month'],1)) ?></td><td class="text-end text-success fw-semibold"><?= formatMoney($row['total']) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light fw-bold"><td>Total</td><td class="text-end"><?= formatMoney($totalInc) ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- Expenses by month -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold text-danger"><i class="fas fa-arrow-down me-1"></i>Monthly Expenses</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Month</th><th class="text-end">Amount (ETB)</th></tr></thead>
          <tbody>
            <?php $totalExp=0; foreach ($expenses as $row): $totalExp+=$row['total']; ?>
            <tr><td><?= date('F', mktime(0,0,0,$row['month'],1)) ?></td><td class="text-end text-danger fw-semibold"><?= formatMoney($row['total']) ?></td></tr>
            <?php endforeach; ?>
            <tr class="table-light fw-bold"><td>Total</td><td class="text-end"><?= formatMoney($totalExp) ?></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Unpaid Fees -->
<?php if (!empty($unpaid)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold text-danger">Unpaid Fees by Class</h6></div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 small">
      <thead class="table-light"><tr><th>Class</th><th>Count</th><th class="text-end">Total Unpaid</th></tr></thead>
      <tbody>
        <?php foreach ($unpaid as $u): ?>
        <tr><td>Grade <?= e($u['grade']) ?>-<?= e($u['section']) ?></td><td><?= $u['count'] ?></td><td class="text-end text-danger fw-semibold"><?= formatMoney($u['total']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php
$iMonths = array_fill(1,12,0); foreach ($income as $r) $iMonths[$r['month']]=$r['total'];
$eMonths = array_fill(1,12,0); foreach ($expenses as $r) $eMonths[$r['month']]=$r['total'];
$months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  new Chart(document.getElementById('finChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($months) ?>,
      datasets: [
        { label:'Income', data:<?= json_encode(array_values($iMonths)) ?>, backgroundColor:'rgba(46,125,50,0.7)', borderRadius:4 },
        { label:'Expenses', data:<?= json_encode(array_values($eMonths)) ?>, backgroundColor:'rgba(198,40,40,0.7)', borderRadius:4 }
      ]
    },
    options: { responsive:true, plugins:{ legend:{ position:'top' } } }
  });
});
</script>
