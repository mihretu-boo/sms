<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-clock text-primary me-2"></i>Manage Timetable</h4>
  </div>
  <a href="<?= url('timetable') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<!-- Class selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4"><select name="class_id" class="form-select" onchange="this.form.submit()"><option value="">Select Class</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= selected($classId,$c['id']) ?>>Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach; ?></select></div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($subjects)): ?>
<div class="alert alert-info small"><i class="fas fa-info-circle me-2"></i>Set the timetable slot by selecting subject, teacher, time, and room for each period and day.</div>

<form action="<?= url('timetable/save') ?>" method="POST">
  <?= csrfField() ?>
  <input type="hidden" name="class_id" value="<?= e($classId) ?>">
  <input type="hidden" name="semester_id" value="<?= e($semId) ?>">

  <?php
  $days    = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
  $periods = [1=>'08:00-08:45',2=>'08:45-09:30',3=>'09:45-10:30',4=>'10:30-11:15',5=>'11:30-12:15',6=>'13:00-13:45',7=>'13:45-14:30',8=>'14:30-15:15'];
  ?>

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered mb-0 small">
          <thead class="table-primary">
            <tr><th style="width:80px">Period</th><?php foreach ($days as $d): ?><th class="text-center"><?= $d ?></th><?php endforeach; ?></tr>
          </thead>
          <tbody>
            <?php foreach ($periods as $p => $time): ?>
            <tr>
              <td class="bg-light text-center fw-semibold"><div>P<?= $p ?></div><div class="text-muted" style="font-size:10px"><?= $time ?></div></td>
              <?php foreach ($days as $day): ?>
              <td class="p-1" style="min-width:160px">
                <select name="timetable[<?= $day ?>][<?= $p ?>][subject_id]" class="form-select form-select-sm mb-1">
                  <option value="">—</option>
                  <?php foreach ($subjects as $s): ?><option value="<?= $s['subject_id'] ?>"><?= e($s['subject_name']) ?></option><?php endforeach; ?>
                </select>
                <select name="timetable[<?= $day ?>][<?= $p ?>][teacher_id]" class="form-select form-select-sm mb-1">
                  <option value="">Teacher</option>
                  <?php foreach ($teachers as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['first_name'].' '.$t['last_name']) ?></option><?php endforeach; ?>
                </select>
                <div class="d-flex gap-1">
                  <input type="time" name="timetable[<?= $day ?>][<?= $p ?>][start_time]" class="form-control form-control-sm" value="<?= substr($time,0,5) ?>" style="width:80px">
                  <input type="text" name="timetable[<?= $day ?>][<?= $p ?>][room]" class="form-control form-control-sm" placeholder="Room">
                </div>
              </td>
              <?php endforeach; ?>
            </tr>
            <?php if ($p==2||$p==5): ?>
            <tr class="table-secondary"><td colspan="6" class="text-center text-muted py-1 small"><?= $p==2?'Break (15 min)':'Lunch Break (45 min)' ?></td></tr>
            <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer bg-white py-3 text-end">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Timetable</button>
    </div>
  </div>
</form>
<?php elseif ($classId): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No subjects assigned to this class yet. Please <a href="<?= url('academics/assign-subjects?class_id='.$classId) ?>">assign subjects</a> first.</div>
<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-clock fa-3x mb-3"></i><br><h6>Select a class to create its timetable</h6></div></div>
<?php endif; ?>
