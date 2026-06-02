<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-users text-primary me-2"></i>Students</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Students</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('reports/export?type=students') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>Export</a>
    <?php if (Auth::can('students')): ?>
    <a href="<?= url('students/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-user-plus me-1"></i>Add Student</a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" action="<?= url('students') ?>" class="row g-2 align-items-end">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" name="search" value="<?= e($search) ?>" placeholder="Search name, ID, admission no...">
        </div>
      </div>
      <div class="col-md-3">
        <select name="class_id" class="form-select">
          <option value="">All Classes</option>
          <?php foreach ($classes as $cls): ?>
          <option value="<?= $cls['id'] ?>" <?= selected($classId, $cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="active" <?= selected($status,'active') ?>>Active</option>
          <option value="inactive" <?= selected($status,'inactive') ?>>Inactive</option>
          <option value="graduated" <?= selected($status,'graduated') ?>>Graduated</option>
          <option value="transferred" <?= selected($status,'transferred') ?>>Transferred</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('students') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Stats Summary -->
<div class="row g-3 mb-4">
  <div class="col"><div class="card border-0 shadow-sm text-center py-3"><div class="fw-bold fs-5 text-primary"><?= number_format($total) ?></div><div class="text-muted small">Total Shown</div></div></div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Student List</h6>
    <span class="text-muted small">Showing <?= count($students) ?> of <?= number_format($total) ?> students</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="studentsTable">
        <thead class="table-light">
          <tr>
            <th width="40">#</th>
            <th>Student</th>
            <th>Student ID</th>
            <th>Class</th>
            <th>Gender</th>
            <th>Phone</th>
            <th>Status</th>
            <th width="100">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-search fa-2x mb-2"></i><br>No students found</td></tr>
          <?php else: ?>
          <?php $rowNum = (($page - 1) * PER_PAGE) + 1; foreach ($students as $s): ?>
          <tr>
            <td class="text-muted small"><?= $rowNum++ ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= photoUrl($s['photo'], 'student') ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                <div>
                  <div class="fw-semibold small"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                  <div class="text-muted" style="font-size:11px"><?= e($s['admission_no']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge bg-light text-dark font-monospace"><?= e($s['student_id']) ?></span></td>
            <td><?= $s['grade'] ? 'Grade ' . e($s['grade']) . '-' . e($s['section']) : '<span class="text-muted">—</span>' ?></td>
            <td>
              <i class="fas fa-<?= $s['gender']==='male' ? 'mars text-primary' : 'venus text-danger' ?>"></i>
              <?= ucfirst($s['gender']) ?>
            </td>
            <td class="small"><?= e($s['phone'] ?? '—') ?></td>
            <td><?= getStatusBadge($s['status']) ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <a href="<?= url('students/view/'.$s['id']) ?>" class="btn btn-outline-primary btn-xs" title="View"><i class="fas fa-eye"></i></a>
                <?php if (Auth::can('students')): ?>
                <a href="<?= url('students/edit/'.$s['id']) ?>" class="btn btn-outline-secondary btn-xs" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="<?= url('students/id-card/'.$s['id']) ?>" class="btn btn-outline-info btn-xs" target="_blank" title="ID Card"><i class="fas fa-id-card"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between align-items-center">
    <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
    <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
  </div>
  <?php endif; ?>
</div>
