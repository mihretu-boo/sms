<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-users-cog text-primary me-2"></i>Student Clubs</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Clubs</li></ol></nav>
  </div>
  <?php if (Auth::hasRole(['super_admin','principal','vice_principal'])): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#clubModal"><i class="fas fa-plus me-1"></i>Add Club</button>
  <?php endif; ?>
</div>

<div class="row g-3">
  <?php foreach ($clubs as $club): ?>
  <div class="col-md-4 col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center py-4">
        <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-3" style="width:50px;height:50px">
          <i class="fas fa-users fa-lg"></i>
        </div>
        <h6 class="fw-bold"><?= e($club['name']) ?></h6>
        <div class="badge bg-light text-dark mb-2"><?= e($club['code']??'') ?></div>
        <div class="text-muted small mb-2"><?= truncate(e($club['description']??''), 60) ?></div>
        <?php if ($club['first_name']): ?><div class="text-muted small"><i class="fas fa-chalkboard-teacher me-1"></i><?= e($club['first_name'].' '.$club['last_name']) ?></div><?php endif; ?>
        <div class="mt-2"><span class="badge bg-success"><?= $club['member_count'] ?> members</span></div>
      </div>
      <div class="card-footer bg-white text-center border-top-0">
        <a href="<?= url('clubs/view/'.$club['id']) ?>" class="btn btn-xs btn-outline-primary">View Club</a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add Club Modal -->
<?php if (Auth::hasRole(['super_admin','principal','vice_principal'])): ?>
<div class="modal fade" id="clubModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title">Add Club</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('clubs/create') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Club Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" maxlength="10"></div>
          <div class="mb-3"><label class="form-label">Supervisor</label><select name="supervisor_id" class="form-select"><option value="">Select Supervisor</option><?php foreach ($supervisors as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?></option><?php endforeach; ?></select></div>
          <div class="mb-3"><label class="form-label">Meeting Schedule</label><input type="text" name="meeting_schedule" class="form-control" placeholder="e.g. Every Friday 2:00 PM"></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Create Club</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
