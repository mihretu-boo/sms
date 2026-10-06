<?php $title = 'Contact Messages'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="fas fa-envelope me-2 text-info"></i>Contact Messages</h4>
    <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> CMS Dashboard
    </a>
  </div>

  <?php if ($m = \Flash::get('success')): ?>
  <div class="alert alert-success alert-dismissible"><?= e($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-bold">All Messages</span>
      <span class="badge bg-secondary"><?= $total ?> total</span>
    </div>
    <div class="card-body p-0">
      <?php if (empty($messages)): ?>
      <div class="text-center text-muted py-5">
        <i class="fas fa-inbox" style="font-size:3rem;margin-bottom:12px;display:block"></i>
        No contact messages yet.
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Lang</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr>
              <td class="fw-semibold"><?= e($msg['name']) ?></td>
              <td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td>
              <td><?= e($msg['phone'] ?? '-') ?></td>
              <td><?= e(truncate($msg['subject'], 30)) ?></td>
              <td>
                <span title="<?= e($msg['message']) ?>"><?= e(truncate($msg['message'], 60)) ?></span>
              </td>
              <td><span class="badge bg-light text-dark border"><?= strtoupper($msg['lang']) ?></span></td>
              <td><?= formatDate($msg['created_at'], 'd M Y H:i') ?></td>
              <td>
                <form method="POST" action="<?= url('website/contact-messages/delete/' . $msg['id']) ?>"
                      onsubmit="return confirm('Delete this message?')">
                  <?= csrfField() ?>
                  <button class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white text-center">
      <?= paginationLinks(['current_page'=>$page,'total_pages'=>$totalPages,'total'=>$total,'per_page'=>20,'offset'=>0,'data'=>[]]) ?>
    </div>
    <?php endif; ?>
  </div>
</div>
