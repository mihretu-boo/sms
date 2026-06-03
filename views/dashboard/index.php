<?php
$role = Auth::role();
$user = Auth::user();
$isAdmin = in_array($role, ['super_admin','principal','vice_principal','registrar']);
?>

<!-- ===== SMART HEADER ===== -->
<div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
  <div>
    <div class="text-muted small mb-1"><i class="fas fa-calendar-alt me-1"></i><?= $today ?></div>
    <h4 class="mb-0 fw-bold"><?= $time_greeting ?>, <span class="text-primary"><?= e($username_short) ?></span>! 👋</h4>
    <div class="text-muted small"><?= getRoleLabel($role) ?> &mdash; <?= e(getSetting('school_name_short','SJASS')) ?></div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($isAdmin): ?>
      <a href="<?= url('students/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-user-plus me-1"></i>Add Student</a>
      <a href="<?= url('attendance/take') ?>"  class="btn btn-sm btn-success"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
      <a href="<?= url('reports') ?>"           class="btn btn-sm btn-outline-secondary"><i class="fas fa-chart-bar me-1"></i>Reports</a>
    <?php elseif ($role==='teacher' || $role==='dept_head'): ?>
      <a href="<?= url('attendance/take') ?>"   class="btn btn-sm btn-success"><i class="fas fa-calendar-check me-1"></i>Take Attendance</a>
      <a href="<?= url('exams/marks') ?>"       class="btn btn-sm btn-primary"><i class="fas fa-pen me-1"></i>Enter Marks</a>
    <?php elseif ($role==='student'): ?>
      <a href="<?= url('exams/report-cards') ?>" class="btn btn-sm btn-primary"><i class="fas fa-graduation-cap me-1"></i>My Results</a>
      <a href="<?= url('assignments') ?>"         class="btn btn-sm btn-outline-secondary"><i class="fas fa-tasks me-1"></i>Assignments</a>
    <?php endif; ?>
  </div>
</div>

<?php /* ══════════════ ADMIN / PRINCIPAL ══════════════ */ ?>
<?php if ($isAdmin): ?>

<!-- KPI Row -->
<div class="row g-3 mb-4">
  <?php
  $kpis = [
    ['label'=>'Students',       'val'=>number_format($totalStudents??0), 'icon'=>'user-graduate', 'color'=>'primary',  'trend'=>null,         'sub'=>'Active this year'],
    ['label'=>'Staff',          'val'=>number_format($totalStaff??0),    'icon'=>'chalkboard-teacher','color'=>'success','trend'=>null,        'sub'=>'Teaching & admin'],
    ['label'=>'Attendance',     'val'=>($attRate??0).'%',                'icon'=>'calendar-check','color'=>($attRate??0)>=80?'success':'warning',
     'trend'=>$attTrend??0,     'sub'=>'Today vs yesterday'],
    ['label'=>'Month Income',   'val'=>'ETB '.number_format($monthIncome??0,0),'icon'=>'money-bill-wave','color'=>'info',
     'trend'=>$incomeTrend??0,  'sub'=>'vs last month'],
    ['label'=>'Pending Fees',   'val'=>'ETB '.number_format($pendingFees??0,0),'icon'=>'exclamation-circle','color'=>'warning','trend'=>null,'sub'=>'Unpaid / partial'],
    ['label'=>'Discipline',     'val'=>$openIncidents??0,               'icon'=>'gavel',          'color'=>($openIncidents??0)>0?'danger':'secondary','trend'=>null,'sub'=>'Open incidents'],
    ['label'=>'Overdue Books',  'val'=>$overdueBooks??0,                'icon'=>'book',            'color'=>($overdueBooks??0)>0?'danger':'secondary','trend'=>null,'sub'=>'Library'],
    ['label'=>'Exam Review',    'val'=>$pendingExams??0,                'icon'=>'file-alt',       'color'=>($pendingExams??0)>0?'warning':'secondary','trend'=>null,'sub'=>'Awaiting approval'],
  ];
  foreach ($kpis as $k): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm h-100 kpi-card">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="kpi-icon bg-<?= $k['color'] ?>-light text-<?= $k['color'] ?> rounded-3 flex-shrink-0">
          <i class="fas fa-<?= $k['icon'] ?> fa-lg"></i>
        </div>
        <div class="min-w-0">
          <div class="kpi-value fw-bold text-truncate"><?= $k['val'] ?></div>
          <div class="kpi-label text-muted"><?= $k['label'] ?></div>
          <?php if ($k['trend'] !== null): ?>
          <div class="kpi-trend <?= ($k['trend']??0)>=0?'text-success':'text-danger' ?>">
            <i class="fas fa-arrow-<?= ($k['trend']??0)>=0?'up':'down' ?>" style="font-size:9px"></i> <?= abs($k['trend']??0) ?>%
          </div>
          <?php else: ?>
          <div class="text-muted" style="font-size:11px"><?= $k['sub'] ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar text-primary me-2"></i>Attendance Trend &mdash; Last 14 Days</h6>
        <a href="<?= url('attendance/analytics') ?>" class="btn btn-xs btn-outline-primary">Analytics</a>
      </div>
      <div class="card-body pb-2"><canvas id="attTrendChart" height="110"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-users text-success me-2"></i>Students by Grade</h6>
      </div>
      <div class="card-body d-flex flex-column align-items-center justify-content-center">
        <canvas id="gradeDonut" height="160" style="max-width:160px"></canvas>
        <div class="mt-3 w-100">
          <?php foreach ($gradeChart??[] as $g): ?>
          <div class="d-flex justify-content-between small mb-1">
            <span class="text-muted">Grade <?= e($g['grade']) ?></span>
            <span class="badge bg-primary"><?= $g['cnt'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Finance + Activity -->
<div class="row g-3 mb-4">
  <div class="col-md-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-line text-success me-2"></i>Income vs Expenses &mdash; 6 Months</h6>
        <a href="<?= url('finance/reports') ?>" class="btn btn-xs btn-outline-success">Finance</a>
      </div>
      <div class="card-body pb-2"><canvas id="financeChart" height="130"></canvas></div>
    </div>
  </div>
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-stream text-info me-2"></i>Live Activity</h6>
      </div>
      <div class="card-body p-0" style="max-height:295px;overflow-y:auto">
        <?php foreach ($activity??[] as $act): ?>
        <div class="d-flex gap-2 px-3 py-2 border-bottom align-items-start">
          <div class="activity-dot bg-<?= $act['color'] ?>-light text-<?= $act['color'] ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:26px;height:26px;font-size:10px">
            <i class="fas fa-<?= $act['icon'] ?>"></i>
          </div>
          <div class="flex-fill min-w-0">
            <div class="small text-truncate"><?= e($act['text']) ?></div>
            <div class="text-muted" style="font-size:10px"><?= timeAgo($act['time']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Bottom Row -->
<div class="row g-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-receipt text-success me-2"></i>Recent Payments</h6>
        <a href="<?= url('finance/payments') ?>" class="text-primary small">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (array_slice($recentPayments??[],0,7) as $p): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
          <div><div class="fw-semibold small"><?= e($p['first_name'].' '.$p['last_name']) ?></div><div class="text-muted" style="font-size:11px"><?= formatDate($p['payment_date'],'d M') ?></div></div>
          <span class="badge bg-success-light text-success border">ETB <?= number_format($p['amount'],0) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-user-plus text-primary me-2"></i>Recent Enrollments</h6>
        <a href="<?= url('students') ?>" class="text-primary small">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (array_slice($recentStudents??[],0,7) as $s): ?>
        <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
          <img src="<?= photoUrl($s['photo']??null,'student') ?>" class="rounded-circle flex-shrink-0" width="28" height="28" style="object-fit:cover">
          <div class="flex-fill min-w-0">
            <div class="fw-semibold small text-truncate"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
            <div class="text-muted" style="font-size:11px">Gr <?= e($s['grade']??'—') ?>-<?= e($s['section']??'') ?></div>
          </div>
          <?= getStatusBadge($s['status']) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap text-warning me-2"></i>Upcoming Exams</h6>
        <a href="<?= url('exams') ?>" class="text-primary small">All Exams</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($upcomingExams??[])): ?>
        <div class="text-center py-4 text-muted small"><i class="fas fa-check-circle text-success"></i><br>No exams in next 14 days</div>
        <?php else: foreach ($upcomingExams??[] as $ex): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
          <div><div class="fw-semibold small"><?= e(truncate($ex['title'],30)) ?></div><div class="text-muted" style="font-size:11px">Gr <?= e($ex['grade']??'') ?>-<?= e($ex['section']??'') ?> &bull; <?= e($ex['subject_name']??'') ?></div></div>
          <div class="text-end"><div class="small fw-bold text-warning"><?= formatDate($ex['exam_date'],'d M') ?></div><div style="font-size:10px" class="text-muted"><?= ucfirst(str_replace('_',' ',$ex['type'])) ?></div></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php /* ══════════════ TEACHER / DEPT HEAD ══════════════ */ ?>
<?php elseif (in_array($role,['teacher','dept_head'])): ?>

<div class="row g-3 mb-4">
  <?php $tc=count($myClasses??[]); $ts=array_sum(array_column($myClasses??[],'student_count')); ?>
  <?php foreach ([
    ['val'=>$tc,'label'=>'My Classes','icon'=>'layer-group','color'=>'primary'],
    ['val'=>$ts,'label'=>'Students','icon'=>'users','color'=>'success'],
    ['val'=>count($todayTT??[]),'label'=>'Periods Today','icon'=>'clock','color'=>'info'],
    ['val'=>$pendingGrading??0,'label'=>'To Grade','icon'=>'tasks','color'=>($pendingGrading??0)>0?'warning':'secondary'],
  ] as $k): ?>
  <div class="col-6 col-md-3"><div class="kpi-card card border-0 shadow-sm text-center"><div class="card-body py-4">
    <div class="kpi-icon bg-<?= $k['color'] ?>-light text-<?= $k['color'] ?> rounded-3 mx-auto mb-2"><i class="fas fa-<?= $k['icon'] ?> fa-lg"></i></div>
    <div class="kpi-value"><?= $k['val'] ?></div><div class="kpi-label text-muted"><?= $k['label'] ?></div>
  </div></div></div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Today &mdash; <?= date('l, d M') ?></h6></div>
      <div class="card-body p-0">
        <?php if (empty($todayTT??[])): ?><div class="text-center py-5 text-muted"><i class="fas fa-couch fa-2x mb-2"></i><br>No classes today</div>
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
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
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
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
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
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
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

<?php /* ══════════════ STUDENT ══════════════ */ ?>
<?php elseif ($role==='student'): ?>
<?php if (!($student??null)): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Your profile is not linked. Contact the registrar.</div>
<?php else: ?>
<div class="row g-3 mb-4">
  <?php foreach ([
    ['val'=>'Grade '.e($student['grade']??'?'),'sub'=>'Section '.e($student['section']??'?'),'icon'=>'user-graduate','color'=>'primary','extra'=>in_array($student['grade']??'',['11','12'])?'<span class="badge bg-'.((($student['stream']??'')=='natural')?'success':'info').' mt-1" style="font-size:9px">'.((($student['stream']??'')=='natural')?'🔬 Natural':'🌍 Social').'</span>':''],
    ['val'=>$attPct.'%','sub'=>($attRow['p']??0).'/'.($attRow['total']??0).' days','icon'=>'calendar-check','color'=>$attPct>=80?'success':'warning','extra'=>''],
    ['val'=>number_format($gpa??0,2),'sub'=>'Rank #'.($rank??'?').' in class','icon'=>'star','color'=>'warning','extra'=>''],
    ['val'=>($feeRow['unpaid']??0),'sub'=>($feeRow['unpaid']??0)>0?'ETB '.number_format($feeRow['amount_due']??0,0).' due':'All paid','icon'=>'money-bill','color'=>($feeRow['unpaid']??0)>0?'danger':'success','extra'=>''],
  ] as $k): ?>
  <div class="col-6 col-md-3"><div class="kpi-card card border-0 shadow-sm text-center"><div class="card-body py-4">
    <div class="kpi-icon bg-<?= $k['color'] ?>-light text-<?= $k['color'] ?> rounded-3 mx-auto mb-2"><i class="fas fa-<?= $k['icon'] ?> fa-lg"></i></div>
    <div class="kpi-value <?= $k['icon']==='star'?getGpaClass($gpa??0):'' ?>"><?= $k['val'] ?></div>
    <div class="kpi-label text-muted"><?= $k['icon']==='user-graduate'?'My Class':($k['icon']==='calendar-check'?'Attendance':($k['icon']==='star'?'GPA':'Fees')) ?></div>
    <div style="font-size:11px" class="text-muted"><?= $k['sub'] ?></div>
    <?= $k['extra'] ?>
  </div></div></div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <!-- Attendance tiles -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-calendar text-primary me-2"></i>14-Day Attendance</h6></div>
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

  <!-- Subject Performance -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar text-warning me-2"></i>Subject Performance</h6></div>
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

  <!-- Today's Timetable -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Today &mdash; <?= date('l') ?></h6></div>
      <div class="card-body p-0">
        <?php if (empty($todayTT??[])): ?><div class="text-center py-5 text-muted"><i class="fas fa-coffee fa-2x mb-2"></i><br>No classes today</div>
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
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-tasks text-danger me-2"></i>Pending Assignments</h6><a href="<?= url('assignments') ?>" class="text-primary small">All</a></div>
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
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap text-warning me-2"></i>Upcoming Exams</h6></div>
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
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-star text-info me-2"></i>Recent Results</h6><a href="<?= url('exams/report-cards') ?>" class="text-primary small">Report Card</a></div>
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

<?php /* ══════════════ PARENT ══════════════ */ ?>
<?php elseif ($role==='parent'): ?>
<?php $linked=$linked??null; if(!$linked): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No student linked. Please contact the registrar.</div>
<?php else: ?>
<div class="alert alert-info mb-4 d-flex align-items-center gap-3 py-3">
  <img src="<?= photoUrl($linked['photo']??null,'student') ?>" class="rounded-circle" width="48" height="48" style="object-fit:cover">
  <div><div class="fw-bold"><?= e($linked['first_name'].' '.$linked['last_name']) ?></div>
  <div class="small text-muted"><?= e($linked['stud_no']??'') ?> &bull; Grade <?= e($linked['grade']??'—') ?>-<?= e($linked['section']??'') ?> &bull; GPA: <strong class="<?= getGpaClass($gpa??0) ?>"><?= number_format($gpa??0,2) ?></strong></div></div>
</div>
<div class="row g-3 mb-4">
  <?php foreach ([
    ['val'=>$attPct.'%','label'=>'Attendance','sub'=>($attRow['p']??0).'/'.($attRow['total']??0).' days','color'=>$attPct>=80?'success':'warning','icon'=>'calendar-check'],
    ['val'=>number_format($gpa??0,2),'label'=>'GPA','sub'=>'This semester','color'=>'primary','icon'=>'star'],
    ['val'=>count($pendingFees??[]),'label'=>'Pending Fees','sub'=>count($pendingFees??[])>0?'Payment needed':'All clear','color'=>count($pendingFees??[])>0?'danger':'success','icon'=>'money-bill'],
    ['val'=>count($upcomingExams??[]),'label'=>'Upcoming Exams','sub'=>'Next 14 days','color'=>'warning','icon'=>'graduation-cap'],
  ] as $k): ?>
  <div class="col-6 col-md-3"><div class="kpi-card card border-0 shadow-sm text-center"><div class="card-body py-4">
    <div class="kpi-icon bg-<?= $k['color'] ?>-light text-<?= $k['color'] ?> rounded-3 mx-auto mb-2"><i class="fas fa-<?= $k['icon'] ?> fa-lg"></i></div>
    <div class="kpi-value <?= $k['label']==='GPA'?getGpaClass($gpa??0):'' ?>"><?= $k['val'] ?></div>
    <div class="kpi-label text-muted"><?= $k['label'] ?></div>
    <div style="font-size:11px" class="text-muted"><?= $k['sub'] ?></div>
  </div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-3">
  <div class="col-md-5"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-star text-info me-2"></i>Recent Results</h6></div>
  <div class="card-body p-0"><?php if(empty($recentMarks??[])): ?><div class="text-center py-3 text-muted small">No results yet</div>
  <?php else: foreach($recentMarks??[] as $m): ?><div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom"><div class="flex-fill"><div class="fw-semibold small"><?= e($m['subject_name']??'') ?></div><div class="text-muted" style="font-size:11px"><?= ucfirst(str_replace('_',' ',$m['type']??'')) ?></div></div>
  <div class="text-center"><div class="small"><?= $m['marks_obtained'] ?>/<?= $m['total_marks'] ?></div><span class="badge bg-primary" style="font-size:10px"><?= e($m['grade_letter']??'—') ?></span></div></div><?php endforeach; endif; ?>
  </div></div></div>
  <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-exclamation-circle text-danger me-2"></i>Pending Fees</h6></div>
  <div class="card-body p-0"><?php if(empty($pendingFees??[])): ?><div class="text-center py-3 text-success small"><i class="fas fa-check-circle me-1"></i>All fees paid!</div>
  <?php else: foreach($pendingFees??[] as $f): ?><div class="d-flex justify-content-between px-3 py-2 border-bottom"><div><div class="fw-semibold small"><?= e($f['fee_name']??'') ?></div><div class="text-muted" style="font-size:11px">Due <?= formatDate($f['due_date']) ?></div></div>
  <span class="badge bg-danger-light text-danger border">ETB <?= number_format($f['amount'],0) ?></span></div><?php endforeach; endif; ?>
  </div></div></div>
  <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-graduation-cap text-warning me-2"></i>Upcoming Exams</h6></div>
  <div class="card-body p-0"><?php if(empty($upcomingExams??[])): ?><div class="text-center py-3 text-muted small">None</div>
  <?php else: foreach($upcomingExams??[] as $ex): ?><div class="px-3 py-2 border-bottom"><div class="fw-semibold small"><?= e($ex['subject_name']??'') ?></div><div class="fw-bold text-warning small"><?= formatDate($ex['exam_date'],'d M') ?></div></div><?php endforeach; endif; ?>
  </div></div></div>
</div>
<?php endif; // parent linked ?>

<?php /* ══════════════ FINANCE ══════════════ */ ?>
<?php elseif ($role==='finance_officer'): ?>
<div class="row g-3 mb-4">
  <?php foreach ([
    ['val'=>'ETB '.number_format($todayCollected??0,0),'label'=>'Today\'s Collections','color'=>'success','icon'=>'calendar-day'],
    ['val'=>'ETB '.number_format($monthIncome??0,0),'label'=>'This Month Income','color'=>'primary','icon'=>'chart-line'],
    ['val'=>'ETB '.number_format($monthExpenses??0,0),'label'=>'This Month Expenses','color'=>'danger','icon'=>'arrow-down'],
    ['val'=>'ETB '.number_format($pendingFees??0,0),'label'=>'Pending Fees','color'=>'warning','icon'=>'exclamation-circle'],
    ['val'=>$pendingPayroll??0,'label'=>'Pending Payroll','color'=>($pendingPayroll??0)>0?'danger':'secondary','icon'=>'users-cog'],
    ['val'=>'ETB '.number_format($yearIncome??0,0),'label'=>'Year Income','color'=>'info','icon'=>'money-bill-wave'],
  ] as $k): ?>
  <div class="col-6 col-md-4 col-lg-2"><div class="kpi-card card border-0 shadow-sm text-center"><div class="card-body py-3">
    <div class="kpi-icon bg-<?= $k['color'] ?>-light text-<?= $k['color'] ?> rounded-3 mx-auto mb-2" style="width:40px;height:40px"><i class="fas fa-<?= $k['icon'] ?>"></i></div>
    <div class="fw-bold small"><?= $k['val'] ?></div><div class="text-muted" style="font-size:11px"><?= $k['label'] ?></div>
  </div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-8"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom d-flex justify-content-between py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-chart-bar text-primary me-2"></i>Income vs Expenses &mdash; 6 Months</h6><a href="<?= url('finance/reports') ?>" class="btn btn-xs btn-outline-primary">Full Report</a></div><div class="card-body"><canvas id="financeChart" height="120"></canvas></div></div></div>
  <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">By Fee Type</h6></div>
  <div class="card-body p-0"><?php foreach($feeByCategory??[] as $f): if(!$f['total']) continue; ?><div class="d-flex justify-content-between px-3 py-2 border-bottom small"><span><?= e($f['name']??'General') ?></span><span class="fw-bold text-success">ETB <?= number_format($f['total'],0) ?></span></div><?php endforeach; ?></div></div></div>
</div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white border-bottom d-flex justify-content-between py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-receipt text-success me-2"></i>Recent Payments</h6><a href="<?= url('finance/payments') ?>" class="text-primary small">View All</a></div>
<div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 small"><thead class="table-light"><tr><th>Student</th><th>Amount</th><th>Date</th><th>Method</th><th>Receipt</th></tr></thead><tbody>
<?php foreach(array_slice($recentPayments??[],0,8) as $p): ?><tr><td class="fw-semibold"><?= e($p['first_name'].' '.$p['last_name']) ?></td><td class="text-success fw-bold">ETB <?= number_format($p['amount'],0) ?></td><td><?= formatDate($p['payment_date'],'d M') ?></td><td><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></td><td class="font-monospace text-muted" style="font-size:11px"><?= e($p['receipt_no']) ?></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<?php endif; /* role */ ?>

<!-- Announcements (all roles) -->
<?php if (!empty($announcements??[])): ?>
<div class="card border-0 shadow-sm mt-4">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
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

<!-- Chart JS initialization -->
<?php if ($isAdmin): ?>
<?php
$attLabels  = json_encode(array_column($attChart??[],'date'));
$attPresent = json_encode(array_column($attChart??[],'present'));
$attAbsent  = json_encode(array_column($attChart??[],'absent'));
$attRates   = json_encode(array_column($attChart??[],'rate'));
$finMonths  = json_encode(array_column($finChart??[],'month'));
$finIncome  = json_encode(array_column($finChart??[],'income'));
$finExp     = json_encode(array_column($finChart??[],'expenses'));
$gradeLabels= json_encode(array_map(fn($g)=>'Grade '.$g['grade'], $gradeChart??[]));
$gradeCounts= json_encode(array_column($gradeChart??[],'cnt'));
?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var c1=document.getElementById('attTrendChart');
  if(c1) new Chart(c1,{data:{labels:<?= $attLabels ?>,datasets:[
    {type:'bar', label:'Present',data:<?= $attPresent ?>,backgroundColor:'rgba(46,125,50,.6)',borderRadius:3,yAxisID:'y'},
    {type:'bar', label:'Absent', data:<?= $attAbsent  ?>,backgroundColor:'rgba(198,40,40,.5)', borderRadius:3,yAxisID:'y'},
    {type:'line',label:'Rate %', data:<?= $attRates   ?>,borderColor:'#1565C0',borderWidth:2,pointRadius:3,tension:.4,fill:false,yAxisID:'y2'}
  ]},options:{responsive:true,plugins:{legend:{position:'top'}},scales:{y:{stacked:true,beginAtZero:true},y2:{position:'right',min:0,max:100,ticks:{callback:v=>v+'%'},grid:{drawOnChartArea:false}}}}});

  var c2=document.getElementById('gradeDonut');
  if(c2) new Chart(c2,{type:'doughnut',data:{labels:<?= $gradeLabels ?>,datasets:[{data:<?= $gradeCounts ?>,backgroundColor:['#1565C0','#2E7D32','#F57F17','#6A1B9A'],borderWidth:2}]},options:{responsive:true,plugins:{legend:{display:false}},cutout:'70%'}});

  var c3=document.getElementById('financeChart');
  if(c3) new Chart(c3,{type:'bar',data:{labels:<?= $finMonths ?>,datasets:[
    {label:'Income',  data:<?= $finIncome ?>,backgroundColor:'rgba(46,125,50,.7)',borderRadius:4},
    {label:'Expenses',data:<?= $finExp    ?>,backgroundColor:'rgba(198,40,40,.6)',borderRadius:4}
  ]},options:{responsive:true,plugins:{legend:{position:'top'}}}});
});
</script>

<?php elseif ($role==='finance_officer'): ?>
<?php $fc=$finChart??[]; ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  var c=document.getElementById('financeChart');
  if(c) new Chart(c,{type:'bar',data:{labels:<?= json_encode(array_column($fc,'month')) ?>,datasets:[
    {label:'Income',  data:<?= json_encode(array_column($fc,'income')) ?>,  backgroundColor:'rgba(46,125,50,.7)',borderRadius:4},
    {label:'Expenses',data:<?= json_encode(array_column($fc,'expenses')) ?>,backgroundColor:'rgba(198,40,40,.6)',borderRadius:4}
  ]},options:{responsive:true,plugins:{legend:{position:'top'}}}});
});
</script>
<?php endif; ?>

<style>
.kpi-card{transition:transform .18s,box-shadow .18s}
.kpi-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.1)!important}
.kpi-value{font-size:1.3rem;font-weight:700;line-height:1.2}
.kpi-label{font-size:.72rem;color:#6c757d}
.kpi-trend{font-size:.7rem}
.kpi-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.activity-dot{font-size:11px}
</style>
