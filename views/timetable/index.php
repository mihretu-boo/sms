<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-clock text-primary me-2"></i>Timetable</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Timetable</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <?php if (Auth::can('academics')): ?>
    <a href="<?= url('timetable/create'.($classId ? '?class_id='.$classId : '')) ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Manage Timetable</a>
    <?php endif; ?>
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print me-1"></i>Print</button>
  </div>
</div>

<!-- Class Selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <select name="class_id" class="form-select" onchange="this.form.submit()">
          <option value="">Select Class to view</option>
          <?php foreach ($classes as $cls): ?>
          <option value="<?= $cls['id'] ?>" <?= selected($classId, $cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($timetable)): ?>
<!-- Timetable Grid -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-primary text-white py-3">
    <h6 class="mb-0"><i class="fas fa-table me-2"></i>Weekly Timetable</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered mb-0 small">
        <thead class="bg-light">
          <tr>
            <th class="text-center" width="80">Period / Time</th>
            <?php foreach ($days as $day): ?>
            <th class="text-center" style="min-width:130px"><?= $day ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($periods as $p => $time): ?>
          <tr>
            <td class="text-center bg-light fw-semibold">
              <div>P<?= $p ?></div>
              <div class="text-muted" style="font-size:10px"><?= $time['start'] ?><br><?= $time['end'] ?></div>
            </td>
            <?php foreach ($days as $day): ?>
            <td class="timetable-cell p-1">
              <?php if (!empty($timetable[$day][$p])): $slot = $timetable[$day][$p]; ?>
              <div class="timetable-slot">
                <div class="fw-semibold" style="color:#1565C0"><?= e($slot['subject_name']) ?></div>
                <div class="text-muted" style="font-size:10px"><?= e($slot['first_name'].' '.$slot['last_name']) ?></div>
                <?php if ($slot['room']): ?><div class="text-muted" style="font-size:10px"><i class="fas fa-door-open me-1"></i><?= e($slot['room']) ?></div><?php endif; ?>
              </div>
              <?php else: ?>
              <div class="text-muted text-center small py-2">—</div>
              <?php endif; ?>
            </td>
            <?php endforeach; ?>
          </tr>
          <?php if ($p == 2 || $p == 5): ?>
          <tr class="table-secondary">
            <td class="text-center text-muted small py-1 fw-semibold" colspan="<?= count($days)+1 ?>">
              <i class="fas fa-coffee me-1"></i><?= $p == 2 ? 'Morning Break (15 min)' : 'Lunch Break (45 min)' ?>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php elseif ($classId): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No timetable found for this class. <a href="<?= url('timetable/create?class_id='.$classId) ?>">Create timetable</a></div>
<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5">
  <div class="text-muted"><i class="fas fa-clock fa-3x mb-3"></i><br><h6>Select a class to view its timetable</h6></div>
</div>
<?php endif; ?>

<style>
@media print {
  .topbar, #sidebar, .page-footer, form, .btn, .breadcrumb { display: none !important; }
  .card { box-shadow: none !important; }
}
</style>
