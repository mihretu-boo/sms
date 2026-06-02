<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-book text-primary me-2"></i>Subjects</h4>
  </div>
  <?php if (Auth::can('academics')): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal"><i class="fas fa-plus me-1"></i>Add Subject</button>
  <?php endif; ?>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-3"><select name="grade" class="form-select" onchange="this.form.submit()"><option value="">All Grades</option><?php foreach (['9','10','11','12'] as $g): ?><option value="<?= $g ?>" <?= selected($grade,$g) ?>>Grade <?= $g ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><select name="dept_id" class="form-select" onchange="this.form.submit()"><option value="">All Departments</option><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>" <?= selected($deptId,$d['id']) ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-auto"><a href="<?= url('academics/subjects') ?>" class="btn btn-outline-secondary">Clear</a></div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="subjectsTable">
        <thead class="table-light"><tr><th>Code</th><th>Subject Name</th><th>Department</th><th>Grade</th><th>Credits</th><th>Type</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($subjects as $s): ?>
          <tr>
            <td class="font-monospace fw-semibold"><?= e($s['code']) ?></td>
            <td class="fw-semibold"><?= e($s['name']) ?></td>
            <td><span class="badge bg-light text-dark"><?= e($s['dept_name']??'—') ?></span></td>
            <td><?= $s['grade']==='all'?'All Grades':'Grade '.e($s['grade']) ?></td>
            <td><?= $s['credit_hours'] ?></td>
            <td><span class="badge bg-<?= match($s['type']){'core'=>'primary','elective'=>'info','optional'=>'secondary',default=>'light'} ?>"><?= ucfirst($s['type']) ?></span></td>
            <td>
              <?php if (Auth::can('academics')): ?>
              <button class="btn btn-xs btn-outline-primary" onclick="editSubject(<?= htmlspecialchars(json_encode($s)) ?>)"><i class="fas fa-edit"></i></button>
              <form action="<?= url('academics/subjects/delete/'.$s['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete subject?')">
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="subjectModalTitle">Add Subject</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('academics/subjects') ?>" method="POST" id="subjectForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="subjectId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Code <span class="text-danger">*</span></label><input type="text" name="code" id="subjectCode" class="form-control" required></div>
            <div class="col-md-8"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" id="subjectName" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" id="subjectDept" class="form-select"><option value="">None</option><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Grade</label><select name="grade" id="subjectGrade" class="form-select"><option value="all">All Grades</option><?php foreach (['9','10','11','12'] as $g): ?><option value="<?= $g ?>">Grade <?= $g ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Credits</label><input type="number" name="credit_hours" id="subjectCredits" class="form-control" value="3" min="1"></div>
            <div class="col-md-6"><label class="form-label">Type</label><select name="type" id="subjectType" class="form-select"><option value="core">Core</option><option value="elective">Elective</option><option value="optional">Optional</option></select></div>
          </div>
          <div class="mt-3"><label class="form-label">Description</label><textarea name="description" id="subjectDesc" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function editSubject(s) {
  document.getElementById('subjectModalTitle').textContent = 'Edit Subject';
  document.getElementById('subjectId').value = s.id;
  document.getElementById('subjectCode').value = s.code;
  document.getElementById('subjectName').value = s.name;
  document.getElementById('subjectDept').value = s.department_id || '';
  document.getElementById('subjectGrade').value = s.grade;
  document.getElementById('subjectCredits').value = s.credit_hours;
  document.getElementById('subjectType').value = s.type;
  new bootstrap.Modal(document.getElementById('subjectModal')).show();
}
</script>
