<?php
use function ExamRepositoryController as Repo;
$canUpload   = Auth::can('academics') || Auth::hasRole(['teacher','dept_head']);
$canManage   = Auth::hasRole(['super_admin','principal','vice_principal','registrar','dept_head']);
$canApprove  = Auth::hasRole(['super_admin','principal','vice_principal','dept_head']);
$role        = Auth::role();
?>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-archive text-primary me-2"></i>Examination Repository</h4>
    <p class="text-muted mb-0 small">Digital storage for all examination documents</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('exam-repository/browse') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-search me-1"></i>Browse</a>
    <?php if ($canUpload): ?>
    <a href="<?= url('exam-repository/upload') ?>" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Upload Exam</a>
    <?php endif; ?>
  </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $totalAll    = array_sum($stats);
  $approved    = $stats['approved']   ?? 0;
  $pending     = ($stats['submitted'] ?? 0) + ($stats['under_review'] ?? 0);
  $drafts      = $stats['draft']      ?? 0;
  $rejected    = $stats['rejected']   ?? 0;
  ?>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary-light text-primary rounded-3"><i class="fas fa-archive fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($totalAll) ?></div><div class="stat-label text-muted small">Total Exams</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-light text-success rounded-3"><i class="fas fa-check-circle fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($approved) ?></div><div class="stat-label text-muted small">Approved</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning-light text-warning rounded-3"><i class="fas fa-hourglass-half fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($pending) ?></div><div class="stat-label text-muted small">Pending Review</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-info-light text-info rounded-3"><i class="fas fa-download fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($total_downloads) ?></div><div class="stat-label text-muted small">Total Downloads</div></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Main: Recent Exams -->
  <div class="col-md-8">

    <!-- Pending Approvals (for reviewers) -->
    <?php if ($canApprove && !empty($pending_list ?? []) || !empty($pending ?? [])): ?>
    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-hourglass-half text-warning me-2"></i>Awaiting Your Review</h6>
        <a href="<?= url('exam-repository/manage?status=submitted') ?>" class="text-primary small">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach (($pending ?? []) as $p): ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="stat-icon bg-warning-light text-warning rounded-3" style="width:38px;height:38px;flex-shrink:0"><i class="fas fa-file-alt"></i></div>
          <div class="flex-fill">
            <div class="fw-semibold small"><?= e($p['title']) ?></div>
            <div class="text-muted" style="font-size:11px">
              <?= $p['grade'] !== 'all' ? 'Grade '.$p['grade'] : 'All Grades' ?> |
              <?= e($p['subject_name'] ?? '—') ?> |
              <span class="text-muted">by <?= e($p['uploader']) ?></span>
            </div>
          </div>
          <div class="d-flex gap-1">
            <a href="<?= url('exam-repository/view/'.$p['id']) ?>" class="btn btn-xs btn-outline-warning">Review</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- My Uploads (for teachers) -->
    <?php if (!empty($my_uploads ?? [])): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-upload text-primary me-2"></i>My Uploads</h6>
        <a href="<?= url('exam-repository/manage') ?>" class="text-primary small">Manage</a>
      </div>
      <div class="card-body p-0">
        <?php foreach ($my_uploads as $e): ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <i class="fas <?= ExamRepositoryController::fileIcon($e['file_mime'] ?? '') ?> fa-lg" style="width:24px;text-align:center;flex-shrink:0"></i>
          <div class="flex-fill">
            <div class="fw-semibold small"><?= e($e['title']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= ucfirst(str_replace('_',' ',$e['exam_type'])) ?> | v<?= $e['version'] ?> | <?= timeAgo($e['created_at']) ?></div>
          </div>
          <?= getStatusBadge($e['status']) ?>
          <a href="<?= url('exam-repository/view/'.$e['id']) ?>" class="btn btn-xs btn-outline-primary ms-1">View</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Recent Exams -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-clock text-primary me-2"></i>Recent Examinations</h6>
        <a href="<?= url('exam-repository/browse') ?>" class="text-primary small">Browse All</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recent)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-folder-open fa-2x mb-2"></i><br>No exams uploaded yet</div>
        <?php else: foreach ($recent as $e): ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="text-center" style="width:36px;flex-shrink:0">
            <i class="fas <?= ExamRepositoryController::fileIcon($e['file_mime'] ?? '') ?> fa-lg"></i>
          </div>
          <div class="flex-fill">
            <div class="fw-semibold small"><?= e($e['title']) ?></div>
            <div class="text-muted" style="font-size:11px">
              <?= $e['grade'] !== 'all' ? 'Grade '.$e['grade'] : 'All Grades' ?>
              <?php if ($e['subject_name']): ?> | <?= e($e['subject_name']) ?><?php endif; ?>
              | <?= ucfirst(str_replace('_',' ',$e['exam_type'])) ?>
              | <?= e($e['uploader']) ?>
            </div>
          </div>
          <div class="text-end" style="flex-shrink:0">
            <?= getStatusBadge($e['status']) ?>
            <div class="text-muted mt-1" style="font-size:10px"><i class="fas fa-download me-1"></i><?= $e['download_count'] ?></div>
          </div>
          <a href="<?= url('exam-repository/view/'.$e['id']) ?>" class="btn btn-xs btn-outline-primary">View</a>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

  </div>

  <!-- Sidebar -->
  <div class="col-md-4">

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6></div>
      <div class="list-group list-group-flush">
        <?php if ($canUpload): ?>
        <a href="<?= url('exam-repository/upload') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
          <i class="fas fa-upload text-primary"></i><span>Upload Examination</span>
        </a>
        <?php endif; ?>
        <a href="<?= url('exam-repository/browse') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
          <i class="fas fa-search text-success"></i><span>Browse & Search</span>
        </a>
        <a href="<?= url('exam-repository/question-bank') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
          <i class="fas fa-database text-info"></i><span>Question Bank</span>
        </a>
        <?php if ($canManage): ?>
        <a href="<?= url('exam-repository/manage') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
          <i class="fas fa-cog text-warning"></i><span>Manage Repository</span>
        </a>
        <a href="<?= url('exam-repository/reports') ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
          <i class="fas fa-chart-bar text-danger"></i><span>View Reports</span>
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Status Summary -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Repository Summary</h6></div>
      <div class="card-body p-0">
        <?php
        $statusList = [
            'approved'     => ['label'=>'Approved',     'color'=>'success'],
            'under_review' => ['label'=>'Under Review', 'color'=>'info'],
            'submitted'    => ['label'=>'Submitted',    'color'=>'warning'],
            'draft'        => ['label'=>'Draft',        'color'=>'secondary'],
            'rejected'     => ['label'=>'Rejected',     'color'=>'danger'],
            'archived'     => ['label'=>'Archived',     'color'=>'dark'],
        ];
        foreach ($statusList as $key => $meta): $cnt = $stats[$key] ?? 0; if ($cnt == 0) continue; ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
          <span class="small"><span class="badge bg-<?= $meta['color'] ?> me-2"><?= $meta['label'] ?></span></span>
          <div class="d-flex align-items-center gap-2">
            <div class="progress" style="width:80px;height:6px"><div class="progress-bar bg-<?= $meta['color'] ?>" style="width:<?= $totalAll > 0 ? round($cnt/$totalAll*100) : 0 ?>%"></div></div>
            <span class="fw-bold small"><?= $cnt ?></span>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between px-3 py-2">
          <span class="small text-muted">Question Bank</span>
          <span class="fw-bold small"><?= number_format($total_questions) ?> questions</span>
        </div>
      </div>
    </div>

    <!-- Supported File Types -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Supported File Types</h6></div>
      <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
          <?php foreach (['PDF','DOC','DOCX','XLS','XLSX','PPT','PPTX','ZIP'] as $ft): ?>
          <span class="badge bg-light text-dark border"><?= $ft ?></span>
          <?php endforeach; ?>
        </div>
        <div class="mt-2 text-muted small"><i class="fas fa-info-circle me-1"></i>Max file size: 50 MB per file</div>
      </div>
    </div>

  </div>
</div>
