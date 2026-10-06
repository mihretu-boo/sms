<h5 class="text-center fw-bold mb-1">Welcome Back</h5>
<p class="text-muted text-center small mb-4">Sign in to your account</p>

<form action="<?= url('login') ?>" method="POST" id="loginForm" data-no-loading="1">
  <?= csrfField() ?>

  <div class="mb-3">
    <label class="form-label fw-semibold">Username or Email</label>
    <div class="input-group">
      <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
      <input type="text" class="form-control border-start-0 ps-0" name="credential"
             value="<?= e(old('credential')) ?>" placeholder="Enter username or email"
             required autofocus autocomplete="username">
    </div>
  </div>

  <div class="mb-3">
    <div class="d-flex justify-content-between">
      <label class="form-label fw-semibold">Password</label>
      <a href="<?= url('forgot-password') ?>" class="text-primary small">
        <i class="fas fa-key me-1"></i>Forgot password?
      </a>
    </div>
    <div class="input-group">
      <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
      <input type="password" class="form-control border-start-0 border-end-0 ps-0" name="password"
             id="passwordInput" placeholder="Enter password" required autocomplete="current-password">
      <button class="input-group-text bg-light border-start-0" type="button" id="togglePassword" tabindex="-1">
        <i class="fas fa-eye text-muted" id="toggleIcon"></i>
      </button>
    </div>
  </div>

  <div class="mb-4">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberMe">
      <label class="form-check-label small" for="rememberMe">Remember me for 30 days</label>
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
    <i class="fas fa-sign-in-alt me-2"></i>Sign In
  </button>
</form>


<div class="mt-4 pt-2">
  <div class="demo-box p-3 rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="small fw-bold text-dark">
        <i class="fas fa-bolt text-warning me-1"></i>Demo Accounts
      </span>
      <span class="badge bg-white text-muted border shadow-xs py-1 px-2" style="font-size:10px; font-weight:500;">
        password: <code class="text-primary fw-bold">password</code>
      </span>
    </div>
    <div class="row g-1 text-center">
      <div class="col-4">
        <button type="button" class="btn btn-sm w-100 demo-btn demo-btn-admin" data-user="admin" title="Click to fill">
          <i class="fas fa-user-shield me-1"></i>admin
        </button>
      </div>
      <div class="col-4">
        <button type="button" class="btn btn-sm w-100 demo-btn demo-btn-principal" data-user="principal" title="Click to fill">
          <i class="fas fa-user-tie me-1"></i>principal
        </button>
      </div>
      <div class="col-4">
        <button type="button" class="btn btn-sm w-100 demo-btn demo-btn-teacher" data-user="teacher1" title="Click to fill">
          <i class="fas fa-chalkboard-teacher me-1"></i>teacher
        </button>
      </div>
      <div class="col-4">
        <button type="button" class="btn btn-sm w-100 demo-btn demo-btn-student" data-user="student1" title="Click to fill">
          <i class="fas fa-user-graduate me-1"></i>student
        </button>
      </div>
      <div class="col-4">
        <button type="button" class="btn btn-sm w-100 demo-btn demo-btn-parent" data-user="parent1" title="Click to fill">
          <i class="fas fa-user-friends me-1"></i>parent
        </button>
      </div>
      <div class="col-4">
        <button type="button" class="btn btn-sm w-100 demo-btn demo-btn-finance" data-user="finance" title="Click to fill">
          <i class="fas fa-coins me-1"></i>finance
        </button>
      </div>
    </div>
    <div class="text-center mt-2">
      <small class="text-muted" style="font-size:11px;">
        <i class="fas fa-hand-pointer me-1 text-primary"></i>Click any role to auto-fill
      </small>
    </div>
  </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
  var input = document.getElementById('passwordInput');
  var icon  = document.getElementById('toggleIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye','fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash','fa-eye');
  }
});

// Demo accounts auto-fill
document.querySelectorAll('.demo-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var user = this.getAttribute('data-user');
    var credentialInput = document.querySelector('input[name="credential"]');
    var passwordInput = document.getElementById('passwordInput');
    credentialInput.value = user;
    passwordInput.value = 'password';

    // Visual feedback
    this.classList.add('demo-btn-active');
    setTimeout(() => this.classList.remove('demo-btn-active'), 300);

    // Focus submit button
    document.querySelector('button[type="submit"]').focus();
  });
});
</script>
