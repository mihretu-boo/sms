<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-users-cog text-primary me-2"></i>User Management</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('settings') ?>">Settings</a></li><li class="breadcrumb-item active">Users</li></ol></nav>
  </div>
  <?php if (Auth::isAdmin()): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
    <i class="fas fa-user-plus me-1"></i>Add User
  </button>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search username or email..." value="<?= e($search) ?>">
      </div>
      <div class="col-md-3">
        <select name="role" class="form-select">
          <option value="">All Roles</option>
          <?php foreach (['super_admin','principal','vice_principal','registrar','teacher','dept_head','student','parent','finance_officer'] as $r): ?>
          <option value="<?= $r ?>" <?= selected($role, $r) ?>><?= getRoleLabel($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('settings/users') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="usersTable">
        <thead class="table-light">
          <tr><th>#</th><th>User</th><th>Role</th><th>Status</th><th>Last Login</th><th>Activity</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php $n=1; foreach ($users as $u): ?>
          <tr>
            <td class="text-muted"><?= $n++ ?></td>
            <td>
              <div class="fw-semibold"><?= e($u['username']) ?></div>
              <div class="text-muted"><?= e($u['email']) ?></div>
            </td>
            <td><?= getRoleBadge($u['role']) ?></td>
            <td><?= getStatusBadge($u['status']) ?></td>
            <td class="text-muted"><?= $u['last_login'] ? timeAgo($u['last_login']) : 'Never' ?></td>
            <td><span class="badge bg-light text-dark"><?= $u['log_count'] ?> actions</span></td>
            <td>
              <?php if (Auth::isAdmin() && $u['id'] != Auth::id()): ?>
              <div class="d-flex gap-1">
                <button class="btn btn-xs btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                <form action="<?= url('settings/users/toggle/'.$u['id']) ?>" method="POST" class="d-inline">
                  <?= csrfField() ?>
                  <button class="btn btn-xs btn-outline-<?= $u['status']==='active' ? 'warning' : 'success' ?>" title="<?= $u['status']==='active' ? 'Suspend' : 'Activate' ?>">
                    <i class="fas fa-<?= $u['status']==='active' ? 'ban' : 'check' ?>"></i>
                  </button>
                </form>
                <form action="<?= url('settings/users/reset-password/'.$u['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Reset password to Admin@123?')">
                  <?= csrfField() ?>
                  <button class="btn btn-xs btn-outline-secondary" title="Reset Password"><i class="fas fa-key"></i></button>
                </form>
              </div>
              <?php elseif ($u['id'] == Auth::id()): ?>
              <span class="text-muted small">Current User</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<?php if (Auth::isAdmin()): ?>
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add User</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="<?= url('settings/users/create') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Leave blank = Admin@123">
          </div>
          <div class="mb-3">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select" required>
              <?php foreach (['super_admin','principal','vice_principal','registrar','teacher','dept_head','student','parent','finance_officer'] as $r): ?>
              <option value="<?= $r ?>"><?= getRoleLabel($r) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h6 class="modal-title"><i class="fas fa-edit me-2"></i>Edit User</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editUserForm" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" id="editUsername" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="editEmail" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="password" class="form-control" placeholder="Leave blank to keep current"></div>
          <div class="mb-3"><label class="form-label">Role</label>
            <select name="role" id="editRole" class="form-select">
              <?php foreach (['super_admin','principal','vice_principal','registrar','teacher','dept_head','student','parent','finance_officer'] as $r): ?>
              <option value="<?= $r ?>"><?= getRoleLabel($r) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3"><label class="form-label">Status</label>
            <select name="status" id="editStatus" class="form-select">
              <option value="active">Active</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i>Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function editUser(user) {
  document.getElementById('editUsername').value = user.username;
  document.getElementById('editEmail').value    = user.email;
  document.getElementById('editRole').value     = user.role;
  document.getElementById('editStatus').value   = user.status;
  document.getElementById('editUserForm').action = BASE_URL + '/settings/users/edit/' + user.id;
  new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
</script>
