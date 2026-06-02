<h5 class="text-center fw-bold mb-1">Welcome Back</h5>
<p class="text-muted text-center small mb-4">Sign in to your account</p>

<form action="<?= url('login') ?>" method="POST" id="loginForm">
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
      <a href="<?= url('forgot-password') ?>" class="text-primary small">Forgot password?</a>
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

  <div class="mb-4 d-flex justify-content-between align-items-center">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberMe">
      <label class="form-check-label small" for="rememberMe">Remember me</label>
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
    <i class="fas fa-sign-in-alt me-2"></i>Sign In
  </button>
</form>

<div class="mt-4 p-3 bg-light rounded border">
  <p class="text-muted small mb-2 fw-semibold"><i class="fas fa-info-circle me-1"></i>Demo Accounts</p>
  <div class="row g-1 text-center" style="font-size:11px">
    <div class="col-6"><span class="badge bg-danger w-100">admin / password</span></div>
    <div class="col-6"><span class="badge bg-primary w-100">principal / password</span></div>
    <div class="col-6"><span class="badge bg-success w-100">teacher1 / password</span></div>
    <div class="col-6"><span class="badge bg-info w-100">student1 / password</span></div>
    <div class="col-6"><span class="badge bg-warning text-dark w-100">parent1 / password</span></div>
    <div class="col-6"><span class="badge bg-secondary w-100">finance / password</span></div>
  </div>
</div>

<!-- Language selector -->
<div class="text-center mt-3">
  <small class="text-muted me-2">Language:</small>
  <a href="?lang=en" class="text-primary small me-2">English</a>
  <a href="?lang=om" class="text-muted small me-2">Afaan Oromoo</a>
  <a href="?lang=am" class="text-muted small">አማርኛ</a>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
  var input = document.getElementById('passwordInput');
  var icon  = document.getElementById('toggleIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.replace('fa-eye', 'fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.replace('fa-eye-slash', 'fa-eye');
  }
});
</script>
