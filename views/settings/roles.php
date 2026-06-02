<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-shield text-primary me-2"></i>Roles & Permissions</h4>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('settings') ?>">General</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/users') ?>">Users</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('settings/roles') ?>">Roles</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/audit') ?>">Audit Logs</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/backup') ?>">Backup</a></li>
</ul>

<div class="row g-3">
  <?php foreach ($permissions as $role => $perms): ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-2">
        <?= getRoleBadge($role) ?>
        <span class="fw-semibold"><?= getRoleLabel($role) ?></span>
      </div>
      <div class="card-body">
        <?php if (in_array('*', $perms)): ?>
        <span class="badge bg-danger">Full Access (All Permissions)</span>
        <?php else: ?>
        <div class="d-flex flex-wrap gap-1">
          <?php foreach ($perms as $p): ?>
          <span class="badge bg-light text-dark border"><?= e($p) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="alert alert-info mt-4">
  <i class="fas fa-info-circle me-2"></i>
  Role permissions are defined in <code>config/app.php</code>. Contact your system administrator to modify permissions.
</div>
