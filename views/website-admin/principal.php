<?php
$title   = "Principal's Message — Website CMS";
$pName   = $settings['principal_name']      ?? '';
$pTitle  = $settings['principal_title_en']  ?? 'School Principal';
$pMsg    = $settings['principal_message_en']?? '';
$pPhoto  = $settings['principal_photo']     ?? '';
?>

<div class="container-fluid px-4 py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1 fw-bold">
        <i class="fas fa-user-tie me-2 text-primary"></i>Principal's Message
      </h4>
      <p class="text-muted mb-0 small">
        Appears as a pull-quote on the Home page and in the Administration section of the About page
      </p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= websiteUrl() ?>" target="_blank" class="btn btn-sm btn-outline-success">
        <i class="fas fa-eye me-1"></i>Preview
      </a>
      <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>CMS Dashboard
      </a>
    </div>
  </div>

  <?php if ($m = \Flash::get('success')): ?>
  <div class="alert alert-success alert-dismissible">
    <i class="fas fa-check-circle me-2"></i><?= e($m) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>
  <?php if ($err = \Flash::get('error')): ?>
  <div class="alert alert-danger alert-dismissible">
    <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <form method="POST" action="<?= url('website/principal/save') ?>"
        enctype="multipart/form-data">
    <?= csrfField() ?>

    <div class="row g-4">

      <!-- ── LEFT COLUMN: photo + name/title ── -->
      <div class="col-lg-4">

        <!-- Photo Upload -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-camera me-2 text-primary"></i>Principal Photo
          </div>
          <div class="card-body text-center">

            <!-- Current / preview image -->
            <div id="photoPreviewWrap" class="mb-3">
              <?php if ($pPhoto): ?>
              <img id="photoPreview"
                   src="<?= e(BASE_URL . '/' . $pPhoto) ?>"
                   alt="Principal Photo"
                   class="rounded-circle shadow"
                   style="width:150px;height:150px;object-fit:cover;border:4px solid #1B3A6B">
              <?php else: ?>
              <div id="photoPlaceholder"
                   style="width:150px;height:150px;border-radius:50%;background:linear-gradient(135deg,#1B3A6B,#2A5298);
                          display:flex;align-items:center;justify-content:center;margin:0 auto;
                          color:rgba(255,255,255,.6);font-size:3.5rem;border:4px solid #e2e8f0">
                <i class="fas fa-user-tie"></i>
              </div>
              <img id="photoPreview" src="" alt="" class="rounded-circle shadow d-none"
                   style="width:150px;height:150px;object-fit:cover;border:4px solid #1B3A6B">
              <?php endif; ?>
            </div>

            <!-- Upload button -->
            <label for="principal_photo" class="btn btn-primary btn-sm mb-2 w-100">
              <i class="fas fa-upload me-1"></i>
              <?= $pPhoto ? 'Change Photo' : 'Upload Photo' ?>
            </label>
            <input type="file" id="principal_photo" name="principal_photo"
                   class="d-none" accept="image/jpeg,image/png,image/webp">
            <div class="form-text mb-2">JPG, PNG or WebP · max 15 MB</div>

            <!-- Remove checkbox (only when a photo exists) -->
            <div id="removeWrap" class="<?= $pPhoto ? '' : 'd-none' ?> mt-2">
              <input type="hidden" name="remove_photo" value="0" id="removePhotoHidden">
              <button type="button" id="removePhotoBtn"
                      class="btn btn-sm btn-outline-danger w-100">
                <i class="fas fa-trash me-1"></i>Remove Photo
              </button>
            </div>

          </div>
        </div>

        <!-- Identity card -->
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-id-card me-2 text-primary"></i>Identity
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">
                Full Name <span class="text-danger">*</span>
              </label>
              <input type="text" name="principal_name" class="form-control"
                     value="<?= e($settings['principal_name'] ?? '') ?>"
                     placeholder="e.g. Ato Girma Bekele" required>
            </div>
            <label class="form-label fw-semibold">Title</label>
            <div class="row g-2">
              <div class="col-12">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="width:36px;justify-content:center">EN</span>
                  <input type="text" name="principal_title_en" class="form-control"
                         value="<?= e($settings['principal_title_en'] ?? 'School Principal') ?>">
                </div>
              </div>
              <div class="col-12">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="width:36px;justify-content:center">OM</span>
                  <input type="text" name="principal_title_om" class="form-control"
                         value="<?= e($settings['principal_title_om'] ?? '') ?>">
                </div>
              </div>
              <div class="col-12">
                <div class="input-group input-group-sm">
                  <span class="input-group-text" style="width:36px;justify-content:center">AM</span>
                  <input type="text" name="principal_title_am" class="form-control"
                         value="<?= e($settings['principal_title_am'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <!-- ── END LEFT ── -->

      <!-- ── RIGHT COLUMN: message + vice principal + preview ── -->
      <div class="col-lg-8">

        <!-- Message -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-quote-left me-2 text-primary"></i>Message
            <small class="text-muted fw-normal ms-1">— keep 2–4 sentences for best layout</small>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3" id="msgTabs">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab"
                        data-bs-target="#msg-en" type="button">
                  <i class="fas fa-flag me-1"></i>English
                </button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab"
                        data-bs-target="#msg-om" type="button">OM</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab"
                        data-bs-target="#msg-am" type="button">AM</button>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="msg-en">
                <textarea name="principal_message_en" class="form-control" rows="6"
                          placeholder="Write the principal's message in English..."
                          ><?= e($settings['principal_message_en'] ?? '') ?></textarea>
              </div>
              <div class="tab-pane fade" id="msg-om">
                <textarea name="principal_message_om" class="form-control" rows="6"
                          placeholder="Ergaa Hogganaa Mana Barumsaa..."
                          ><?= e($settings['principal_message_om'] ?? '') ?></textarea>
              </div>
              <div class="tab-pane fade" id="msg-am">
                <textarea name="principal_message_am" class="form-control" rows="6"
                          placeholder="የዳይሬክተሩ መልዕክት..."
                          ><?= e($settings['principal_message_am'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Vice Principal -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-user-graduate me-2 text-secondary"></i>Vice Principal
            <small class="text-muted fw-normal ms-1">— leave name blank to hide</small>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-5">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="vice_principal_name" class="form-control"
                       value="<?= e($settings['vice_principal_name'] ?? '') ?>"
                       placeholder="Leave blank to hide">
              </div>
              <div class="col-md-7">
                <label class="form-label fw-semibold">Title</label>
                <div class="row g-2">
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text" style="width:36px;justify-content:center">EN</span>
                      <input type="text" name="vice_principal_title_en" class="form-control"
                             value="<?= e($settings['vice_principal_title_en'] ?? 'Vice Principal') ?>">
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text" style="width:36px;justify-content:center">OM</span>
                      <input type="text" name="vice_principal_title_om" class="form-control"
                             value="<?= e($settings['vice_principal_title_om'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <span class="input-group-text" style="width:36px;justify-content:center">AM</span>
                      <input type="text" name="vice_principal_title_am" class="form-control"
                             value="<?= e($settings['vice_principal_title_am'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Live preview -->
        <?php if ($pName && $pMsg): ?>
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-eye me-2 text-success"></i>Live Preview
          </div>
          <div class="card-body" style="background:#f8fafc">
            <div style="max-width:500px;background:#fff;border-radius:12px;padding:28px 24px;
                        border-left:5px solid #1B3A6B;box-shadow:0 4px 16px rgba(0,0,0,.07)">
              <div style="font-size:3.5rem;color:#1B3A6B;opacity:.1;line-height:.8;font-family:Georgia,serif">&ldquo;</div>
              <p style="font-style:italic;color:#334155;font-size:.95rem;line-height:1.82;margin-bottom:20px">
                <?= e(mb_strimwidth($pMsg, 0, 380, '…')) ?>
              </p>
              <div style="display:flex;align-items:center;gap:12px">
                <!-- avatar -->
                <?php if ($pPhoto): ?>
                <img src="<?= e(BASE_URL . '/' . $pPhoto) ?>" alt=""
                     style="width:48px;height:48px;border-radius:50%;object-fit:cover;
                            border:3px solid #E8EEF8;flex-shrink:0;
                            box-shadow:0 2px 8px rgba(27,58,107,.25)">
                <?php else: ?>
                <div style="width:48px;height:48px;border-radius:50%;flex-shrink:0;
                            background:linear-gradient(135deg,#1B3A6B,#2A5298);
                            display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem">
                  <i class="fas fa-user-tie"></i>
                </div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:700;color:#0F172A;font-size:.95rem"><?= e($pName) ?></div>
                  <div style="color:#1B3A6B;font-size:.8rem;font-weight:600"><?= e($pTitle) ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

      </div>
      <!-- ── END RIGHT ── -->

    </div>

    <div class="mt-4 d-flex gap-2 flex-wrap">
      <button type="submit" class="btn btn-primary px-5">
        <i class="fas fa-save me-2"></i>Save
      </button>
      <a href="<?= websiteUrl() ?>" target="_blank" class="btn btn-outline-success">
        <i class="fas fa-external-link-alt me-1"></i>View Live
      </a>
    </div>

  </form>
</div>

<script>
(function () {
  var input       = document.getElementById('principal_photo');
  var preview     = document.getElementById('photoPreview');
  var placeholder = document.getElementById('photoPlaceholder');
  var removeWrap  = document.getElementById('removeWrap');
  var removeBtn   = document.getElementById('removePhotoBtn');
  var removeHidden= document.getElementById('removePhotoHidden');

  // Show image preview when a file is chosen
  input.addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      preview.src = e.target.result;
      preview.classList.remove('d-none');
      if (placeholder) placeholder.style.display = 'none';
      removeWrap.classList.remove('d-none');
      removeHidden.value = '0';   // uploading new photo, don't remove
    };
    reader.readAsDataURL(file);
  });

  // Remove photo
  if (removeBtn) {
    removeBtn.addEventListener('click', function () {
      if (!confirm('Remove the principal\'s photo?')) return;
      removeHidden.value = '1';
      preview.src = '';
      preview.classList.add('d-none');
      if (placeholder) placeholder.style.display = 'flex';
      removeWrap.classList.add('d-none');
      input.value = '';
    });
  }
}());
</script>
