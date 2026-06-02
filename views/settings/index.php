<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-cog text-primary me-2"></i>System Settings</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Settings</li></ol></nav>
  </div>
</div>

<!-- Settings Nav -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link active" href="<?= url('settings') ?>">General</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/users') ?>">Users</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/roles') ?>">Roles</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/audit') ?>">Audit Logs</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/backup') ?>">Backup</a></li>
</ul>

<form action="<?= url('settings') ?>" method="POST">
  <?= csrfField() ?>

  <?php foreach ($groups as $group => $settings): ?>
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
      <h6 class="mb-0 fw-semibold text-capitalize"><i class="fas fa-<?= match($group) {'general'=>'school','academic'=>'graduation-cap','finance'=>'money-bill','email'=>'envelope','sms'=>'sms','library'=>'book',default=>'sliders-h'} ?> text-primary me-2"></i><?= ucfirst($group) ?> Settings</h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?php foreach ($settings as $s): ?>
        <div class="col-md-6">
          <label class="form-label"><?= ucwords(str_replace('_',' ',$s['setting_key'])) ?></label>
          <?php if (in_array($s['setting_key'], ['school_name','school_address','school_motto'])): ?>
          <input type="text" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" value="<?= e($s['setting_value']) ?>">
          <?php elseif (str_contains($s['setting_key'], 'pass')): ?>
          <input type="password" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" placeholder="(unchanged)">
          <?php elseif (str_contains($s['setting_key'], 'description') || str_contains($s['setting_key'], 'address')): ?>
          <textarea name="settings[<?= e($s['setting_key']) ?>]" class="form-control" rows="2"><?= e($s['setting_value']) ?></textarea>
          <?php else: ?>
          <input type="text" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" value="<?= e($s['setting_value']) ?>">
          <?php endif; ?>
          <?php if ($s['description']): ?><div class="form-text text-muted"><?= e($s['description']) ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary px-5"><i class="fas fa-save me-2"></i>Save All Settings</button>
  </div>
</form>
