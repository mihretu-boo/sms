<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-envelope text-primary me-2"></i>Messages</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Messages</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#composeModal">
    <i class="fas fa-plus me-1"></i>Compose
  </button>
</div>

<!-- Folder tabs -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link <?= $folder==='inbox'?'active':'' ?>" href="<?= url('communication/messages?folder=inbox') ?>"><i class="fas fa-inbox me-1"></i>Inbox</a></li>
  <li class="nav-item"><a class="nav-link <?= $folder==='sent'?'active':'' ?>" href="<?= url('communication/messages?folder=sent') ?>"><i class="fas fa-paper-plane me-1"></i>Sent</a></li>
</ul>

<!-- Messages list -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <?php if (empty($messages)): ?>
    <div class="text-center py-5 text-muted">
      <i class="fas fa-inbox fa-3x mb-3"></i><br>
      <h6><?= $folder === 'inbox' ? 'No messages in inbox' : 'No sent messages' ?></h6>
    </div>
    <?php else: foreach ($messages as $msg): ?>
    <a href="<?= url('communication/messages/view/'.$msg['id']) ?>" class="d-block text-decoration-none border-bottom px-3 py-2 <?= !$msg['read_at'] && $folder==='inbox' ? 'bg-light-blue' : '' ?> message-item">
      <div class="d-flex justify-content-between align-items-start">
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2">
            <?php if (!$msg['read_at'] && $folder==='inbox'): ?>
            <span class="badge bg-primary" style="font-size:9px">NEW</span>
            <?php endif; ?>
            <div class="fw-semibold small text-dark"><?= e($folder==='inbox' ? ($msg['sender_name']??'') : ($msg['receiver_name']??'')) ?></div>
          </div>
          <div class="text-muted small"><?= e($msg['subject'] ?: '(No subject)') ?></div>
          <div class="text-muted" style="font-size:11px"><?= truncate(e($msg['content']), 80) ?></div>
        </div>
        <div class="text-muted" style="font-size:11px; white-space:nowrap; margin-left:12px"><?= timeAgo($msg['created_at']) ?></div>
      </div>
    </a>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- Compose Modal -->
<div class="modal fade" id="composeModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title"><i class="fas fa-pen me-2"></i>New Message</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('communication/messages/send') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">To <span class="text-danger">*</span></label>
            <select name="receiver_id" class="form-select" required>
              <option value="">Select recipient</option>
              <?php foreach ($users as $u): ?>
              <option value="<?= $u['id'] ?>"><?= e($u['username']) ?> — <?= getRoleLabel($u['role']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-control" placeholder="Message subject...">
          </div>
          <div class="mb-3">
            <label class="form-label">Message <span class="text-danger">*</span></label>
            <textarea name="content" class="form-control" rows="5" required placeholder="Type your message here..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send Message</button>
        </div>
      </form>
    </div>
  </div>
</div>
