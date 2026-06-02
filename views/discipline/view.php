<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-gavel text-primary me-2"></i>Incident Detail</h4>
  </div>
  <a href="<?= url('discipline') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-<?= match($incident['severity']) {'minor'=>'info','moderate'=>'warning','major'=>'orange','critical'=>'danger',default=>'secondary'} ?> text-white py-2">
        <h6 class="mb-0 small"><?= ucfirst($incident['severity']) ?> Incident</h6>
      </div>
      <div class="card-body small">
        <div class="mb-2"><strong>Student:</strong> <?= e($incident['first_name'].' '.$incident['last_name']) ?> <span class="text-muted">(<?= e($incident['stud_no']) ?>)</span></div>
        <div class="mb-2"><strong>Class:</strong> Grade <?= e($incident['grade']??'—') ?>-<?= e($incident['section']??'') ?></div>
        <div class="mb-2"><strong>Date:</strong> <?= formatDate($incident['incident_date']) ?></div>
        <div class="mb-2"><strong>Type:</strong> <?= e($incident['incident_type']) ?></div>
        <div class="mb-2"><strong>Reported By:</strong> <?= e($incident['reporter']) ?></div>
        <div class="mb-2"><strong>Status:</strong> <?= getStatusBadge($incident['status']) ?></div>
        <div class="mb-2"><strong>Parent Notified:</strong> <?= $incident['parent_notified'] ? '<span class="text-success">Yes</span>' : '<span class="text-muted">No</span>' ?></div>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-2"><h6 class="mb-0 fw-semibold">Description</h6></div>
      <div class="card-body"><p class="mb-0"><?= nl2br(e($incident['description'])) ?></p></div>
    </div>

    <?php if ($incident['action_taken']): ?>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white border-bottom py-2"><h6 class="mb-0 fw-semibold">Action Taken</h6></div>
      <div class="card-body"><p class="mb-0"><?= nl2br(e($incident['action_taken'])) ?></p></div>
    </div>
    <?php endif; ?>

    <?php if ($incident['status']==='open' && Auth::hasRole(['super_admin','principal','vice_principal'])): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white py-2"><h6 class="mb-0 small">Resolve Incident</h6></div>
      <div class="card-body">
        <form action="<?= url('discipline/resolve/'.$incident['id']) ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3"><label class="form-label small">Resolution Details <span class="text-danger">*</span></label><textarea name="resolution" class="form-control" rows="3" required></textarea></div>
          <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Mark Resolved</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
