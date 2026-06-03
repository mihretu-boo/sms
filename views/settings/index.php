<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-cog text-primary me-2"></i>System Settings</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Settings</li></ol></nav>
  </div>
</div>

<!-- Settings Nav Tabs -->
<ul class="nav nav-tabs mb-4" id="settingsTabs">
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/users') ?>">Users</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/roles') ?>">Roles</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/audit') ?>">Audit Logs</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('settings/backup') ?>">Backup</a></li>
</ul>

<form action="<?= url('settings') ?>" method="POST" id="settingsForm">
  <?= csrfField() ?>

  <?php
  $tab = $_GET['tab'] ?? 'general';
  $tabGroups = [
    'general'  => ['icon'=>'school',        'label'=>'General',        'color'=>'primary'],
    'academic' => ['icon'=>'graduation-cap', 'label'=>'Academic',       'color'=>'success'],
    'email'    => ['icon'=>'envelope',       'label'=>'Email & SMTP',   'color'=>'info'],
    'library'  => ['icon'=>'book',           'label'=>'Library',        'color'=>'warning'],
    'finance'  => ['icon'=>'money-bill',     'label'=>'Finance',        'color'=>'danger'],
    'sms'      => ['icon'=>'sms',            'label'=>'SMS',            'color'=>'secondary'],
  ];
  ?>

  <!-- Group Pills -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <?php foreach ($tabGroups as $key => $meta): ?>
    <a href="?tab=<?= $key ?>" class="btn btn-sm btn-<?= $tab===$key ? $meta['color'] : 'outline-'.$meta['color'] ?>">
      <i class="fas fa-<?= $meta['icon'] ?> me-1"></i><?= $meta['label'] ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php foreach ($groups as $group => $settings): ?>
  <?php if ($tab !== 'all' && $group !== $tab) continue; ?>

  <div class="card border-0 shadow-sm mb-4" id="group-<?= $group ?>">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 fw-semibold text-capitalize">
        <i class="fas fa-<?= $tabGroups[$group]['icon'] ?? 'sliders-h' ?> text-<?= $tabGroups[$group]['color'] ?? 'primary' ?> me-2"></i>
        <?= ucfirst($group) ?> Settings
      </h6>
      <?php if ($group === 'email'): ?>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-info" onclick="testSmtpConnection()">
          <i class="fas fa-plug me-1"></i>Test Connection
        </button>
      </div>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?php foreach ($settings as $s):
          $isPassword = str_contains($s['setting_key'], 'pass') || str_contains($s['setting_key'], '_secret');
        ?>
        <div class="col-md-6">
          <label class="form-label">
            <?= ucwords(str_replace('_', ' ', $s['setting_key'])) ?>
            <?php if ($s['description']): ?>
            <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="<?= e($s['description']) ?>"></i>
            <?php endif; ?>
          </label>

          <?php if ($s['setting_key'] === 'smtp_encryption'): ?>
          <select name="settings[<?= e($s['setting_key']) ?>]" class="form-select">
            <option value="tls"  <?= selected($s['setting_value'],'tls')  ?>>TLS (port 587 — recommended)</option>
            <option value="ssl"  <?= selected($s['setting_value'],'ssl')  ?>>SSL (port 465)</option>
            <option value="none" <?= selected($s['setting_value'],'none') ?>>None (port 25 — not recommended)</option>
          </select>
          <?php elseif ($s['setting_key'] === 'smtp_auth'): ?>
          <select name="settings[<?= e($s['setting_key']) ?>]" class="form-select">
            <option value="1" <?= selected($s['setting_value'],'1') ?>>Yes (required for Gmail/Outlook)</option>
            <option value="0" <?= selected($s['setting_value'],'0') ?>>No</option>
          </select>
          <?php elseif ($isPassword): ?>
          <div class="input-group">
            <input type="password" name="settings[<?= e($s['setting_key']) ?>]" class="form-control" id="field_<?= e($s['setting_key']) ?>" value="<?= e($s['setting_value']) ?>" placeholder="(hidden)">
            <button type="button" class="btn btn-outline-secondary" onclick="toggleFieldVisibility('field_<?= e($s['setting_key']) ?>', this)"><i class="fas fa-eye"></i></button>
          </div>
          <?php elseif (in_array($s['setting_key'], ['school_address','smtp_host']) && strlen($s['setting_value'] ?? '') > 60): ?>
          <textarea name="settings[<?= e($s['setting_key']) ?>]" class="form-control" rows="2"><?= e($s['setting_value']) ?></textarea>
          <?php else: ?>
          <input type="text" name="settings[<?= e($s['setting_key']) ?>]" class="form-control"
                 value="<?= e($s['setting_value']) ?>"
                 placeholder="<?= e($s['description'] ?? '') ?>">
          <?php endif; ?>

          <?php if ($s['description']): ?>
          <div class="form-text text-muted small"><?= e($s['description']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($group === 'email'): ?>
      <!-- SMTP Help Box -->
      <div class="alert alert-light border mt-4 small">
        <p class="fw-semibold mb-2"><i class="fas fa-question-circle text-info me-1"></i>SMTP Configuration Guide:</p>
        <div class="row g-3">
          <div class="col-md-4">
            <strong>Gmail:</strong><br>
            Host: <code>smtp.gmail.com</code><br>
            Port: <code>587</code> (TLS)<br>
            Use <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">App Password</a> (not regular password)
          </div>
          <div class="col-md-4">
            <strong>Outlook/Hotmail:</strong><br>
            Host: <code>smtp-mail.outlook.com</code><br>
            Port: <code>587</code> (TLS)<br>
            Use your Outlook email + password
          </div>
          <div class="col-md-4">
            <strong>Institutional SMTP:</strong><br>
            Contact your IT department for:<br>
            SMTP host, port, credentials
          </div>
        </div>
      </div>

      <!-- Test Email Form -->
      <div class="mt-4 p-3 bg-light rounded border">
        <h6 class="fw-semibold mb-3"><i class="fas fa-paper-plane text-primary me-2"></i>Send Test Email</h6>
        <form action="<?= url('settings/send-test-email') ?>" method="POST" class="row g-2 align-items-end">
          <?= csrfField() ?>
          <div class="col-md-5">
            <label class="form-label small fw-semibold">Test Email Address</label>
            <input type="email" name="test_email" class="form-control form-control-sm"
                   placeholder="recipient@example.com" required
                   value="<?= e(Auth::user()['email'] ?? '') ?>">
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-info text-white">
              <i class="fas fa-paper-plane me-1"></i>Send Test Email
            </button>
          </div>
        </form>
      </div>

      <!-- Connection Test Result -->
      <div id="smtpTestResult" class="mt-3" style="display:none">
        <div class="alert" id="smtpTestAlert">
          <div id="smtpTestMessage"></div>
          <details class="mt-2">
            <summary class="small text-muted">Connection log</summary>
            <pre id="smtpTestLog" class="mt-2 small bg-dark text-success p-3 rounded" style="max-height:200px;overflow-y:auto;font-size:11px"></pre>
          </details>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <?php endforeach; ?>

  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary px-5">
      <i class="fas fa-save me-2"></i>Save All Settings
    </button>
  </div>
</form>

<script>
function toggleFieldVisibility(id, btn) {
  var input = document.getElementById(id);
  var icon  = btn.querySelector('i');
  if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
  else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}

function testSmtpConnection() {
  var resultDiv = document.getElementById('smtpTestResult');
  var alertDiv  = document.getElementById('smtpTestAlert');
  var msgDiv    = document.getElementById('smtpTestMessage');
  var logPre    = document.getElementById('smtpTestLog');

  resultDiv.style.display = '';
  alertDiv.className = 'alert alert-info';
  msgDiv.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Testing SMTP connection...';
  logPre.textContent = '';

  fetch(BASE_URL + '/settings/smtp-test', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': CSRF_TOKEN
    },
    body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN)
  })
  .then(r => r.json())
  .then(data => {
    alertDiv.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
    msgDiv.innerHTML   = (data.success ? '✅ ' : '❌ ') + data.message;
    if (data.log && data.log.length) logPre.textContent = data.log.join('\n');
  })
  .catch(err => {
    alertDiv.className = 'alert alert-danger';
    msgDiv.textContent = 'Request failed: ' + err.message;
  });
}
</script>
