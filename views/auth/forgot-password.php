<h5 class="text-center fw-bold mb-1">Forgot Password</h5>
<p class="text-muted text-center small mb-4">Enter your email to receive a reset link</p>

<form action="<?= url('forgot-password') ?>" method="POST">
  <?= csrfField() ?>
  <div class="mb-3">
    <label class="form-label fw-semibold">Email Address</label>
    <div class="input-group">
      <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
      <input type="email" class="form-control border-start-0 ps-0" name="email" placeholder="Enter your email" required>
    </div>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
    <i class="fas fa-paper-plane me-2"></i>Send Reset Link
  </button>
</form>

<div class="text-center mt-3">
  <a href="<?= url('login') ?>" class="text-primary small"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
</div>
