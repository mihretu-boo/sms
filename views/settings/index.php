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

<?php
$tab = $_GET['tab'] ?? 'general';
$tabGroups = [
  'general'  => ['icon'=>'school',        'label'=>'General',      'color'=>'primary'],
  'academic' => ['icon'=>'graduation-cap','label'=>'Academic',     'color'=>'success'],
  'email'    => ['icon'=>'envelope',      'label'=>'Email & SMTP', 'color'=>'info'],
  'library'  => ['icon'=>'book',          'label'=>'Library',      'color'=>'warning'],
  'finance'  => ['icon'=>'money-bill',    'label'=>'Finance',      'color'=>'danger'],
  'sms'      => ['icon'=>'sms',           'label'=>'SMS',          'color'=>'secondary'],
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

<!-- ===== EMAIL TAB: FULL PROVIDER SWITCHER UI ===== -->
<?php if ($tab === 'email'): ?>
<?php
  require_once ROOT . '/app/Core/MailerProviders.php';
  $currentProvider = getSetting('smtp_provider','google_workspace');
  $smtpUser  = getSetting('smtp_user','');
  $smtpPass  = getSetting('smtp_pass','');
  $smtpHost  = getSetting('smtp_host','');
  $preset    = MailerProviders::get($currentProvider);
?>

<div class="row g-4">
  <!-- LEFT: Provider Selection -->
  <div class="col-md-5 col-lg-4">

    <!-- Status Banner -->
    <?php if (!empty($smtpUser) && empty($smtpPass)): ?>
    <div class="alert alert-warning border-start border-4 border-warning mb-4 py-3 small">
      <i class="fas fa-exclamation-triangle text-warning me-2"></i>
      <strong>Password required!</strong><br>
      Email sending is configured for <strong><?= e($smtpUser) ?></strong> but the password is missing.
      Emails are saved to <code>storage/email-logs/</code> until the password is set.
    </div>
    <?php elseif (!empty($smtpUser) && !empty($smtpPass)): ?>
    <div class="alert alert-success border-start border-4 border-success mb-4 py-2 small">
      <i class="fas fa-check-circle text-success me-2"></i>
      <strong>SMTP Configured</strong> — <?= e($smtpUser) ?>
    </div>
    <?php endif; ?>

    <!-- Provider Cards -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-exchange-alt text-primary me-2"></i>Email Provider</h6>
        <small class="text-muted">Select your email service</small>
      </div>
      <div class="card-body p-2">
        <?php foreach (MailerProviders::PRESETS as $key => $p): ?>
        <div class="provider-card p-3 rounded mb-2 cursor-pointer border
                    <?= $currentProvider===$key ? 'border-primary bg-primary-light' : 'border-transparent bg-light' ?>"
             onclick="selectProvider('<?= $key ?>')"
             data-provider="<?= $key ?>"
             id="card_<?= $key ?>">
          <div class="d-flex align-items-center gap-3">
            <!-- Provider Icon -->
            <div class="provider-icon rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:40px;height:40px;background:<?= $p['color'] ?>20;border:2px solid <?= $p['color'] ?>40">
              <?php if ($key === 'google_workspace'): ?>
                <span style="font-size:18px">🏫</span>
              <?php elseif ($key === 'gmail'): ?>
                <span style="font-size:18px">📧</span>
              <?php elseif ($key === 'outlook'): ?>
                <span style="font-size:18px">🔷</span>
              <?php elseif ($key === 'yahoo'): ?>
                <span style="font-size:18px">💜</span>
              <?php else: ?>
                <i class="fas fa-server" style="color:<?= $p['color'] ?>"></i>
              <?php endif; ?>
            </div>
            <!-- Info -->
            <div class="flex-fill">
              <div class="fw-semibold small d-flex align-items-center gap-2">
                <?= e($p['label']) ?>
                <?php if ($currentProvider===$key): ?>
                <span class="badge bg-primary" style="font-size:9px">Active</span>
                <?php endif; ?>
              </div>
              <div class="text-muted" style="font-size:11px"><?= e($p['description']) ?></div>
            </div>
            <!-- Check -->
            <div class="provider-check text-primary <?= $currentProvider===$key ? '' : 'd-none' ?>" id="check_<?= $key ?>">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Dynamic Provider Help -->
    <div class="card border-0 shadow-sm" id="providerHelpCard">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold" id="helpTitle">
          <i class="fas fa-life-ring text-info me-2"></i>Setup Guide
        </h6>
      </div>
      <div class="card-body small" id="helpBody">
        <p class="text-muted small">Select a provider to see setup instructions.</p>
      </div>
    </div>
  </div>

  <!-- RIGHT: SMTP Config Form -->
  <div class="col-md-7 col-lg-8">
    <form action="<?= url('settings') ?>" method="POST" id="emailSettingsForm">
      <?= csrfField() ?>

      <!-- Quick Setup Card (Essential fields only) -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
          <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Setup</h6>
          <span class="badge bg-white text-primary" id="activeProviderBadge"><?= e($preset['label']) ?></span>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="settings[smtp_user]"
                       class="form-control border-start-0"
                       value="<?= e($smtpUser) ?>"
                       placeholder="your.email@domain.com" required>
              </div>
              <div class="form-text text-muted small">This is the address emails will be sent FROM.</div>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold" id="passLabel">
                Password <span class="text-danger">*</span>
                <span class="badge bg-warning text-dark ms-1 d-none" id="appPassBadge">App Password Required</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-muted"></i></span>
                <input type="password" name="settings[smtp_pass]"
                       class="form-control border-start-0 border-end-0"
                       id="smtpPassInput"
                       value="<?= e($smtpPass) ?>"
                       placeholder="<?= empty($smtpPass) ? 'Enter password to enable email sending' : '(saved — leave blank to keep current)' ?>">
                <button class="input-group-text bg-light border-start-0" type="button"
                        onclick="var i=document.getElementById('smtpPassInput');i.type=i.type==='password'?'text':'password'">
                  <i class="fas fa-eye text-muted"></i>
                </button>
              </div>
              <div id="appPassHint" class="form-text d-none">
                <i class="fas fa-info-circle text-warning me-1"></i>
                Regular password won't work. You need an App Password.
                <a href="#" id="appPassLink" target="_blank" class="text-primary fw-semibold ms-1">Get App Password →</a>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">From Name</label>
              <input type="text" name="settings[smtp_from_name]"
                     class="form-control"
                     value="<?= e(getSetting('smtp_from_name','Shalaka Jatan Ali Secondary School')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">From Email <small class="text-muted">(optional — defaults to Email Address)</small></label>
              <input type="email" name="settings[smtp_from_email]"
                     class="form-control"
                     value="<?= e(getSetting('smtp_from_email','')) ?>"
                     placeholder="Same as email address">
            </div>
          </div>
        </div>
      </div>

      <!-- Advanced SMTP Settings (collapsible) -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-2 d-flex justify-content-between align-items-center"
             role="button" data-bs-toggle="collapse" data-bs-target="#advancedSmtp" aria-expanded="false">
          <h6 class="mb-0 fw-semibold small text-muted">
            <i class="fas fa-sliders-h me-2"></i>Advanced SMTP Settings
          </h6>
          <i class="fas fa-chevron-down text-muted small"></i>
        </div>
        <div class="collapse" id="advancedSmtp">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label small fw-semibold">SMTP Host</label>
                <input type="text" name="settings[smtp_host]" class="form-control form-control-sm"
                       id="smtpHostInput" value="<?= e($smtpHost) ?>" placeholder="smtp.example.com">
              </div>
              <div class="col-md-3">
                <label class="form-label small fw-semibold">Port</label>
                <input type="number" name="settings[smtp_port]" class="form-control form-control-sm"
                       id="smtpPortInput" value="<?= e(getSetting('smtp_port','587')) ?>" min="1" max="65535">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Encryption</label>
                <select name="settings[smtp_encryption]" class="form-select form-select-sm" id="smtpEncInput">
                  <?php foreach (['tls'=>'TLS (port 587 — recommended)','ssl'=>'SSL (port 465)','none'=>'None (port 25 — insecure)'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= selected(getSetting('smtp_encryption','tls'),$v) ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Authentication</label>
                <select name="settings[smtp_auth]" class="form-select form-select-sm">
                  <option value="1" <?= selected(getSetting('smtp_auth','1'),'1') ?>>Yes (required)</option>
                  <option value="0" <?= selected(getSetting('smtp_auth','1'),'0') ?>>No</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Timeout (seconds)</label>
                <input type="number" name="settings[smtp_timeout]" class="form-control form-control-sm"
                       value="<?= e(getSetting('smtp_timeout','30')) ?>" min="5" max="120">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Reset Link Expiry (min)</label>
                <input type="number" name="settings[reset_token_expiry]" class="form-control form-control-sm"
                       value="<?= e(getSetting('reset_token_expiry','30')) ?>" min="5" max="1440">
              </div>
            </div>
            <div class="alert alert-light border mt-3 mb-0 py-2 small">
              <i class="fas fa-info-circle text-muted me-1"></i>
              Current auto-filled values:
              <strong id="advancedSummary">
                <?= e($smtpHost) ?>:<?= e(getSetting('smtp_port','587')) ?> (<?= e(getSetting('smtp_encryption','tls')) ?>)
              </strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Hidden: provider key -->
      <input type="hidden" name="settings[smtp_provider]" id="smtpProviderInput" value="<?= e($currentProvider) ?>">

      <!-- Action Buttons -->
      <div class="d-flex gap-2 flex-wrap mb-4">
        <button type="submit" class="btn btn-primary px-4">
          <i class="fas fa-save me-2"></i>Save Email Settings
        </button>
        <button type="button" class="btn btn-outline-info" onclick="testSmtpConnection()">
          <i class="fas fa-plug me-1"></i>Test Connection
        </button>
      </div>

      <!-- Test email form -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
          <h6 class="mb-0 fw-semibold"><i class="fas fa-paper-plane text-success me-2"></i>Send Test Email</h6>
        </div>
        <div class="card-body">
          <form action="<?= url('settings/send-test-email') ?>" method="POST" class="row g-2 align-items-end">
            <?= csrfField() ?>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Recipient Email</label>
              <input type="email" name="test_email" class="form-control" required
                     value="<?= e($smtpUser ?: getSetting('school_email','mihretu.yangu@bru.edu.et')) ?>"
                     placeholder="recipient@example.com">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-paper-plane me-1"></i>Send Test
              </button>
            </div>
            <div class="col-12">
              <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Save settings first, then send a test to confirm email delivery.
                Check your inbox AND spam/junk folder.
              </small>
            </div>
          </form>
        </div>
      </div>

      <!-- Connection Test Result -->
      <div id="smtpTestResult" class="mt-3" style="display:none">
        <div class="alert" id="smtpTestAlert">
          <div id="smtpTestMessage"></div>
          <details class="mt-2">
            <summary class="small text-muted cursor-pointer">Connection log</summary>
            <pre id="smtpTestLog" class="mt-2 small bg-dark text-success p-3 rounded"
                 style="max-height:200px;overflow-y:auto;font-size:11px"></pre>
          </details>
        </div>
      </div>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ===== OTHER TABS ===== -->
<form action="<?= url('settings') ?>" method="POST" id="settingsForm">
  <?= csrfField() ?>

  <?php foreach ($groups as $group => $settings): ?>
  <?php if ($tab !== 'all' && $group !== $tab) continue; ?>
  <?php if ($group === 'email') continue; // handled above ?>

  <div class="card border-0 shadow-sm mb-4" id="group-<?= $group ?>">
    <div class="card-header bg-white border-bottom py-3">
      <h6 class="mb-0 fw-semibold text-capitalize">
        <i class="fas fa-<?= $tabGroups[$group]['icon'] ?? 'sliders-h' ?> text-<?= $tabGroups[$group]['color'] ?? 'primary' ?> me-2"></i>
        <?= ucfirst($group) ?> Settings
      </h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?php foreach ($settings as $s):
          $isPassword = str_contains($s['setting_key'], 'pass') || str_contains($s['setting_key'], '_secret');
        ?>
        <div class="col-md-6">
          <label class="form-label small fw-semibold">
            <?= ucwords(str_replace('_', ' ', $s['setting_key'])) ?>
          </label>
          <?php if ($isPassword): ?>
          <div class="input-group">
            <input type="password" name="settings[<?= e($s['setting_key']) ?>]" class="form-control"
                   id="field_<?= e($s['setting_key']) ?>" value="<?= e($s['setting_value']) ?>">
            <button type="button" class="btn btn-outline-secondary"
                    onclick="toggleFieldVisibility('field_<?= e($s['setting_key']) ?>', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
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
    </div>
  </div>
  <?php endforeach; ?>

  <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary px-5">
      <i class="fas fa-save me-2"></i>Save Settings
    </button>
  </div>
</form>
<?php endif; ?>

<style>
.provider-card { transition: all 0.2s; cursor: pointer; }
.provider-card:hover { border-color: #1565C0 !important; background: #EFF6FF !important; }
.provider-card.border-transparent { border-color: transparent !important; }
</style>

<script>
/* ===== Provider Presets (from PHP) ===== */
const PROVIDERS = <?= MailerProviders::presetsJson() ?>;
const CSRF_TOKEN_VAL = '<?= Auth::csrf() ?>';

function selectProvider(key) {
  const preset = PROVIDERS[key];
  if (!preset) return;

  // Update card UI
  document.querySelectorAll('.provider-card').forEach(function(card) {
    const isSelected = card.dataset.provider === key;
    card.classList.toggle('border-primary', isSelected);
    card.classList.toggle('bg-primary-light', isSelected);
    card.classList.toggle('border-transparent', !isSelected);
    card.classList.toggle('bg-light', !isSelected);
    const check = document.getElementById('check_' + card.dataset.provider);
    if (check) check.classList.toggle('d-none', !isSelected);
    // Remove old "Active" badge
    const badge = card.querySelector('.badge');
    if (badge && badge.textContent.trim() === 'Active') badge.remove();
    // Add "Active" badge to selected
    if (isSelected) {
      const nameEl = card.querySelector('.fw-semibold.small');
      if (nameEl && !nameEl.querySelector('.badge')) {
        nameEl.insertAdjacentHTML('beforeend', '<span class="badge bg-primary ms-1" style="font-size:9px">Active</span>');
      }
    }
  });

  // Auto-fill SMTP fields (advanced section)
  if (preset.host) document.getElementById('smtpHostInput').value = preset.host;
  document.getElementById('smtpPortInput').value = preset.port;
  document.getElementById('smtpEncInput').value  = preset.encryption;
  document.getElementById('smtpProviderInput').value = key;
  document.getElementById('activeProviderBadge').textContent = preset.label;
  document.getElementById('advancedSummary').textContent =
    preset.host + ':' + preset.port + ' (' + preset.encryption.toUpperCase() + ')';

  // Show/hide App Password badge & hint
  const needsAppPass = preset.requires_app_password;
  document.getElementById('appPassBadge').classList.toggle('d-none', !needsAppPass);
  document.getElementById('appPassHint').classList.toggle('d-none', !needsAppPass);
  if (needsAppPass && preset.app_password_url) {
    document.getElementById('appPassLink').href = preset.app_password_url;
  }

  // Update help panel
  updateHelpPanel(key, preset);

  // Tell server to also update SMTP host/port/encryption via AJAX
  applyPresetToServer(key);
}

function applyPresetToServer(provider) {
  fetch(BASE_URL + '/settings/switch-email-provider', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': CSRF_TOKEN_VAL,
    },
    body: 'provider=' + encodeURIComponent(provider) + '&_csrf_token=' + encodeURIComponent(CSRF_TOKEN_VAL)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
    } else {
      showToast(data.message || 'Failed to apply preset.', 'danger');
    }
  })
  .catch(err => showToast('Error: ' + err.message, 'danger'));
}

function updateHelpPanel(key, preset) {
  var title  = document.getElementById('helpTitle');
  var body   = document.getElementById('helpBody');
  var icons  = {gmail:'📧', google_workspace:'🏫', outlook:'🔷', yahoo:'💜', custom:'🔧'};

  title.innerHTML = '<i class="fas fa-life-ring text-info me-2"></i>' +
    (icons[key] || '') + ' ' + preset.label + ' — Setup Guide';

  var stepsHtml = preset.steps.map((s, i) =>
    '<li class="mb-2"><span class="badge bg-primary me-2">' + (i+1) + '</span>' + s + '</li>'
  ).join('');

  var appPassHtml = preset.requires_app_password && preset.app_password_url ?
    '<div class="alert alert-warning py-2 mt-3 mb-0 small"><i class="fas fa-shield-alt me-1"></i>' +
    '<strong>App Password Required</strong><br>' +
    'Your regular password will be rejected by this provider.<br>' +
    '<a href="' + preset.app_password_url + '" target="_blank" class="btn btn-sm btn-warning mt-2">' +
    '<i class="fas fa-external-link-alt me-1"></i>Get App Password</a></div>' : '';

  var smtpHtml = preset.host ?
    '<div class="mt-3 p-2 bg-light rounded border small">' +
    '<div><strong>Host:</strong> <code>' + preset.host + '</code></div>' +
    '<div><strong>Port:</strong> <code>' + preset.port + '</code> (' + preset.encryption.toUpperCase() + ')</div>' +
    '</div>' : '';

  var noteHtml = preset.note ?
    '<div class="text-muted mt-2 small"><i class="fas fa-info-circle me-1"></i>' + preset.note + '</div>' : '';

  body.innerHTML = '<ol class="ps-3 mb-0 small">' + stepsHtml + '</ol>' + appPassHtml + smtpHtml + noteHtml;
}

function showToast(msg, type) {
  // Remove existing
  const old = document.getElementById('settingsToast');
  if (old) old.remove();

  const t = document.createElement('div');
  t.id = 'settingsToast';
  t.className = 'position-fixed bottom-0 end-0 m-3 alert alert-' + type + ' shadow d-flex align-items-center gap-2';
  t.style.cssText = 'z-index:9999;min-width:280px;max-width:380px';
  t.innerHTML = '<i class="fas fa-' + (type==='success'?'check-circle':'exclamation-circle') + '"></i><div>' + msg + '</div>';
  t.innerHTML += '<button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>';
  document.body.appendChild(t);
  setTimeout(function() { if (t.parentElement) t.remove(); }, 5000);
}

function toggleFieldVisibility(id, btn) {
  var input = document.getElementById(id);
  var icon  = btn.querySelector('i');
  if (input.type === 'password') { input.type='text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
  else { input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}

function testSmtpConnection() {
  var result = document.getElementById('smtpTestResult');
  var alert  = document.getElementById('smtpTestAlert');
  var msg    = document.getElementById('smtpTestMessage');
  var log    = document.getElementById('smtpTestLog');
  result.style.display = '';
  alert.className = 'alert alert-info';
  msg.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Testing SMTP connection...';
  log.textContent = '';

  fetch(BASE_URL + '/settings/smtp-test', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':CSRF_TOKEN_VAL},
    body: '_csrf_token=' + encodeURIComponent(CSRF_TOKEN_VAL)
  })
  .then(r => r.json())
  .then(data => {
    alert.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger');
    msg.innerHTML   = (data.success ? '✅ ' : '❌ ') + data.message;
    if (data.log && data.log.length) log.textContent = data.log.join('\n');
  })
  .catch(err => {
    alert.className = 'alert alert-danger';
    msg.textContent = 'Request failed: ' + err.message;
  });
}

// Init: show help for current provider on page load
document.addEventListener('DOMContentLoaded', function() {
  var current = '<?= e($currentProvider) ?>';
  if (PROVIDERS[current]) updateHelpPanel(current, PROVIDERS[current]);
  // Show App Password hint if current provider requires it
  if (PROVIDERS[current] && PROVIDERS[current].requires_app_password) {
    document.getElementById('appPassBadge').classList.remove('d-none');
    document.getElementById('appPassHint').classList.remove('d-none');
    if (PROVIDERS[current].app_password_url) {
      document.getElementById('appPassLink').href = PROVIDERS[current].app_password_url;
    }
  }
});
</script>
