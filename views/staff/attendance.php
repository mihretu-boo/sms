<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-clock text-primary me-2"></i>Staff Attendance</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('staff') ?>">Staff</a></li><li class="breadcrumb-item active">Attendance</li></ol></nav>
  </div>
</div>

<!-- Date selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3"><label class="form-label small fw-semibold">Date</label><input type="date" name="date" class="form-control flatpickr" value="<?= e($date) ?>" max="<?= date('Y-m-d') ?>"></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="fas fa-arrow-right me-1"></i>Load</button></div>
    </form>
  </div>
</div>

<form action="<?= url('staff/attendance') ?>" method="POST">
  <?= csrfField() ?>
  <input type="hidden" name="date" value="<?= e($date) ?>">

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
      <h6 class="mb-0 fw-semibold"><?= count($staff) ?> Staff Members — <?= date('D, d M Y', strtotime($date)) ?></h6>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-success" onclick="markAllStaff('present')"><i class="fas fa-check me-1"></i>All Present</button>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr><th>Staff Member</th><th>Department</th><th>Check In</th><th>Check Out</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach ($staff as $s): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= photoUrl($s['photo'],'user') ?>" class="rounded-circle" width="30" height="30" style="object-fit:cover">
                  <div>
                    <div class="fw-semibold"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
                    <div class="text-muted" style="font-size:11px"><?= e($s['position']) ?></div>
                  </div>
                </div>
              </td>
              <td class="small text-muted"><?= e($s['dept_name']??'—') ?></td>
              <td><input type="time" name="check_in[<?= $s['id'] ?>]" class="form-control form-control-sm" value="<?= e($s['check_in']??'08:00') ?>" style="width:110px"></td>
              <td><input type="time" name="check_out[<?= $s['id'] ?>]" class="form-control form-control-sm" value="<?= e($s['check_out']??'17:00') ?>" style="width:110px"></td>
              <td>
                <select name="status[<?= $s['id'] ?>]" class="form-select form-select-sm" style="width:130px">
                  <?php $cur = $s['att_status']??'present'; ?>
                  <?php foreach (['present'=>'Present','absent'=>'Absent','late'=>'Late','half_day'=>'Half Day','on_leave'=>'On Leave'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= selected($cur,$v) ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-white py-3 text-end">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Attendance</button>
    </div>
  </div>
</form>

<script>
function markAllStaff(status) {
  document.querySelectorAll('select[name^="status["]').forEach(function(s){ s.value = status; });
}
</script>
