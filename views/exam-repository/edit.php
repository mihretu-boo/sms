<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-edit text-warning me-2"></i>Edit Exam</h4>
  </div>
  <a href="<?= url('exam-repository/view/'.$exam['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= url('exam-repository/edit/'.$exam['id']) ?>" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-warning text-dark py-3"><h6 class="mb-0">Exam Metadata</h6></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" value="<?= e($exam['title']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Subject</label>
              <select name="subject_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>" <?= selected($exam['subject_id'],$s['id']) ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Grade</label>
              <select name="grade" class="form-select">
                <option value="all" <?= selected($exam['grade'],'all') ?>>All Grades</option>
                <?php foreach (['9','10','11','12'] as $g): ?><option value="<?= $g ?>" <?= selected($exam['grade'],$g) ?>>Grade <?= $g ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Difficulty</label>
              <select name="difficulty" class="form-select">
                <?php foreach (['easy','medium','hard'] as $d): ?><option value="<?= $d ?>" <?= selected($exam['difficulty'],$d) ?>><?= ucfirst($d) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Exam Type</label>
              <select name="exam_type" class="form-select">
                <optgroup label="Internal">
                  <?php foreach (['quiz','test','assignment','mid_semester','final','practical'] as $t): ?><option value="<?= $t ?>" <?= selected($exam['exam_type'],$t) ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option><?php endforeach; ?>
                </optgroup>
                <optgroup label="External">
                  <?php foreach (['regional','national','mock','entrance'] as $t): ?><option value="<?= $t ?>" <?= selected($exam['exam_type'],$t) ?>><?= ucfirst($t) ?></option><?php endforeach; ?>
                </optgroup>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Category</label>
              <select name="category_type" class="form-select">
                <option value="internal" <?= selected($exam['category_type'],'internal') ?>>Internal</option>
                <option value="external" <?= selected($exam['category_type'],'external') ?>>External</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Department</label>
              <select name="department_id" class="form-select">
                <option value="">None</option>
                <?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>" <?= selected($exam['department_id'],$d['id']) ?>><?= e($d['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?= e($exam['description']) ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Instructions</label>
              <textarea name="instructions" class="form-control" rows="2"><?= e($exam['instructions']) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tags</label>
              <input type="text" name="tags" class="form-control" value="<?= e($exam['tags']) ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Replace file (new version) -->
      <?php if (in_array($exam['status'], ['draft','rejected'])): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-file-upload text-info me-2"></i>Replace File (New Version)</h6></div>
        <div class="card-body">
          <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i>Uploading a new file will increment the version number and archive the previous file.</div>
          <div class="mb-3">
            <label class="form-label">New File <small class="text-muted">(optional)</small></label>
            <input type="file" name="exam_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip">
            <div class="form-text">Current: <?= e($exam['file_original_name']) ?> (v<?= $exam['version'] ?>)</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Change Notes</label>
            <input type="text" name="change_notes" class="form-control" placeholder="What changed in this version?">
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="col-md-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Access Settings</h6></div>
        <div class="card-body">
          <div class="form-check form-switch mb-3">
            <input type="hidden" name="is_public" value="0">
            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublic" <?= $exam['is_public']?'checked':'' ?>>
            <label class="form-check-label" for="isPublic">Visible to Students</label>
          </div>
          <div class="alert alert-warning small py-2">
            <i class="fas fa-exclamation-triangle me-1"></i>Uploading a new file resets status to <strong>Draft</strong>.
          </div>
        </div>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <button type="submit" class="btn btn-warning w-100 mb-2"><i class="fas fa-save me-2"></i>Update Exam</button>
          <a href="<?= url('exam-repository/view/'.$exam['id']) ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>
