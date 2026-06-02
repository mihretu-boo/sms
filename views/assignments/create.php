<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Create Assignment</h4>
  </div>
  <a href="<?= url('assignments') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white py-3"><h6 class="mb-0">Assignment Details</h6></div>
      <div class="card-body">
        <form action="<?= url('assignments/create') ?>" method="POST" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="hidden" name="semester_id" value="<?= e($semId) ?>">
          <div class="mb-3"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required placeholder="Assignment title..."></div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Class <span class="text-danger">*</span></label><select name="class_id" class="form-select" id="classSelect" required><option value="">Select Class</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>">Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label><select name="subject_id" class="form-select" id="subjectSelect" required><option value="">Select Class first</option></select></div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Due Date & Time <span class="text-danger">*</span></label><input type="datetime-local" name="due_date" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Max Marks</label><input type="number" name="max_marks" class="form-control" value="100" min="1"></div>
          </div>
          <div class="mb-3"><label class="form-label fw-semibold">Description / Instructions</label><textarea name="description" class="form-control" rows="4" placeholder="Describe the assignment..."></textarea></div>
          <div class="mb-4"><label class="form-label fw-semibold">Attach File <small class="text-muted">(PDF, DOCX, PPTX)</small></label><input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx"></div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create Assignment</button>
            <a href="<?= url('assignments') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php $semId = $semId ?? 1; ?>
<script>const SEMESTER_ID = <?= (int)$semId ?>;</script>
