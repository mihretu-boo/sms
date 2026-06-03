<div class="text-center mb-4">
  <div class="mx-auto mb-3 success-bounce" style="width:70px;height:70px;background:#E8F5E9;border-radius:50%;display:flex;align-items:center;justify-content:center">
    <i class="fas fa-check-circle fa-2x text-success"></i>
  </div>
  <h5 class="fw-bold mb-1 text-success">Password Updated!</h5>
  <p class="text-muted small">
    Your password has been changed successfully, <strong><?= e($username) ?></strong>.
  </p>
</div>

<div class="p-3 bg-success bg-opacity-10 border border-success rounded mb-4 small text-center">
  <i class="fas fa-envelope me-1 text-success"></i>
  A confirmation email has been sent to your registered email address.
</div>

<a href="<?= url('login') ?>" class="btn btn-primary w-100 py-2 fw-semibold">
  <i class="fas fa-sign-in-alt me-2"></i>Login with New Password
</a>

<div class="text-center mt-3 text-muted small">
  <i class="fas fa-shield-alt me-1"></i>
  For security, all other active sessions have been invalidated.
</div>

<style>
@keyframes bounceIn {
  0%   { transform: scale(0.3); opacity: 0; }
  50%  { transform: scale(1.05); opacity: 0.9; }
  80%  { transform: scale(0.95); }
  100% { transform: scale(1); opacity: 1; }
}
.success-bounce { animation: bounceIn 0.6s ease forwards; }
</style>

<script>
// Auto-redirect to login after 8 seconds
var countdown = 8;
var timer = setInterval(function() {
  countdown--;
  var btn = document.querySelector('.btn-primary');
  if (btn) btn.textContent = 'Login with New Password (' + countdown + 's)';
  if (countdown <= 0) {
    clearInterval(timer);
    window.location.href = '<?= url('login') ?>';
  }
}, 1000);
</script>
