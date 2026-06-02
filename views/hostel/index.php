<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-bed text-primary me-2"></i>Hostel Management</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Hostel</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal"><i class="fas fa-plus me-1"></i>Allocate Room</button>
</div>

<!-- Hostel Summary -->
<div class="row g-3 mb-4">
  <?php foreach ($hostels as $h): ?>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h6 class="fw-bold"><?= e($h['name']) ?></h6>
        <div class="text-muted small mb-2"><?= ucfirst($h['type']) ?> Hostel</div>
        <div class="row g-2 text-center">
          <div class="col-4"><div class="fw-bold text-primary"><?= $h['room_count'] ?></div><div class="text-muted small">Rooms</div></div>
          <div class="col-4"><div class="fw-bold text-success"><?= $h['current_students'] ?></div><div class="text-muted small">Occupied</div></div>
          <div class="col-4"><div class="fw-bold text-secondary"><?= $h['total_capacity'] ?></div><div class="text-muted small">Capacity</div></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Current Allocations -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Current Allocations</h6></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light"><tr><th>Student</th><th>Hostel</th><th>Room</th><th>Check In</th><th>Fee</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (empty($allocations)): ?>
          <tr><td colspan="6" class="text-center py-5 text-muted">No current allocations</td></tr>
          <?php else: foreach ($allocations as $a): ?>
          <tr>
            <td class="fw-semibold"><?= e($a['first_name'].' '.$a['last_name']) ?> <small class="text-muted">(<?= e($a['student_id']) ?>)</small></td>
            <td><?= e($a['hostel_name']) ?></td>
            <td><?= e($a['room_number']) ?></td>
            <td><?= formatDate($a['check_in']) ?></td>
            <td><?= formatMoney($a['fee']) ?></td>
            <td>
              <form action="<?= url('hostel/vacate/'.$a['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Mark as vacated?')">
                <?= csrfField() ?>
                <button class="btn btn-xs btn-outline-warning">Vacate</button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Allocate Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title">Allocate Room</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('hostel/allocate') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Student <span class="text-danger">*</span></label><select name="student_id" class="form-select" required><option value="">Select Student</option><?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?> (<?= e($s['student_id']) ?>)</option><?php endforeach; ?></select></div>
          <div class="mb-3"><label class="form-label">Room <span class="text-danger">*</span></label><select name="room_id" class="form-select" required><option value="">Select Room</option><?php foreach ($rooms as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['hostel_name']) ?> — Room <?= e($r['room_number']) ?> (<?= $r['capacity']-$r['current_occupancy'] ?> available)</option><?php endforeach; ?></select></div>
          <div class="mb-3"><label class="form-label">Check In Date</label><input type="date" name="check_in" class="form-control flatpickr" value="<?= date('Y-m-d') ?>"></div>
          <div class="mb-3"><label class="form-label">Fee (ETB)</label><input type="number" name="fee" class="form-control" value="500" min="0" step="0.01"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Allocate</button></div>
      </form>
    </div>
  </div>
</div>
