<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>GPA Calculator</h4>
  </div>
</div>

<!-- Class selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4"><select name="class_id" class="form-select" onchange="this.form.submit()"><option value="">Select Class</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= selected($classId,$c['id']) ?>>Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach; ?></select></div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($gpaData)): ?>

<!-- Grade distribution chart -->
<div class="row g-4 mb-4">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Class GPA Rankings</h6></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small" id="gpaTable">
            <thead class="table-light">
              <tr><th>Rank</th><th>Student</th><th>Student ID</th><th>Subjects</th><th class="text-center">GPA</th><th class="text-center">Grade</th></tr>
            </thead>
            <tbody>
              <?php foreach ($gpaData as $g): ?>
              <tr>
                <td>
                  <?php if ($g['rank']<=3): ?>
                  <span class="badge bg-<?= ['1'=>'warning','2'=>'secondary','3'=>'dark'][$g['rank']] ?>">#<?= $g['rank'] ?></span>
                  <?php else: ?><span class="text-muted">#<?= $g['rank'] ?></span><?php endif; ?>
                </td>
                <td class="fw-semibold"><?= e($g['first_name'].' '.$g['last_name']) ?></td>
                <td class="font-monospace text-muted small"><?= e($g['student_id']) ?></td>
                <td><?= $g['subjects'] ?></td>
                <td class="text-center"><span class="fw-bold fs-6 <?= getGpaClass($g['gpa']) ?>"><?= number_format($g['gpa'],2) ?></span></td>
                <td class="text-center">
                  <?php $gl = calcGrade($g['gpa']*25)['letter']; ?>
                  <span class="badge bg-<?= match($gl[0]) {'A'=>'success','B'=>'primary','C'=>'info','D'=>'warning',default=>'danger'} ?>"><?= $gl ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Grade Distribution</h6></div>
      <div class="card-body"><canvas id="gpaDistChart"></canvas></div>
    </div>
  </div>
</div>

<?php
$gpaDist = ['A'=>0,'B'=>0,'C'=>0,'D'=>0,'F'=>0];
foreach ($gpaData as $g) {
  $l = calcGrade($g['gpa']*25)['letter'][0];
  $gpaDist[$l] = ($gpaDist[$l]??0)+1;
}
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  new Chart(document.getElementById('gpaDistChart'), {
    type: 'doughnut',
    data: {
      labels: ['A (Excellent)','B (Good)','C (Average)','D (Pass)','F (Fail)'],
      datasets: [{ data: <?= json_encode(array_values($gpaDist)) ?>, backgroundColor: ['#2E7D32','#1565C0','#F57F17','#795548','#C62828'] }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
  });
});
</script>

<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-chart-line fa-3x mb-3"></i><br><h6>Select a class to view GPA rankings</h6></div></div>
<?php endif; ?>
