<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-exchange-alt text-primary me-2"></i>Student Transfers</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li><li class="breadcrumb-item active">Transfers</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
    <i class="fas fa-plus me-1"></i>New Transfer
  </button>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="transfersTable">
        <thead class="table-light">
          <tr><th>Student</th><th>Type</th><th>From School</th><th>To School</th><th>Date</th><th>Certificate No</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (empty($transfers)): ?>
          <tr><td class="text-center py-4 text-muted">No transfers found</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <?php else: foreach ($transfers as $t): ?>
          <tr>
            <td class="fw-semibold"><?= e($t['first_name'].' '.$t['last_name']) ?><br><small class="text-muted"><?= e($t['student_id']) ?></small></td>
            <td><span class="badge bg-<?= $t['transfer_type']==='in'?'success':'warning' ?>"><?= $t['transfer_type']==='in'?'Transfer In':'Transfer Out' ?></span></td>
            <td class="small"><?= e($t['from_school']??'—') ?></td>
            <td class="small"><?= e($t['to_school']??'—') ?></td>
            <td><?= formatDate($t['transfer_date']) ?></td>
            <td class="font-monospace small"><?= e($t['certificate_no']??'—') ?></td>
            <td><?= getStatusBadge($t['status']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- New Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title">Record Transfer</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('students/transfers') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i>Use "Transfer In" for students joining from another school. "Transfer Out" for students leaving.</div>
          <div class="mb-3">
            <label class="form-label">Student <span class="text-danger">*</span></label>
            <select name="student_id" class="form-select" required>
              <option value="">Select student</option>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Transfer Type</label><select name="transfer_type" class="form-select"><option value="in">Transfer In (joining)</option><option value="out">Transfer Out (leaving)</option></select></div>
          <div class="mb-3"><label class="form-label">From School</label><input type="text" name="from_school" class="form-control"></div>
          <div class="mb-3"><label class="form-label">To School</label><input type="text" name="to_school" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Transfer Date</label><input type="date" name="transfer_date" class="form-control flatpickr" value="<?= date('Y-m-d') ?>"></div>
          <div class="mb-3"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="2"></textarea></div>
          <div class="mb-3"><label class="form-label">Certificate No</label><input type="text" name="certificate_no" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Transfer</button></div>
      </form>
    </div>
  </div>
</div>
