<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-calendar-check text-primary me-2"></i>Take Attendance</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('attendance') ?>">Attendance</a></li><li class="breadcrumb-item active">Take</li></ol></nav>
  </div>
</div>

<!-- Class & Date Selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <form method="GET" action="<?= url('attendance/take') ?>" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Class <span class="text-danger">*</span></label>
        <select name="class_id" class="form-select" required>
          <option value="">Select Class</option>
          <?php foreach ($classes as $cls): ?>
          <option value="<?= $cls['id'] ?>" <?= selected($classId, $cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control flatpickr" value="<?= e($date) ?>" max="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-arrow-right me-1"></i>Load Students</button>
      </div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($students)): ?>
<!-- Attendance Form -->
<form action="<?= url('attendance/save') ?>" method="POST" id="attendanceForm">
  <?= csrfField() ?>
  <input type="hidden" name="class_id" value="<?= e($classId) ?>">
  <input type="hidden" name="date" value="<?= e($date) ?>">

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
      <h6 class="mb-0 fw-semibold">
        <i class="fas fa-users text-primary me-2"></i>
        <?= count($students) ?> Students — <?= date('D, d M Y', strtotime($date)) ?>
        <?php if (!empty($existing)): ?><span class="badge bg-warning ms-2">Attendance already recorded</span><?php endif; ?>
      </h6>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')"><i class="fas fa-check me-1"></i>All Present</button>
        <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')"><i class="fas fa-times me-1"></i>All Absent</button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th width="40">#</th>
              <th>Student</th>
              <th>Student ID</th>
              <th class="text-center">
                <div class="d-flex gap-3 justify-content-center">
                  <span class="text-success"><i class="fas fa-check-circle me-1"></i>Present</span>
                  <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Absent</span>
                  <span class="text-warning"><i class="fas fa-clock me-1"></i>Late</span>
                  <span class="text-info"><i class="fas fa-info-circle me-1"></i>Excused</span>
                </div>
              </th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $i => $s): ?>
            <tr class="att-row" data-idx="<?= $i ?>">
              <td class="text-muted small"><?= $i + 1 ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photoUrl($s['photo'],'student') ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                  <div>
                    <div class="fw-semibold small"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge bg-light text-dark small"><?= e($s['student_id']) ?></span></td>
              <td>
                <div class="d-flex gap-3 justify-content-center">
                  <?php $curStatus = $s['att_status'] ?: 'present'; ?>
                  <?php foreach (['present'=>'success','absent'=>'danger','late'=>'warning','excused'=>'info'] as $st => $color): ?>
                  <div class="form-check form-check-inline mb-0">
                    <input class="form-check-input att-radio" type="radio" name="status[<?= $s['id'] ?>]" id="att_<?= $s['id'] ?>_<?= $st ?>" value="<?= $st ?>" <?= checked($curStatus, $st) ?> required>
                    <label class="form-check-label text-<?= $color ?>" for="att_<?= $s['id'] ?>_<?= $st ?>">
                      <i class="fas fa-<?= match($st) {'present'=>'check','absent'=>'times','late'=>'clock','excused'=>'info-circle'} ?>"></i>
                    </label>
                  </div>
                  <?php endforeach; ?>
                </div>
              </td>
              <td>
                <input type="text" name="remarks[<?= $s['id'] ?>]" class="form-control form-control-sm" placeholder="Optional..." value="<?= e($s['remarks']??'') ?>" style="max-width:150px">
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
      <div class="attendance-summary small text-muted" id="attSummary">
        <span class="text-success me-3"><i class="fas fa-check-circle me-1"></i>Present: <strong id="cntPresent">0</strong></span>
        <span class="text-danger me-3"><i class="fas fa-times-circle me-1"></i>Absent: <strong id="cntAbsent">0</strong></span>
        <span class="text-warning me-3"><i class="fas fa-clock me-1"></i>Late: <strong id="cntLate">0</strong></span>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Attendance</button>
    </div>
  </div>
</form>
<?php elseif ($classId): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No active students found in this class.</div>
<?php else: ?>
<div class="alert alert-light border text-center py-5">
  <i class="fas fa-hand-pointer fa-3x text-muted mb-3"></i><br>
  <h6 class="text-muted">Select a class and date to start taking attendance</h6>
</div>
<?php endif; ?>

<script>
function markAll(status) {
  document.querySelectorAll('.att-row').forEach(function(row) {
    var stuId = row.querySelector('.att-radio').name.match(/\[(\d+)\]/)[1];
    var radio = document.getElementById('att_' + stuId + '_' + status);
    if (radio) radio.checked = true;
  });
  updateSummary();
}

function updateSummary() {
  var counts = {present:0, absent:0, late:0, excused:0};
  document.querySelectorAll('.att-radio:checked').forEach(function(r) { counts[r.value] = (counts[r.value]||0)+1; });
  document.getElementById('cntPresent').textContent = counts.present || 0;
  document.getElementById('cntAbsent').textContent  = counts.absent || 0;
  document.getElementById('cntLate').textContent    = counts.late || 0;
}

document.querySelectorAll('.att-radio').forEach(function(r) { r.addEventListener('change', updateSummary); });
updateSummary();
</script>
