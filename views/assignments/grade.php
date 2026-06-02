<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-star text-warning me-2"></i>Grade Submission</h4>
  </div>
  <a href="<?= url('assignments/view/'.$submission['assignment_id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><?= e($submission['title']) ?></h6>
        <small class="text-muted">Submitted by <strong><?= e($submission['first_name'].' '.$submission['last_name']) ?></strong> (<?= e($submission['student_id']) ?>)</small>
      </div>
      <div class="card-body">
        <?php if ($submission['file_path']): ?>
        <div class="mb-3"><a href="<?= uploadUrl($submission['file_path']) ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-download me-1"></i>Download Submission File</a></div>
        <?php endif; ?>
        <?php if ($submission['text_content']): ?>
        <div class="mb-3 p-3 bg-light rounded"><strong class="small">Student's Answer:</strong><div class="mt-2 small"><?= nl2br(e($submission['text_content'])) ?></div></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark py-3"><h6 class="mb-0">Grade This Submission</h6></div>
      <div class="card-body">
        <form action="<?= url('assignments/grade/'.$submission['id']) ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Marks <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" name="marks" class="form-control" min="0" max="<?= e($submission['max_marks']) ?>" step="0.5" value="<?= e($submission['marks']??'') ?>" required>
              <span class="input-group-text">/ <?= e($submission['max_marks']) ?></span>
            </div>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Feedback</label>
            <textarea name="feedback" class="form-control" rows="4" placeholder="Comments and feedback for the student..."><?= e($submission['feedback']??'') ?></textarea>
          </div>
          <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save me-2"></i>Save Grade</button>
        </form>
      </div>
    </div>
  </div>
</div>
