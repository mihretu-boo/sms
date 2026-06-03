<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-upload text-primary me-2"></i>Upload Examination</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('exam-repository') ?>">Exam Repository</a></li><li class="breadcrumb-item active">Upload</li></ol></nav>
  </div>
  <a href="<?= url('exam-repository') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= url('exam-repository/upload') ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
  <?= csrfField() ?>

  <div class="row g-4">
    <!-- Left: File Upload + Metadata -->
    <div class="col-md-8">

      <!-- Drag-and-Drop Zone -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-cloud-upload-alt me-2"></i>File Upload</h6></div>
        <div class="card-body">
          <!-- Drop Zone -->
          <div class="upload-dropzone border-2 border-dashed rounded-3 text-center py-5 px-4 mb-3 position-relative" id="dropZone">
            <div class="upload-icon mb-3">
              <i class="fas fa-cloud-upload-alt fa-3x text-primary opacity-75"></i>
            </div>
            <h6 class="fw-bold text-primary mb-1">Drag & Drop files here</h6>
            <p class="text-muted small mb-3">or click to browse files<br>Supported: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP — Max 50MB each</p>
            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
              <i class="fas fa-folder-open me-2"></i>Browse Files
            </button>
            <input type="file" id="fileInput" name="exam_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip" class="d-none">
          </div>

          <!-- File Preview List -->
          <div id="fileList" class="d-none">
            <h6 class="fw-semibold mb-3"><i class="fas fa-list me-2"></i>Selected Files <span id="fileCount" class="badge bg-primary ms-1">0</span></h6>
            <div id="fileItems"></div>
          </div>
        </div>
      </div>

      <!-- Exam Metadata -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle text-primary me-2"></i>Exam Metadata</h6></div>
        <div class="card-body">
          <div class="alert alert-info small mb-4">
            <i class="fas fa-info-circle me-1"></i>
            When uploading multiple files, the metadata below applies to all files. You can edit individual titles after upload.
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Exam Title <span class="text-danger">*</span></label>
              <input type="text" name="title[]" id="mainTitle" class="form-control" required placeholder="e.g. Mathematics Grade 9 Mid-Semester Exam 2025">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Subject</label>
              <select name="subject_id" class="form-select select2">
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (Grade <?= e($s['grade']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Grade Level <span class="text-danger">*</span></label>
              <select name="grade" class="form-select" required>
                <option value="all">All Grades</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Difficulty</label>
              <select name="difficulty" class="form-select">
                <option value="easy">Easy</option>
                <option value="medium" selected>Medium</option>
                <option value="hard">Hard</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Exam Type <span class="text-danger">*</span></label>
              <select name="exam_type" class="form-select" required id="examTypeSelect">
                <optgroup label="Internal Examinations">
                  <option value="quiz">Quiz</option>
                  <option value="test">Test</option>
                  <option value="assignment">Assignment</option>
                  <option value="mid_semester">Mid-Semester Exam</option>
                  <option value="final">Final Exam</option>
                  <option value="practical">Practical Exam</option>
                </optgroup>
                <optgroup label="External Examinations">
                  <option value="regional">Regional Examination</option>
                  <option value="national">National Examination</option>
                  <option value="mock">Mock Examination</option>
                  <option value="entrance">Entrance Examination</option>
                </optgroup>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category</label>
              <select name="category_type" class="form-select" id="categoryType">
                <option value="internal">Internal</option>
                <option value="external">External</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Department</label>
              <select name="department_id" class="form-select">
                <option value="">Select Department</option>
                <?php foreach ($depts as $d): ?>
                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Semester</label>
              <select name="semester_id" class="form-select">
                <option value="">Not Semester Specific</option>
                <?php foreach ($semesters as $sem): ?>
                <option value="<?= $sem['id'] ?>"><?= e($sem['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Academic Year</label>
              <select name="academic_year_id" class="form-select">
                <?php foreach ($years as $y): ?>
                <option value="<?= $y['id'] ?>" <?= $y['id']==$ayId?'selected':'' ?>><?= e($y['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Describe the exam content, topics covered, etc."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Instructions for Students</label>
              <textarea name="instructions" class="form-control" rows="2" placeholder="e.g. Answer all questions. Time allowed: 2 hours."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tags <small class="text-muted">(comma separated)</small></label>
              <input type="text" name="tags" class="form-control" placeholder="e.g. algebra, chapter3, revision">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Options & Submit -->
    <div class="col-md-4">
      <!-- Permissions -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-lock text-warning me-2"></i>Access & Security</h6></div>
        <div class="card-body">
          <div class="form-check form-switch mb-3">
            <input type="hidden" name="is_public" value="0">
            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublic">
            <label class="form-check-label fw-semibold" for="isPublic">
              Visible to Students
              <div class="text-muted fw-normal" style="font-size:12px">Students can browse and download when approved</div>
            </label>
          </div>
          <div class="form-check form-switch">
            <input type="hidden" name="watermark" value="0">
            <input class="form-check-input" type="checkbox" name="watermark" value="1" id="watermark">
            <label class="form-check-label fw-semibold" for="watermark">
              Apply Watermark
              <div class="text-muted fw-normal" style="font-size:12px">Add school name to PDF pages</div>
            </label>
          </div>
        </div>
      </div>

      <!-- Approval Workflow -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-project-diagram text-info me-2"></i>Approval Workflow</h6></div>
        <div class="card-body small">
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0;font-size:11px">1</div>
            <div><div class="fw-semibold">You Upload</div><div class="text-muted">Status: Draft → Submitted</div></div>
          </div>
          <div class="upload-workflow-line ms-3 ps-1 border-start border-2 py-1 mb-2"><div class="text-muted ms-2" style="font-size:11px">↓</div></div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0;font-size:11px">2</div>
            <div><div class="fw-semibold">Dept Head Reviews</div><div class="text-muted">Approve → Under Review</div></div>
          </div>
          <div class="upload-workflow-line ms-3 ps-1 border-start border-2 py-1 mb-2"><div class="text-muted ms-2" style="font-size:11px">↓</div></div>
          <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;flex-shrink:0;font-size:11px">3</div>
            <div><div class="fw-semibold">Principal Approves</div><div class="text-muted">Status: Approved ✓</div></div>
          </div>
          <?php if (Auth::hasRole(['super_admin','principal'])): ?>
          <div class="alert alert-success mt-3 py-2 mb-0 small"><i class="fas fa-bolt me-1"></i>As Principal/Admin, your uploads are <strong>auto-approved</strong>.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Submit -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2" id="submitBtn" disabled>
            <i class="fas fa-upload me-2"></i>Upload Examination(s)
          </button>
          <button type="button" class="btn btn-success w-100 mb-2" id="submitDraftBtn" onclick="document.getElementById('uploadForm').submit()">
            <i class="fas fa-save me-2"></i>Save as Draft
          </button>
          <a href="<?= url('exam-repository') ?>" class="btn btn-outline-secondary w-100">
            <i class="fas fa-times me-2"></i>Cancel
          </a>
          <div class="mt-3 text-muted small">
            <i class="fas fa-info-circle me-1"></i>Drafts are private until submitted for review.
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- CSS for upload zone -->
<style>
.upload-dropzone {
  border: 2px dashed #1565C0;
  background: #F8FBFF;
  transition: all 0.3s;
  cursor: pointer;
}
.upload-dropzone.dragover {
  background: #E3F2FD;
  border-color: #0D47A1;
  transform: scale(1.01);
}
.file-item {
  background: #F8F9FA;
  border: 1px solid #E9ECEF;
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.file-item .file-icon { font-size: 1.4rem; width: 30px; text-align: center; flex-shrink: 0; }
.file-item .file-name { font-size: 13px; font-weight: 600; }
.file-item .file-size { font-size: 11px; color: #6c757d; }
.file-item .file-remove { margin-left: auto; cursor: pointer; }
</style>

<script>
var selectedFiles = [];

function getFileIcon(name) {
  var ext = name.split('.').pop().toLowerCase();
  var icons = {
    'pdf':'fa-file-pdf text-danger', 'doc':'fa-file-word text-primary', 'docx':'fa-file-word text-primary',
    'xls':'fa-file-excel text-success', 'xlsx':'fa-file-excel text-success',
    'ppt':'fa-file-powerpoint text-warning', 'pptx':'fa-file-powerpoint text-warning',
    'zip':'fa-file-archive text-secondary'
  };
  return 'fas ' + (icons[ext] || 'fa-file text-muted');
}

function formatBytes(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
  return (bytes/1048576).toFixed(1) + ' MB';
}

function renderFileList() {
  var list = document.getElementById('fileList');
  var items = document.getElementById('fileItems');
  var count = document.getElementById('fileCount');
  var submitBtn = document.getElementById('submitBtn');

  if (selectedFiles.length === 0) {
    list.classList.add('d-none');
    submitBtn.disabled = true;
    return;
  }

  list.classList.remove('d-none');
  count.textContent = selectedFiles.length;
  submitBtn.disabled = false;

  items.innerHTML = '';
  selectedFiles.forEach(function(file, idx) {
    items.innerHTML += `
      <div class="file-item">
        <span class="file-icon"><i class="${getFileIcon(file.name)}"></i></span>
        <div class="flex-fill">
          <div class="file-name">${file.name}</div>
          <div class="file-size">${formatBytes(file.size)}</div>
        </div>
        <button type="button" class="btn btn-xs btn-outline-danger file-remove" onclick="removeFile(${idx})">
          <i class="fas fa-times"></i>
        </button>
      </div>`;
  });
}

function removeFile(idx) {
  selectedFiles.splice(idx, 1);
  updateFileInput();
  renderFileList();
}

function updateFileInput() {
  var dt = new DataTransfer();
  selectedFiles.forEach(function(f) { dt.items.add(f); });
  document.getElementById('fileInput').files = dt.files;
}

document.getElementById('fileInput').addEventListener('change', function(e) {
  Array.from(e.target.files).forEach(function(f) { selectedFiles.push(f); });
  renderFileList();
});

// Drag and drop
var dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', function(e) { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', function() { dropZone.classList.remove('dragover'); });
dropZone.addEventListener('drop', function(e) {
  e.preventDefault();
  dropZone.classList.remove('dragover');
  Array.from(e.dataTransfer.files).forEach(function(f) { selectedFiles.push(f); });
  updateFileInput();
  renderFileList();
});
dropZone.addEventListener('click', function(e) {
  if (e.target === dropZone || e.target.closest('.upload-icon') || e.target.tagName === 'P' || e.target.tagName === 'H6') {
    document.getElementById('fileInput').click();
  }
});

// Auto-set category type based on exam type
document.getElementById('examTypeSelect').addEventListener('change', function() {
  var external = ['regional','national','mock','entrance'];
  document.getElementById('categoryType').value = external.includes(this.value) ? 'external' : 'internal';
});
</script>
