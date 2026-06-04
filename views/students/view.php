<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-circle text-primary me-2"></i><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li>
      <li class="breadcrumb-item active"><?= e($student['first_name']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('students/id-card/'.$student['id']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-id-card me-1"></i>ID Card</a>
    <?php if (Auth::can('students')): ?>
    <a href="<?= url('students/edit/'.$student['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
    <?php endif; ?>
    <a href="<?= url('students') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>
</div>

<div class="row g-4">
  <!-- Profile Card -->
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center mb-4">
      <div class="card-body py-4">
        <img src="<?= photoUrl($student['photo'], 'student') ?>" class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover; border:3px solid #1565C0">
        <h6 class="fw-bold mb-1"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h6>
        <span class="badge bg-primary mb-2"><?= e($student['student_id']) ?></span>
        <div><?= getStatusBadge($student['status']) ?></div>
        <hr>
        <div class="text-start small">
          <div class="mb-2"><i class="fas fa-layer-group text-primary me-2"></i> <strong>Grade:</strong> <?= $student['grade'] ? 'Grade '.$student['grade'].'-'.$student['section'] : '—' ?></div>
          <?php if (in_array($student['grade']??'', ['11','12'])): ?>
          <?php $streamInfo = match($student['stream']??'general') {'natural'=>['🔬 Natural Science','success'],'social'=>['🌍 Social Science','info'],default=>['General','secondary']}; ?>
          <div class="mb-2">
            <span class="badge bg-<?= $streamInfo[1] ?>"><?= $streamInfo[0] ?></span>
          </div>
          <?php endif; ?>
          <div class="mb-2"><i class="fas fa-<?= $student['gender']==='male' ? 'mars' : 'venus' ?> text-info me-2"></i> <?= ucfirst($student['gender']) ?></div>
          <div class="mb-2"><i class="fas fa-birthday-cake text-warning me-2"></i> <?= formatDate($student['dob']) ?></div>
          <div class="mb-2"><i class="fas fa-phone text-success me-2"></i> <?= e($student['phone'] ?? '—') ?></div>
          <div class="mb-2"><i class="fas fa-envelope text-danger me-2"></i> <?= e($student['email'] ?? '—') ?></div>
          <div class="mb-2"><i class="fas fa-map-marker-alt text-secondary me-2"></i> <?= e($student['city'] ?? '—') ?></div>
        </div>
        <!-- Student login -->
        <?php if ($user): ?>
        <hr>
        <div class="text-start small">
          <div class="fw-semibold text-muted mb-1"><i class="fas fa-user-graduate me-1 text-primary"></i>Student Login</div>
          <div class="p-2 bg-light rounded">
            <div><i class="fas fa-user me-1 text-muted"></i><strong><?= e($user['username']) ?></strong></div>
            <div><i class="fas fa-envelope me-1 text-muted"></i><?= e($user['email']) ?></div>
            <div class="text-muted mt-1" style="font-size:10px">URL: <?= BASE_URL ?>/login</div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Parent Info + Login -->
    <?php if ($parent): ?>
    <?php
      // Load parent user account info
      $parentUser = null;
      if (!empty($parent['user_id'])) {
          $puStmt = getDB()->prepare("SELECT username, email, status FROM users WHERE id=? LIMIT 1");
          $puStmt->execute([$parent['user_id']]);
          $parentUser = $puStmt->fetch();
      }
    ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0 small"><i class="fas fa-users me-1"></i>Parent / Guardian</h6>
      </div>
      <div class="card-body small">
        <div class="mb-2">
          <strong><?= e($parent['first_name'].' '.$parent['last_name']) ?></strong>
          <span class="badge bg-success ms-1"><?= ucfirst($parent['relation']) ?></span>
        </div>
        <div class="mb-1"><i class="fas fa-phone me-1 text-muted"></i><?= e($parent['phone']) ?></div>
        <?php if ($parent['email']): ?>
        <div class="mb-1"><i class="fas fa-envelope me-1 text-muted"></i><?= e($parent['email']) ?></div>
        <?php endif; ?>
        <?php if (!empty($parent['occupation'])): ?>
        <div class="mb-1"><i class="fas fa-briefcase me-1 text-muted"></i><?= e($parent['occupation']) ?></div>
        <?php endif; ?>

        <!-- Parent login account -->
        <hr class="my-2">
        <?php if ($parentUser): ?>
        <div class="fw-semibold text-muted mb-1"><i class="fas fa-key me-1 text-success"></i>Parent Login Account</div>
        <div class="p-2 bg-success bg-opacity-10 rounded border border-success border-opacity-25">
          <div><i class="fas fa-user me-1 text-muted"></i><strong><?= e($parentUser['username']) ?></strong></div>
          <div><i class="fas fa-envelope me-1 text-muted"></i><?= e($parentUser['email']) ?></div>
          <div class="d-flex justify-content-between align-items-center mt-1">
            <?= getStatusBadge($parentUser['status']) ?>
            <?php if (Auth::hasRole(['super_admin','principal','registrar'])): ?>
            <form action="<?= url('settings/users/reset-password/'.$parent['user_id']) ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('Reset parent password to Admin@123?')">
              <?= csrfField() ?>
              <button class="btn btn-xs btn-outline-warning" style="font-size:10px">
                <i class="fas fa-key me-1"></i>Reset Password
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning py-2 mb-0 small">
          <i class="fas fa-exclamation-triangle me-1"></i>
          No login account linked to this parent.
          <?php if (Auth::hasRole(['super_admin','principal','registrar'])): ?>
          <a href="<?= url('students/create-parent-account/'.$student['id']) ?>" class="alert-link">Create Account</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Details Tabs -->
  <div class="col-md-9">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom">
        <ul class="nav nav-tabs card-header-tabs" id="studentTabs">
          <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#info">Info</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#marks">Marks</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#attendance">Attendance</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#fees">Fees</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#timetable">Timetable</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#discipline">Discipline</a></li>
        </ul>
      </div>
      <div class="card-body tab-content p-3">

        <!-- Info Tab -->
        <div class="tab-pane fade show active" id="info">
          <div class="row g-3">
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Admission No</label><div class="fw-semibold"><?= e($student['admission_no']) ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Admission Date</label><div class="fw-semibold"><?= formatDate($student['admission_date']) ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Blood Type</label><div class="fw-semibold"><?= e($student['blood_type'] ?? '—') ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Nationality</label><div class="fw-semibold"><?= e($student['nationality'] ?? '—') ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Religion</label><div class="fw-semibold"><?= e($student['religion'] ?? '—') ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Previous School</label><div class="fw-semibold"><?= e($student['previous_school'] ?? '—') ?></div></div></div>
            <div class="col-12"><div class="mb-3"><label class="text-muted small">Address</label><div class="fw-semibold"><?= e($student['address'] ?? '—') ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Emergency Contact</label><div class="fw-semibold"><?= e($student['emergency_contact_name'] ?? '—') ?></div></div></div>
            <div class="col-md-6"><div class="mb-3"><label class="text-muted small">Emergency Phone</label><div class="fw-semibold"><?= e($student['emergency_contact_phone'] ?? '—') ?></div></div></div>
            <div class="col-12"><div><label class="text-muted small">Medical Information</label><div class="fw-semibold"><?= e($student['medical_info'] ?? '—') ?></div></div></div>
          </div>
        </div>

        <!-- Marks Tab -->
        <div class="tab-pane fade" id="marks">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light">
                <tr><th>Subject</th><th>Type</th><th>Score</th><th>Total</th><th>%</th><th>Grade</th></tr>
              </thead>
              <tbody>
                <?php if (empty($marks)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No marks recorded yet</td></tr>
                <?php else: foreach ($marks as $m): $pct = $m['total_marks'] > 0 ? round(($m['marks_obtained']/$m['total_marks'])*100,1) : 0; ?>
                <tr>
                  <td><?= e($m['subject']) ?></td>
                  <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$m['type'])) ?></span></td>
                  <td class="fw-semibold"><?= e($m['marks_obtained']) ?></td>
                  <td class="text-muted"><?= e($m['total_marks']) ?></td>
                  <td><?= $pct ?>%</td>
                  <td><span class="badge bg-<?= match(($m['grade_letter'] ?? 'F')[0]) {'A'=>'success','B'=>'primary','C'=>'info','D'=>'warning',default=>'danger'} ?>"><?= e($m['grade_letter'] ?? '—') ?></span></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <div class="text-end mt-2">
            <a href="<?= url('exams/report-card/'.$student['id']) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file-alt me-1"></i>View Report Card</a>
          </div>
        </div>

        <!-- Attendance Tab -->
        <div class="tab-pane fade" id="attendance">
          <?php if ($attendance): ?>
          <div class="row g-3 mb-4">
            <?php $total = $attendance['total']??0; $present = $attendance['present']??0; $pct = $total > 0 ? round(($present/$total)*100) : 0; ?>
            <div class="col-3"><div class="text-center"><div class="fw-bold fs-4 text-success"><?= $present ?></div><div class="text-muted small">Present</div></div></div>
            <div class="col-3"><div class="text-center"><div class="fw-bold fs-4 text-danger"><?= $attendance['absent']??0 ?></div><div class="text-muted small">Absent</div></div></div>
            <div class="col-3"><div class="text-center"><div class="fw-bold fs-4 text-warning"><?= $attendance['late']??0 ?></div><div class="text-muted small">Late</div></div></div>
            <div class="col-3"><div class="text-center"><div class="fw-bold fs-4 <?= $pct>=80?'text-success':'text-danger' ?>"><?= $pct ?>%</div><div class="text-muted small">Rate</div></div></div>
          </div>
          <?php endif; ?>
          <a href="<?= url('attendance/report?student_id='.$student['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-chart-line me-1"></i>Full Attendance Report</a>
        </div>

        <!-- Fees Tab -->
        <div class="tab-pane fade" id="fees">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light"><tr><th>Fee</th><th>Amount</th><th>Due Date</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (empty($fees)): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">No fees assigned</td></tr>
                <?php else: foreach ($fees as $f): ?>
                <tr>
                  <td><?= e($f['fee_name']) ?></td>
                  <td><?= formatMoney($f['amount']) ?></td>
                  <td class="<?= strtotime($f['due_date'])<time() && $f['status']!=='paid' ? 'text-danger' : '' ?>"><?= formatDate($f['due_date']) ?></td>
                  <td><?= getStatusBadge($f['status']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
          <?php if (Auth::can('finance')): ?>
          <a href="<?= url('finance/payments/create?student_id='.$student['id']) ?>" class="btn btn-sm btn-success mt-2"><i class="fas fa-plus me-1"></i>Record Payment</a>
          <?php endif; ?>
        </div>

        <!-- Timetable Tab -->
        <div class="tab-pane fade" id="timetable">
          <?php
          $days = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
          $ttByDay = [];
          foreach ($timetable as $tt) $ttByDay[$tt['day']][] = $tt;
          ?>
          <div class="table-responsive">
            <table class="table table-bordered table-sm small">
              <thead class="table-primary"><tr>
                <?php foreach ($days as $d): ?><th class="text-center"><?= $d ?></th><?php endforeach; ?>
              </tr></thead>
              <tbody>
                <tr>
                  <?php foreach ($days as $d): ?>
                  <td>
                    <?php foreach ($ttByDay[$d] ?? [] as $tt): ?>
                    <div class="mb-1 p-1 bg-light rounded">
                      <div class="fw-semibold"><?= e($tt['subject_name']) ?></div>
                      <div class="text-muted"><?= date('H:i',strtotime($tt['start_time'])) ?>-<?= date('H:i',strtotime($tt['end_time'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                  </td>
                  <?php endforeach; ?>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Discipline Tab -->
        <div class="tab-pane fade" id="discipline">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Severity</th><th>Status</th><th>Reported By</th></tr></thead>
              <tbody>
                <?php if (empty($discipline)): ?>
                <tr><td colspan="5" class="text-center py-4 text-success"><i class="fas fa-check-circle me-1"></i>No discipline incidents</td></tr>
                <?php else: foreach ($discipline as $d): ?>
                <tr>
                  <td><?= formatDate($d['incident_date']) ?></td>
                  <td><?= e($d['incident_type']) ?></td>
                  <td><?= getStatusBadge($d['severity']) ?></td>
                  <td><?= getStatusBadge($d['status']) ?></td>
                  <td class="text-muted small"><?= e($d['reported_by_name']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
