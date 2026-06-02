<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-edit text-warning me-2"></i>Edit Exam</h4>
  </div>
  <a href="<?= url('exams') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark py-3"><h6 class="mb-0">Edit: <?= e($exam['title']) ?></h6></div>
      <div class="card-body">
        <form action="<?= url('exams/edit/'.$exam['id']) ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Class / Subject</label>
            <div class="form-control bg-light text-muted">Grade <?= e($exam['grade']) ?>-<?= e($exam['section']) ?> | <?= e($exam['subject_name']) ?></div>
          </div>
          <div class="mb-3"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="<?= e($exam['title']) ?>" required></div>
          <div class="row g-3 mb-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Exam Date</label><input type="date" name="exam_date" class="form-control flatpickr" value="<?= e($exam['exam_date']??'') ?>"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Total Marks</label><input type="number" name="total_marks" class="form-control" value="<?= e($exam['total_marks']) ?>" min="1" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Pass Marks</label><input type="number" name="pass_marks" class="form-control" value="<?= e($exam['pass_marks']) ?>" min="0" required></div>
          </div>
          <div class="mb-4"><label class="form-label fw-semibold">Instructions</label><textarea name="instructions" class="form-control" rows="3"><?= e($exam['instructions']??'') ?></textarea></div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-2"></i>Update Exam</button>
            <a href="<?= url('exams') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
