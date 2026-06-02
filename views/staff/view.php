<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= e($staff['first_name'].' '.$staff['last_name']) ?></h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('staff') ?>">Staff</a></li><li class="breadcrumb-item active"><?= e($staff['first_name']) ?></li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <?php if (Auth::hasRole(['super_admin','principal'])): ?>
    <a href="<?= url('staff/edit/'.$staff['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
    <?php endif; ?>
    <a href="<?= url('staff') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-md-3">
    <div class="card border-0 shadow-sm text-center mb-4">
      <div class="card-body py-4">
        <img src="<?= photoUrl($staff['photo'],'user') ?>" class="rounded-circle mb-3" width="90" height="90" style="object-fit:cover;border:3px solid #1565C0">
        <h6 class="fw-bold mb-1"><?= e($staff['first_name'].' '.$staff['last_name']) ?></h6>
        <span class="badge bg-primary mb-2"><?= e($staff['employee_id']) ?></span>
        <div class="mb-1"><?= getStatusBadge($staff['status']) ?></div>
        <hr>
        <div class="text-start small">
          <div class="mb-2"><i class="fas fa-briefcase text-primary me-2"></i><?= e($staff['position']) ?></div>
          <div class="mb-2"><i class="fas fa-building text-info me-2"></i><?= e($staff['dept_name']??'—') ?></div>
          <div class="mb-2"><i class="fas fa-phone text-success me-2"></i><?= e($staff['phone']??'—') ?></div>
          <div class="mb-2"><i class="fas fa-envelope text-danger me-2"></i><?= e($staff['email']??'—') ?></div>
          <div class="mb-2"><i class="fas fa-graduation-cap text-warning me-2"></i><?= e($staff['qualification']??'—') ?></div>
          <div class="mb-2"><i class="fas fa-calendar text-secondary me-2"></i>Hired: <?= formatDate($staff['hire_date']) ?></div>
        </div>
        <?php if ($user): ?>
        <hr>
        <div class="text-start small">
          <div class="fw-semibold text-muted mb-1">Login</div>
          <div><?= e($user['username']) ?></div>
          <div><?= getRoleBadge($user['role']) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-9">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom">
        <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#details">Details</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#leaves">Leave Requests</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payroll">Payroll</a></li>
          <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#attendance">Attendance</a></li>
        </ul>
      </div>
      <div class="card-body tab-content p-3">
        <div class="tab-pane fade show active" id="details">
          <div class="row g-3">
            <div class="col-md-4"><label class="text-muted small">Employee ID</label><div class="fw-semibold"><?= e($staff['employee_id']) ?></div></div>
            <div class="col-md-4"><label class="text-muted small">Contract Type</label><div class="fw-semibold"><?= ucfirst($staff['contract_type']??'—') ?></div></div>
            <div class="col-md-4"><label class="text-muted small">Basic Salary</label><div class="fw-semibold"><?= formatMoney($staff['basic_salary']) ?></div></div>
            <div class="col-md-4"><label class="text-muted small">Gender</label><div><?= ucfirst($staff['gender']) ?></div></div>
            <div class="col-md-4"><label class="text-muted small">Nationality</label><div><?= e($staff['nationality']??'—') ?></div></div>
            <div class="col-md-4"><label class="text-muted small">Specialization</label><div><?= e($staff['specialization']??'—') ?></div></div>
            <div class="col-12"><label class="text-muted small">Address</label><div><?= e($staff['address']??'—') ?></div></div>
            <?php if ($staff['notes']): ?><div class="col-12"><label class="text-muted small">Notes</label><div><?= e($staff['notes']) ?></div></div><?php endif; ?>
          </div>
        </div>

        <div class="tab-pane fade" id="leaves">
          <div class="table-responsive">
            <table class="table table-sm table-hover small">
              <thead class="table-light"><tr><th>Type</th><th>Start</th><th>End</th><th>Days</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (empty($leaves)): ?>
                <tr><td colspan="5" class="text-center py-3 text-muted">No leave requests</td></tr>
                <?php else: foreach ($leaves as $l): ?>
                <tr><td><?= ucfirst($l['leave_type']) ?></td><td><?= formatDate($l['start_date']) ?></td><td><?= formatDate($l['end_date']) ?></td><td><?= $l['days'] ?></td><td><?= getStatusBadge($l['status']) ?></td></tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="payroll">
          <div class="table-responsive">
            <table class="table table-sm table-hover small">
              <thead class="table-light"><tr><th>Month/Year</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net Salary</th><th>Status</th></tr></thead>
              <tbody>
                <?php if (empty($payroll)): ?>
                <tr><td colspan="6" class="text-center py-3 text-muted">No payroll records</td></tr>
                <?php else: foreach ($payroll as $p): ?>
                <tr>
                  <td><?= date('M Y', mktime(0,0,0,$p['month'],1,$p['year'])) ?></td>
                  <td><?= formatMoney($p['basic_salary']) ?></td>
                  <td class="text-success"><?= formatMoney($p['allowances']) ?></td>
                  <td class="text-danger"><?= formatMoney($p['deductions']+$p['income_tax']+$p['pension']) ?></td>
                  <td class="fw-bold"><?= formatMoney($p['net_salary']) ?></td>
                  <td><?= getStatusBadge($p['status']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="tab-pane fade" id="attendance">
          <?php if ($attendance): ?>
          <div class="row g-3 mb-3">
            <div class="col-4 text-center"><div class="fw-bold fs-5 text-success"><?= $attendance['present']??0 ?></div><div class="text-muted small">Present</div></div>
            <div class="col-4 text-center"><div class="fw-bold fs-5 text-danger"><?= $attendance['absent']??0 ?></div><div class="text-muted small">Absent</div></div>
            <div class="col-4 text-center"><div class="fw-bold fs-5"><?= $attendance['total']??0 ?></div><div class="text-muted small">Total</div></div>
          </div>
          <?php endif; ?>
          <a href="<?= url('staff/attendance') ?>" class="btn btn-sm btn-outline-primary">View Full Attendance</a>
        </div>
      </div>
    </div>
  </div>
</div>
