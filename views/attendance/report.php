<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i>Attendance Report</h4>
  </div>
  <?php if ($classId && !empty($students)): ?>
  <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print me-1"></i>Print</button>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3"><label class="form-label small fw-semibold">Class</label><select name="class_id" class="form-select"><option value="">Select Class</option><?php foreach ($classes as $cls): ?><option value="<?= $cls['id'] ?>" <?= selected($classId,$cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label class="form-label small fw-semibold">Month</label><input type="month" name="month" class="form-control" value="<?= e($month) ?>"></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Generate</button></div>
    </form>
  </div>
</div>

<?php if ($classId && !empty($students)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3">
    <h6 class="mb-0 fw-semibold">Attendance for <?= date('F Y', strtotime($month.'-01')) ?></h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-sm mb-0 small">
        <thead class="table-primary">
          <tr>
            <th style="min-width:140px">Student</th>
            <?php foreach ($dates as $d): ?>
            <th class="text-center px-1" style="min-width:30px"><?= date('d',strtotime($d)) ?><br><span style="font-size:9px"><?= date('D',strtotime($d)) ?></span></th>
            <?php endforeach; ?>
            <th class="text-center">P</th><th class="text-center">A</th><th class="text-center text-success">%</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
          <tr>
            <td class="fw-semibold small"><?= e($s['first_name'].' '.$s['last_name']) ?></td>
            <?php foreach ($dates as $d):
              $att = $s['attendance'][$d] ?? null;
              $cls = match($att) {'present'=>'bg-success','absent'=>'bg-danger','late'=>'bg-warning','excused'=>'bg-info',default=>''};
              $lbl = match($att) {'present'=>'P','absent'=>'A','late'=>'L','excused'=>'E',default=>'—'};
            ?>
            <td class="text-center px-1 <?= $cls ?> text-white" style="font-size:10px"><?= $lbl ?></td>
            <?php endforeach; ?>
            <td class="text-center fw-bold text-success"><?= $s['summary']['present']??0 ?></td>
            <td class="text-center fw-bold text-danger"><?= $s['summary']['absent']??0 ?></td>
            <?php $tot=$s['summary']['total']??0; $prs=$s['summary']['present']??0; $pct=$tot>0?round(($prs/$tot)*100):0; ?>
            <td class="text-center fw-bold <?= $pct>=80?'text-success':'text-danger' ?>"><?= $pct ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-calendar fa-3x mb-3"></i><br><h6>Select a class and month to generate the report</h6></div></div>
<?php endif; ?>
