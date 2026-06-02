<?php
$role = Auth::role();
$user = Auth::user();
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold">Welcome back, <?= e(explode('.', $user['username'])[0] ?? $user['username']) ?>!</h4>
    <p class="text-muted mb-0 small"><i class="fas fa-calendar me-1"></i><?= date('l, F d, Y') ?> &nbsp;|&nbsp; <?= e(getSetting('school_name','')) ?></p>
  </div>
  <div class="d-none d-md-flex gap-2">
    <?php if (Auth::can('students')): ?>
    <a href="<?= url('students/create') ?>" class="btn btn-sm btn-primary">
      <i class="fas fa-user-plus me-1"></i>Add Student
    </a>
    <?php endif; ?>
    <?php if (Auth::can('attendance.take')): ?>
    <a href="<?= url('attendance/take') ?>" class="btn btn-sm btn-success">
      <i class="fas fa-calendar-check me-1"></i>Take Attendance
    </a>
    <?php endif; ?>
  </div>
</div>

<?php if (in_array($role, ['super_admin','principal','vice_principal','registrar'])): ?>
<!-- ==== ADMIN DASHBOARD ==== -->

<!-- Stats Cards Row 1 -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary-light text-primary rounded-3">
          <i class="fas fa-users fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= number_format($total_students ?? 0) ?></div>
          <div class="stat-label text-muted small">Total Students</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-light text-success rounded-3">
          <i class="fas fa-chalkboard-teacher fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= number_format($total_staff ?? 0) ?></div>
          <div class="stat-label text-muted small">Total Staff</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-info-light text-info rounded-3">
          <i class="fas fa-layer-group fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= number_format($total_classes ?? 0) ?></div>
          <div class="stat-label text-muted small">Classes</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon <?= ($att_rate ?? 0) >= 80 ? 'bg-success-light text-success' : 'bg-warning-light text-warning' ?> rounded-3">
          <i class="fas fa-calendar-check fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= ($att_rate ?? 0) ?>%</div>
          <div class="stat-label text-muted small">Today's Attendance</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Stats Cards Row 2 -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-light text-success rounded-3">
          <i class="fas fa-money-bill-wave fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= number_format($fee_collected ?? 0, 0) ?> <small class="text-muted fs-6">ETB</small></div>
          <div class="stat-label text-muted small">This Month Income</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-danger-light text-danger rounded-3">
          <i class="fas fa-exclamation-circle fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= number_format($pending_fees ?? 0, 0) ?> <small class="text-muted fs-6">ETB</small></div>
          <div class="stat-label text-muted small">Pending Fees</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning-light text-warning rounded-3">
          <i class="fas fa-gavel fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= $open_incidents ?? 0 ?></div>
          <div class="stat-label text-muted small">Open Incidents</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-danger-light text-danger rounded-3">
          <i class="fas fa-book fa-lg"></i>
        </div>
        <div>
          <div class="stat-value"><?= $overdue_books ?? 0 ?></div>
          <div class="stat-label text-muted small">Overdue Books</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts + Tables Row -->
<div class="row g-3 mb-4">
  <!-- Attendance Chart -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-line text-primary me-2"></i>Attendance Trend (Last 7 Days)</h6>
        <a href="<?= url('attendance/analytics') ?>" class="btn btn-sm btn-outline-primary">View Details</a>
      </div>
      <div class="card-body">
        <canvas id="attendanceChart" height="120"></canvas>
      </div>
    </div>
  </div>

  <!-- Grade Distribution -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-pie text-success me-2"></i>Students by Grade</h6>
      </div>
      <div class="card-body">
        <canvas id="gradeChart" height="200"></canvas>
        <div class="mt-3">
          <?php foreach (($grade_distrib ?? []) as $g): ?>
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small text-muted">Grade <?= e($g['grade']) ?></span>
            <span class="badge bg-primary"><?= $g['count'] ?> classes</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Activity -->
<div class="row g-3 mb-4">
  <!-- Recent Payments -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-money-bill text-success me-2"></i>Recent Payments</h6>
        <a href="<?= url('finance/payments') ?>" class="btn btn-sm btn-link p-0 text-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 small">
            <thead class="table-light"><tr><th>Student</th><th>Amount</th><th>Date</th><th>Method</th></tr></thead>
            <tbody>
              <?php if (empty($recent_payments ?? [])): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No recent payments</td></tr>
              <?php else: foreach (($recent_payments ?? []) as $p): ?>
              <tr>
                <td><strong><?= e($p['first_name'] . ' ' . $p['last_name']) ?></strong><br><small class="text-muted"><?= e($p['stud_no']) ?></small></td>
                <td class="text-success fw-semibold"><?= formatMoney($p['amount']) ?></td>
                <td><?= formatDate($p['payment_date'], 'd M') ?></td>
                <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Students -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-user-plus text-primary me-2"></i>Recent Enrollments</h6>
        <a href="<?= url('students') ?>" class="btn btn-sm btn-link p-0 text-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 small">
            <thead class="table-light"><tr><th>Student</th><th>ID</th><th>Class</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($recent_students ?? [])): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No recent enrollments</td></tr>
              <?php else: foreach (($recent_students ?? []) as $s): ?>
              <tr>
                <td>
                  <?php if ($s['photo']): ?>
                  <img src="<?= photoUrl($s['photo']) ?>" class="rounded-circle me-1" width="24" height="24" style="object-fit:cover">
                  <?php endif; ?>
                  <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                </td>
                <td class="text-muted"><?= e($s['student_id']) ?></td>
                <td>Grade <?= e($s['grade'] ?? '-') ?>-<?= e($s['section'] ?? '') ?></td>
                <td><?= getStatusBadge($s['status']) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Inject chart data into JS
$chartLabels = array_column($attendance_chart ?? [], 'date');
$chartPresent = array_column($attendance_chart ?? [], 'present');
$chartAbsent  = array_column($attendance_chart ?? [], 'absent');
$gradeLabels  = array_map(fn($g) => 'Grade '.$g['grade'], $grade_distrib ?? []);
$gradeCounts  = array_column($grade_distrib ?? [], 'count');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Attendance chart
  var attCtx = document.getElementById('attendanceChart');
  if (attCtx) {
    new Chart(attCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
          { label: 'Present', data: <?= json_encode($chartPresent) ?>, backgroundColor: '#2E7D32', borderRadius: 4 },
          { label: 'Absent',  data: <?= json_encode($chartAbsent) ?>,  backgroundColor: '#e53935', borderRadius: 4 }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
    });
  }

  // Grade distribution chart
  var gradeCtx = document.getElementById('gradeChart');
  if (gradeCtx) {
    new Chart(gradeCtx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($gradeLabels) ?>,
        datasets: [{ data: <?= json_encode($gradeCounts) ?>, backgroundColor: ['#1565C0','#2E7D32','#F57F17','#6A1B9A'] }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
});
</script>

<?php elseif ($role === 'teacher'): ?>
<!-- ==== TEACHER DASHBOARD ==== -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-2"><i class="fas fa-layer-group fa-lg"></i></div>
        <div class="stat-value"><?= $class_count ?? 0 ?></div>
        <div class="stat-label text-muted small">My Classes</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-info-light text-info rounded-3 mx-auto mb-2"><i class="fas fa-book fa-lg"></i></div>
        <div class="stat-value"><?= $subject_count ?? 0 ?></div>
        <div class="stat-label text-muted small">Subjects</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-warning-light text-warning rounded-3 mx-auto mb-2"><i class="fas fa-tasks fa-lg"></i></div>
        <div class="stat-value"><?= $pending_assignments ?? 0 ?></div>
        <div class="stat-label text-muted small">Pending Grading</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <?php $att = $attendance_stats ?? []; $attPct = ($att['total'] ?? 0) > 0 ? round(($att['present']/$att['total'])*100) : 0; ?>
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon <?= $attPct >= 90 ? 'bg-success-light text-success' : 'bg-warning-light text-warning' ?> rounded-3 mx-auto mb-2"><i class="fas fa-calendar-check fa-lg"></i></div>
        <div class="stat-value"><?= $attPct ?>%</div>
        <div class="stat-label text-muted small">My Attendance</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Today's schedule -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Today's Schedule — <?= date('l') ?></h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($today_timetable ?? [])): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-calendar-times fa-2x mb-2"></i><br>No classes today</div>
        <?php else: foreach (($today_timetable ?? []) as $tt): ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="text-center" style="min-width:60px">
            <div class="fw-bold text-primary small"><?= date('H:i', strtotime($tt['start_time'])) ?></div>
            <div class="text-muted" style="font-size:10px"><?= date('H:i', strtotime($tt['end_time'])) ?></div>
          </div>
          <div class="flex-fill">
            <div class="fw-semibold small"><?= e($tt['subject_name']) ?></div>
            <div class="text-muted" style="font-size:12px">Grade <?= e($tt['grade']) ?>-<?= e($tt['section']) ?> | Period <?= $tt['period'] ?></div>
          </div>
          <div><span class="badge bg-light text-dark">P<?= $tt['period'] ?></span></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- My Classes -->
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-users text-success me-2"></i>My Classes</h6>
        <a href="<?= url('attendance/take') ?>" class="btn btn-sm btn-primary">Take Attendance</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (($my_classes ?? []) as $cls): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
          <div>
            <div class="fw-semibold small">Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></div>
            <div class="text-muted" style="font-size:12px">Room <?= e($cls['room_no'] ?? 'TBA') ?></div>
          </div>
          <div class="d-flex gap-1">
            <a href="<?= url('attendance/take?class_id='.$cls['id']) ?>" class="btn btn-xs btn-outline-primary" style="font-size:11px">Attendance</a>
            <a href="<?= url('exams/marks?class_id='.$cls['id']) ?>" class="btn btn-xs btn-outline-success" style="font-size:11px">Marks</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php elseif ($role === 'student'): ?>
<!-- ==== STUDENT DASHBOARD ==== -->
<?php
$student = $student ?? null;
$att     = $attendance ?? [];
$attPct  = ($att['total'] ?? 0) > 0 ? round(($att['present']/$att['total'])*100) : 0;
?>

<?php if (!$student): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Your student profile is not linked to this account. Please contact the registrar.</div>
<?php else: ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-2"><i class="fas fa-user-graduate fa-lg"></i></div>
        <div class="stat-value">Grade <?= e($student['grade'] ?? '-') ?></div>
        <div class="stat-label text-muted small">Section <?= e($student['section'] ?? '-') ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon <?= $attPct >= 80 ? 'bg-success-light text-success' : 'bg-warning-light text-warning' ?> rounded-3 mx-auto mb-2"><i class="fas fa-calendar-check fa-lg"></i></div>
        <div class="stat-value"><?= $attPct ?>%</div>
        <div class="stat-label text-muted small">Attendance Rate</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <?php $feeStatus = $fee_status ?? []; ?>
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon <?= ($feeStatus['unpaid'] ?? 0) > 0 ? 'bg-danger-light text-danger' : 'bg-success-light text-success' ?> rounded-3 mx-auto mb-2"><i class="fas fa-money-bill fa-lg"></i></div>
        <div class="stat-value"><?= $feeStatus['unpaid'] ?? 0 ?></div>
        <div class="stat-label text-muted small">Unpaid Fees</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card h-100 border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-warning-light text-warning rounded-3 mx-auto mb-2"><i class="fas fa-tasks fa-lg"></i></div>
        <div class="stat-value"><?= count($pending_assignments ?? []) ?></div>
        <div class="stat-label text-muted small">Pending Assignments</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Today's Timetable -->
  <div class="col-md-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Today — <?= date('l') ?></h6>
        <a href="<?= url('timetable') ?>" class="text-primary small">Full</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($today_timetable ?? [])): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-coffee fa-2x mb-2"></i><br>No classes today</div>
        <?php else: foreach (($today_timetable ?? []) as $tt): ?>
        <div class="d-flex gap-3 px-3 py-2 border-bottom align-items-center">
          <div class="text-center" style="min-width:50px">
            <div class="fw-bold small text-primary"><?= date('H:i', strtotime($tt['start_time'])) ?></div>
          </div>
          <div>
            <div class="fw-semibold small"><?= e($tt['subject_name']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= e($tt['first_name'] . ' ' . $tt['last_name']) ?></div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Marks -->
  <div class="col-md-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-star text-warning me-2"></i>Recent Results</h6>
        <a href="<?= url('exams/report-cards') ?>" class="text-primary small">Report Card</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover small mb-0">
            <thead class="table-light"><tr><th>Subject</th><th>Exam</th><th>Marks</th><th>Grade</th></tr></thead>
            <tbody>
              <?php if (empty($recent_marks ?? [])): ?>
              <tr><td colspan="4" class="text-center py-3 text-muted">No results yet</td></tr>
              <?php else: foreach (($recent_marks ?? []) as $m): ?>
              <tr>
                <td><?= e($m['subject_name'] ?? $m['subject']) ?></td>
                <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$m['type'])) ?></span></td>
                <td class="fw-semibold"><?= e($m['marks_obtained']) ?>/<?= e($m['total_marks']) ?></td>
                <td><span class="badge bg-<?= match($m['grade_letter'][0] ?? 'F') {'A'=>'success','B'=>'primary','C'=>'info','D'=>'warning',default=>'danger'} ?>"><?= e($m['grade_letter'] ?? '-') ?></span></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; // student not null ?>

<?php elseif ($role === 'parent'): ?>
<!-- ==== PARENT DASHBOARD ==== -->
<?php $linked = $linked_student ?? null; ?>
<?php if (!$linked): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No student linked to your account. Please contact the registrar.</div>
<?php else: ?>
<div class="alert alert-info mb-4">
  <i class="fas fa-user-graduate me-2"></i>
  Viewing progress for: <strong><?= e($linked['first_name'] . ' ' . $linked['last_name']) ?></strong> (<?= e($linked['student_id']) ?>)
</div>

<div class="row g-3 mb-4">
  <?php $att = $attendance ?? []; $attPct = ($att['total']??0) > 0 ? round(($att['present']/$att['total'])*100) : 0; ?>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-2"><i class="fas fa-calendar-check fa-lg"></i></div>
        <div class="stat-value"><?= $attPct ?>%</div>
        <div class="stat-label text-muted small">Attendance</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-danger-light text-danger rounded-3 mx-auto mb-2"><i class="fas fa-calendar-times fa-lg"></i></div>
        <div class="stat-value"><?= $att['absent'] ?? 0 ?></div>
        <div class="stat-label text-muted small">Absences (Month)</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-warning-light text-warning rounded-3 mx-auto mb-2"><i class="fas fa-money-bill fa-lg"></i></div>
        <div class="stat-value"><?= count($pending_fees ?? []) ?></div>
        <div class="stat-label text-muted small">Pending Fees</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-info-light text-info rounded-3 mx-auto mb-2"><i class="fas fa-star fa-lg"></i></div>
        <div class="stat-value"><?= count($recent_marks ?? []) ?></div>
        <div class="stat-label text-muted small">Recent Results</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-star text-warning me-2"></i>Recent Marks</h6></div>
      <div class="table-responsive">
        <table class="table small mb-0">
          <thead class="table-light"><tr><th>Subject</th><th>Type</th><th>Score</th><th>Grade</th></tr></thead>
          <tbody>
            <?php foreach (($recent_marks ?? []) as $m): ?>
            <tr>
              <td><?= e($m['subject'] ?? '') ?></td>
              <td><?= ucfirst(str_replace('_',' ',$m['type'])) ?></td>
              <td><?= e($m['marks_obtained']) ?>/<?= e($m['total_marks']) ?></td>
              <td><span class="badge bg-primary"><?= e($m['grade_letter'] ?? '-') ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-exclamation-circle text-danger me-2"></i>Pending Fees</h6></div>
      <div class="table-responsive">
        <table class="table small mb-0">
          <thead class="table-light"><tr><th>Fee</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($pending_fees)): ?>
            <tr><td colspan="4" class="text-center text-success py-3"><i class="fas fa-check-circle me-1"></i>All fees paid</td></tr>
            <?php else: foreach (($pending_fees ?? []) as $f): ?>
            <tr>
              <td><?= e($f['fee_name']) ?></td>
              <td><?= formatMoney($f['amount']) ?></td>
              <td class="<?= strtotime($f['due_date']) < time() ? 'text-danger' : '' ?>"><?= formatDate($f['due_date']) ?></td>
              <td><?= getStatusBadge($f['status']) ?></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php endif; // linked student ?>

<?php elseif ($role === 'finance_officer'): ?>
<!-- ==== FINANCE DASHBOARD ==== -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-light text-success rounded-3"><i class="fas fa-arrow-up fa-lg"></i></div>
        <div><div class="stat-value"><?= formatMoney($month_collected ?? 0) ?></div><div class="stat-label text-muted small">This Month Income</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-danger-light text-danger rounded-3"><i class="fas fa-arrow-down fa-lg"></i></div>
        <div><div class="stat-value"><?= formatMoney($month_expenses ?? 0) ?></div><div class="stat-label text-muted small">This Month Expenses</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning-light text-warning rounded-3"><i class="fas fa-exclamation-circle fa-lg"></i></div>
        <div><div class="stat-value"><?= formatMoney($pending_fees ?? 0) ?></div><div class="stat-label text-muted small">Pending Fees</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-info-light text-info rounded-3"><i class="fas fa-users fa-lg"></i></div>
        <div><div class="stat-value"><?= $pending_payroll ?? 0 ?></div><div class="stat-label text-muted small">Pending Payroll</div></div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar text-primary me-2"></i>Monthly Finance Overview</h6>
    <a href="<?= url('finance/reports') ?>" class="btn btn-sm btn-outline-primary">Full Report</a>
  </div>
  <div class="card-body"><canvas id="financeChart" height="100"></canvas></div>
</div>
<?php $mc = $monthly_chart ?? []; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('financeChart');
  if (ctx) new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($mc,'month')) ?>,
      datasets: [
        { label:'Income', data:<?= json_encode(array_column($mc,'collected')) ?>, borderColor:'#2E7D32', tension:0.4, fill:false },
        { label:'Expenses', data:<?= json_encode(array_column($mc,'expenses')) ?>, borderColor:'#e53935', tension:0.4, fill:false }
      ]
    },
    options: { responsive: true, plugins: { legend: { position:'top' } } }
  });
});
</script>
<?php endif; ?>

<!-- Announcements (all roles) -->
<?php if (!empty($announcements ?? [])): ?>
<div class="card border-0 shadow-sm mt-4">
  <div class="card-header bg-white border-bottom py-3">
    <h6 class="mb-0 fw-semibold"><i class="fas fa-bullhorn text-warning me-2"></i>Announcements</h6>
  </div>
  <div class="card-body p-0">
    <?php foreach (array_slice($announcements ?? [], 0, 3) as $ann): ?>
    <div class="d-flex gap-3 px-3 py-3 border-bottom align-items-start">
      <div class="flex-shrink-0">
        <?php $badgeColor = match($ann['priority']) {'urgent'=>'danger','important'=>'warning','normal'=>'info',default=>'secondary'}; ?>
        <span class="badge bg-<?= $badgeColor ?>"><?= ucfirst($ann['priority']) ?></span>
      </div>
      <div>
        <div class="fw-semibold small"><?= e($ann['title']) ?></div>
        <div class="text-muted small"><?= truncate(e($ann['content']), 120) ?></div>
        <div class="text-muted mt-1" style="font-size:11px"><i class="fas fa-clock me-1"></i><?= timeAgo($ann['created_at']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="card-footer bg-white text-center py-2">
    <a href="<?= url('communication/announcements') ?>" class="text-primary small">View all announcements</a>
  </div>
</div>
<?php endif; ?>
