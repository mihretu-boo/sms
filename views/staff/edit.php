<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-edit text-warning me-2"></i>Edit Staff</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('staff') ?>">Staff</a></li><li class="breadcrumb-item"><a href="<?= url('staff/view/'.$staff['id']) ?>"><?= e($staff['first_name']) ?></a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
  </div>
  <a href="<?= url('staff/view/'.$staff['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= url('staff/edit/'.$staff['id']) ?>" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" name="first_name" class="form-control" value="<?= e($staff['first_name']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" name="last_name" class="form-control" value="<?= e($staff['last_name']) ?>" required></div>
            <div class="col-md-4"><label class="form-label">Gender</label><select name="gender" class="form-select"><option value="male" <?= selected($staff['gender'],'male') ?>>Male</option><option value="female" <?= selected($staff['gender'],'female') ?>>Female</option></select></div>
            <div class="col-md-4"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($staff['phone']) ?>"></div>
            <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($staff['email']) ?>"></div>
            <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($staff['address']) ?>"></div>
          </div>
        </div>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white py-3"><h6 class="mb-0"><i class="fas fa-briefcase me-2"></i>Employment</h6></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Position <span class="text-danger">*</span></label><input type="text" name="position" class="form-control" value="<?= e($staff['position']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">None</option><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>" <?= selected($staff['department_id'],$d['id']) ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control" value="<?= e($staff['qualification']) ?>"></div>
            <div class="col-md-4"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control flatpickr" value="<?= e($staff['hire_date']) ?>"></div>
            <div class="col-md-4"><label class="form-label">Basic Salary (ETB)</label><input type="number" name="basic_salary" class="form-control" value="<?= e($staff['basic_salary']) ?>" step="0.01" min="0"></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['active','inactive','terminated','on_leave'] as $s): ?><option value="<?= $s ?>" <?= selected($staff['status'],$s) ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= e($staff['notes']) ?></textarea></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white py-3"><h6 class="mb-0"><i class="fas fa-camera me-2"></i>Photo</h6></div>
        <div class="card-body text-center">
          <img src="<?= photoUrl($staff['photo'],'user') ?>" id="photoPreview" class="rounded mb-3" width="120" height="140" style="object-fit:cover">
          <input type="file" name="photo" class="form-control form-control-sm" accept="image/*" id="photoInput">
        </div>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <button type="submit" class="btn btn-warning w-100 mb-2"><i class="fas fa-save me-2"></i>Update Staff</button>
          <a href="<?= url('staff/view/'.$staff['id']) ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>
<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
  var f=e.target.files[0]; if(f){var r=new FileReader();r.onload=function(ev){document.getElementById('photoPreview').src=ev.target.result;};r.readAsDataURL(f);}
});
</script>
