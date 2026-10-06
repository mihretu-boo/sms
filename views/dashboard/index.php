<?php
$role    = Auth::role();
$user    = Auth::user();
$isAdmin = in_array($role, ['super_admin','principal','vice_principal','registrar']);
?>

<?php /* ══════════════════════════════════════════════════════════════
         ADMIN / PRINCIPAL / VICE-PRINCIPAL / REGISTRAR
   ══════════════════════════════════════════════════════════════ */ ?>
<?php if ($isAdmin): ?>

<!-- ── Greeting + Quick Actions ─────────────────────────────── -->
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1"><?= $time_greeting ?>, <span class="text-primary"><?= e($username_short) ?></span> 👋</h4>
    <p class="text-muted small mb-0"><?= $today ?></p>
    <p class="text-muted small mb-0">Here is today's school summary.</p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= url('students/create') ?>" class="btn btn-sm btn-primary fw-medium">
      <i class="fas fa-plus me-1"></i>Add Student
    </a>
    <a href="<?= url('staff/create') ?>" class="btn btn-sm btn-success fw-medium">
      <i class="fas fa-user-plus me-1"></i>Add Teacher
    </a>
    <a href="<?= url('attendance/take') ?>" class="btn btn-sm fw-medium" style="background:#7B1FA2;border-color:#7B1FA2;color:#fff">
      <i class="fas fa-calendar-check me-1"></i>Record Attendance
    </a>
    <a href="<?= url('finance/fees') ?>" class="btn btn-sm btn-warning fw-medium text-white">
      <i class="fas fa-money-bill me-1"></i>Collect Fees
    </a>
    <a href="<?= url('reports') ?>" class="btn btn-sm btn-outline-primary fw-medium">
      <i class="fas fa-chart-bar me-1"></i>Reports
    </a>
    <a href="<?= url('exams') ?>" class="btn btn-sm btn-danger fw-medium">
      <i class="fas fa-file-alt me-1"></i>Create Exam
    </a>
  </div>
</div>

<!-- ── KPI Cards ─────────────────────────────────────────────── -->
<?php
$kpis = [
  ['label'=>'Students',     'val'=>number_format($totalStudents??0),              'sub'=>'Active Students',    'icon'=>'users',              'ic'=>'#1976D2','ibg'=>'#E3F2FD', 'trend'=>0,                         'up'=>true,  'url'=>'students'],
  ['label'=>'Teachers',     'val'=>number_format($totalStaff??0),                 'sub'=>'Teaching Staff',     'icon'=>'chalkboard-teacher', 'ic'=>'#388E3C','ibg'=>'#E8F5E9', 'trend'=>25,                        'up'=>true,  'url'=>'staff'],
  ['label'=>'Attendance',   'val'=>($attRate??0).'%',                             'sub'=>"Today's Attendance", 'icon'=>'calendar-check',     'ic'=>'#0288D1','ibg'=>'#E1F5FE', 'trend'=>abs($attTrend??0),         'up'=>($attTrend??0)>=0, 'url'=>'attendance'],
  ['label'=>'Revenue',      'val'=>'ETB '.number_format($monthIncome??0,0),       'sub'=>'Monthly Income',     'icon'=>'chart-line',         'ic'=>'#7B1FA2','ibg'=>'#F3E5F5', 'trend'=>abs($incomeTrend??0),      'up'=>($incomeTrend??0)>=0, 'url'=>'finance/payments'],
  ['label'=>'Pending Fees', 'val'=>'ETB '.number_format($pendingFees??0,0),       'sub'=>'Awaiting Payment',   'icon'=>'exclamation-circle', 'ic'=>'#E65100','ibg'=>'#FFF3E0', 'trend'=>12,                        'up'=>true,  'url'=>'finance/fees'],
  ['label'=>'Discipline',   'val'=>$openIncidents??0,                             'sub'=>'Open Cases',         'icon'=>'gavel',              'ic'=>'#D32F2F','ibg'=>'#FFEBEE', 'trend'=>0,                         'up'=>false, 'url'=>'discipline'],
  ['label'=>'Library',      'val'=>$overdueBooks??0,                              'sub'=>'Overdue Books',      'icon'=>'book',               'ic'=>'#3949AB','ibg'=>'#E8EAF6', 'trend'=>100,                       'up'=>true,  'url'=>'library'],
];
?>
<div class="row g-3 mb-4">
  <?php foreach ($kpis as $k): ?>
  <div class="col-6 col-md-4 col-xl">
    <a href="<?= url($k['url']) ?>" class="text-decoration-none">
    <div class="card border-0 shadow-sm dash-kpi-card h-100">
      <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div class="flex-fill min-w-0">
            <p class="mb-1 fw-semibold" style="font-size:11px;color:#888;text-transform:uppercase;letter-spacing:.5px"><?= $k['label'] ?></p>
            <h3 class="fw-bold mb-0 text-dark" style="font-size:1.45rem;line-height:1.1"><?= $k['val'] ?></h3>
            <p class="mb-0 text-muted" style="font-size:11px;margin-top:2px"><?= $k['sub'] ?></p>
          </div>
          <div class="dash-kpi-icon flex-shrink-0" style="background:<?= $k['ibg'] ?>;color:<?= $k['ic'] ?>">
            <i class="fas fa-<?= $k['icon'] ?>"></i>
          </div>
        </div>
        <div class="mt-3 pt-2 border-top" style="border-color:rgba(0,0,0,.06)!important">
          <div class="d-flex align-items-center gap-1" style="font-size:11px">
            <i class="fas fa-arrow-<?= $k['up']?'up':'down' ?>" style="color:<?= $k['up']?'#2E7D32':'#C62828' ?>"></i>
            <span class="fw-semibold" style="color:<?= $k['up']?'#2E7D32':'#C62828' ?>"><?= $k['trend'] ?>%</span>
            <span class="text-muted">from last month</span>
          </div>
          <div class="progress mt-1" style="height:3px;background:<?= $k['ibg'] ?>">
            <div class="progress-bar" style="width:<?= min(100,max(5,$k['trend'])) ?>%;background:<?= $k['ic'] ?>"></div>
          </div>
        </div>
      </div>
    </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Charts Row 1: Attendance Trend + Side Panels ─────────── -->
<div class="row g-3 mb-4">

  <!-- Attendance Trend -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <div>
          <h6 class="mb-0 fw-semibold">Attendance Trend</h6>
          <p class="mb-0 text-muted" style="font-size:11px">This Week — last 14 days</p>
        </div>
        <a href="<?= url('attendance/analytics') ?>" class="btn btn-sm btn-outline-secondary" style="font-size:11px">
          This Week <i class="fas fa-chevron-down ms-1" style="font-size:9px"></i>
        </a>
      </div>
      <div class="card-body pt-0 pb-3">
        <canvas id="attTrendChart" height="105"></canvas>
      </div>
    </div>
  </div>

  <!-- Right: Today's Schedule + Recent Activities -->
  <div class="col-lg-4 d-flex flex-column gap-3">

    <!-- Today's Schedule -->
    <div class="card border-0 shadow-sm" style="flex:1">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2 px-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Today's Schedule</h6>
        <a href="<?= url('timetable') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0" style="max-height:165px;overflow-y:auto">
        <?php if (!empty($todaySchedule??[])): ?>
          <?php foreach ($todaySchedule as $ts): ?>
          <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
            <span class="sched-dot" style="background:#1976D2"></span>
            <div class="flex-fill min-w-0">
              <div class="fw-semibold text-dark" style="font-size:12px"><?= e($ts['subject_name']) ?> Class</div>
              <div class="text-muted" style="font-size:11px">Grade <?= e($ts['grade']) ?><?= e($ts['section']) ?></div>
            </div>
            <div class="text-muted flex-shrink-0 text-end" style="font-size:11px">
              <?= isset($ts['start_time']) ? date('h:i A', strtotime($ts['start_time'])) : 'P.'.$ts['period'] ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php elseif (!empty($upcomingExams??[])): ?>
          <?php
          $schedColors = ['#1976D2','#388E3C','#7B1FA2','#E65100','#3949AB'];
          foreach (array_slice($upcomingExams, 0, 4) as $i => $ex):
          ?>
          <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
            <span class="sched-dot" style="background:<?= $schedColors[$i%5] ?>"></span>
            <div class="flex-fill min-w-0">
              <div class="fw-semibold text-dark" style="font-size:12px"><?= e($ex['subject_name']??'Exam') ?></div>
              <div class="text-muted" style="font-size:11px">Grade <?= e($ex['grade']??'') ?>-<?= e($ex['section']??'') ?></div>
            </div>
            <div class="fw-semibold flex-shrink-0" style="font-size:11px;color:<?= $schedColors[$i%5] ?>"><?= formatDate($ex['exam_date'],'d M') ?></div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
        <div class="text-center py-4 text-muted small">
          <i class="fas fa-calendar-alt fa-lg mb-2 d-block text-muted opacity-50"></i>No schedule today
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="card border-0 shadow-sm" style="flex:1">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2 px-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Recent Activities</h6>
        <a href="<?= url('settings/audit') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0" style="max-height:165px;overflow-y:auto">
        <?php
        $actColors = ['primary'=>'#1565C0','success'=>'#2E7D32','danger'=>'#C62828','warning'=>'#F57F17','info'=>'#0097A7','secondary'=>'#607D8B'];
        foreach (array_slice($activity??[], 0, 5) as $act):
          $ac = $actColors[$act['color']] ?? '#607D8B';
        ?>
        <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom">
          <div class="act-icon-sm flex-shrink-0" style="background:<?= $ac ?>18;color:<?= $ac ?>">
            <i class="fas fa-<?= $act['icon'] ?>" style="font-size:9px"></i>
          </div>
          <div class="flex-fill min-w-0">
            <div class="fw-medium text-dark text-truncate" style="font-size:12px"><?= e($act['text']) ?></div>
            <div class="text-muted" style="font-size:10px"><?= timeAgo($act['time']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($activity??[])): ?>
        <div class="text-center py-4 text-muted small">No recent activity</div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /col-lg-4 -->
</div><!-- /Charts Row 1 -->

<!-- ── Charts Row 2: Revenue + Student Growth + Events ──────── -->
<div class="row g-3 mb-4">

  <!-- Monthly Revenue -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <div>
          <h6 class="mb-0 fw-semibold">Monthly Revenue</h6>
          <p class="mb-0 text-muted" style="font-size:11px">Income vs Expenses</p>
        </div>
        <a href="<?= url('finance/reports') ?>" class="btn btn-sm btn-outline-secondary" style="font-size:11px">
          This Year <i class="fas fa-chevron-down ms-1" style="font-size:9px"></i>
        </a>
      </div>
      <div class="card-body pt-0">
        <div class="d-flex gap-4 mb-3">
          <div>
            <div class="text-muted" style="font-size:11px">Total Revenue</div>
            <div class="fw-bold" style="font-size:1rem;color:#1a1a2e">ETB <?= number_format($monthIncome??0,0) ?></div>
          </div>
          <div>
            <div class="text-muted" style="font-size:11px">Growth</div>
            <div class="fw-bold <?= ($incomeTrend??0)>=0?'text-success':'text-danger' ?>" style="font-size:1rem">
              <?= ($incomeTrend??0)>=0?'+':'' ?><?= $incomeTrend??0 ?>%
            </div>
          </div>
        </div>
        <canvas id="revenueChart" height="140"></canvas>
      </div>
    </div>
  </div>

  <!-- Student Growth -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <div>
          <h6 class="mb-0 fw-semibold">Student Growth</h6>
          <p class="mb-0 text-muted" style="font-size:11px">Enrollment by Grade</p>
        </div>
        <a href="<?= url('students') ?>" class="btn btn-sm btn-outline-secondary" style="font-size:11px">
          This Year <i class="fas fa-chevron-down ms-1" style="font-size:9px"></i>
        </a>
      </div>
      <div class="card-body pt-0">
        <canvas id="growthChart" height="175"></canvas>
      </div>
    </div>
  </div>

  <!-- Upcoming Events + Notifications -->
  <div class="col-lg-4 d-flex flex-column gap-3">

    <!-- Upcoming Events -->
    <div class="card border-0 shadow-sm" style="flex:1">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2 px-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Upcoming Events</h6>
        <a href="<?= url('exams') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0">
        <?php
        $evColors = [['#E3F2FD','#1565C0'],['#E8F5E9','#2E7D32'],['#FFF8E1','#E65100']];
        foreach (array_slice($upcomingExams??[], 0, 3) as $i => $ex):
          [$ebg,$efg] = $evColors[$i % 3];
        ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="text-center flex-shrink-0" style="background:<?= $ebg ?>;color:<?= $efg ?>;border-radius:8px;padding:4px 8px;min-width:38px">
            <div class="fw-bold" style="font-size:14px;line-height:1.1"><?= date('d',strtotime($ex['exam_date'])) ?></div>
            <div style="font-size:9px;text-transform:uppercase"><?= date('M',strtotime($ex['exam_date'])) ?></div>
          </div>
          <div class="flex-fill min-w-0">
            <div class="fw-semibold text-dark text-truncate" style="font-size:12px"><?= e($ex['subject_name']??'') ?></div>
            <div class="text-muted" style="font-size:11px">Grade <?= e($ex['grade']??'') ?>-<?= e($ex['section']??'') ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($upcomingExams??[])): ?>
        <div class="text-center py-3 text-muted small">No upcoming exams</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Notifications -->
    <?php $notifs = getUnreadNotifications(); ?>
    <div class="card border-0 shadow-sm" style="flex:1">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-2 px-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">
          Notifications
          <?php if (!empty($notifs)): ?>
          <span class="badge bg-danger ms-1" style="font-size:9px;vertical-align:middle"><?= count($notifs) ?></span>
          <?php endif; ?>
        </h6>
        <a href="<?= url('communication/notifications') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($notifs)): ?>
        <div class="text-center py-3 text-muted small">
          <i class="fas fa-check-circle text-success me-1"></i>All caught up!
        </div>
        <?php else: foreach (array_slice($notifs, 0, 3) as $n):
          $ntc = ['primary'=>'#1565C0','success'=>'#2E7D32','warning'=>'#F57F17','danger'=>'#C62828','info'=>'#0097A7'];
          $nc  = $ntc[$n['type']??'primary'] ?? '#607D8B';
        ?>
        <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom">
          <span class="flex-shrink-0 mt-1" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $nc ?>"></span>
          <div class="flex-fill min-w-0">
            <div class="fw-semibold text-dark text-truncate" style="font-size:12px"><?= e($n['title']) ?></div>
            <div class="text-muted text-truncate" style="font-size:11px"><?= e(truncate($n['message'],55)) ?></div>
            <div class="text-muted" style="font-size:10px"><?= timeAgo($n['created_at']) ?></div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

  </div><!-- /events+notif col -->
</div><!-- /Charts Row 2 -->

<!-- ── Bottom Row: 4 panels ──────────────────────────────────── -->
<div class="row g-3">

  <!-- Recent Students -->
  <div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Recent Students</h6>
        <a href="<?= url('students') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (array_slice($recentStudents??[], 0, 5) as $s): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <img src="<?= photoUrl($s['photo']??null,'student') ?>" class="rounded-circle flex-shrink-0" width="30" height="30" style="object-fit:cover">
          <div class="flex-fill min-w-0">
            <div class="fw-semibold text-dark text-truncate" style="font-size:12px"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
            <div class="text-muted" style="font-size:11px">Grade <?= e($s['grade']??'—') ?><?= e($s['section']??'') ?></div>
          </div>
          <div class="text-muted flex-shrink-0" style="font-size:11px"><?= formatDate($s['created_at'],'d M Y') ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recentStudents??[])): ?>
        <div class="text-center py-4 text-muted small">No students yet</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Payments -->
  <div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Recent Payments</h6>
        <a href="<?= url('finance/payments') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (array_slice($recentPayments??[], 0, 5) as $p): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <div class="flex-fill min-w-0">
            <div class="fw-semibold text-dark text-truncate" style="font-size:12px"><?= e(($p['first_name']??'').' '.($p['last_name']??'')) ?></div>
            <div class="text-muted" style="font-size:11px">ETB <?= number_format($p['amount'],0) ?> &bull; <?= formatDate($p['payment_date'],'d M Y') ?></div>
          </div>
          <span class="badge" style="background:#E8F5E9;color:#2E7D32;font-size:10px">Paid</span>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recentPayments??[])): ?>
        <div class="text-center py-4 text-muted small">No payments yet</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Latest Announcements -->
  <div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Latest Announcements</h6>
        <a href="<?= url('communication/announcements') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0">
        <?php
        $abullet = ['urgent'=>'#D32F2F','important'=>'#F57C00','normal'=>'#0288D1'];
        foreach (array_slice($announcements??[], 0, 4) as $ann):
          $ac = $abullet[$ann['priority']??'normal'] ?? '#9E9E9E';
        ?>
        <div class="d-flex align-items-start gap-2 px-3 py-2 border-bottom">
          <span class="flex-shrink-0 mt-1" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= $ac ?>;margin-top:4px"></span>
          <div class="flex-fill min-w-0">
            <div class="fw-semibold text-dark text-truncate" style="font-size:12px"><?= e($ann['title']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= timeAgo($ann['created_at']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($announcements??[])): ?>
        <div class="text-center py-4 text-muted small">No announcements</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Latest Admissions -->
  <div class="col-lg-3 col-md-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold" style="font-size:13px">Latest Admissions</h6>
        <a href="<?= url('students/admissions') ?>" class="text-primary" style="font-size:11px">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (array_slice($recentStudents??[], 0, 5) as $s): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <img src="<?= photoUrl($s['photo']??null,'student') ?>" class="rounded-circle flex-shrink-0" width="30" height="30" style="object-fit:cover">
          <div class="flex-fill min-w-0">
            <div class="fw-semibold text-dark text-truncate" style="font-size:12px"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
            <div class="text-muted" style="font-size:11px">Grade <?= e($s['grade']??'—') ?></div>
          </div>
          <div class="text-muted flex-shrink-0" style="font-size:11px"><?= formatDate($s['created_at'],'d M Y') ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($recentStudents??[])): ?>
        <div class="text-center py-4 text-muted small">No admissions yet</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div><!-- /Bottom Row -->

<!-- ── Chart.js init ─────────────────────────────────────────── -->
<?php
$attLabels  = json_encode(array_column($attChart??[],'date'));
$attRates   = json_encode(array_column($attChart??[],'rate'));
$attPresent = json_encode(array_column($attChart??[],'present'));
$finMonths  = json_encode(array_column($finChart??[],'month'));
$finIncome  = json_encode(array_column($finChart??[],'income'));
$finExp     = json_encode(array_column($finChart??[],'expenses'));
$gradeLabels= json_encode(array_map(fn($g)=>'Gr '.$g['grade'], $gradeChart??[]));
$gradeCounts= json_encode(array_column($gradeChart??[],'cnt'));
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  Chart.defaults.font.family = "'Segoe UI', system-ui, sans-serif";
  Chart.defaults.font.size   = 11;
  Chart.defaults.color       = '#9e9e9e';

  /* Attendance Trend — smooth area line */
  var c1 = document.getElementById('attTrendChart');
  if (c1) new Chart(c1, {
    type: 'line',
    data: {
      labels: <?= $attLabels ?>,
      datasets: [{
        label: 'Attendance %',
        data: <?= $attRates ?>,
        borderColor: '#1565C0',
        borderWidth: 2.5,
        pointRadius: 4,
        pointBackgroundColor: '#1565C0',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        tension: 0.45,
        fill: true,
        backgroundColor: 'rgba(21,101,192,0.08)'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: 'rgba(0,0,0,0.04)' } },
        x: { grid: { display: false } }
      }
    }
  });

  /* Monthly Revenue — grouped bar */
  var c2 = document.getElementById('revenueChart');
  if (c2) new Chart(c2, {
    type: 'bar',
    data: {
      labels: <?= $finMonths ?>,
      datasets: [
        { label: 'Income',   data: <?= $finIncome ?>, backgroundColor: 'rgba(25,118,210,.85)', borderRadius: 4 },
        { label: 'Expenses', data: <?= $finExp    ?>, backgroundColor: 'rgba(211,47,47,.75)',   borderRadius: 4 }
      ]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
        x: { grid: { display: false } }
      }
    }
  });

  /* Student Growth — smooth area */
  var c3 = document.getElementById('growthChart');
  if (c3) new Chart(c3, {
    type: 'line',
    data: {
      labels: <?= $gradeLabels ?>,
      datasets: [{
        label: 'Students',
        data: <?= $gradeCounts ?>,
        borderColor: '#7B1FA2',
        borderWidth: 2.5,
        pointRadius: 5,
        pointBackgroundColor: '#7B1FA2',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        tension: 0.35,
        fill: true,
        backgroundColor: 'rgba(123,31,162,0.08)'
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => ' ' + ctx.parsed.y + ' students' } }
      },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 10 }, grid: { color: 'rgba(0,0,0,0.04)' } },
        x: { grid: { display: false } }
      }
    }
  });
});
</script>


<?php /* ══════════════════════════════════════════════════════════════
         TEACHER / DEPARTMENT HEAD
   ══════════════════════════════════════════════════════════════ */ ?>
<?php elseif (in_array($role, ['teacher','dept_head'])): ?>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1"><?= $time_greeting ?>, <span class="text-primary"><?= e($username_short) ?></span> 👋</h4>
    <p class="text-muted small mb-0"><?= $today ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('attendance/take') ?>"  class="btn btn-sm btn-success"><i class="fas fa-calendar-check me-1"></i>Take Attendance</a>
    <a href="<?= url('exams/marks') ?>"      class="btn btn-sm btn-primary"><i class="fas fa-pen me-1"></i>Enter Marks</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <?php $tc=count($myClasses??[]); $ts=array_sum(array_column($myClasses??[],'student_count')); ?>
  <?php foreach ([
    ['val'=>$tc,'label'=>'My Classes','sub'=>'This semester','icon'=>'layer-group','ic'=>'#1976D2','ibg'=>'#E3F2FD'],
    ['val'=>$ts,'label'=>'Students','sub'=>'Total enrolled','icon'=>'users','ic'=>'#388E3C','ibg'=>'#E8F5E9'],
    ['val'=>count($todayTT??[]),'label'=>'Periods Today','sub'=>date('l'),'icon'=>'clock','ic'=>'#0288D1','ibg'=>'#E1F5FE'],
    ['val'=>$pendingGrading??0,'label'=>'To Grade','sub'=>'Assignments','icon'=>'tasks','ic'=>($pendingGrading??0)>0?'#E65100':'#607D8B','ibg'=>($pendingGrading??0)>0?'#FFF3E0':'#F5F5F5'],
  ] as $k): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm dash-kpi-card h-100">
      <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <p class="mb-1 fw-semibold text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px"><?= $k['label'] ?></p>
            <h3 class="fw-bold text-dark mb-0" style="font-size:1.45rem"><?= $k['val'] ?></h3>
            <p class="text-muted mb-0" style="font-size:11px"><?= $k['sub'] ?></p>
          </div>
          <div class="dash-kpi-icon" style="background:<?= $k['ibg'] ?>;color:<?= $k['ic'] ?>">
            <i class="fas fa-<?= $k['icon'] ?>"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Today &mdash; <?= date('l, d M') ?></h6></div>
      <div class="card-body p-0">
        <?php if (empty($todayTT??[])): ?><div class="text-center py-5 text-muted"><i class="fas fa-couch fa-2x mb-2 d-block"></i>No classes today</div>
        <?php else: foreach ($todayTT??[] as $tt): ?>
        <div class="d-flex gap-3 px-3 py-2 border-bottom align-items-center">
          <div class="text-center flex-shrink-0" style="min-width:48px"><div class="fw-bold text-primary small"><?= date('H:i',strtotime($tt['start_time'])) ?></div><div class="text-muted" style="font-size:10px"><?= date('H:i',strtotime($tt['end_time'])) ?></div></div>
          <div class="flex-fill"><div class="fw-semibold small"><?= e($tt['subject_name']) ?></div><div class="text-muted" style="font-size:11px">Grade <?= e($tt['grade']) ?>-<?= e($tt['section']) ?></div></div>
          <a href="<?= url('attendance/take?class_id='.$tt['class_id'].'&date='.date('Y-m-d')) ?>" class="btn btn-xs btn-outline-success" title="Attendance"><i class="fas fa-clipboard-check"></i></a>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-users text-success me-2"></i>My Classes</h6>
        <a href="<?= url('attendance/take') ?>" class="btn btn-xs btn-success">Take Attendance</a>
      </div>
      <div class="card-body p-0">
        <?php foreach ($myClasses??[] as $cls): $att=$attToday[$cls['id']]??['total'=>0,'present'=>0]; $pct=$att['total']>0?round($att['present']/$att['total']*100):0; ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-fill"><div class="fw-semibold small">Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></div><div class="text-muted" style="font-size:11px"><?= $cls['student_count'] ?> students</div></div>
          <?php if ($att['total']>0): ?>
          <div class="d-flex align-items-center gap-2"><div class="progress flex-shrink-0" style="width:60px;height:6px"><div class="progress-bar bg-<?= $pct>=80?'success':'warning' ?>" style="width:<?= $pct ?>%"></div></div><small class="fw-bold text-<?= $pct>=80?'success':'warning' ?>"><?= $pct ?>%</small></div>
          <?php else: ?><span class="badge bg-light text-muted border" style="font-size:10px">Not taken</span><?php endif; ?>
          <div class="d-flex gap-1">
            <a href="<?= url('attendance/take?class_id='.$cls['id']) ?>" class="btn btn-xs btn-outline-primary">Attend</a>
            <a href="<?= url('exams/marks?class_id='.$cls['id']) ?>"     class="btn btn-xs btn-outline-success">Marks</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-tasks text-warning me-2"></i>Assignments Due</h6>
        <a href="<?= url('assignments') ?>" class="text-primary small">All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($upcomingDue??[])): ?><div class="text-center py-3 text-muted small">No upcoming assignments</div>
        <?php else: foreach ($upcomingDue??[] as $a): $dl=(int)ceil((strtotime($a['due_date'])-time())/86400); ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-fill"><div class="fw-semibold small"><?= e(truncate($a['title'],38)) ?></div><div class="text-muted" style="font-size:11px"><?= e($a['subject_name']??'') ?> &bull; Gr <?= e($a['grade']??'') ?>-<?= e($a['section']??'') ?></div></div>
          <div class="text-end flex-shrink-0"><div class="small fw-bold text-<?= $dl<=2?'danger':($dl<=5?'warning':'success') ?>"><?= $dl ?>d left</div><div style="font-size:10px" class="text-muted"><?= $a['submissions']??0 ?> sub.</div></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-star text-info me-2"></i>Recent Marks Entered</h6>
        <a href="<?= url('exams/marks') ?>" class="text-primary small">Enter Marks</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentMarks??[])): ?><div class="text-center py-3 text-muted small">No marks yet</div>
        <?php else: foreach ($recentMarks??[] as $m): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <div class="flex-fill"><div class="fw-semibold small"><?= e($m['first_name'].' '.$m['last_name']) ?></div><div class="text-muted" style="font-size:11px"><?= e($m['subject_name']??$m['title']??'') ?></div></div>
          <div class="text-center"><div class="small fw-bold"><?= $m['marks_obtained'] ?>/<?= $m['total_marks'] ?></div>
          <span class="badge bg-<?= match(($m['grade_letter']??'F')[0]) {'A'=>'success','B'=>'primary','C'=>'info','D'=>'warning',default=>'danger'} ?>" style="font-size:10px"><?= e($m['grade_letter']??'—') ?></span></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>


<?php /* ══════════════════════════════════════════════════════════════
         STUDENT
   ══════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($role === 'student'): ?>
<?php if (!($student??null)): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Your profile is not linked. Contact the registrar.</div>
<?php else: ?>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1"><?= $time_greeting ?>, <span class="text-primary"><?= e($username_short) ?></span> 👋</h4>
    <p class="text-muted small mb-0"><?= $today ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('exams/report-cards') ?>" class="btn btn-sm btn-primary"><i class="fas fa-graduation-cap me-1"></i>My Results</a>
    <a href="<?= url('assignments') ?>"         class="btn btn-sm btn-outline-secondary"><i class="fas fa-tasks me-1"></i>Assignments</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <?php foreach ([
    ['val'=>'Grade '.e($student['grade']??'?'),'sub'=>'Section '.e($student['section']??'?'),'icon'=>'user-graduate','ic'=>'#1976D2','ibg'=>'#E3F2FD'],
    ['val'=>$attPct.'%','sub'=>($attRow['p']??0).'/'.($attRow['total']??0).' days','icon'=>'calendar-check','ic'=>$attPct>=80?'#388E3C':'#E65100','ibg'=>$attPct>=80?'#E8F5E9':'#FFF3E0'],
    ['val'=>number_format($gpa??0,2),'sub'=>'Rank #'.($rank??'?').' in class','icon'=>'star','ic'=>'#F57F17','ibg'=>'#FFF8E1'],
    ['val'=>($feeRow['unpaid']??0),'sub'=>($feeRow['unpaid']??0)>0?'ETB '.number_format($feeRow['amount_due']??0,0).' due':'All paid','icon'=>'money-bill','ic'=>($feeRow['unpaid']??0)>0?'#D32F2F':'#388E3C','ibg'=>($feeRow['unpaid']??0)>0?'#FFEBEE':'#E8F5E9'],
  ] as $k): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm dash-kpi-card h-100">
      <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <h3 class="fw-bold text-dark mb-0" style="font-size:1.4rem"><?= $k['val'] ?></h3>
            <p class="text-muted mb-0" style="font-size:11px;margin-top:2px"><?= $k['sub'] ?></p>
          </div>
          <div class="dash-kpi-icon flex-shrink-0" style="background:<?= $k['ibg'] ?>;color:<?= $k['ic'] ?>">
            <i class="fas fa-<?= $k['icon'] ?>"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-calendar text-primary me-2"></i>14-Day Attendance</h6></div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-1 justify-content-center mb-3">
          <?php foreach ($attSpark??[] as $d): $c=match($d['status']){'present'=>'#2E7D32','absent'=>'#C62828','late'=>'#F57F17','excused'=>'#1565C0',default=>'#E0E0E0'}; ?>
          <div title="<?= $d['date'] ?>: <?= $d['status'] ?>" style="width:24px;height:24px;border-radius:4px;background:<?= $c ?>;display:flex;align-items:center;justify-content:center;font-size:9px;color:white;font-weight:bold"><?= $d['date'] ?></div>
          <?php endforeach; ?>
        </div>
        <div class="row g-2 text-center small">
          <div class="col-3"><div class="fw-bold text-success"><?= $attRow['p']??0 ?></div><div class="text-muted">Present</div></div>
          <div class="col-3"><div class="fw-bold text-danger"><?= $attRow['a']??0 ?></div><div class="text-muted">Absent</div></div>
          <div class="col-3"><div class="fw-bold text-warning"><?= $attRow['l']??0 ?></div><div class="text-muted">Late</div></div>
          <div class="col-3"><div class="fw-bold text-<?= $attPct>=80?'success':'danger' ?>"><?= $attPct ?>%</div><div class="text-muted">Rate</div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar text-warning me-2"></i>Subject Performance</h6></div>
      <div class="card-body">
        <?php foreach (array_slice($subjectPerf??[],0,7) as $sp): $pct=round($sp['avg_pct']??0); ?>
        <div class="mb-2">
          <div class="d-flex justify-content-between small mb-1">
            <span class="text-truncate" style="max-width:120px"><?= e($sp['name']) ?></span>
            <span class="fw-bold text-<?= $pct>=80?'success':($pct>=50?'warning':'danger') ?>"><?= $pct ?>%</span>
          </div>
          <div class="progress" style="height:5px"><div class="progress-bar bg-<?= $pct>=80?'success':($pct>=50?'warning':'danger') ?>" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($subjectPerf??[])): ?><p class="text-muted small text-center">No marks recorded yet</p><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Today &mdash; <?= date('l') ?></h6></div>
      <div class="card-body p-0">
        <?php if (empty($todayTT??[])): ?><div class="text-center py-5 text-muted"><i class="fas fa-coffee fa-2x mb-2 d-block"></i>No classes today</div>
        <?php else: foreach ($todayTT??[] as $tt): ?>
        <div class="d-flex gap-2 px-3 py-2 border-bottom align-items-center">
          <div class="fw-bold text-primary small flex-shrink-0" style="min-width:38px"><?= date('H:i',strtotime($tt['start_time'])) ?></div>
          <div><div class="fw-semibold small"><?= e($tt['subject_name']) ?></div><div class="text-muted" style="font-size:10px"><?= e($tt['first_name']??'') ?> <?= e($tt['last_name']??'') ?></div></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex justify-content-between py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-tasks text-danger me-2"></i>Pending Assignments</h6><a href="<?= url('assignments') ?>" class="text-primary small">All</a></div>
      <div class="card-body p-0">
        <?php if (empty($pendingAsgn??[])): ?><div class="text-center py-3 text-success small"><i class="fas fa-check-circle me-1"></i>All done!</div>
        <?php else: foreach ($pendingAsgn??[] as $a): $dl=(int)ceil((strtotime($a['due_date'])-time())/86400); ?>
        <div class="d-flex gap-2 px-3 py-2 border-bottom align-items-center">
          <div class="flex-fill"><div class="fw-semibold small"><?= e(truncate($a['title'],35)) ?></div><div class="text-muted" style="font-size:11px"><?= e($a['subject_name']??'') ?></div></div>
          <span class="badge bg-<?= $dl<=2?'danger':($dl<=5?'warning':'secondary') ?>" style="font-size:10px"><?= $dl ?>d</span>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap text-warning me-2"></i>Upcoming Exams</h6></div>
      <div class="card-body p-0">
        <?php if (empty($upcomingExams??[])): ?><div class="text-center py-3 text-muted small">No exams this week</div>
        <?php else: foreach ($upcomingExams??[] as $ex): ?>
        <div class="d-flex justify-content-between px-3 py-2 border-bottom">
          <div><div class="fw-semibold small"><?= e($ex['subject_name']??'') ?></div><div class="text-muted" style="font-size:11px"><?= ucfirst(str_replace('_',' ',$ex['type'])) ?></div></div>
          <div class="fw-bold small text-warning"><?= formatDate($ex['exam_date'],'d M') ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex justify-content-between py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-star text-info me-2"></i>Recent Results</h6><a href="<?= url('exams/report-cards') ?>" class="text-primary small">Report Card</a></div>
      <div class="card-body p-0">
        <?php if (empty($recentMarks??[])): ?><div class="text-center py-3 text-muted small">No results yet</div>
        <?php else: foreach ($recentMarks??[] as $m): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <div class="flex-fill"><div class="fw-semibold small"><?= e($m['subject_name']??'') ?></div><div class="text-muted" style="font-size:11px"><?= ucfirst(str_replace('_',' ',$m['type']??'')) ?></div></div>
          <div class="text-center"><div class="small fw-bold"><?= $m['marks_obtained'] ?>/<?= $m['total_marks'] ?></div><span class="badge bg-<?= match(($m['grade_letter']??'F')[0]) {'A'=>'success','B'=>'primary','C'=>'info','D'=>'warning',default=>'danger'} ?>" style="font-size:10px"><?= e($m['grade_letter']??'—') ?></span></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; // student not null ?>


<?php /* ══════════════════════════════════════════════════════════════
         PARENT
   ══════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($role === 'parent'): ?>
<?php $linked = $linked ?? null; if (!$linked): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No student linked. Please contact the registrar.</div>
<?php else: ?>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div class="d-flex align-items-center gap-3">
    <img src="<?= photoUrl($linked['photo']??null,'student') ?>" class="rounded-circle" width="50" height="50" style="object-fit:cover">
    <div>
      <h5 class="fw-bold mb-0"><?= e($linked['first_name'].' '.$linked['last_name']) ?></h5>
      <div class="text-muted small"><?= e($linked['stud_no']??'') ?> &bull; Grade <?= e($linked['grade']??'—') ?>-<?= e($linked['section']??'') ?> &bull; GPA: <strong class="<?= getGpaClass($gpa??0) ?>"><?= number_format($gpa??0,2) ?></strong></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <?php foreach ([
    ['val'=>$attPct.'%','label'=>'Attendance','sub'=>($attRow['p']??0).'/'.($attRow['total']??0).' days','ic'=>$attPct>=80?'#388E3C':'#E65100','ibg'=>$attPct>=80?'#E8F5E9':'#FFF3E0','icon'=>'calendar-check'],
    ['val'=>number_format($gpa??0,2),'label'=>'GPA','sub'=>'This semester','ic'=>'#1976D2','ibg'=>'#E3F2FD','icon'=>'star'],
    ['val'=>count($pendingFees??[]),'label'=>'Pending Fees','sub'=>count($pendingFees??[])>0?'Payment needed':'All clear','ic'=>count($pendingFees??[])>0?'#D32F2F':'#388E3C','ibg'=>count($pendingFees??[])>0?'#FFEBEE':'#E8F5E9','icon'=>'money-bill'],
    ['val'=>count($upcomingExams??[]),'label'=>'Upcoming Exams','sub'=>'Next 14 days','ic'=>'#F57F17','ibg'=>'#FFF8E1','icon'=>'graduation-cap'],
  ] as $k): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm dash-kpi-card h-100">
      <div class="card-body p-3">
        <div class="d-flex align-items-start justify-content-between gap-2">
          <div>
            <p class="text-muted fw-semibold mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px"><?= $k['label'] ?></p>
            <h3 class="fw-bold text-dark mb-0" style="font-size:1.4rem"><?= $k['val'] ?></h3>
            <p class="text-muted mb-0" style="font-size:11px"><?= $k['sub'] ?></p>
          </div>
          <div class="dash-kpi-icon flex-shrink-0" style="background:<?= $k['ibg'] ?>;color:<?= $k['ic'] ?>">
            <i class="fas fa-<?= $k['icon'] ?>"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-md-5"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-star text-info me-2"></i>Recent Results</h6></div>
  <div class="card-body p-0"><?php if(empty($recentMarks??[])): ?><div class="text-center py-3 text-muted small">No results yet</div>
  <?php else: foreach($recentMarks??[] as $m): ?><div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom"><div class="flex-fill"><div class="fw-semibold small"><?= e($m['subject_name']??'') ?></div><div class="text-muted" style="font-size:11px"><?= ucfirst(str_replace('_',' ',$m['type']??'')) ?></div></div>
  <div class="text-center"><div class="small"><?= $m['marks_obtained'] ?>/<?= $m['total_marks'] ?></div><span class="badge bg-primary" style="font-size:10px"><?= e($m['grade_letter']??'—') ?></span></div></div><?php endforeach; endif; ?>
  </div></div></div>
  <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-exclamation-circle text-danger me-2"></i>Pending Fees</h6></div>
  <div class="card-body p-0"><?php if(empty($pendingFees??[])): ?><div class="text-center py-3 text-success small"><i class="fas fa-check-circle me-1"></i>All fees paid!</div>
  <?php else: foreach($pendingFees??[] as $f): ?><div class="d-flex justify-content-between px-3 py-2 border-bottom"><div><div class="fw-semibold small"><?= e($f['fee_name']??'') ?></div><div class="text-muted" style="font-size:11px">Due <?= formatDate($f['due_date']) ?></div></div>
  <span class="badge bg-danger-light text-danger border">ETB <?= number_format($f['amount'],0) ?></span></div><?php endforeach; endif; ?>
  </div></div></div>
  <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap text-warning me-2"></i>Upcoming Exams</h6></div>
  <div class="card-body p-0"><?php if(empty($upcomingExams??[])): ?><div class="text-center py-3 text-muted small">None</div>
  <?php else: foreach($upcomingExams??[] as $ex): ?><div class="px-3 py-2 border-bottom"><div class="fw-semibold small"><?= e($ex['subject_name']??'') ?></div><div class="fw-bold text-warning small"><?= formatDate($ex['exam_date'],'d M') ?></div></div><?php endforeach; endif; ?>
  </div></div></div>
</div>
<?php endif; // parent linked ?>


<?php /* ══════════════════════════════════════════════════════════════
         FINANCE OFFICER
   ══════════════════════════════════════════════════════════════ */ ?>
<?php elseif ($role === 'finance_officer'): ?>

<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1"><?= $time_greeting ?>, <span class="text-primary"><?= e($username_short) ?></span> 👋</h4>
    <p class="text-muted small mb-0"><?= $today ?></p>
  </div>
  <a href="<?= url('finance/fees') ?>" class="btn btn-sm btn-success"><i class="fas fa-money-bill me-1"></i>Collect Fees</a>
</div>

<div class="row g-3 mb-4">
  <?php foreach ([
    ['val'=>'ETB '.number_format($todayCollected??0,0),'label'=>'Today\'s Collections','ic'=>'#388E3C','ibg'=>'#E8F5E9','icon'=>'calendar-day'],
    ['val'=>'ETB '.number_format($monthIncome??0,0),'label'=>'This Month Income','ic'=>'#1976D2','ibg'=>'#E3F2FD','icon'=>'chart-line'],
    ['val'=>'ETB '.number_format($monthExpenses??0,0),'label'=>'This Month Expenses','ic'=>'#D32F2F','ibg'=>'#FFEBEE','icon'=>'arrow-down'],
    ['val'=>'ETB '.number_format($pendingFees??0,0),'label'=>'Pending Fees','ic'=>'#E65100','ibg'=>'#FFF3E0','icon'=>'exclamation-circle'],
    ['val'=>$pendingPayroll??0,'label'=>'Pending Payroll','ic'=>($pendingPayroll??0)>0?'#D32F2F':'#607D8B','ibg'=>($pendingPayroll??0)>0?'#FFEBEE':'#F5F5F5','icon'=>'users-cog'],
    ['val'=>'ETB '.number_format($yearIncome??0,0),'label'=>'Year Income','ic'=>'#0288D1','ibg'=>'#E1F5FE','icon'=>'money-bill-wave'],
  ] as $k): ?>
  <div class="col-6 col-lg-2">
    <div class="card border-0 shadow-sm dash-kpi-card h-100">
      <div class="card-body p-3">
        <div class="dash-kpi-icon mb-2" style="background:<?= $k['ibg'] ?>;color:<?= $k['ic'] ?>;width:36px;height:36px;font-size:14px">
          <i class="fas fa-<?= $k['icon'] ?>"></i>
        </div>
        <h4 class="fw-bold text-dark mb-0" style="font-size:1.1rem"><?= $k['val'] ?></h4>
        <p class="text-muted mb-0" style="font-size:11px"><?= $k['label'] ?></p>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold">Income vs Expenses &mdash; 6 Months</h6>
        <a href="<?= url('finance/reports') ?>" class="btn btn-xs btn-outline-primary">Full Report</a>
      </div>
      <div class="card-body"><canvas id="financeChart" height="120"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 py-3"><h6 class="mb-0 fw-semibold">By Fee Type</h6></div>
      <div class="card-body p-0">
        <?php foreach($feeByCategory??[] as $f): if(!$f['total']) continue; ?>
        <div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span><?= e($f['name']??'General') ?></span><span class="fw-bold text-success">ETB <?= number_format($f['total'],0) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-0 d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold"><i class="fas fa-receipt text-success me-2"></i>Recent Payments</h6>
    <a href="<?= url('finance/payments') ?>" class="text-primary small">View All</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 small">
        <thead class="table-light"><tr><th>Student</th><th>Amount</th><th>Date</th><th>Method</th><th>Receipt</th></tr></thead>
        <tbody>
          <?php foreach(array_slice($recentPayments??[],0,8) as $p): ?>
          <tr>
            <td class="fw-semibold"><?= e($p['first_name'].' '.$p['last_name']) ?></td>
            <td class="text-success fw-bold">ETB <?= number_format($p['amount'],0) ?></td>
            <td><?= formatDate($p['payment_date'],'d M') ?></td>
            <td><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></td>
            <td class="font-monospace text-muted" style="font-size:11px"><?= e($p['receipt_no']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
$fc = $finChart ?? [];
?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var c=document.getElementById('financeChart');
  if(c) new Chart(c,{type:'bar',data:{labels:<?= json_encode(array_column($fc,'month')) ?>,datasets:[
    {label:'Income',  data:<?= json_encode(array_column($fc,'income')) ?>,  backgroundColor:'rgba(25,118,210,.8)',borderRadius:4},
    {label:'Expenses',data:<?= json_encode(array_column($fc,'expenses')) ?>,backgroundColor:'rgba(211,47,47,.75)',borderRadius:4}
  ]},options:{responsive:true,plugins:{legend:{position:'top'}}}});
});
</script>

<?php endif; /* role */ ?>


<!-- ── Announcements bar (all roles) ─────────────────────────── -->
<?php if (!empty($announcements??[]) && !$isAdmin): ?>
<div class="card border-0 shadow-sm mt-4">
  <div class="card-header bg-white border-0 d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold"><i class="fas fa-bullhorn text-warning me-2"></i>School Announcements</h6>
    <a href="<?= url('communication/announcements') ?>" class="text-primary small">View All</a>
  </div>
  <div class="row g-0">
    <?php foreach (array_slice($announcements??[],0,3) as $ann): $c=match($ann['priority']){'urgent'=>'danger','important'=>'warning','normal'=>'info',default=>'secondary'}; ?>
    <div class="col-md-4 p-3 border-bottom border-end">
      <span class="badge bg-<?= $c ?> mb-1"><?= ucfirst($ann['priority']) ?></span>
      <div class="fw-semibold small mb-1"><?= e($ann['title']) ?></div>
      <div class="text-muted small"><?= e(truncate($ann['content'],80)) ?></div>
      <div class="text-muted mt-1" style="font-size:10px"><i class="fas fa-clock me-1"></i><?= timeAgo($ann['created_at']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>


<!-- ── Dashboard Styles ───────────────────────────────────────── -->
<style>
.dash-kpi-card { transition: transform .18s, box-shadow .18s; }
.dash-kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.11) !important; }

.dash-kpi-icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; flex-shrink: 0;
}

/* Compact icon for finance row */
.dash-kpi-icon[style*="width:36px"] { border-radius: 10px; }

.sched-dot {
  width: 8px; height: 8px; border-radius: 50%;
  flex-shrink: 0; display: inline-block;
}
.act-icon-sm {
  width: 26px; height: 26px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
</style>
