<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-tasks text-primary me-2"></i>Assignments</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Assignments</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('materials') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-folder-open me-1"></i>Materials</a>
    <?php if (Auth::hasRole(['super_admin','principal','teacher'])): ?>
    <a href="<?= url('assignments/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>New Assignment</a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <?php if (empty($assignments)): ?>
  <div class="col-12">
    <div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-tasks fa-3x mb-3"></i><br><h6>No assignments found</h6></div></div>
  </div>
  <?php else: foreach ($assignments as $asgn): ?>
  <?php
    $role = Auth::role();
    $isOverdue = strtotime($asgn['due_date']) < time() && $asgn['status'] === 'active';
    $submitted = $role === 'student' && !empty($asgn['submission_id']);
    $graded    = $submitted && $asgn['sub_status'] === 'graded';
  ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100 border-start border-4 <?= $isOverdue ? 'border-danger' : ($submitted ? 'border-success' : 'border-primary') ?>">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="badge bg-<?= $isOverdue?'danger':($submitted?'success':'primary') ?> me-1"><?= $isOverdue?'Overdue':($graded?'Graded':($submitted?'Submitted':'Active')) ?></span>
            <span class="badge bg-light text-dark"><?= e($asgn['subject']??$asgn['subject_name']??'') ?></span>
          </div>
          <small class="text-muted"><?= formatDate($asgn['due_date'], 'd M Y H:i') ?></small>
        </div>
        <h6 class="fw-bold mb-1"><?= e($asgn['title']) ?></h6>
        <?php if (!empty($asgn['description'])): ?>
        <p class="text-muted small mb-2"><?= truncate(e($asgn['description']), 100) ?></p>
        <?php endif; ?>
        <?php if ($role==='teacher'): ?>
        <div class="text-muted small">Grade <?= e($asgn['grade']??'') ?>-<?= e($asgn['section']??'') ?> | <?= $asgn['submission_count']??0 ?> submissions</div>
        <?php elseif ($role==='student' && $graded): ?>
        <div class="text-success small fw-semibold"><i class="fas fa-check me-1"></i>Score: <?= e($asgn['marks']??'—') ?>/<?= e($asgn['max_marks']) ?></div>
        <?php else: ?>
        <div class="text-muted small">Max marks: <?= e($asgn['max_marks']) ?> | By: <?= e(($asgn['first_name']??'').' '.($asgn['last_name']??'')) ?></div>
        <?php endif; ?>
      </div>
      <div class="card-footer bg-white border-top-0">
        <a href="<?= url('assignments/view/'.$asgn['id']) ?>" class="btn btn-sm btn-outline-primary">
          <?= $role==='student' && !$submitted ? '<i class="fas fa-upload me-1"></i>Submit' : '<i class="fas fa-eye me-1"></i>View' ?>
        </a>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>
