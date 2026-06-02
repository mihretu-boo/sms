<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-building text-primary me-2"></i>Departments</h4></div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#deptModal"><i class="fas fa-plus me-1"></i>Add Department</button>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<div class="row g-3">
  <?php foreach ($departments as $d): ?>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h6 class="fw-bold mb-1"><?= e($d['name']) ?></h6>
            <span class="badge bg-primary"><?= e($d['code']) ?></span>
          </div>
          <button class="btn btn-xs btn-outline-secondary" onclick="editDept(<?= htmlspecialchars(json_encode($d)) ?>)"><i class="fas fa-edit"></i></button>
        </div>
        <div class="text-muted small mb-2"><?= e($d['description']??'') ?></div>
        <div class="d-flex gap-3 small text-muted">
          <span><i class="fas fa-users me-1"></i><?= $d['staff_count'] ?> staff</span>
          <?php if ($d['head_first']): ?><span><i class="fas fa-user-tie me-1"></i><?= e($d['head_first'].' '.$d['head_last']) ?></span><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="modal fade" id="deptModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="deptModalTitle">Add Department</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('academics/departments') ?>" method="POST" id="deptForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="deptId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" name="name" id="deptName" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Code</label><input type="text" name="code" id="deptCode" class="form-control" maxlength="10"></div>
            <div class="col-12"><label class="form-label">Head of Department</label><select name="head_id" id="deptHead" class="form-select"><option value="">Select HOD</option><?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['first_name'].' '.$t['last_name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="deptDesc" class="form-control" rows="3"></textarea></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function editDept(d) {
  document.getElementById('deptModalTitle').textContent = 'Edit Department';
  document.getElementById('deptId').value = d.id;
  document.getElementById('deptName').value = d.name;
  document.getElementById('deptCode').value = d.code || '';
  document.getElementById('deptHead').value = d.head_id || '';
  document.getElementById('deptDesc').value = d.description || '';
  new bootstrap.Modal(document.getElementById('deptModal')).show();
}
</script>
