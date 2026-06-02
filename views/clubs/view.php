<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-users-cog text-primary me-2"></i><?= e($club['name']) ?></h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('clubs') ?>">Clubs</a></li><li class="breadcrumb-item active"><?= e($club['name']) ?></li></ol></nav>
  </div>
  <a href="<?= url('clubs') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
  <!-- Club info -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm mb-4 text-center">
      <div class="card-body py-4">
        <div class="stat-icon bg-primary-light text-primary rounded-3 mx-auto mb-3" style="width:60px;height:60px;font-size:1.5rem">
          <i class="fas fa-users"></i>
        </div>
        <h5 class="fw-bold"><?= e($club['name']) ?></h5>
        <span class="badge bg-primary mb-2"><?= e($club['code']??'') ?></span>
        <?php if ($club['description']): ?><p class="text-muted small"><?= e($club['description']) ?></p><?php endif; ?>
        <hr>
        <div class="text-start small">
          <?php if ($club['sup_first']): ?>
          <div class="mb-2"><i class="fas fa-chalkboard-teacher text-primary me-2"></i><strong>Supervisor:</strong> <?= e($club['sup_first'].' '.$club['sup_last']) ?></div>
          <?php endif; ?>
          <?php if ($club['meeting_schedule']): ?>
          <div class="mb-2"><i class="fas fa-calendar text-success me-2"></i><strong>Meetings:</strong> <?= e($club['meeting_schedule']) ?></div>
          <?php endif; ?>
          <div class="mb-2"><i class="fas fa-users text-info me-2"></i><strong>Members:</strong> <?= count($members) ?></div>
          <div><i class="fas fa-circle text-<?= $club['status']==='active'?'success':'muted' ?> me-2"></i><strong>Status:</strong> <?= ucfirst($club['status']) ?></div>
        </div>
      </div>
    </div>

    <!-- Enroll Student -->
    <?php if (Auth::hasRole(['super_admin','principal','vice_principal','teacher'])): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white py-2"><h6 class="mb-0 small"><i class="fas fa-user-plus me-1"></i>Enroll Student</h6></div>
      <div class="card-body">
        <form action="<?= url('clubs/enroll') ?>" method="POST">
          <?= csrfField() ?>
          <input type="hidden" name="club_id" value="<?= e($club['id']) ?>">
          <div class="mb-2">
            <select name="student_id" class="form-select form-select-sm" required>
              <option value="">Select Student</option>
              <?php
              $db = getDB();
              $enrolledIds = array_column($members, 'student_id');
              $stuStmt = $db->query("SELECT id, first_name, last_name, student_id FROM students WHERE status='active' ORDER BY first_name");
              foreach ($stuStmt->fetchAll() as $s):
                if (in_array($s['id'], $enrolledIds)) continue;
              ?>
              <option value="<?= $s['id'] ?>"><?= e($s['first_name'].' '.$s['last_name']) ?> (<?= e($s['student_id']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus me-1"></i>Enroll</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Members list -->
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
        <h6 class="mb-0 fw-semibold">Members (<?= count($members) ?>)</h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 small" id="membersTable">
            <thead class="table-light">
              <tr><th>#</th><th>Student</th><th>Student ID</th><th>Role</th><th>Joined</th></tr>
            </thead>
            <tbody>
              <?php if (empty($members)): ?>
              <tr><td class="text-center py-5 text-muted">No members yet</td><td></td><td></td><td></td><td></td></tr>
              <?php else: $n=1; foreach ($members as $m): ?>
              <tr>
                <td><?= $n++ ?></td>
                <td class="fw-semibold"><?= e($m['first_name'].' '.$m['last_name']) ?></td>
                <td class="text-muted font-monospace"><?= e($m['student_id']) ?></td>
                <td>
                  <span class="badge bg-<?= $m['role']==='president'?'primary':($m['role']==='vice_president'?'info':'light text-dark') ?>">
                    <?= ucfirst(str_replace('_',' ',$m['role'])) ?>
                  </span>
                </td>
                <td><?= formatDate($m['joined_date']) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
