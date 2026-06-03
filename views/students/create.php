<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-user-plus text-primary me-2"></i>Add New Student</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li>
      <li class="breadcrumb-item active">Add Student</li>
    </ol></nav>
  </div>
  <a href="<?= url('students') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= url('students/create') ?>" method="POST" enctype="multipart/form-data" id="studentForm">
  <?= csrfField() ?>

  <div class="row g-4">
    <!-- Left Column -->
    <div class="col-md-8">
      <!-- Personal Information -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
          <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label required">First Name</label>
              <input type="text" name="first_name" class="form-control" value="<?= e(old('first_name')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Last Name (Father's Name)</label>
              <input type="text" name="last_name" class="form-control" value="<?= e(old('last_name')) ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label required">Gender</label>
              <select name="gender" class="form-select" required>
                <option value="male" <?= selected(old('gender','male'),'male') ?>>Male</option>
                <option value="female" <?= selected(old('gender'),'female') ?>>Female</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label required">Date of Birth</label>
              <input type="date" name="dob" class="form-control flatpickr" value="<?= e(old('dob')) ?>" required max="<?= date('Y-m-d', strtotime('-5 years')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Blood Type</label>
              <select name="blood_type" class="form-select">
                <option value="">Unknown</option>
                <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bt): ?>
                <option value="<?= $bt ?>" <?= selected(old('blood_type'), $bt) ?>><?= $bt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Nationality</label>
              <input type="text" name="nationality" class="form-control" value="<?= e(old('nationality','Ethiopian')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Religion</label>
              <select name="religion" class="form-select">
                <option value="">Select</option>
                <?php foreach (['Muslim','Christian (Orthodox)','Christian (Protestant)','Christian (Catholic)','Traditional','Other'] as $r): ?>
                <option value="<?= $r ?>" <?= selected(old('religion'), $r) ?>><?= $r ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" class="form-control" value="<?= e(old('phone')) ?>" placeholder="+251 9...">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Previous School</label>
              <input type="text" name="previous_school" class="form-control" value="<?= e(old('previous_school')) ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input type="text" name="address" class="form-control" value="<?= e(old('address')) ?>" placeholder="Kebele, Woreda, Zone...">
            </div>
            <div class="col-md-6">
              <label class="form-label">City / Town</label>
              <input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Medical Information</label>
              <input type="text" name="medical_info" class="form-control" value="<?= e(old('medical_info')) ?>" placeholder="Allergies, conditions...">
            </div>
          </div>
        </div>
      </div>

      <!-- Emergency Contact -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-warning text-dark py-3">
          <h6 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Emergency Contact</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Contact Name</label>
              <input type="text" name="emergency_contact_name" class="form-control" value="<?= e(old('emergency_contact_name')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Phone</label>
              <input type="tel" name="emergency_contact_phone" class="form-control" value="<?= e(old('emergency_contact_phone')) ?>" placeholder="+251 9...">
            </div>
          </div>
        </div>
      </div>

      <!-- Parent/Guardian Information -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-success text-white py-3">
          <h6 class="mb-0"><i class="fas fa-users me-2"></i>Parent / Guardian Information</h6>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Relation</label>
              <select name="parent_relation" class="form-select">
                <option value="father">Father</option>
                <option value="mother">Mother</option>
                <option value="guardian">Guardian</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">First Name</label>
              <input type="text" name="parent_first_name" class="form-control" value="<?= e(old('parent_first_name')) ?>">
            </div>
            <div class="col-md-5">
              <label class="form-label">Last Name</label>
              <input type="text" name="parent_last_name" class="form-control" value="<?= e(old('parent_last_name')) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Phone</label>
              <input type="tel" name="parent_phone" class="form-control" value="<?= e(old('parent_phone')) ?>" placeholder="+251 9...">
            </div>
            <div class="col-md-8">
              <label class="form-label">Email</label>
              <input type="email" name="parent_email" class="form-control" value="<?= e(old('parent_email')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="col-md-4">
      <!-- Photo Upload -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white py-3">
          <h6 class="mb-0"><i class="fas fa-camera me-2"></i>Student Photo</h6>
        </div>
        <div class="card-body text-center">
          <div class="photo-upload-wrap mb-3">
            <img src="<?= asset('images/placeholders/student.png') ?>" id="photoPreview" class="rounded" width="150" height="180" style="object-fit:cover; border: 2px dashed #ccc">
          </div>
          <input type="file" name="photo" id="photoInput" class="form-control form-control-sm" accept="image/*">
          <small class="text-muted">Max 2MB. JPEG/PNG/GIF</small>
        </div>
      </div>

      <!-- Enrollment -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white py-3">
          <h6 class="mb-0"><i class="fas fa-school me-2"></i>Enrollment</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label required">Class</label>
            <select name="class_id" class="form-select" id="classSelect" required onchange="onClassChange(this)">
              <option value="">Select Class</option>
              <?php foreach ($classes as $cls):
                $sl = match($cls['stream']) {'natural'=>' [Natural]','social'=>' [Social]',default=>''};
              ?>
              <option value="<?= $cls['id'] ?>"
                      data-grade="<?= $cls['grade'] ?>"
                      data-stream="<?= $cls['stream'] ?>"
                      <?= selected(old('class_id'), $cls['id']) ?>>
                Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?><?= $sl ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Stream selection — shown only for Grade 11/12 -->
          <div class="mb-3" id="streamDiv" style="display:none">
            <label class="form-label fw-semibold">Stream <span class="text-danger">*</span></label>
            <select name="stream" id="streamSelect" class="form-select">
              <option value="natural">🔬 Natural Science</option>
              <option value="social">🌍 Social Science</option>
            </select>
            <div class="form-text small">
              <strong>Natural Science:</strong> Afaan Oromo, English, Math, Physics, Chemistry, Biology, Agriculture, IT<br>
              <strong>Social Science:</strong> Afaan Oromo, English, Math, Geography, History, Economics, Citizenship Ed, IT
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label required">Admission Date</label>
            <input type="date" name="admission_date" class="form-control flatpickr" value="<?= e(old('admission_date', date('Y-m-d'))) ?>" required>
          </div>
        </div>

        <script>
        function onClassChange(sel) {
          var opt    = sel.options[sel.selectedIndex];
          var grade  = opt.dataset.grade || '';
          var stream = opt.dataset.stream || 'general';
          var div    = document.getElementById('streamDiv');
          var sSelect= document.getElementById('streamSelect');
          if (grade === '11' || grade === '12') {
            div.style.display = '';
            if (stream && stream !== 'general') sSelect.value = stream;
          } else {
            div.style.display = 'none';
          }
        }
        // Run on page load if class is pre-selected
        var cs = document.getElementById('classSelect');
        if (cs && cs.value) onClassChange(cs);
        </script>
      </div>

      <!-- Submit -->
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2"><i class="fas fa-save me-2"></i>Save Student</button>
          <a href="<?= url('students') ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-times me-2"></i>Cancel</a>
          <div class="mt-3 p-2 bg-light rounded small text-muted">
            <i class="fas fa-info-circle me-1"></i>A login account will be auto-created with the default password <strong>Student@123</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<style>
.required::after { content: ' *'; color: red; }
</style>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
  var file = e.target.files[0];
  if (file) {
    var reader = new FileReader();
    reader.onload = function(ev) { document.getElementById('photoPreview').src = ev.target.result; };
    reader.readAsDataURL(file);
  }
});
</script>
