<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i>Report Cards</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Report Cards</li></ol></nav>
  </div>
</div>

<!-- Class Filter -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4">
        <select name="class_id" class="form-select" onchange="this.form.submit()">
          <option value="">Select Class</option>
          <?php foreach ($classes as $cls): ?>
          <option value="<?= $cls['id'] ?>" <?= selected($classId,$cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($students)): ?>
<!-- Students Table with GPA -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Class Results — Semester <?= $semId ?></h6>
    <span class="text-muted small"><?= count($students) ?> students</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="reportTable">
        <thead class="table-light">
          <tr><th>Rank</th><th>Student</th><th>Student ID</th><th class="text-center">GPA</th><th class="text-center">Grade</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s):
            $gpa  = $s['gpa'];
            $gradeData = calcGrade($gpa * 25); // Convert to percentage scale (4.0 = 100%)
          ?>
          <tr>
            <td>
              <?php if ($s['rank'] <= 3): ?>
              <span class="badge bg-<?= ['1'=>'warning','2'=>'secondary','3'=>'danger'][$s['rank']] ?>">#<?= $s['rank'] ?></span>
              <?php else: ?><span class="text-muted">#<?= $s['rank'] ?></span><?php endif; ?>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= photoUrl($s['photo']??null,'student') ?>" class="rounded-circle" width="30" height="30" style="object-fit:cover">
                <div class="fw-semibold"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
              </div>
            </td>
            <td class="text-muted"><?= e($s['student_id']) ?></td>
            <td class="text-center">
              <span class="fw-bold <?= getGpaClass($gpa) ?>"><?= number_format($gpa,2) ?></span>
            </td>
            <td class="text-center">
              <?php $gl = calcGrade($gpa * 25)['letter']; $glC = ['A'=>'success','B'=>'primary','C'=>'info','D'=>'warning','F'=>'danger'][$gl[0]] ?? 'secondary'; ?>
              <span class="badge bg-<?= $glC ?>"><?= $gl ?></span>
            </td>
            <td>
              <a href="<?= url('exams/report-card/'.$s['id']) ?>" class="btn btn-xs btn-outline-primary" target="_blank"><i class="fas fa-eye me-1"></i>View</a>
              <a href="<?= url('exams/report-card/'.$s['id']) ?>" class="btn btn-xs btn-outline-secondary" target="_blank"><i class="fas fa-print me-1"></i>Print</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php elseif ($classId): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No marks entered yet for this class in the current semester.</div>
<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5">
  <div class="text-muted"><i class="fas fa-file-alt fa-3x mb-3"></i><br><h6>Select a class to view report cards</h6></div>
</div>
<?php endif; ?>
