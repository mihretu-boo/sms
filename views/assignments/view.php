<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><?= e($assignment['title']) ?></h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('assignments') ?>">Assignments</a></li><li class="breadcrumb-item active"><?= e($assignment['title']) ?></li></ol></nav>
  </div>
  <a href="<?= url('assignments') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
  <!-- Assignment Details -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-primary text-white py-2"><h6 class="mb-0 small">Assignment Info</h6></div>
      <div class="card-body small">
        <div class="mb-2"><strong>Subject:</strong> <?= e($assignment['subject']) ?></div>
        <div class="mb-2"><strong>Class:</strong> Grade <?= e($assignment['grade']) ?>-<?= e($assignment['section']) ?></div>
        <div class="mb-2"><strong>Teacher:</strong> <?= e($assignment['first_name'].' '.$assignment['last_name']) ?></div>
        <div class="mb-2"><strong>Due:</strong> <span class="<?= strtotime($assignment['due_date'])<time()?'text-danger':'text-success' ?>"><?= formatDate($assignment['due_date'],'d M Y H:i') ?></span></div>
        <div class="mb-2"><strong>Max Marks:</strong> <?= e($assignment['max_marks']) ?></div>
        <?php if ($assignment['file_path']): ?><div class="mb-2"><a href="<?= uploadUrl($assignment['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-download me-1"></i>Download File</a></div><?php endif; ?>
      </div>
    </div>

    <!-- Submit (students) -->
    <?php if (Auth::role()==='student' && !$my_submission): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white py-2"><h6 class="mb-0 small">Submit Assignment</h6></div>
      <div class="card-body">
        <form action="<?= url('assignments/submit/'.$assignment['id']) ?>" method="POST" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="hidden" name="due_date" value="<?= e($assignment['due_date']) ?>">
          <div class="mb-3"><label class="form-label small">Upload File</label><input type="file" name="file" class="form-control form-control-sm"></div>
          <div class="mb-3"><label class="form-label small">Or Write Answer</label><textarea name="text_content" class="form-control form-control-sm" rows="4" placeholder="Type your answer here..."></textarea></div>
          <button type="submit" class="btn btn-success w-100 btn-sm"><i class="fas fa-upload me-1"></i>Submit</button>
        </form>
      </div>
    </div>
    <?php elseif (Auth::role()==='student' && $my_submission): ?>
    <div class="card border-0 shadow-sm border-success">
      <div class="card-body text-center py-4">
        <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
        <div class="fw-bold">Submitted</div>
        <div class="text-muted small"><?= formatDate($my_submission['submitted_at'],'d M H:i') ?></div>
        <?php if ($my_submission['marks'] !== null): ?>
        <div class="mt-2"><span class="badge bg-primary">Score: <?= e($my_submission['marks']) ?>/<?= e($assignment['max_marks']) ?></span></div>
        <?php if ($my_submission['feedback']): ?><div class="mt-2 text-muted small"><?= e($my_submission['feedback']) ?></div><?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Submissions (teachers) -->
  <div class="col-md-8">
    <?php if ($assignment['description']): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-2"><h6 class="mb-0 small fw-semibold">Description</h6></div>
      <div class="card-body small"><?= nl2br(e($assignment['description'])) ?></div>
    </div>
    <?php endif; ?>

    <?php if (Auth::hasRole(['super_admin','principal','teacher'])): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold">Submissions (<?= count($submissions) ?>)</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light"><tr><th>Student</th><th>Submitted</th><th>Status</th><th>Score</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if (empty($submissions)): ?>
              <tr><td colspan="5" class="text-center py-4 text-muted">No submissions yet</td></tr>
              <?php else: foreach ($submissions as $sub): ?>
              <tr>
                <td class="fw-semibold"><?= e($sub['first_name'].' '.$sub['last_name']) ?></td>
                <td><?= formatDate($sub['submitted_at'],'d M H:i') ?></td>
                <td><?= getStatusBadge($sub['status']) ?></td>
                <td><?= $sub['marks']!==null ? e($sub['marks']).'/'.$assignment['max_marks'] : '—' ?></td>
                <td>
                  <a href="<?= url('assignments/grade/'.$sub['id']) ?>" class="btn btn-xs btn-outline-primary">Grade</a>
                  <?php if ($sub['file_path']): ?>
                  <a href="<?= uploadUrl($sub['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary">File</a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
