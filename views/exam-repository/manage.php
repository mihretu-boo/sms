<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-cog text-primary me-2"></i>Manage Examinations</h4>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('exam-repository/reports') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-chart-bar me-1"></i>Reports</a>
    <a href="<?= url('exam-repository/upload') ?>" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Upload</a>
  </div>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link <?= !$status?'active':'' ?>" href="<?= url('exam-repository/manage') ?>">All</a></li>
  <?php foreach (['submitted'=>'warning','under_review'=>'info','approved'=>'success','draft'=>'secondary','rejected'=>'danger','archived'=>'dark'] as $s=>$c): ?>
  <li class="nav-item"><a class="nav-link <?= $status===$s?'active':'' ?>" href="<?= url('exam-repository/manage?status='.$s) ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></a></li>
  <?php endforeach; ?>
</ul>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <input type="hidden" name="status" value="<?= e($status) ?>">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Search title, uploader...">
      </div>
      <div class="col-md-2">
        <select name="grade" class="form-select">
          <option value="">All Grades</option>
          <?php foreach (['9','10','11','12','all'] as $g): ?>
          <option value="<?= $g ?>" <?= selected($grade,$g) ?>><?= $g==='all'?'All Grades':'Grade '.$g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('exam-repository/manage') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Examinations</h6>
    <span class="text-muted small"><?= number_format($total) ?> total</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="manageTable">
        <thead class="table-light">
          <tr><th>Title</th><th>Grade</th><th>Subject</th><th>Type</th><th>Uploaded By</th><th>Date</th><th class="text-center">Downloads</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($exams)): ?>
          <tr><td class="text-center py-5 text-muted" colspan="9">No examinations found</td></tr>
          <?php else: foreach ($exams as $e): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <i class="fas <?= ExamRepositoryController::fileIcon($e['file_mime'] ?? '') ?>"></i>
                <div>
                  <div class="fw-semibold"><?= e(truncate($e['title'],50)) ?></div>
                  <div class="text-muted" style="font-size:11px">v<?= $e['version'] ?> | <?= ucfirst($e['category_type']) ?></div>
                </div>
              </div>
            </td>
            <td><?= $e['grade']==='all'?'All':'Grade '.$e['grade'] ?></td>
            <td class="small text-muted"><?= e($e['subject_name'] ?? '—') ?></td>
            <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$e['exam_type'])) ?></span></td>
            <td><?= e($e['uploader']) ?></td>
            <td><?= formatDate($e['created_at'],'d M Y') ?></td>
            <td class="text-center"><span class="badge bg-light text-dark"><?= $e['download_count'] ?></span></td>
            <td><?= getStatusBadge($e['status']) ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <a href="<?= url('exam-repository/view/'.$e['id']) ?>" class="btn btn-outline-primary btn-xs" title="View"><i class="fas fa-eye"></i></a>
                <a href="<?= url('exam-repository/download/'.$e['id']) ?>" class="btn btn-outline-success btn-xs" title="Download"><i class="fas fa-download"></i></a>
                <?php if ($e['status'] !== 'archived'): ?>
                <form action="<?= url('exam-repository/archive/'.$e['id']) ?>" method="POST" class="d-inline">
                  <?= csrfField() ?>
                  <button class="btn btn-outline-secondary btn-xs" title="Archive" onclick="return confirm('Archive?')"><i class="fas fa-archive"></i></button>
                </form>
                <?php endif; ?>
                <?php if (Auth::isAdmin()): ?>
                <form action="<?= url('exam-repository/delete/'.$e['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete permanently?')">
                  <?= csrfField() ?>
                  <button class="btn btn-outline-danger btn-xs" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between">
    <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
    <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
  </div>
  <?php endif; ?>
</div>
