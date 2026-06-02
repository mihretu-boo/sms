<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-database text-primary me-2"></i>Backup & Restore</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('settings') ?>">Settings</a></li><li class="breadcrumb-item active">Backup</li></ol></nav>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('settings') ?>">General</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/users') ?>">Users</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/roles') ?>">Roles</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/audit') ?>">Audit Logs</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('settings/backup') ?>">Backup</a></li>
</ul>

<div class="row g-4">
  <div class="col-md-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-save me-2"></i>Create Backup</h6></div>
      <div class="card-body">
        <p class="text-muted small">Create a complete SQL backup of the database. Backup files are stored in the <code>backups/</code> folder.</p>
        <form action="<?= url('settings/backup/create') ?>" method="POST">
          <?= csrfField() ?>
          <button type="submit" class="btn btn-primary w-100"><i class="fas fa-download me-2"></i>Create Database Backup</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold">Available Backups</h6>
        <span class="badge bg-primary"><?= count($backups) ?> files</span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($backups)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-folder-open fa-2x mb-2"></i><br>No backups found</div>
        <?php else: ?>
        <div class="list-group list-group-flush">
          <?php foreach ($backups as $b): ?>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <div class="fw-semibold small"><?= e($b['name']) ?></div>
              <div class="text-muted" style="font-size:11px"><?= number_format($b['size']/1024, 0) ?> KB — <?= date('d M Y H:i', $b['date']) ?></div>
            </div>
            <a href="<?= url('backups/'.urlencode($b['name'])) ?>" class="btn btn-xs btn-outline-primary"><i class="fas fa-download"></i></a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
