<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-bell text-primary me-2"></i>Notifications</h4></div>
  <?php if (!empty($notifications)): ?>
  <form action="<?= url('communication/notifications/read-all') ?>" method="POST">
    <?= csrfField() ?>
    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-check-double me-1"></i>Mark All Read</button>
  </form>
  <?php endif; ?>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($notifications)): ?>
    <div class="text-center py-5 text-muted"><i class="fas fa-bell-slash fa-3x mb-3"></i><br><h6>No notifications</h6></div>
    <?php else: foreach ($notifications as $n): ?>
    <div class="d-flex gap-3 px-3 py-3 border-bottom align-items-start <?= !$n['is_read']?'bg-light-blue':'' ?>">
      <div class="notif-icon text-<?= e($n['type']) ?> mt-1" style="width:28px;text-align:center;flex-shrink:0">
        <i class="fas fa-<?= e($n['icon']??'bell') ?> fa-lg"></i>
      </div>
      <div class="flex-fill">
        <div class="fw-semibold small"><?= e($n['title']) ?></div>
        <div class="text-muted small"><?= e($n['message']) ?></div>
        <div class="text-muted mt-1" style="font-size:11px"><i class="fas fa-clock me-1"></i><?= timeAgo($n['created_at']) ?></div>
      </div>
      <div class="flex-shrink-0">
        <?php if (!$n['is_read']): ?>
        <form action="<?= url('communication/notifications/read/'.$n['id']) ?>" method="POST">
          <?= csrfField() ?>
          <button class="btn btn-xs btn-outline-secondary" title="Mark read"><i class="fas fa-check"></i></button>
        </form>
        <?php else: ?>
        <span class="badge bg-secondary" style="font-size:9px">Read</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
