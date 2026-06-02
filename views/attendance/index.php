<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-calendar-check text-primary me-2"></i>Attendance Dashboard</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Attendance</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('attendance/take') ?>" class="btn btn-sm btn-primary"><i class="fas fa-clipboard-check me-1"></i>Take Attendance</a>
    <a href="<?= url('attendance/report') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-chart-bar me-1"></i>Reports</a>
  </div>
</div>

<!-- Today's Summary -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom py-3">
    <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-day text-primary me-2"></i>Today's Attendance — <?= date('l, d M Y') ?></h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr><th>Class</th><th>Enrolled</th><th class="text-center text-success">Present</th><th class="text-center text-danger">Absent</th><th class="text-center text-warning">Late</th><th>Rate</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($classes)): ?>
          <tr><td colspan="7" class="text-center py-5 text-muted">No class data available</td></tr>
          <?php else: foreach ($classes as $cls):
            $total = max($cls['enrolled'], 1);
            $present = $cls['present'] ?? 0;
            $absent  = $cls['absent'] ?? 0;
            $late    = $cls['late'] ?? 0;
            $rate = $present > 0 ? round(($present/$total)*100) : ($absent > 0 || $late > 0 ? 0 : null);
          ?>
          <tr>
            <td class="fw-semibold">Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></td>
            <td><?= $cls['enrolled'] ?></td>
            <td class="text-center fw-bold text-success"><?= $present ?></td>
            <td class="text-center fw-bold text-danger"><?= $absent ?></td>
            <td class="text-center fw-bold text-warning"><?= $late ?></td>
            <td>
              <?php if ($rate !== null): ?>
              <div class="progress" style="height:10px;width:80px">
                <div class="progress-bar bg-<?= $rate>=80?'success':($rate>=60?'warning':'danger') ?>" style="width:<?= $rate ?>%"></div>
              </div>
              <small class="text-muted"><?= $rate ?>%</small>
              <?php else: ?>
              <span class="text-muted small">Not taken</span>
              <?php endif; ?>
            </td>
            <td><a href="<?= url('attendance/take?class_id=') ?>?class_id=<?php /* need id */ ?>" class="btn btn-xs btn-outline-primary">Take</a></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
