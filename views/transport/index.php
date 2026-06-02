<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-bus text-primary me-2"></i>Transport Management</h4>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link active" href="#vehicles" data-bs-toggle="tab">Vehicles</a></li>
  <li class="nav-item"><a class="nav-link" href="#routes" data-bs-toggle="tab">Routes</a></li>
</ul>

<div class="tab-content">
  <!-- Vehicles -->
  <div class="tab-pane fade show active" id="vehicles">
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#vehicleModal"><i class="fas fa-plus me-1"></i>Add Vehicle</button>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light"><tr><th>Plate No</th><th>Type</th><th>Capacity</th><th>Driver</th><th>Phone</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($vehicles)): ?><tr><td colspan="6" class="text-center py-5 text-muted">No vehicles</td></tr><?php else: foreach ($vehicles as $v): ?>
              <tr><td class="fw-semibold font-monospace"><?= e($v['plate_no']) ?></td><td><?= ucfirst($v['type']) ?></td><td><?= $v['capacity'] ?></td><td><?= e($v['driver_name']??'—') ?></td><td><?= e($v['driver_phone']??'—') ?></td><td><?= getStatusBadge($v['status']) ?></td></tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Routes -->
  <div class="tab-pane fade" id="routes">
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#routeModal"><i class="fas fa-plus me-1"></i>Add Route</button>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light"><tr><th>Route Name</th><th>Vehicle</th><th>Morning</th><th>Evening</th><th>Fee/Month</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (empty($routes)): ?><tr><td colspan="6" class="text-center py-5 text-muted">No routes</td></tr><?php else: foreach ($routes as $r): ?>
              <tr><td class="fw-semibold"><?= e($r['name']) ?></td><td><?= e($r['plate_no']??'—') ?></td><td><?= $r['morning_time'] ? date('H:i',strtotime($r['morning_time'])) : '—' ?></td><td><?= $r['evening_time'] ? date('H:i',strtotime($r['evening_time'])) : '—' ?></td><td><?= formatMoney($r['monthly_fee']) ?></td><td><?= getStatusBadge($r['status']) ?></td></tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title">Add Vehicle</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('transport/vehicles') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Plate No <span class="text-danger">*</span></label><input type="text" name="plate_no" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Type</label><select name="type" class="form-select"><option value="bus">Bus</option><option value="minibus">Minibus</option><option value="van">Van</option></select></div>
            <div class="col-md-3"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="40"></div>
            <div class="col-md-6"><label class="form-label">Driver Name</label><input type="text" name="driver_name" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Driver Phone</label><input type="tel" name="driver_phone" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Model</label><input type="text" name="model" class="form-control"></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Add Route Modal -->
<div class="modal fade" id="routeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white"><h6 class="modal-title">Add Route</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('transport/routes') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Route Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Stops</label><textarea name="stops" class="form-control" rows="2" placeholder="Stop 1, Stop 2, Stop 3..."></textarea></div>
            <div class="col-md-6"><label class="form-label">Vehicle</label><select name="vehicle_id" class="form-select"><option value="">Select</option><?php foreach ($vehicles as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['plate_no']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Morning Time</label><input type="time" name="morning_time" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Evening Time</label><input type="time" name="evening_time" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Monthly Fee (ETB)</label><input type="number" name="monthly_fee" class="form-control" value="0" step="0.01"></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Save</button></div>
      </form>
    </div>
  </div>
</div>
