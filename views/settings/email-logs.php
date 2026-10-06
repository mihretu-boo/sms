<?php
$statusColors = ['sent'=>'success','failed'=>'danger','queued'=>'warning'];
?>
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-history text-primary me-2"></i>Email Logs</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('settings?tab=email') ?>">Settings → Email</a></li>
      <li class="breadcrumb-item active">Email Logs</li>
    </ol></nav>
  </div>
  <a href="<?= url('settings?tab=email') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="fas fa-arrow-left me-1"></i>Back to Email Settings
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-semibold">
      All Sent Emails
      <span class="badge bg-secondary ms-1"><?= number_format($total) ?></span>
    </h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>To</th>
          <th>Subject</th>
          <th>Type</th>
          <th>Sent By</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No emails logged yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $i => $log): ?>
        <tr>
          <td class="text-muted small"><?= ($page - 1) * 50 + $i + 1 ?></td>
          <td>
            <div class="small fw-semibold"><?= e($log['to_email']) ?></div>
            <?php if ($log['to_name']): ?>
            <div class="text-muted" style="font-size:11px"><?= e($log['to_name']) ?></div>
            <?php endif; ?>
          </td>
          <td class="small"><?= e(mb_strimwidth($log['subject'],0,70,'…')) ?></td>
          <td>
            <?php if ($log['template_key']): ?>
            <span class="badge bg-light text-secondary border" style="font-size:10px">
              <?= e(str_replace('_',' ',$log['template_key'])) ?>
            </span>
            <?php endif; ?>
          </td>
          <td class="small text-muted"><?= e($log['sent_by_name'] ?? '—') ?></td>
          <td>
            <?php $sc = $statusColors[$log['status']] ?? 'secondary'; ?>
            <span class="badge bg-<?= $sc ?>-subtle text-<?= $sc ?> border-0" style="font-size:11px">
              <?= $log['status'] ?>
            </span>
            <?php if ($log['status'] === 'failed' && $log['error_message']): ?>
            <i class="fas fa-info-circle text-danger ms-1 small" title="<?= e($log['error_message']) ?>"></i>
            <?php endif; ?>
          </td>
          <td class="small text-muted text-nowrap"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white border-top py-2">
    <nav>
      <ul class="pagination pagination-sm mb-0 justify-content-center">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
          <a class="page-link" href="?<?= http_build_query(['page'=>$p]) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>
