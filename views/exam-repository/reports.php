<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i>Repository Reports</h4>
  </div>
  <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print"><i class="fas fa-print me-1"></i>Print</button>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
  <!-- By Status -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">By Status</h6></div>
      <div class="card-body">
        <canvas id="statusChart" height="200"></canvas>
        <div class="mt-3">
          <?php foreach ($by_status as $row): ?>
          <div class="d-flex justify-content-between align-items-center mb-1 small">
            <span><?= getStatusBadge($row['status']) ?></span>
            <span class="fw-bold"><?= $row['cnt'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- By Exam Type -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">By Exam Type</h6></div>
      <div class="card-body">
        <canvas id="typeChart" height="200"></canvas>
        <div class="mt-3">
          <?php foreach ($by_type as $row): ?>
          <div class="d-flex justify-content-between align-items-center mb-1 small">
            <span><?= ucfirst(str_replace('_',' ',$row['exam_type'])) ?></span>
            <span class="badge bg-primary"><?= $row['cnt'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- By Grade -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">By Grade Level</h6></div>
      <div class="card-body">
        <canvas id="gradeChart" height="200"></canvas>
        <div class="mt-3">
          <?php foreach ($by_grade as $row): ?>
          <div class="d-flex justify-content-between align-items-center mb-1 small">
            <span><?= $row['grade']==='all'?'All Grades':'Grade '.$row['grade'] ?></span>
            <span class="badge bg-info"><?= $row['cnt'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Upload Trend + Department Stats -->
<div class="row g-4 mb-4">
  <!-- Monthly uploads -->
  <div class="col-md-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-chart-line text-primary me-2"></i>Uploads — Last 6 Months</h6></div>
      <div class="card-body"><canvas id="monthlyChart" height="120"></canvas></div>
    </div>
  </div>

  <!-- By Department -->
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-building text-success me-2"></i>By Department</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Department</th><th class="text-end">Exams</th></tr></thead>
          <tbody>
            <?php foreach ($by_dept as $d): ?>
            <tr><td><?= e($d['name']) ?></td><td class="text-end fw-bold"><?= $d['cnt'] ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Top Downloads + Recent Activity -->
<div class="row g-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-fire text-danger me-2"></i>Top Downloaded Exams</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>#</th><th>Exam</th><th>Grade</th><th class="text-center">Downloads</th></tr></thead>
          <tbody>
            <?php if (empty($top_downloads)): ?>
            <tr><td colspan="4" class="text-center py-3 text-muted">No downloads yet</td></tr>
            <?php else: $n=1; foreach ($top_downloads as $e): ?>
            <tr>
              <td class="text-muted"><?= $n++ ?></td>
              <td><a href="<?= url('exam-repository/view/'.$e['id']) ?>" class="text-decoration-none fw-semibold"><?= e(truncate($e['title'],40)) ?></a></td>
              <td><?= $e['grade']==='all'?'All':'Gr.'.$e['grade'] ?></td>
              <td class="text-center"><span class="badge bg-danger"><?= $e['download_count'] ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-history text-info me-2"></i>Recent Download Activity</h6></div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush" style="max-height:300px;overflow-y:auto">
          <?php if (empty($recent_activity)): ?>
          <div class="text-center py-4 text-muted small">No activity yet</div>
          <?php else: foreach ($recent_activity as $d): ?>
          <div class="list-group-item px-3 py-2">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="small fw-semibold"><?= e(truncate($d['title'],40)) ?></div>
                <div class="text-muted" style="font-size:11px">Downloaded by <strong><?= e($d['username']) ?></strong></div>
              </div>
              <span class="text-muted" style="font-size:11px"><?= timeAgo($d['downloaded_at']) ?></span>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$statusLabels = array_map(fn($r) => ucfirst(str_replace('_',' ',$r['status'])), $by_status);
$statusData   = array_column($by_status,'cnt');
$typeLabels   = array_map(fn($r) => ucfirst(str_replace('_',' ',$r['exam_type'])), $by_type);
$typeData     = array_column($by_type,'cnt');
$gradeLabels  = array_map(fn($r) => $r['grade']==='all'?'All Grades':'Grade '.$r['grade'], $by_grade);
$gradeData    = array_column($by_grade,'cnt');
$monthlyLabels = array_column($monthly,'month');
$monthlyData   = array_column($monthly,'cnt');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var palette = ['#1565C0','#2E7D32','#F57F17','#6A1B9A','#C62828','#00695C','#BF360C'];

  new Chart(document.getElementById('statusChart'), {
    type:'doughnut', data:{ labels:<?= json_encode($statusLabels) ?>, datasets:[{ data:<?= json_encode($statusData) ?>, backgroundColor:palette }] },
    options:{ plugins:{ legend:{ display:false } } }
  });

  new Chart(document.getElementById('typeChart'), {
    type:'bar', data:{ labels:<?= json_encode($typeLabels) ?>, datasets:[{ data:<?= json_encode($typeData) ?>, backgroundColor:'rgba(21,101,192,0.7)', borderRadius:4 }] },
    options:{ indexAxis:'y', plugins:{ legend:{ display:false } }, scales:{ x:{ beginAtZero:true } } }
  });

  new Chart(document.getElementById('gradeChart'), {
    type:'pie', data:{ labels:<?= json_encode($gradeLabels) ?>, datasets:[{ data:<?= json_encode($gradeData) ?>, backgroundColor:palette }] },
    options:{ plugins:{ legend:{ position:'bottom' } } }
  });

  new Chart(document.getElementById('monthlyChart'), {
    type:'line',
    data:{ labels:<?= json_encode($monthlyLabels) ?>, datasets:[{ label:'Uploads', data:<?= json_encode($monthlyData) ?>, borderColor:'#1565C0', backgroundColor:'rgba(21,101,192,0.1)', tension:0.4, fill:true }] },
    options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } } }
  });
});
</script>
