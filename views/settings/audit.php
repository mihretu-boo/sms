<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-clipboard-list text-primary me-2"></i>Audit Logs</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('settings') ?>">Settings</a></li><li class="breadcrumb-item active">Audit Logs</li></ol></nav>
  </div>
</div>

<!-- Settings Nav -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('settings') ?>">General</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/users') ?>">Users</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/roles') ?>">Roles</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('settings/audit') ?>">Audit Logs</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/backup') ?>">Backup</a></li>
</ul>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-3">
        <select name="module" class="form-select">
          <option value="">All Modules</option>
          <?php foreach ($modules as $m): ?><option value="<?= e($m) ?>" <?= selected($module,$m) ?>><?= ucfirst($m) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="user_id" class="form-select">
          <option value="">All Users</option>
          <?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= selected($userId,$u['id']) ?>><?= e($u['username']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('settings/audit') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Activity Log</h6>
    <span class="text-muted small"><?= number_format($total) ?> entries</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 small align-middle">
        <thead class="table-light">
          <tr><th>Time</th><th>User</th><th>Action</th><th>Module</th><th>IP Address</th></tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">No audit logs found</td></tr>
          <?php else: foreach ($logs as $log): ?>
          <tr>
            <td class="text-muted"><?= formatDate($log['created_at'], 'd M H:i') ?></td>
            <td><?= e($log['username'] ?? 'System') ?></td>
            <td><span class="badge bg-light text-dark"><?= e($log['action']) ?></span></td>
            <td><?= e($log['module']) ?></td>
            <td class="text-muted font-monospace"><?= e($log['ip_address']??'—') ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between">
    <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
    <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
  </div>
  <?php endif; ?>
</div>
