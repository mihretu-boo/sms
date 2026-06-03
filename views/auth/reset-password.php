<div class="text-center mb-4">
  <div class="auth-step-icon mx-auto mb-3">
    <i class="fas fa-lock fa-2x text-primary"></i>
  </div>
  <h5 class="fw-bold mb-1">Set New Password</h5>
  <p class="text-muted small">
    Creating password for <strong><?= e($email ?? '') ?></strong>
  </p>
</div>

<form action="<?= url('reset-password') ?>" method="POST" id="resetForm" data-no-loading="1">
  <?= csrfField() ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">

  <!-- New Password -->
  <div class="mb-3">
    <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
      <input type="password" class="form-control border-start-0 border-end-0 ps-0"
             name="password" id="newPassword"
             placeholder="Enter new password"
             required autocomplete="new-password"
             minlength="8">
      <button class="input-group-text bg-light border-start-0" type="button" id="toggleNew" tabindex="-1">
        <i class="fas fa-eye text-muted"></i>
      </button>
    </div>

    <!-- Password Strength Meter -->
    <div class="mt-2" id="strengthWrap" style="display:none">
      <div class="progress mb-1" style="height:5px">
        <div class="progress-bar" id="strengthBar" role="progressbar"></div>
      </div>
      <small id="strengthLabel" class="text-muted"></small>
    </div>
  </div>

  <!-- Confirm Password -->
  <div class="mb-3">
    <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text bg-light border-end-0"><i class="fas fa-check-circle text-muted" id="confirmIcon"></i></span>
      <input type="password" class="form-control border-start-0 border-end-0 ps-0"
             name="password_confirm" id="confirmPassword"
             placeholder="Repeat new password"
             required autocomplete="new-password">
      <button class="input-group-text bg-light border-start-0" type="button" id="toggleConfirm" tabindex="-1">
        <i class="fas fa-eye text-muted"></i>
      </button>
    </div>
    <small id="matchMsg" class="mt-1 d-block"></small>
  </div>

  <!-- Requirements Checklist -->
  <div class="p-3 bg-light rounded border mb-4">
    <p class="small fw-semibold text-muted mb-2">Password requirements:</p>
    <ul class="list-unstyled mb-0 small" id="reqList">
      <li id="req-len"><i class="fas fa-circle text-muted me-2" style="font-size:8px"></i>At least 8 characters</li>
      <li id="req-upper"><i class="fas fa-circle text-muted me-2" style="font-size:8px"></i>At least 1 uppercase letter (A-Z)</li>
      <li id="req-lower"><i class="fas fa-circle text-muted me-2" style="font-size:8px"></i>At least 1 lowercase letter (a-z)</li>
      <li id="req-num"><i class="fas fa-circle text-muted me-2" style="font-size:8px"></i>At least 1 number (0-9)</li>
      <li id="req-special"><i class="fas fa-circle text-muted me-2" style="font-size:8px"></i>At least 1 special character (@#!%...)</li>
    </ul>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="submitBtn" disabled>
    <i class="fas fa-shield-alt me-2"></i>Update Password
  </button>
</form>

<div class="text-center mt-3">
  <a href="<?= url('forgot-password') ?>" class="text-muted small">
    <i class="fas fa-redo me-1"></i>Request a new reset link
  </a>
</div>

<style>
.auth-step-icon { width:64px;height:64px;background:#E3F2FD;border-radius:50%;display:flex;align-items:center;justify-content:center; }
#reqList li { padding:2px 0; transition:color 0.2s; }
#reqList li.ok { color:#2E7D32; }
#reqList li.ok i { color:#2E7D32; }
</style>

<script>
var pwd     = document.getElementById('newPassword');
var confirm = document.getElementById('confirmPassword');
var submit  = document.getElementById('submitBtn');

function checkReq(id, pass) {
  var el = document.getElementById(id);
  if (pass) { el.classList.add('ok'); el.querySelector('i').className='fas fa-check-circle text-success me-2'; }
  else       { el.classList.remove('ok'); el.querySelector('i').className='fas fa-circle text-muted me-2'; el.style.fontSize='8px'; }
}

function calcStrength(p) {
  var score = 0;
  if (p.length >= 8)  score++;
  if (p.length >= 12) score++;
  if (/[A-Z]/.test(p)) score++;
  if (/[a-z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[\W_]/.test(p)) score++;
  return score;
}

pwd.addEventListener('input', function() {
  var p = this.value;
  document.getElementById('strengthWrap').style.display = p ? '' : 'none';

  // Check each requirement
  checkReq('req-len',     p.length >= 8);
  checkReq('req-upper',   /[A-Z]/.test(p));
  checkReq('req-lower',   /[a-z]/.test(p));
  checkReq('req-num',     /[0-9]/.test(p));
  checkReq('req-special', /[\W_]/.test(p));

  // Strength bar
  var score  = calcStrength(p);
  var bar    = document.getElementById('strengthBar');
  var label  = document.getElementById('strengthLabel');
  var pct    = Math.round((score / 6) * 100);

  bar.style.width = pct + '%';
  if (score <= 2)      { bar.className='progress-bar bg-danger';  label.textContent='Weak'; label.className='text-danger small'; }
  else if (score <= 4) { bar.className='progress-bar bg-warning'; label.textContent='Fair'; label.className='text-warning small'; }
  else                 { bar.className='progress-bar bg-success'; label.textContent='Strong'; label.className='text-success small'; }

  updateSubmit();
});

confirm.addEventListener('input', function() {
  var msg = document.getElementById('matchMsg');
  var ico = document.getElementById('confirmIcon');
  if (this.value && this.value === pwd.value) {
    msg.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Passwords match</span>';
    ico.className = 'fas fa-check-circle text-success';
  } else if (this.value) {
    msg.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Passwords do not match</span>';
    ico.className = 'fas fa-times-circle text-danger';
  } else {
    msg.innerHTML = '';
    ico.className = 'fas fa-check-circle text-muted';
  }
  updateSubmit();
});

function allRequirementsMet(p) {
  return p.length >= 8 && /[A-Z]/.test(p) && /[a-z]/.test(p) && /[0-9]/.test(p) && /[\W_]/.test(p);
}

function updateSubmit() {
  var p = pwd.value, c = confirm.value;
  submit.disabled = !(allRequirementsMet(p) && p === c);
}

// Toggle visibility
[['toggleNew','newPassword'],['toggleConfirm','confirmPassword']].forEach(function(pair) {
  document.getElementById(pair[0]).addEventListener('click', function() {
    var inp = document.getElementById(pair[1]);
    var ico = this.querySelector('i');
    if (inp.type === 'password') { inp.type='text'; ico.classList.replace('fa-eye','fa-eye-slash'); }
    else { inp.type='password'; ico.classList.replace('fa-eye-slash','fa-eye'); }
  });
});

// Form submit loading state
document.getElementById('resetForm').addEventListener('submit', function() {
  submit.disabled = true;
  submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating password...';
});
</script>
