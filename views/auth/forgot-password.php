<div class="text-center mb-4">
  <div class="auth-step-icon mx-auto mb-3">
    <i class="fas fa-key fa-2x text-primary"></i>
  </div>
  <h5 class="fw-bold mb-1">Forgot Your Password?</h5>
  <p class="text-muted small">Enter your registered email address and we'll send you a secure reset link.</p>
</div>

<form action="<?= url('forgot-password') ?>" method="POST" id="forgotForm" data-no-loading="1">
  <?= csrfField() ?>

  <div class="mb-4">
    <label class="form-label fw-semibold">Registered Email Address</label>
    <div class="input-group">
      <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
      <input type="email" class="form-control border-start-0 ps-0" name="email"
             placeholder="Enter your email address"
             required autofocus autocomplete="email">
    </div>
    <div class="form-text text-muted">
      <i class="fas fa-shield-alt me-1"></i>
      We'll send a secure link that expires in <?= getSetting('reset_token_expiry','30') ?> minutes.
    </div>
  </div>

  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" id="submitBtn">
    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
  </button>
</form>

<!-- How it works -->
<div class="mt-4 p-3 bg-light rounded border small">
  <p class="fw-semibold text-muted mb-2"><i class="fas fa-info-circle me-1"></i>How it works:</p>
  <ol class="mb-0 text-muted ps-3" style="line-height:1.8">
    <li>Enter your registered email address</li>
    <li>Check your inbox for the reset email</li>
    <li>Click the secure link in the email</li>
    <li>Set your new password</li>
  </ol>
</div>

<div class="text-center mt-3">
  <a href="<?= url('login') ?>" class="text-primary small">
    <i class="fas fa-arrow-left me-1"></i>Back to Login
  </a>
</div>

<style>
.auth-step-icon {
  width: 64px; height: 64px;
  background: #E3F2FD;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}
</style>

<script>
document.getElementById('forgotForm').addEventListener('submit', function() {
  var btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
});
</script>
