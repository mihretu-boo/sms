<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-calendar-check text-primary me-2"></i>Attendance Report</h4></div>
  <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print"><i class="fas fa-print me-1"></i>Print</button>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-3"><input type="month" name="month" class="form-control" value="<?= e($month) ?>"></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary">Generate</button></div>
    </form>
  </div>
</div>

<!-- Daily attendance bar chart -->
<?php if (!empty($daily_stats)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Daily Attendance Rate — <?= date('F Y', strtotime($month.'-01')) ?></h6></div>
  <div class="card-body"><canvas id="attReportChart" height="80"></canvas></div>
</div>
<?php endif; ?>

<!-- Per class summary -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">By Class</h6></div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 small">
      <thead class="table-light"><tr><th>Class</th><th>Students</th><th class="text-center">Total Records</th><th class="text-center text-success">Present</th><th class="text-center text-danger">Absent</th><th class="text-center text-warning">Late</th><th>Rate</th></tr></thead>
      <tbody>
        <?php if (empty($class_stats)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No data for selected period</td></tr>
        <?php else: foreach ($class_stats as $c):
          $rate = $c['total_records']>0 ? round(($c['present']/$c['total_records'])*100) : 0; ?>
        <tr>
          <td class="fw-semibold">Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></td>
          <td><?= $c['students'] ?></td>
          <td class="text-center"><?= $c['total_records'] ?></td>
          <td class="text-center fw-bold text-success"><?= $c['present'] ?></td>
          <td class="text-center fw-bold text-danger"><?= $c['absent'] ?></td>
          <td class="text-center fw-bold text-warning"><?= $c['late'] ?></td>
          <td><div class="d-flex align-items-center gap-2"><div class="progress flex-fill" style="height:8px;width:80px"><div class="progress-bar bg-<?= $rate>=80?'success':'danger' ?>" style="width:<?= $rate ?>%"></div></div><small class="fw-bold <?= $rate>=80?'text-success':'text-danger' ?>"><?= $rate ?>%</small></div></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $ds = $daily_stats ?? []; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('attReportChart');
  if (ctx) new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_map(fn($d)=>date('d M',strtotime($d['date'])), $ds)) ?>,
      datasets: [{ label:'Attendance Rate %', data:<?= json_encode(array_column($ds,'rate')) ?>, backgroundColor: 'rgba(21,101,192,0.7)', borderRadius:3 }]
    },
    options: { responsive:true, scales:{ y:{ min:0, max:100, ticks:{ callback: v=>v+'%' } } }, plugins:{ legend:{ display:false } } }
  });
});
</script>
