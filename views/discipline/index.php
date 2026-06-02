<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-gavel text-primary me-2"></i>Discipline Management</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Discipline</li></ol></nav>
  </div>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#incidentModal">
    <i class="fas fa-plus me-1"></i>Record Incident
  </button>
</div>

<!-- Summary -->
<div class="row g-3 mb-4">
  <?php $severities = ['minor'=>'info','moderate'=>'warning','major'=>'orange','critical'=>'danger']; ?>
  <?php foreach ($severities as $sev => $color): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="fw-bold fs-4 text-<?= $color ?>"><?= $summary[$sev] ?? 0 ?></div>
      <div class="text-muted small"><?= ucfirst($sev) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" value="<?= e($search ?? '') ?>" placeholder="Search student or type...">
      </div>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="open" <?= selected($status,'open') ?>>Open</option>
          <option value="resolved" <?= selected($status,'resolved') ?>>Resolved</option>
          <option value="escalated" <?= selected($status,'escalated') ?>>Escalated</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="severity" class="form-select">
          <option value="">All Severity</option>
          <?php foreach (['minor','moderate','major','critical'] as $s): ?>
          <option value="<?= $s ?>" <?= selected($severity??'',$s) ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('discipline') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Incidents Table -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr><th>Date</th><th>Student</th><th>Class</th><th>Incident Type</th><th>Severity</th><th>Reported By</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($incidents)): ?>
          <tr><td colspan="8" class="text-center py-5 text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No incidents recorded</td></tr>
          <?php else: foreach ($incidents as $i): ?>
          <tr>
            <td><?= formatDate($i['incident_date']) ?></td>
            <td>
              <div class="fw-semibold"><?= e($i['first_name'].' '.$i['last_name']) ?></div>
              <div class="text-muted" style="font-size:11px"><?= e($i['stud_no']) ?></div>
            </td>
            <td>Grade <?= e($i['grade']??'—') ?>-<?= e($i['section']??'') ?></td>
            <td><?= e($i['incident_type']) ?></td>
            <td>
              <span class="badge bg-<?= match($i['severity']) {'minor'=>'info','moderate'=>'warning','major'=>'orange','critical'=>'danger',default=>'secondary'} ?>">
                <?= ucfirst($i['severity']) ?>
              </span>
            </td>
            <td class="text-muted"><?= e($i['reporter']) ?></td>
            <td><?= getStatusBadge($i['status']) ?></td>
            <td>
              <a href="<?= url('discipline/view/'.$i['id']) ?>" class="btn btn-xs btn-outline-primary"><i class="fas fa-eye"></i></a>
              <?php if ($i['status'] === 'open' && Auth::hasRole(['super_admin','principal','vice_principal'])): ?>
              <button class="btn btn-xs btn-outline-success" onclick="resolveIncident(<?= $i['id'] ?>)"><i class="fas fa-check"></i></button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Record Incident Modal -->
<div class="modal fade" id="incidentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white"><h6 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Record Incident</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('discipline/create') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Student <span class="text-danger">*</span></label>
              <select name="student_id" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?> (<?= e($s['student_id']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date <span class="text-danger">*</span></label>
              <input type="date" name="incident_date" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Severity <span class="text-danger">*</span></label>
              <select name="severity" class="form-select" required>
                <?php foreach (['minor','moderate','major','critical'] as $s): ?>
                <option value="<?= $s ?>"><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Incident Type <span class="text-danger">*</span></label>
              <input type="text" name="incident_type" class="form-control" placeholder="e.g. Fighting, Disruptive behavior..." required>
            </div>
            <div class="col-12">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control" rows="3" required placeholder="Describe the incident in detail..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Action Taken</label>
              <textarea name="action_taken" class="form-control" rows="2" placeholder="Describe the action taken..."></textarea>
            </div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="parent_notified" value="1" id="parentNotified">
                <label class="form-check-label" for="parentNotified">Parent has been notified</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>Record Incident</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white"><h6 class="modal-title">Resolve Incident</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form id="resolveForm" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <label class="form-label">Resolution Details</label>
          <textarea name="resolution" class="form-control" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Mark Resolved</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function resolveIncident(id) {
  document.getElementById('resolveForm').action = BASE_URL + '/discipline/resolve/' + id;
  new bootstrap.Modal(document.getElementById('resolveModal')).show();
}
</script>
