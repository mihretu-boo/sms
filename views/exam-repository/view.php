<?php
$role        = Auth::role();
$canApprove  = Auth::hasRole(['super_admin','principal','vice_principal','dept_head']);
$canEdit     = $exam['uploaded_by'] == Auth::id() || Auth::hasRole(['super_admin','principal']);
$isOwner     = $exam['uploaded_by'] == Auth::id();
$canDownload = in_array($exam['status'], ['approved']) || !Auth::hasRole(['student','parent'])
               || ($exam['is_public'] && $exam['status']==='approved');

$statusColors = ['draft'=>'secondary','submitted'=>'primary','under_review'=>'info','approved'=>'success','rejected'=>'danger','archived'=>'dark'];
$statusColor  = $statusColors[$exam['status']] ?? 'secondary';
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= e($exam['title']) ?></h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('exam-repository') ?>">Repository</a></li>
      <li class="breadcrumb-item active"><?= e(truncate($exam['title'],40)) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($canDownload && $exam['status']==='approved'): ?>
    <a href="<?= url('exam-repository/download/'.$exam['id']) ?>" class="btn btn-sm btn-success">
      <i class="fas fa-download me-1"></i>Download
    </a>
    <?php endif; ?>
    <?php if ($canEdit && in_array($exam['status'],['draft','rejected'])): ?>
    <a href="<?= url('exam-repository/edit/'.$exam['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
    <?php endif; ?>
    <?php if ($isOwner && in_array($exam['status'],['draft','rejected'])): ?>
    <form action="<?= url('exam-repository/submit/'.$exam['id']) ?>" method="POST" class="d-inline">
      <?= csrfField() ?>
      <button class="btn btn-sm btn-primary" onclick="return confirm('Submit for review?')"><i class="fas fa-paper-plane me-1"></i>Submit for Review</button>
    </form>
    <?php endif; ?>
    <a href="<?= url('exam-repository') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
  </div>
</div>

<div class="row g-4">
  <!-- Main info -->
  <div class="col-md-8">

    <!-- File Card -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="d-flex align-items-start gap-4">
          <div class="text-center p-3 bg-light rounded-3" style="min-width:80px">
            <i class="fas <?= ExamRepositoryController::fileIcon($exam['file_mime'] ?? '') ?> fa-3x mb-1"></i>
            <div class="small text-muted"><?= strtoupper(pathinfo($exam['file_original_name'], PATHINFO_EXTENSION)) ?></div>
          </div>
          <div class="flex-fill">
            <h5 class="fw-bold mb-2"><?= e($exam['title']) ?></h5>
            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="badge bg-<?= $statusColor ?> fs-6"><?= ucfirst(str_replace('_',' ',$exam['status'])) ?></span>
              <span class="badge bg-light text-dark border"><?= ucfirst(str_replace('_',' ',$exam['exam_type'])) ?></span>
              <span class="badge bg-light text-dark border"><?= ucfirst($exam['category_type']) ?></span>
              <span class="badge bg-light text-dark border"><?= ucfirst($exam['difficulty']) ?></span>
              <?php if ($exam['is_public']): ?><span class="badge bg-success-light text-success border"><i class="fas fa-globe me-1"></i>Public</span><?php endif; ?>
              <?php if ($exam['watermark']): ?><span class="badge bg-warning-light text-warning border"><i class="fas fa-stamp me-1"></i>Watermarked</span><?php endif; ?>
            </div>
            <div class="row g-2 small text-muted">
              <div class="col-md-4"><i class="fas fa-graduation-cap me-1"></i><strong>Grade:</strong> <?= $exam['grade']==='all'?'All Grades':'Grade '.$exam['grade'] ?></div>
              <div class="col-md-4"><i class="fas fa-book me-1"></i><strong>Subject:</strong> <?= e($exam['subject_name'] ?? '—') ?></div>
              <div class="col-md-4"><i class="fas fa-building me-1"></i><strong>Dept:</strong> <?= e($exam['dept_name'] ?? '—') ?></div>
              <div class="col-md-4"><i class="fas fa-calendar me-1"></i><strong>Year:</strong> <?= e($exam['year_name'] ?? '—') ?></div>
              <div class="col-md-4"><i class="fas fa-hdd me-1"></i><strong>Size:</strong> <?= ExamRepositoryController::formatFileSize((int)$exam['file_size']) ?></div>
              <div class="col-md-4"><i class="fas fa-code-branch me-1"></i><strong>Version:</strong> v<?= $exam['version'] ?></div>
              <div class="col-md-4"><i class="fas fa-download me-1"></i><strong>Downloads:</strong> <?= $exam['download_count'] ?></div>
              <div class="col-md-4"><i class="fas fa-user me-1"></i><strong>By:</strong> <?= e($exam['uploader']) ?></div>
              <div class="col-md-4"><i class="fas fa-clock me-1"></i><strong>Uploaded:</strong> <?= formatDate($exam['created_at'], 'd M Y') ?></div>
            </div>
          </div>
        </div>

        <?php if ($exam['description']): ?>
        <hr><h6 class="fw-semibold">Description</h6><p class="text-muted mb-0 small"><?= nl2br(e($exam['description'])) ?></p>
        <?php endif; ?>

        <?php if ($exam['instructions']): ?>
        <hr><h6 class="fw-semibold"><i class="fas fa-list-ul me-1"></i>Instructions for Students</h6>
        <div class="alert alert-light border py-2 mb-0 small"><?= nl2br(e($exam['instructions'])) ?></div>
        <?php endif; ?>

        <?php if ($exam['tags']): ?>
        <hr>
        <div class="d-flex flex-wrap gap-1">
          <?php foreach (array_filter(explode(',', $exam['tags'])) as $tag): ?>
          <span class="badge bg-light text-dark border"><?= e(trim($tag)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($exam['status']==='rejected' && $exam['rejection_reason']): ?>
        <hr>
        <div class="alert alert-danger py-2 mb-0 small">
          <i class="fas fa-times-circle me-1"></i><strong>Rejected:</strong> <?= e($exam['rejection_reason']) ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Download bar -->
      <?php if ($exam['status']==='approved' || !Auth::hasRole(['student'])): ?>
      <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center py-2">
        <div class="small text-muted">
          <i class="fas fa-file me-1"></i><?= e($exam['file_original_name']) ?>
        </div>
        <a href="<?= url('exam-repository/download/'.$exam['id']) ?>" class="btn btn-sm btn-success">
          <i class="fas fa-download me-1"></i>Download File
        </a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Approval Review Panel -->
    <?php if ($canApprove && in_array($exam['status'], ['submitted','under_review'])): ?>
    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-gavel text-warning me-2"></i>Review This Examination</h6>
      </div>
      <div class="card-body">
        <form action="<?= url('exam-repository/approve/'.$exam['id']) ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Comments / Feedback</label>
            <textarea name="comments" class="form-control" rows="3" placeholder="Provide review comments, suggestions, or reason for rejection..."></textarea>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" name="action" value="approve" class="btn btn-success px-4">
              <i class="fas fa-check me-2"></i><?= Auth::hasRole(['dept_head']) ? 'Forward for Final Approval' : 'Approve & Publish' ?>
            </button>
            <button type="submit" name="action" value="reject" class="btn btn-danger px-4" onclick="return confirm('Reject this exam?')">
              <i class="fas fa-times me-2"></i>Reject
            </button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <!-- Version History -->
    <?php if (!empty($versions)): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-code-branch text-info me-2"></i>Version History</h6>
        <span class="badge bg-info"><?= count($versions) ?> version(s)</span>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>Version</th><th>File</th><th>Size</th><th>Notes</th><th>Uploaded By</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($versions as $v): ?>
            <tr>
              <td><span class="badge bg-primary">v<?= $v['version'] ?></span></td>
              <td><?= e($v['file_original_name']) ?></td>
              <td><?= ExamRepositoryController::formatFileSize((int)$v['file_size']) ?></td>
              <td class="text-muted"><?= e($v['change_notes'] ?? '—') ?></td>
              <td><?= e($v['username'] ?? '—') ?></td>
              <td><?= formatDate($v['created_at'], 'd M Y') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Download History (admin) -->
    <?php if (!empty($downloads)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Download History</h6></div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0 small">
          <thead class="table-light"><tr><th>User</th><th>Date/Time</th><th>IP</th></tr></thead>
          <tbody>
            <?php foreach ($downloads as $d): ?>
            <tr>
              <td><?= e($d['username']) ?></td>
              <td><?= formatDate($d['downloaded_at'],'d M Y H:i') ?></td>
              <td class="font-monospace text-muted"><?= e($d['ip_address']??'—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar: Approval Timeline -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold"><i class="fas fa-project-diagram text-primary me-2"></i>Approval Timeline</h6></div>
      <div class="card-body">
        <?php if (empty($approvals)): ?>
        <div class="text-muted small text-center py-2">No approval actions yet</div>
        <?php else: foreach ($approvals as $a):
          $aColor = match($a['action']) {'approved'=>'success','rejected'=>'danger','submitted'=>'primary','revision_requested'=>'warning',default=>'secondary'};
          $aIcon  = match($a['action']) {'approved'=>'check-circle','rejected'=>'times-circle','submitted'=>'paper-plane','revision_requested'=>'edit',default=>'circle'};
        ?>
        <div class="d-flex gap-3 mb-3">
          <div class="text-<?= $aColor ?> mt-1" style="flex-shrink:0"><i class="fas fa-<?= $aIcon ?> fa-lg"></i></div>
          <div class="flex-fill">
            <div class="fw-semibold small"><?= ucfirst($a['action']) ?></div>
            <div class="text-muted" style="font-size:11px">
              by <strong><?= e($a['username']) ?></strong> (<?= ucfirst(str_replace('_',' ',$a['reviewer_role'])) ?>)
              <br><?= timeAgo($a['created_at']) ?>
            </div>
            <?php if ($a['comments']): ?>
            <div class="mt-1 p-2 bg-light rounded small"><?= e($a['comments']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Actions for admin -->
    <?php if (Auth::hasRole(['super_admin','principal'])): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Admin Actions</h6></div>
      <div class="card-body d-flex flex-column gap-2">
        <?php if ($exam['status'] !== 'archived'): ?>
        <form action="<?= url('exam-repository/archive/'.$exam['id']) ?>" method="POST" onsubmit="return confirm('Archive this exam?')">
          <?= csrfField() ?>
          <button class="btn btn-sm btn-outline-secondary w-100"><i class="fas fa-archive me-1"></i>Archive</button>
        </form>
        <?php endif; ?>
        <?php if (Auth::isAdmin()): ?>
        <form action="<?= url('exam-repository/delete/'.$exam['id']) ?>" method="POST" onsubmit="return confirm('PERMANENTLY delete? This cannot be undone.')">
          <?= csrfField() ?>
          <button class="btn btn-sm btn-outline-danger w-100"><i class="fas fa-trash me-1"></i>Delete Permanently</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
