<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-book text-primary me-2"></i>Subject Management</h4>
    <p class="text-muted small mb-0">Ethiopian Secondary School Curriculum — Shalaka Jatan Ali</p>
  </div>
  <?php if (Auth::can('academics')): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal">
    <i class="fas fa-plus me-1"></i>Add Subject
  </button>
  <?php endif; ?>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<!-- Curriculum Overview Cards -->
<div class="row g-3 mb-4">
  <?php
  $gradeSummary = [];
  foreach ($gradeCount as $row) {
    $gradeSummary[$row['grade']][$row['stream']] = $row['cnt'];
  }
  $gradeGroups = [
    '9'  => ['label'=>'Grade 9',        'streams'=>['all'=>'13 subjects (all students)'], 'color'=>'primary'],
    '10' => ['label'=>'Grade 10',       'streams'=>['all'=>'13 subjects (all students)'], 'color'=>'info'],
    '11' => ['label'=>'Grade 11',       'streams'=>['Natural'=>'8 subjects','Social'=>'8 subjects'], 'color'=>'success'],
    '12' => ['label'=>'Grade 12',       'streams'=>['Natural'=>'8 subjects','Social'=>'8 subjects'], 'color'=>'warning'],
  ];
  ?>
  <?php foreach ($gradeGroups as $g => $meta): ?>
  <div class="col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-<?= $meta['color'] ?> text-white py-2">
        <h6 class="mb-0 fw-bold"><?= $meta['label'] ?></h6>
      </div>
      <div class="card-body py-3 small">
        <?php if ($g <= '10'): ?>
        <div class="text-center">
          <div class="fw-bold fs-4 text-<?= $meta['color'] ?>"><?= $gradeSummary[$g]['all'] ?? 0 ?></div>
          <div class="text-muted">Subjects (All Students)</div>
        </div>
        <?php else: ?>
        <div class="row g-2 text-center">
          <div class="col-6">
            <div class="bg-success-light rounded p-2">
              <div class="fw-bold text-success"><?= ($gradeSummary[$g]['all'] ?? 0) + ($gradeSummary[$g]['natural'] ?? 0) ?></div>
              <div class="text-muted" style="font-size:11px"><i class="fas fa-flask me-1"></i>Natural</div>
            </div>
          </div>
          <div class="col-6">
            <div class="bg-info-light rounded p-2">
              <div class="fw-bold text-info"><?= ($gradeSummary[$g]['all'] ?? 0) + ($gradeSummary[$g]['social'] ?? 0) ?></div>
              <div class="text-muted" style="font-size:11px"><i class="fas fa-globe me-1"></i>Social</div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-white border-top-0 pt-0">
        <a href="<?= url('academics/subjects?grade='.$g) ?>" class="btn btn-sm btn-outline-<?= $meta['color'] ?> w-100">View Subjects</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Grade</label>
        <select name="grade" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Grades</option>
          <?php foreach (['9','10','11','12'] as $g): ?>
          <option value="<?= $g ?>" <?= selected($grade,$g) ?>>Grade <?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Stream</label>
        <select name="stream" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Streams</option>
          <option value="all"     <?= selected($stream,'all') ?>>Common (All)</option>
          <option value="natural" <?= selected($stream,'natural') ?>>Natural Science</option>
          <option value="social"  <?= selected($stream,'social') ?>>Social Science</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Department</label>
        <select name="dept_id" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">All Departments</option>
          <?php foreach ($depts as $d): ?>
          <option value="<?= $d['id'] ?>" <?= selected($deptId,$d['id']) ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label small">&nbsp;</label>
        <a href="<?= url('academics/subjects') ?>" class="btn btn-sm btn-outline-secondary d-block">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Subjects Table -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Subjects List</h6>
    <span class="text-muted small"><?= count($subjects) ?> subjects shown</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="subjectsTable">
        <thead class="table-light">
          <tr><th>Code</th><th>Subject Name</th><th>Grade</th><th>Stream</th><th>Department</th><th class="text-center">Hrs/Week</th><th>Type</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($subjects as $s):
            $streamBadge = match($s['stream'] ?? 'all') {
                'natural' => '<span class="badge bg-success-light text-success border border-success"><i class="fas fa-flask me-1"></i>Natural Science</span>',
                'social'  => '<span class="badge bg-info-light text-info border border-info"><i class="fas fa-globe me-1"></i>Social Science</span>',
                default   => '<span class="badge bg-light text-dark border">All Students</span>',
            };
          ?>
          <tr>
            <td class="font-monospace fw-semibold text-primary"><?= e($s['code']) ?></td>
            <td class="fw-semibold"><?= e($s['name']) ?></td>
            <td><span class="badge bg-primary">Grade <?= e($s['grade']) ?></span></td>
            <td><?= $streamBadge ?></td>
            <td class="text-muted small"><?= e($s['dept_name'] ?? '—') ?></td>
            <td class="text-center"><?= $s['periods_week'] ?? $s['credit_hours'] ?></td>
            <td><span class="badge bg-<?= $s['type']==='core'?'primary':'secondary' ?>"><?= ucfirst($s['type']) ?></span></td>
            <td>
              <?php if (Auth::can('academics')): ?>
              <button class="btn btn-xs btn-outline-primary" onclick="editSubject(<?= htmlspecialchars(json_encode($s)) ?>)"><i class="fas fa-edit"></i></button>
              <form action="<?= url('academics/subjects/delete/'.$s['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                <?= csrfField() ?><button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add/Edit Subject Modal -->
<?php if (Auth::can('academics')): ?>
<div class="modal fade" id="subjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="subjectModalTitle">Add Subject</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('academics/subjects') ?>" method="POST" id="subjectForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="subjectId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Code <span class="text-danger">*</span></label><input type="text" name="code" id="subjectCode" class="form-control" required placeholder="e.g. MATH9"></div>
            <div class="col-md-9"><label class="form-label">Subject Name <span class="text-danger">*</span></label><input type="text" name="name" id="subjectName" class="form-control" required placeholder="e.g. Mathematics"></div>
            <div class="col-md-3">
              <label class="form-label">Grade <span class="text-danger">*</span></label>
              <select name="grade" id="subjectGrade" class="form-select" required onchange="updateStreamVisibility()">
                <?php foreach (['9','10','11','12'] as $g): ?><option value="<?= $g ?>"><?= $g ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3" id="streamDiv">
              <label class="form-label">Stream</label>
              <select name="stream" id="subjectStream" class="form-select">
                <option value="all">All Students</option>
                <option value="natural">Natural Science</option>
                <option value="social">Social Science</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Hrs/Week</label>
              <input type="number" name="periods_week" id="subjectPeriods" class="form-control" value="3" min="1" max="10">
            </div>
            <div class="col-md-3">
              <label class="form-label">Credit Hours</label>
              <input type="number" name="credit_hours" id="subjectCredits" class="form-control" value="3" min="1">
            </div>
            <div class="col-md-4">
              <label class="form-label">Department</label>
              <select name="department_id" id="subjectDept" class="form-select">
                <option value="">None</option>
                <?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Type</label>
              <select name="type" id="subjectType" class="form-select">
                <option value="core">Core</option><option value="elective">Elective</option><option value="optional">Optional</option>
              </select>
            </div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="subjectDesc" class="form-control" rows="2"></textarea></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function updateStreamVisibility() {
  var g = document.getElementById('subjectGrade').value;
  document.getElementById('streamDiv').style.display = (g === '11' || g === '12') ? '' : 'none';
  if (g !== '11' && g !== '12') document.getElementById('subjectStream').value = 'all';
}
updateStreamVisibility();

function editSubject(s) {
  document.getElementById('subjectModalTitle').textContent = 'Edit Subject';
  document.getElementById('subjectId').value      = s.id;
  document.getElementById('subjectCode').value    = s.code;
  document.getElementById('subjectName').value    = s.name;
  document.getElementById('subjectGrade').value   = s.grade;
  document.getElementById('subjectStream').value  = s.stream || 'all';
  document.getElementById('subjectPeriods').value = s.periods_week || s.credit_hours || 3;
  document.getElementById('subjectCredits').value = s.credit_hours || 3;
  document.getElementById('subjectDept').value    = s.department_id || '';
  document.getElementById('subjectType').value    = s.type;
  document.getElementById('subjectDesc').value    = s.description || '';
  updateStreamVisibility();
  new bootstrap.Modal(document.getElementById('subjectModal')).show();
}
</script>
