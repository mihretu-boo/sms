<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-check text-primary me-2"></i>Admissions</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li><li class="breadcrumb-item active">Admissions</li></ol></nav>
  </div>
  <a href="<?= url('students/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-user-plus me-1"></i>New Admission</a>
</div>

<!-- Year Filter -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-3">
        <select name="year" class="form-select" onchange="this.form.submit()">
          <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
          <option value="<?= $y ?>" <?= selected($year,$y) ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Admitted in <?= $year ?></h6>
    <span class="badge bg-primary"><?= count($students) ?> students</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="admissionsTable">
        <thead class="table-light">
          <tr><th>#</th><th>Student</th><th>Admission No</th><th>Student ID</th><th>Class</th><th>Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted">No admissions for <?= $year ?></td></tr>
          <?php else: $n=1; foreach ($students as $s): ?>
          <tr>
            <td><?= $n++ ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= photoUrl($s['photo'],'student') ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                <div>
                  <div class="fw-semibold"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
                  <div class="text-muted" style="font-size:11px"><?= $s['gender']==='male'?'Male':'Female' ?></div>
                </div>
              </div>
            </td>
            <td class="font-monospace text-muted"><?= e($s['admission_no']) ?></td>
            <td><span class="badge bg-light text-dark"><?= e($s['student_id']) ?></span></td>
            <td><?= $s['grade'] ? 'Grade '.$s['grade'].'-'.$s['section'] : '—' ?></td>
            <td><?= formatDate($s['admission_date']) ?></td>
            <td><?= getStatusBadge($s['status']) ?></td>
            <td><a href="<?= url('students/view/'.$s['id']) ?>" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
