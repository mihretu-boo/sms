<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-level-up-alt text-primary me-2"></i>Student Promotions</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li><li class="breadcrumb-item active">Promotions</li></ol></nav>
  </div>
</div>

<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>End-of-year promotion moves students from their current grade to the next. Review class results before promoting.</div>

<div class="row g-3 mb-4">
  <?php foreach ($classes as $cls): ?>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fw-bold fs-4 text-primary"><?= $cls['grade'] ?></div>
      <div class="badge bg-primary mb-1">Section <?= e($cls['section']) ?></div>
      <div class="text-muted small"><?= $cls['student_count'] ?> students</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
    <h6 class="mb-0 fw-semibold">Promote Students</h6>
  </div>
  <div class="card-body">
    <form action="<?= url('students/promote') ?>" method="POST" onsubmit="return confirm('Promote all selected students? This cannot be easily undone.')">
      <?= csrfField() ?>
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">From Academic Year</label>
          <select name="from_year_id" class="form-select" required>
            <?php foreach ($years as $y): ?>
            <option value="<?= $y['id'] ?>" <?= $y['status']==='active'?'selected':'' ?>><?= e($y['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">From Class</label>
          <select name="class_id" class="form-select" required>
            <option value="">Select class</option>
            <?php foreach ($classes as $cls): ?>
            <option value="<?= $cls['id'] ?>">Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?> (<?= $cls['student_count'] ?> students)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Promotion Type</label>
          <select name="promotion_type" class="form-select">
            <option value="promoted">Promote to Next Grade</option>
            <option value="graduated">Mark as Graduated (Grade 12)</option>
            <option value="repeated">Repeat Same Grade</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-level-up-alt me-2"></i>Process Promotions
      </button>
    </form>
  </div>
</div>
