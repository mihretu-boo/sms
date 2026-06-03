<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-layer-group text-primary me-2"></i>Classes</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('academics') ?>">Academics</a></li><li class="breadcrumb-item active">Classes</li></ol></nav>
  </div>
  <?php if (Auth::can('academics')): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#classModal">
    <i class="fas fa-plus me-1"></i>Add Class
  </button>
  <?php endif; ?>
</div>

<!-- Academic nav tabs -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<!-- Summary by grade -->
<div class="row g-3 mb-4">
  <?php
  $byGrade = [];
  foreach ($classes as $c) { $byGrade[$c['grade']]['count'] = ($byGrade[$c['grade']]['count']??0)+1; $byGrade[$c['grade']]['students'] = ($byGrade[$c['grade']]['students']??0)+($c['student_count']??0); }
  foreach (['9','10','11','12'] as $g):
  ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-2"><span class="fw-bold"><?= $g ?></span></div>
      <div class="fw-bold"><?= $byGrade[$g]['count'] ?? 0 ?> Sections</div>
      <div class="text-muted small"><?= $byGrade[$g]['students'] ?? 0 ?> Students</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Classes Grid -->
<div class="row g-3">
  <?php foreach ($classes as $cls): ?>
  <div class="col-md-4 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <?php
      $gradeNum = (int)($cls['grade'] ?? 0);
      $streamBg = match($cls['stream']??'general') {'natural'=>'success','social'=>'info',default=>'primary'};
      $streamLabel = match($cls['stream']??'general') {'natural'=>'🔬 Natural','social'=>'🌍 Social',default=>''};
      ?>
      <div class="card-header bg-<?= $streamBg ?> text-white py-2 text-center">
        <div class="fw-bold fs-4">Grade <?= e($cls['grade']) ?><span class="fs-6 ms-1">-<?= e($cls['section']) ?></span></div>
        <?php if ($streamLabel): ?><small><?= $streamLabel ?></small><?php endif; ?>
      </div>
      <div class="card-body text-center py-3">
        <div class="text-muted small mb-2"><i class="fas fa-users me-1"></i><?= $cls['student_count'] ?? 0 ?> / <?= $cls['max_students'] ?> students</div>
        <?php if ($cls['teacher_first']): ?>
        <div class="small text-muted mb-1"><i class="fas fa-chalkboard-teacher me-1"></i><?= e($cls['teacher_first'].' '.$cls['teacher_last']) ?></div>
        <?php endif; ?>
        <?php if ($cls['room_no']): ?>
        <div class="small text-muted"><i class="fas fa-door-open me-1"></i>Room <?= e($cls['room_no']) ?></div>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-white pt-0 d-flex flex-wrap gap-1 justify-content-center pb-2">
        <a href="<?= url('students?class_id='.$cls['id']) ?>" class="btn btn-xs btn-outline-primary">Students</a>
        <a href="<?= url('attendance/take?class_id='.$cls['id']) ?>" class="btn btn-xs btn-outline-success">Attend.</a>
        <a href="<?= url('academics/assign-subjects?class_id='.$cls['id']) ?>" class="btn btn-xs btn-outline-info">Subjects</a>
        <?php if (Auth::can('academics')): ?>
        <button class="btn btn-xs btn-outline-secondary" onclick="editClass(<?= htmlspecialchars(json_encode($cls)) ?>)">Edit</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add/Edit Class Modal -->
<?php if (Auth::can('academics')): ?>
<div class="modal fade" id="classModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title" id="classModalTitle">Add Class</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('academics/classes') ?>" method="POST" id="classForm">
        <?= csrfField() ?>
        <input type="hidden" name="id" id="classId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Grade <span class="text-danger">*</span></label>
              <select name="grade" id="classGrade" class="form-select" required>
                <?php foreach (['9','10','11','12'] as $g): ?><option value="<?= $g ?>"><?= $g ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Section <span class="text-danger">*</span></label>
              <select name="section" id="classSection" class="form-select" required>
                <?php foreach (['A','B','C','D','E'] as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Room No</label>
              <input type="text" name="room_no" id="classRoom" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Class Teacher</label>
              <select name="class_teacher_id" id="classTeacher" class="form-select">
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $t): ?>
                <option value="<?= $t['id'] ?>"><?= e($t['first_name'].' '.$t['last_name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Max Students</label>
              <input type="number" name="max_students" id="classMax" class="form-control" value="50" min="10" max="100">
            </div>
            <div class="col-md-3">
              <label class="form-label">Stream</label>
              <select name="stream" id="classStream" class="form-select">
                <option value="general">General</option>
                <option value="natural">Natural</option>
                <option value="social">Social</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function editClass(cls) {
  document.getElementById('classModalTitle').textContent = 'Edit Class';
  document.getElementById('classId').value      = cls.id;
  document.getElementById('classGrade').value   = cls.grade;
  document.getElementById('classSection').value = cls.section;
  document.getElementById('classRoom').value    = cls.room_no || '';
  document.getElementById('classTeacher').value = cls.class_teacher_id || '';
  document.getElementById('classMax').value     = cls.max_students;
  document.getElementById('classStream').value  = cls.stream || 'general';
  new bootstrap.Modal(document.getElementById('classModal')).show();
}
</script>
