<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= e($message['subject']??'Message') ?></h4>
  </div>
  <a href="<?= url('communication/messages') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold"><?= e($message['subject']??'(No subject)') ?></div>
            <div class="text-muted small">
              From: <strong><?= e($message['sender_name']) ?></strong>
              &nbsp;→&nbsp;
              To: <strong><?= e($message['receiver_name']) ?></strong>
            </div>
          </div>
          <div class="text-muted small"><?= timeAgo($message['created_at']) ?></div>
        </div>
      </div>
      <div class="card-body">
        <div style="white-space:pre-wrap; font-size:14px"><?= e($message['content']) ?></div>
      </div>
    </div>

    <!-- Reply form -->
    <?php if ($message['sender_id'] != Auth::id()): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-2"><h6 class="mb-0 small fw-semibold">Reply</h6></div>
      <div class="card-body">
        <form action="<?= url('communication/messages/send') ?>" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="receiver_id" value="<?= e($message['sender_id']) ?>">
          <input type="hidden" name="subject" value="Re: <?= e($message['subject']??'') ?>">
          <input type="hidden" name="parent_id" value="<?= e($message['id']) ?>">
          <textarea name="content" class="form-control mb-2" rows="4" placeholder="Write your reply..." required></textarea>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane me-1"></i>Send Reply</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
