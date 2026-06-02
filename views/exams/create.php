<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Create Exam</h4>
  </div>
  <a href="<?= url('exams') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Exam Details</h6></div>
      <div class="card-body">
        <form action="<?= url('exams/create') ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3"><label class="form-label fw-semibold">Exam Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required placeholder="e.g. Mathematics Mid-Term Exam"></div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
              <select name="type" class="form-select" required>
                <?php foreach (['assignment'=>'Assignment','quiz'=>'Quiz','project'=>'Project','mid_exam'=>'Mid Exam','final_exam'=>'Final Exam','national_prep'=>'National Exam Prep'] as $v=>$l): ?>
                <option value="<?= $v ?>"><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Semester</label>
              <select name="semester_id" class="form-select">
                <?php foreach ($semesters as $sem): ?>
                <option value="<?= $sem['id'] ?>" <?= selected($semId,$sem['id']) ?>><?= e($sem['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
              <select name="class_id" class="form-select" id="classSelect" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>">Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
              <select name="subject_id" class="form-select" id="subjectSelect" required><option value="">Select Class first</option></select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Exam Date</label><input type="date" name="exam_date" class="form-control flatpickr"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Total Marks <span class="text-danger">*</span></label><input type="number" name="total_marks" class="form-control" value="100" min="1" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Pass Marks <span class="text-danger">*</span></label><input type="number" name="pass_marks" class="form-control" value="50" min="0" required></div>
          </div>
          <div class="mb-3"><label class="form-label fw-semibold">Weight % <small class="text-muted">(of semester grade)</small></label><input type="number" name="weight_percent" class="form-control" placeholder="e.g. 30 for 30%"></div>
          <div class="mb-4"><label class="form-label fw-semibold">Instructions</label><textarea name="instructions" class="form-control" rows="3" placeholder="Optional exam instructions..."></textarea></div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create & Enter Marks</button>
            <a href="<?= url('exams') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php $semId = $semId ?? 1; ?>
<script>
const SEMESTER_ID = <?= (int)$semId ?>;
</script>
