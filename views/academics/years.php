<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i>Academic Years</h4>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#yearModal"><i class="fas fa-plus me-1"></i>Add Year</button>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<div class="row g-3">
  <?php foreach ($years as $y): ?>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm <?= $y['status']==='active'?'border-start border-4 border-success':'' ?>">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h5 class="fw-bold text-primary mb-0"><?= e($y['name']) ?></h5>
          <?= $y['status']==='active' ? '<span class="badge bg-success">Active</span>' : getStatusBadge($y['status']) ?>
        </div>
        <div class="text-muted small mb-3">
          <i class="fas fa-calendar me-1"></i><?= formatDate($y['start_date']) ?> — <?= formatDate($y['end_date']) ?>
        </div>
        <div class="row g-2 text-center small">
          <div class="col-6"><div class="bg-light rounded p-2"><div class="fw-bold text-primary"><?= $y['class_count'] ?></div><div class="text-muted">Classes</div></div></div>
          <div class="col-6"><div class="bg-light rounded p-2"><div class="fw-bold text-success"><?= $y['student_count'] ?></div><div class="text-muted">Students</div></div></div>
        </div>
      </div>
      <div class="card-footer bg-white border-top-0 d-flex gap-2">
        <button class="btn btn-xs btn-outline-primary" onclick="editYear(<?= htmlspecialchars(json_encode($y)) ?>)"><i class="fas fa-edit me-1"></i>Edit</button>
        <?php if ($y['status']!=='active'): ?>
        <form action="<?= url('academics/years') ?>" method="POST" class="d-inline">
          <?= csrfField() ?>
          <input type="hidden" name="id" value="<?= $y['id'] ?>">
          <input type="hidden" name="name" value="<?= e($y['name']) ?>">
          <input type="hidden" name="start_date" value="<?= $y['start_date'] ?>">
          <input type="hidden" name="end_date" value="<?= $y['end_date'] ?>">
          <input type="hidden" name="status" value="active">
          <button class="btn btn-xs btn-outline-success" onclick="return confirm('Set this as the active year?')"><i class="fas fa-check me-1"></i>Set Active</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add/Edit Year Modal -->
<div class="modal fade" id="yearModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="yearModalTitle">Add Academic Year</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('academics/years') ?>" method="POST" id="yearForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="yearId">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Year Name <span class="text-danger">*</span></label><input type="text" name="name" id="yearName" class="form-control" placeholder="e.g. 2025-2026" required></div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" id="yearStart" class="form-control flatpickr" required></div>
            <div class="col-md-6"><label class="form-label">End Date <span class="text-danger">*</span></label><input type="date" name="end_date" id="yearEnd" class="form-control flatpickr" required></div>
          </div>
          <div class="mt-3"><label class="form-label">Status</label><select name="status" id="yearStatus" class="form-select"><option value="inactive">Inactive</option><option value="active">Active</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
      </form>
    </div>
  </div>
</div>

<script>
function editYear(y) {
  document.getElementById('yearModalTitle').textContent = 'Edit Academic Year';
  document.getElementById('yearId').value = y.id;
  document.getElementById('yearName').value = y.name;
  document.getElementById('yearStart').value = y.start_date;
  document.getElementById('yearEnd').value = y.end_date;
  document.getElementById('yearStatus').value = y.status;
  new bootstrap.Modal(document.getElementById('yearModal')).show();
}
</script>
