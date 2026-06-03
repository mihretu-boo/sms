<div class="text-center mb-4">
  <div class="mx-auto mb-3" style="width:70px;height:70px;background:#FFEBEE;border-radius:50%;display:flex;align-items:center;justify-content:center">
    <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
  </div>
  <h5 class="fw-bold mb-1 text-danger">Link Expired or Invalid</h5>
  <p class="text-muted small">
    This password reset link is no longer valid. It may have already been used or has expired.
  </p>
</div>

<div class="alert alert-light border small mb-4">
  <p class="fw-semibold mb-2">Possible reasons:</p>
  <ul class="mb-0 text-muted ps-3">
    <li>The link expired (valid for <?= getSetting('reset_token_expiry','30') ?> minutes only)</li>
    <li>The link was already used to reset the password</li>
    <li>The link was copied incorrectly</li>
    <li>A newer reset link was requested, invalidating this one</li>
  </ul>
</div>

<a href="<?= url('forgot-password') ?>" class="btn btn-primary w-100 mb-2">
  <i class="fas fa-redo me-2"></i>Request a New Reset Link
</a>
<a href="<?= url('login') ?>" class="btn btn-outline-secondary w-100">
  <i class="fas fa-arrow-left me-2"></i>Back to Login
</a>
