<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-file-medical-alt text-primary me-2"></i>Leave Requests</h4>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#leaveModal">
    <i class="fas fa-plus me-1"></i>New Request
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="leavesTable">
        <thead class="table-light">
          <tr><th>Staff</th><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($leaves)): ?>
          <tr><td class="text-center py-4 text-muted">No leave requests</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <?php else: foreach ($leaves as $l): ?>
          <tr>
            <td class="fw-semibold"><?= e(($l['first_name']??'').' '.($l['last_name']??'')) ?></td>
            <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$l['leave_type'])) ?></span></td>
            <td><?= formatDate($l['start_date']) ?></td>
            <td><?= formatDate($l['end_date']) ?></td>
            <td><?= $l['days'] ?> day(s)</td>
            <td class="small text-muted"><?= truncate(e($l['reason']),50) ?></td>
            <td><?= getStatusBadge($l['status']) ?></td>
            <td>
              <?php if ($l['status']==='pending' && Auth::hasRole(['super_admin','principal','vice_principal'])): ?>
              <form action="<?= url('staff/leaves/approve/'.$l['id']) ?>" method="POST" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="approve">
                <button class="btn btn-xs btn-success me-1" title="Approve">✓</button>
              </form>
              <form action="<?= url('staff/leaves/approve/'.$l['id']) ?>" method="POST" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="reject">
                <button class="btn btn-xs btn-danger" title="Reject">✕</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- New Leave Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title">Apply for Leave</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('staff/leaves') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Leave Type <span class="text-danger">*</span></label><select name="leave_type" class="form-select" required><?php foreach (['annual'=>'Annual Leave','sick'=>'Sick Leave','maternity'=>'Maternity Leave','paternity'=>'Paternity Leave','emergency'=>'Emergency Leave','unpaid'=>'Unpaid Leave','other'=>'Other'] as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select></div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control flatpickr" required></div>
            <div class="col-md-6"><label class="form-label">End Date <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control flatpickr" required></div>
          </div>
          <div class="mb-3"><label class="form-label">Reason <span class="text-danger">*</span></label><textarea name="reason" class="form-control" rows="3" required></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Submit Request</button></div>
      </form>
    </div>
  </div>
</div>
