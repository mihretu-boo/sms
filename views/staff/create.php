<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Add Staff Member</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('staff') ?>">Staff</a></li><li class="breadcrumb-item active">Add</li></ol></nav>
  </div>
  <a href="<?= url('staff') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= url('staff/create') ?>" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">First Name <span class="text-danger">*</span></label><input type="text" name="first_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Last Name <span class="text-danger">*</span></label><input type="text" name="last_name" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Gender</label><select name="gender" class="form-select"><option value="male">Male</option><option value="female">Female</option></select></div>
            <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control flatpickr"></div>
            <div class="col-md-4"><label class="form-label">Nationality</label><input type="text" name="nationality" class="form-control" value="Ethiopian"></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" placeholder="+251 9..."></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
          </div>
        </div>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white py-3"><h6 class="mb-0"><i class="fas fa-briefcase me-2"></i>Employment Details</h6></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Position / Role Title <span class="text-danger">*</span></label><input type="text" name="position" class="form-control" required placeholder="e.g. Mathematics Teacher"></div>
            <div class="col-md-6"><label class="form-label">System Role <span class="text-danger">*</span></label><select name="role" class="form-select" required><option value="teacher">Teacher</option><option value="dept_head">Department Head</option><option value="registrar">Registrar</option><option value="finance_officer">Finance Officer</option><option value="vice_principal">Vice Principal</option><option value="principal">Principal</option></select></div>
            <div class="col-md-6"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">Select Department</option><?php foreach ($depts as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control" placeholder="e.g. B.Ed. Mathematics"></div>
            <div class="col-md-4"><label class="form-label">Hire Date</label><input type="date" name="hire_date" class="form-control flatpickr" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-4"><label class="form-label">Contract Type</label><select name="contract_type" class="form-select"><option value="permanent">Permanent</option><option value="contract">Contract</option><option value="temporary">Temporary</option></select></div>
            <div class="col-md-4"><label class="form-label">Basic Salary (ETB)</label><input type="number" name="basic_salary" class="form-control" min="0" step="0.01" value="0"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white py-3"><h6 class="mb-0"><i class="fas fa-camera me-2"></i>Photo</h6></div>
        <div class="card-body text-center">
          <img src="<?= asset('images/placeholders/user.png') ?>" id="photoPreview" class="rounded mb-3" width="120" height="140" style="object-fit:cover">
          <input type="file" name="photo" id="photoInput" class="form-control form-control-sm" accept="image/*">
        </div>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save me-2"></i>Save Staff</button>
          <a href="<?= url('staff') ?>" class="btn btn-outline-secondary w-100">Cancel</a>
          <div class="mt-3 p-2 bg-light rounded small text-muted"><i class="fas fa-info-circle me-1"></i>Default password: <strong>Staff@123</strong></div>
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
