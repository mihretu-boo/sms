<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Attendance Analytics</h4>
  </div>
</div>

<!-- 30-day chart -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Attendance Rate — Last 30 Days</h6></div>
  <div class="card-body"><canvas id="attendanceTrendChart" height="80"></canvas></div>
</div>

<div class="row g-4">
  <!-- Most Absent -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Most Absent Students</h6></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 small">
            <thead class="table-light"><tr><th>Student</th><th>Class</th><th>Absences</th></tr></thead>
            <tbody>
              <?php if (empty($most_absent)): ?>
              <tr><td colspan="3" class="text-center py-3 text-success"><i class="fas fa-check-circle me-1"></i>No absences recorded</td></tr>
              <?php else: foreach ($most_absent as $s): ?>
              <tr>
                <td class="fw-semibold"><?= e($s['first_name'].' '.$s['last_name']) ?></td>
                <td>Grade <?= e($s['grade']) ?>-<?= e($s['section']) ?></td>
                <td><span class="badge bg-danger"><?= $s['absences'] ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Per Class Rate -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-layer-group text-primary me-2"></i>Rate by Class</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Class</th><th>Total</th><th>Present</th><th>Rate</th></tr></thead>
          <tbody>
            <?php foreach ($class_rate as $c): $rate=$c['total']>0?round(($c['present']/$c['total'])*100):0; ?>
            <tr>
              <td class="fw-semibold">Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></td>
              <td><?= $c['total'] ?></td>
              <td><?= $c['present'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-fill" style="height:8px"><div class="progress-bar bg-<?= $rate>=80?'success':($rate>=60?'warning':'danger') ?>" style="width:<?= $rate ?>%"></div></div>
                  <small class="<?= $rate>=80?'text-success':'text-danger' ?> fw-bold"><?= $rate ?>%</small>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php $dc = $daily_chart ?? []; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('attendanceTrendChart');
  if (ctx) new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($dc,'date')) ?>,
      datasets: [{ label: 'Attendance Rate %', data: <?= json_encode(array_column($dc,'rate')) ?>, borderColor: '#2E7D32', backgroundColor: 'rgba(46,125,50,0.1)', tension: 0.4, fill: true }]
    },
    options: { responsive: true, scales: { y: { min: 0, max: 100, ticks: { callback: v => v+'%' } } }, plugins: { legend: { display: false } } }
  });
});
</script>
