<div class="text-center mb-4">
  <div class="email-sent-icon mx-auto mb-3">
    <i class="fas fa-envelope-open-text fa-2x text-success"></i>
  </div>
  <h5 class="fw-bold mb-1 text-success">Check Your Email</h5>
  <p class="text-muted small">
    If <strong><?= e($email) ?></strong> is registered, we've sent a password reset link to that address.
  </p>
</div>

<div class="p-3 bg-light rounded border mb-4 small">
  <p class="fw-semibold text-muted mb-2"><i class="fas fa-list-ul me-1"></i>Next steps:</p>
  <ul class="mb-0 text-muted ps-3" style="line-height:2">
    <li>Open your email inbox</li>
    <li>Look for an email from <strong><?= e(getSetting('school_name','SJASSMS')) ?></strong></li>
    <li>Click the <strong>"Reset My Password"</strong> button in the email</li>
    <li>The link expires in <strong><?= getSetting('reset_token_expiry','30') ?> minutes</strong></li>
  </ul>
</div>

<div class="alert alert-warning py-2 small mb-4">
  <i class="fas fa-exclamation-triangle me-1"></i>
  <strong>Don't see the email?</strong> Check your <strong>Spam / Junk</strong> folder.
  Allow a few minutes for delivery.
</div>

<div class="alert alert-info py-2 small mb-4">
  <i class="fas fa-shield-alt me-1"></i>
  For security, we don't confirm whether an email address is registered.
  The link is single-use and expires automatically.
</div>

<a href="<?= url('forgot-password') ?>" class="btn btn-outline-primary w-100 mb-2">
  <i class="fas fa-redo me-2"></i>Try a different email
</a>
<a href="<?= url('login') ?>" class="btn btn-outline-secondary w-100">
  <i class="fas fa-arrow-left me-2"></i>Back to Login
</a>

<style>
.email-sent-icon {
  width: 70px; height: 70px;
  background: #E8F5E9;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}
</style>
