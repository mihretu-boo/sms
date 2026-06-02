<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i>Academic Report</h4></div>
  <?php if ($classId && !empty($performance)): ?>
  <a href="<?= url('reports/export?type=students') ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-download me-1"></i>Export</a>
  <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4"><select name="class_id" class="form-select" onchange="this.form.submit()"><option value="">Select Class</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= selected($classId,$c['id']) ?>>Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach; ?></select></div>
    </form>
  </div>
</div>

<?php if ($classId): ?>
<?php if (!empty($subject_avg)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Subject Performance Average</h6></div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 small">
      <thead class="table-light"><tr><th>Subject</th><th>Average %</th><th>Min %</th><th>Max %</th><th>Performance</th></tr></thead>
      <tbody>
        <?php foreach ($subject_avg as $sa): ?>
        <tr>
          <td class="fw-semibold"><?= e($sa['name']) ?></td>
          <td><?= round($sa['avg_pct'],1) ?>%</td>
          <td class="text-danger"><?= round($sa['min_pct'],1) ?>%</td>
          <td class="text-success"><?= round($sa['max_pct'],1) ?>%</td>
          <td><div class="progress" style="height:8px;width:100px"><div class="progress-bar bg-<?= $sa['avg_pct']>=80?'success':($sa['avg_pct']>=60?'warning':'danger') ?>" style="width:<?= min(100,round($sa['avg_pct'])) ?>%"></div></div></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Student Rankings</h6></div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 small" id="academicTable">
      <thead class="table-light"><tr><th>Rank</th><th>Student</th><th>Student ID</th><th class="text-center">GPA</th><th class="text-center">Absences</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($performance)): ?>
        <tr><td class="text-center py-4 text-muted">No marks recorded yet</td><td></td><td></td><td></td><td></td><td></td></tr>
        <?php else: foreach ($performance as $s): ?>
        <tr>
          <td><?php if ($s['rank']<=3): ?><span class="badge bg-<?= ['1'=>'warning','2'=>'secondary','3'=>'dark'][$s['rank']] ?>">#<?= $s['rank'] ?></span><?php else: ?><span class="text-muted">#<?= $s['rank'] ?></span><?php endif; ?></td>
          <td class="fw-semibold"><?= e($s['first_name'].' '.$s['last_name']) ?></td>
          <td class="text-muted"><?= e($s['student_id']) ?></td>
          <td class="text-center"><span class="fw-bold <?= getGpaClass($s['gpa']) ?>"><?= number_format($s['gpa'],2) ?></span></td>
          <td class="text-center <?= ($s['absences']??0)>5?'text-danger':'' ?>"><?= $s['absences']??0 ?></td>
          <td><a href="<?= url('exams/report-card/'.$s['id']) ?>" class="btn btn-xs btn-outline-primary" target="_blank"><i class="fas fa-file-alt"></i></a></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-graduation-cap fa-3x mb-3"></i><br><h6>Select a class to view academic performance</h6></div></div>
<?php endif; ?>
