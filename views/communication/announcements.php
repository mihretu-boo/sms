<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-bullhorn text-primary me-2"></i>Announcements</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Announcements</li></ol></nav>
  </div>
  <?php if ($canCreate): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#annModal">
    <i class="fas fa-plus me-1"></i>New Announcement
  </button>
  <?php endif; ?>
</div>

<!-- Announcements list -->
<div class="row g-3">
  <?php if (empty($announcements)): ?>
  <div class="col-12">
    <div class="card border-0 shadow-sm text-center py-5">
      <div class="text-muted"><i class="fas fa-bullhorn fa-3x mb-3"></i><br><h6>No announcements at this time</h6></div>
    </div>
  </div>
  <?php else: foreach ($announcements as $ann):
    $badgeColor = match($ann['priority']) {'urgent'=>'danger','important'=>'warning text-dark','normal'=>'info',default=>'secondary'};
    $borderColor = match($ann['priority']) {'urgent'=>'border-danger','important'=>'border-warning','normal'=>'border-info',default=>''};
  ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm border-start border-4 <?= $borderColor ?> h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="badge bg-<?= $badgeColor ?> me-1"><?= ucfirst($ann['priority']) ?></span>
            <span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$ann['target_role'])) ?></span>
          </div>
          <?php if ($canCreate && Auth::id() == $ann['author_id']): ?>
          <form action="<?= url('communication/announcements/delete/'.$ann['id']) ?>" method="POST" onsubmit="return confirm('Delete this announcement?')">
            <?= csrfField() ?>
            <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
          </form>
          <?php endif; ?>
        </div>
        <h6 class="fw-bold mb-2"><?= e($ann['title']) ?></h6>
        <p class="text-muted small mb-2"><?= nl2br(e($ann['content'])) ?></p>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">
            <i class="fas fa-user me-1"></i><?= e($ann['author_name']) ?>
            &nbsp;&bull;&nbsp;
            <i class="fas fa-clock me-1"></i><?= timeAgo($ann['created_at']) ?>
          </small>
          <small class="text-muted"><?= formatDate($ann['start_date']) ?> – <?= formatDate($ann['end_date']) ?></small>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<!-- New Announcement Modal -->
<?php if ($canCreate): ?>
<div class="modal fade" id="annModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title"><i class="fas fa-bullhorn me-2"></i>New Announcement</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= url('communication/announcements') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required placeholder="Announcement title...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
            <textarea name="content" class="form-control" rows="5" required placeholder="Announcement content..."></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target Audience</label>
              <select name="target_role" class="form-select">
                <option value="all">Everyone</option>
                <option value="student">Students</option>
                <option value="parent">Parents</option>
                <option value="teacher">Teachers</option>
                <option value="staff">All Staff</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Priority</label>
              <select name="priority" class="form-select">
                <option value="normal">Normal</option>
                <option value="important">Important</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Valid Until</label>
              <input type="date" name="end_date" class="form-control flatpickr" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Publish</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
