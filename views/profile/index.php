<!-- Page Header -->
<div class="mb-4">
  <h4 class="mb-0 fw-bold"><i class="fas fa-user-circle text-primary me-2"></i>My Profile</h4>
  <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Profile</li></ol></nav>
</div>

<div class="row g-4">
  <!-- Profile Photo & Info -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm mb-4 text-center">
      <div class="card-body py-4">
        <img src="<?= photoUrl($user['photo'],'user') ?>" class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover; border:3px solid #1565C0">
        <h6 class="fw-bold mb-1"><?= e($user['username']) ?></h6>
        <div class="mb-2"><?= getRoleBadge($user['role']) ?></div>
        <div class="text-muted small mb-1"><i class="fas fa-envelope me-1"></i><?= e($user['email']) ?></div>
        <div class="text-muted small"><i class="fas fa-phone me-1"></i><?= e($user['phone'] ?? 'Not set') ?></div>
        <hr>
        <div class="text-muted small">Last login: <?= $user['last_login'] ? timeAgo($user['last_login']) : 'Never' ?></div>
      </div>
    </div>

    <!-- Upload Photo -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-info text-white py-2"><h6 class="mb-0 small"><i class="fas fa-camera me-1"></i>Update Photo</h6></div>
      <div class="card-body">
        <form action="<?= url('profile/photo') ?>" method="POST" enctype="multipart/form-data">
          <?= csrfField() ?>
          <input type="file" name="photo" class="form-control form-control-sm mb-2" accept="image/*" required>
          <button type="submit" class="btn btn-info btn-sm w-100 text-white"><i class="fas fa-upload me-1"></i>Upload Photo</button>
        </form>
      </div>
    </div>

    <?php if ($linked): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-2"><h6 class="mb-0 small fw-semibold"><i class="fas fa-link text-primary me-1"></i>Linked Record</h6></div>
      <div class="card-body small">
        <?php if (Auth::role() === 'student'): ?>
        <div class="mb-1"><strong>Student ID:</strong> <?= e($linked['student_id']) ?></div>
        <div class="mb-1"><strong>Class:</strong> Grade <?= e($linked['grade']??'—') ?>-<?= e($linked['section']??'') ?></div>
        <?php elseif (Auth::role() === 'parent'): ?>
        <div class="mb-1"><strong>Student:</strong> <?= e($linked['student_first'].' '.$linked['student_last']) ?></div>
        <div class="mb-1"><strong>ID:</strong> <?= e($linked['stud_no']) ?></div>
        <?php else: ?>
        <div class="mb-1"><strong>Employee ID:</strong> <?= e($linked['employee_id']) ?></div>
        <div class="mb-1"><strong>Position:</strong> <?= e($linked['position']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Edit Profile & Change Password -->
  <div class="col-md-8">
    <!-- Edit Profile -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profile</h6></div>
      <div class="card-body">
        <form action="<?= url('profile') ?>" method="POST">
          <?= csrfField() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
              <div class="form-text">Username cannot be changed</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone Number</label>
              <input type="tel" name="phone" class="form-control" value="<?= e($user['phone']) ?>" placeholder="+251 9...">
            </div>
            <div class="col-md-6">
              <label class="form-label">Language</label>
              <select name="lang" class="form-select">
                <option value="en" <?= selected($user['lang'],'en') ?>>English</option>
                <option value="om" <?= selected($user['lang'],'om') ?>>Afaan Oromoo</option>
                <option value="am" <?= selected($user['lang'],'am') ?>>አማርኛ</option>
              </select>
            </div>
          </div>
          <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Profile</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Change Password -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark py-3"><h6 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h6></div>
      <div class="card-body">
        <form action="<?= url('profile/password') ?>" method="POST">
          <?= csrfField() ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Current Password <span class="text-danger">*</span></label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">New Password <span class="text-danger">*</span></label>
              <input type="password" name="new_password" class="form-control" required minlength="8">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
              <input type="password" name="confirm_password" class="form-control" required minlength="8">
            </div>
          </div>
          <div class="mt-3 text-end">
            <button type="submit" class="btn btn-warning"><i class="fas fa-shield-alt me-1"></i>Change Password</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
