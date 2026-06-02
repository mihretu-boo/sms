<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chalkboard text-primary me-2"></i>Assign Subjects to Class</h4>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<!-- Class selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4"><label class="form-label small fw-semibold">Select Class</label><select name="class_id" class="form-select" onchange="this.form.submit()"><option value="">Choose a class...</option><?php foreach ($classes as $cls): ?><option value="<?= $cls['id'] ?>" <?= selected($classId,$cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option><?php endforeach; ?></select></div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($subjects)): ?>
<form action="<?= url('academics/assign-subjects') ?>" method="POST">
  <?= csrfField() ?>
  <input type="hidden" name="class_id" value="<?= e($classId) ?>">
  <input type="hidden" name="semester_id" value="<?= e($semId) ?>">

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
      <h6 class="mb-0 fw-semibold">Available Subjects for this Grade</h6>
      <small class="text-muted">Check subjects to assign, select teacher for each</small>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr><th width="40"><input type="checkbox" id="selectAllSubjects" class="form-check-input"></th><th>Subject</th><th>Code</th><th>Teacher</th><th>Periods/Week</th></tr>
          </thead>
          <tbody>
            <?php foreach ($subjects as $s): $isAssigned = isset($assigned[$s['id']]); ?>
            <tr class="<?= $isAssigned ? 'table-success' : '' ?>">
              <td><input type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>" class="form-check-input subject-cb" <?= $isAssigned ? 'checked' : '' ?>></td>
              <td class="fw-semibold"><?= e($s['name']) ?></td>
              <td class="font-monospace text-muted"><?= e($s['code']) ?></td>
              <td>
                <select name="teacher_id[<?= $s['id'] ?>]" class="form-select form-select-sm" style="min-width:160px">
                  <option value="">Select Teacher</option>
                  <?php foreach ($teachers as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= selected($assigned[$s['id']]['teacher_id']??'',$t['id']) ?>><?= e($t['first_name'].' '.$t['last_name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="number" name="periods[<?= $s['id'] ?>]" class="form-control form-control-sm" value="<?= $assigned[$s['id']]['periods_per_week']??3 ?>" min="1" max="10" style="width:60px"></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-white py-3 d-flex justify-content-between">
      <small class="text-muted">Selected subjects will be assigned to this class for Semester <?= $semId ?></small>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Assignments</button>
    </div>
  </div>
</form>
<?php elseif ($classId): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No subjects found for this grade. Please add subjects first.</div>
<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-chalkboard fa-3x mb-3"></i><br><h6>Select a class to manage subject assignments</h6></div></div>
<?php endif; ?>

<script>
document.getElementById('selectAllSubjects') && document.getElementById('selectAllSubjects').addEventListener('change', function() {
  document.querySelectorAll('.subject-cb').forEach(c => c.checked = this.checked);
});
</script>
