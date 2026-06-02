<h5 class="text-center fw-bold mb-1">Reset Password</h5>
<p class="text-muted text-center small mb-4">Enter and confirm your new password</p>

<form action="<?= url('reset-password') ?>" method="POST">
  <?= csrfField() ?>
  <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
  <div class="mb-3">
    <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
    <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min 8 characters">
  </div>
  <div class="mb-4">
    <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
    <input type="password" name="password_confirm" class="form-control" required minlength="8" placeholder="Repeat password">
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
    <i class="fas fa-key me-2"></i>Reset Password
  </button>
</form>
<div class="text-center mt-3">
  <a href="<?= url('login') ?>" class="text-primary small"><i class="fas fa-arrow-left me-1"></i>Back to Login</a>
</div>
