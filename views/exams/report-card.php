<div class="container py-3" style="max-width:700px">
  <!-- School Header -->
  <div class="text-center mb-4 pb-3 border-bottom border-2 border-primary">
    <h5 class="fw-bold text-primary mb-1"><?= e(getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?></h5>
    <div class="text-muted small"><?= e(getSetting('school_address','Yabelo, Borana Zone, Oromia')) ?></div>
    <h5 class="fw-bold mt-3 mb-0" style="color:#2E7D32">STUDENT REPORT CARD</h5>
    <div class="small text-muted"><?= e($semester['name'] ?? 'Semester 1') ?> — <?= e($ay['name'] ?? '2024-2025') ?></div>
  </div>

  <!-- Student Info -->
  <div class="row mb-4">
    <div class="col-8">
      <table class="table table-sm table-borderless small mb-0">
        <tr><td class="fw-semibold text-muted" width="35%">Full Name:</td><td class="fw-bold"><?= e($student['first_name'].' '.$student['last_name']) ?></td></tr>
        <tr><td class="fw-semibold text-muted">Student ID:</td><td><?= e($student['student_id']) ?></td></tr>
        <tr><td class="fw-semibold text-muted">Class:</td><td>Grade <?= e($student['grade']??'') ?>-<?= e($student['section']??'') ?></td></tr>
        <tr><td class="fw-semibold text-muted">Admission No:</td><td><?= e($student['admission_no']) ?></td></tr>
      </table>
    </div>
    <div class="col-4 text-end">
      <img src="<?= photoUrl($student['photo'],'student') ?>" class="rounded" width="80" height="100" style="object-fit:cover;border:2px solid #1565C0">
    </div>
  </div>

  <!-- Attendance Summary -->
  <div class="row g-3 mb-4">
    <?php $attTotal = $attendance['total']??0; $attPresent = $attendance['present']??0; $attPct = $attTotal>0 ? round(($attPresent/$attTotal)*100) : 0; ?>
    <div class="col-3 text-center border rounded p-2"><div class="fw-bold text-primary"><?= $attTotal ?></div><div class="small text-muted">School Days</div></div>
    <div class="col-3 text-center border rounded p-2"><div class="fw-bold text-success"><?= $attPresent ?></div><div class="small text-muted">Present</div></div>
    <div class="col-3 text-center border rounded p-2"><div class="fw-bold text-danger"><?= $attendance['absent']??0 ?></div><div class="small text-muted">Absent</div></div>
    <div class="col-3 text-center border rounded p-2"><div class="fw-bold <?= $attPct>=80?'text-success':'text-warning' ?>"><?= $attPct ?>%</div><div class="small text-muted">Rate</div></div>
  </div>

  <!-- Marks Table -->
  <table class="table table-bordered small mb-4">
    <thead class="table-primary">
      <tr><th>Subject</th><th class="text-center">Assignment</th><th class="text-center">Quiz</th><th class="text-center">Mid</th><th class="text-center">Final</th><th class="text-center">GPA</th><th class="text-center">Grade</th></tr>
    </thead>
    <tbody>
      <?php foreach ($subjects as $sub): ?>
      <tr>
        <td class="fw-semibold"><?= e($sub['subject']) ?></td>
        <td class="text-center"><?= $sub['assignments'] > 0 ? e($sub['assignments']) : '—' ?></td>
        <td class="text-center"><?= $sub['quizzes'] > 0 ? e($sub['quizzes']) : '—' ?></td>
        <td class="text-center"><?= $sub['mid'] > 0 ? e($sub['mid']) : '—' ?></td>
        <td class="text-center"><?= $sub['final'] > 0 ? e($sub['final']) : '—' ?></td>
        <td class="text-center fw-bold <?= getGpaClass(round($sub['gpa']??0,2)) ?>"><?= number_format($sub['gpa']??0,2) ?></td>
        <td class="text-center">
          <?php $gl = $sub['grade']??'—'; ?>
          <span class="badge bg-<?= match($gl[0]??'F') {'A'=>'success','B'=>'primary','C'=>'info','D'=>'warning',default=>'danger'} ?>"><?= e($gl) ?></span>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light fw-bold">
      <tr>
        <td colspan="5" class="text-end">Overall GPA / Rank:</td>
        <td class="text-center <?= getGpaClass($overall_gpa) ?>"><?= number_format($overall_gpa,2) ?></td>
        <td class="text-center">#<?= $rank ?></td>
      </tr>
    </tfoot>
  </table>

  <!-- Performance Description -->
  <?php
  $desc = match(true) {
    $overall_gpa >= 3.75 => 'Excellent — Outstanding academic performance',
    $overall_gpa >= 3.0  => 'Very Good — Above average academic performance',
    $overall_gpa >= 2.0  => 'Good — Satisfactory academic performance',
    $overall_gpa >= 1.0  => 'Fair — Needs improvement in academic performance',
    default              => 'Unsatisfactory — Requires significant improvement',
  };
  ?>
  <div class="alert alert-<?= $overall_gpa>=3.0?'success':($overall_gpa>=1.0?'warning':'danger') ?> py-2 small">
    <strong>Performance:</strong> <?= $desc ?>
  </div>

  <!-- Signatures -->
  <div class="row mt-5 small">
    <div class="col-4 text-center">
      <div style="border-top: 1px solid #333; padding-top: 4px; margin-top: 24px;">Class Teacher</div>
    </div>
    <div class="col-4 text-center">
      <div style="border-top: 1px solid #333; padding-top: 4px; margin-top: 24px;">Principal</div>
    </div>
    <div class="col-4 text-center">
      <div style="border-top: 1px solid #333; padding-top: 4px; margin-top: 24px;">Parent/Guardian</div>
    </div>
  </div>

  <div class="text-center mt-4 text-muted small">Printed on: <?= date('d M Y H:i') ?></div>
</div>
