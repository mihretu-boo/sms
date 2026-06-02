<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-edit text-warning me-2"></i>Edit Student</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li>
      <li class="breadcrumb-item"><a href="<?= url('students/view/'.$student['id']) ?>"><?= e($student['first_name']) ?></a></li>
      <li class="breadcrumb-item active">Edit</li>
    </ol></nav>
  </div>
  <a href="<?= url('students/view/'.$student['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= url('students/edit/'.$student['id']) ?>" method="POST" enctype="multipart/form-data">
  <?= csrfField() ?>
  <div class="row g-4">
    <div class="col-md-8">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" name="first_name" class="form-control" value="<?= e($student['first_name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" name="last_name" class="form-control" value="<?= e($student['last_name']) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select name="gender" class="form-select">
                <option value="male" <?= selected($student['gender'],'male') ?>>Male</option>
                <option value="female" <?= selected($student['gender'],'female') ?>>Female</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date of Birth</label>
              <input type="date" name="dob" class="form-control flatpickr" value="<?= e($student['dob']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Blood Type</label>
              <select name="blood_type" class="form-select">
                <option value="">Unknown</option>
                <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bt): ?>
                <option value="<?= $bt ?>" <?= selected($student['blood_type'],$bt) ?>><?= $bt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" class="form-control" value="<?= e($student['phone']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= e($student['email']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input type="text" name="address" class="form-control" value="<?= e($student['address']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= e($student['city']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Medical Info</label>
              <input type="text" name="medical_info" class="form-control" value="<?= e($student['medical_info']) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <?php foreach (['active','inactive','graduated','transferred','expelled'] as $s): ?>
                <option value="<?= $s ?>" <?= selected($student['status'],$s) ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white py-3"><h6 class="mb-0"><i class="fas fa-camera me-2"></i>Photo</h6></div>
        <div class="card-body text-center">
          <img src="<?= photoUrl($student['photo'],'student') ?>" id="photoPreview" class="rounded mb-3" width="130" height="160" style="object-fit:cover">
          <input type="file" name="photo" class="form-control form-control-sm" accept="image/*" id="photoInput">
        </div>
      </div>
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-3"><h6 class="mb-0"><i class="fas fa-school me-2"></i>Class</h6></div>
        <div class="card-body">
          <select name="class_id" class="form-select">
            <option value="">No Class</option>
            <?php foreach ($classes as $cls): ?>
            <option value="<?= $cls['id'] ?>" <?= selected($student['class_id'],$cls['id']) ?>>Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <button type="submit" class="btn btn-warning w-100 mb-2"><i class="fas fa-save me-2"></i>Update Student</button>
          <a href="<?= url('students/view/'.$student['id']) ?>" class="btn btn-outline-secondary w-100">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>
<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
  var file = e.target.files[0];
  if (file) { var r=new FileReader(); r.onload=function(ev){document.getElementById('photoPreview').src=ev.target.result;}; r.readAsDataURL(file); }
});
</script>
