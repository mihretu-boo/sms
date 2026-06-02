<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chalkboard-teacher text-primary me-2"></i>Staff</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Staff</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('reports/export?type=staff') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>Export</a>
    <?php if (Auth::hasRole(['super_admin','principal'])): ?>
    <a href="<?= url('staff/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-user-plus me-1"></i>Add Staff</a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" name="search" value="<?= e($search) ?>" placeholder="Search name, ID, position...">
        </div>
      </div>
      <div class="col-md-3">
        <select name="dept_id" class="form-select">
          <option value="">All Departments</option>
          <?php foreach ($depts as $d): ?>
          <option value="<?= $d['id'] ?>" <?= selected($deptId,$d['id']) ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="active" <?= selected($status,'active') ?>>Active</option>
          <option value="inactive" <?= selected($status,'inactive') ?>>Inactive</option>
          <option value="terminated" <?= selected($status,'terminated') ?>>Terminated</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('staff') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Staff List</h6>
    <span class="text-muted small">Showing <?= count($staff) ?> of <?= number_format($total) ?></span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>Staff Member</th><th>Employee ID</th><th>Department</th><th>Position</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($staff)): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-search fa-2x mb-2"></i><br>No staff found</td></tr>
          <?php else: $n=(($page-1)*PER_PAGE)+1; foreach ($staff as $s): ?>
          <tr>
            <td class="text-muted small"><?= $n++ ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="<?= photoUrl($s['photo'],'user') ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                <div>
                  <div class="fw-semibold small"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
                  <div class="text-muted" style="font-size:11px"><?= $s['gender']==='male'?'<i class="fas fa-mars text-primary"></i>':'<i class="fas fa-venus text-danger"></i>' ?> <?= ucfirst($s['gender']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge bg-light text-dark font-monospace"><?= e($s['employee_id']) ?></span></td>
            <td class="small"><?= e($s['dept_name'] ?? '—') ?></td>
            <td class="small"><?= e($s['position']) ?></td>
            <td class="small"><?= e($s['phone'] ?? '—') ?></td>
            <td><?= getStatusBadge($s['status']) ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <a href="<?= url('staff/view/'.$s['id']) ?>" class="btn btn-outline-primary btn-xs" title="View"><i class="fas fa-eye"></i></a>
                <?php if (Auth::hasRole(['super_admin','principal'])): ?>
                <a href="<?= url('staff/edit/'.$s['id']) ?>" class="btn btn-outline-secondary btn-xs" title="Edit"><i class="fas fa-edit"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
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
