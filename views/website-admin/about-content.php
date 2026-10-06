<?php
$title = 'About Page Content — Website CMS';
$cvIcons = [
  'award','balance-scale','users','lightbulb','heart','shield-alt',
  'star','book','graduation-cap','globe','handshake','brain',
  'leaf','fire','flag','gem','microscope','clock',
];
?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1 fw-bold"><i class="fas fa-school me-2 text-primary"></i>About Page Content</h4>
      <p class="text-muted mb-0 small">School history, vision, mission, departments, and core values</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= websiteUrl('about') ?>" target="_blank" class="btn btn-sm btn-outline-success">
        <i class="fas fa-eye me-1"></i> View About Page
      </a>
      <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> CMS Dashboard
      </a>
    </div>
  </div>

  <?php if ($m = \Flash::get('success')): ?>
  <div class="alert alert-success alert-dismissible">
    <i class="fas fa-check-circle me-2"></i><?= e($m) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <form method="POST" action="<?= url('website/about-content/save') ?>">
    <?= csrfField() ?>
    <div class="row g-4">

      <!-- School History -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-landmark me-2 text-warning"></i>School History
          </div>
          <div class="card-body">
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label class="form-label fw-semibold">Year Founded (E.C.)</label>
                <input type="text" name="school_founded" class="form-control"
                       value="<?= e($settings['school_founded'] ?? '1999') ?>"
                       placeholder="1999">
              </div>
            </div>
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sh-en" type="button">English</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sh-om" type="button">Afaan Oromoo</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sh-am" type="button">አማርኛ</button></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="sh-en">
                <textarea name="school_history_en" class="form-control" rows="4"><?= e($settings['school_history_en'] ?? '') ?></textarea>
              </div>
              <div class="tab-pane fade" id="sh-om">
                <textarea name="school_history_om" class="form-control" rows="4"><?= e($settings['school_history_om'] ?? '') ?></textarea>
              </div>
              <div class="tab-pane fade" id="sh-am">
                <textarea name="school_history_am" class="form-control" rows="4"><?= e($settings['school_history_am'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vision & Mission -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white fw-bold"><i class="fas fa-eye me-2 text-success"></i>School Vision</div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sv-en" type="button">EN</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sv-om" type="button">OM</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sv-am" type="button">AM</button></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="sv-en"><textarea name="school_vision_en" class="form-control" rows="4"><?= e($settings['school_vision_en'] ?? '') ?></textarea></div>
              <div class="tab-pane fade" id="sv-om"><textarea name="school_vision_om" class="form-control" rows="4"><?= e($settings['school_vision_om'] ?? '') ?></textarea></div>
              <div class="tab-pane fade" id="sv-am"><textarea name="school_vision_am" class="form-control" rows="4"><?= e($settings['school_vision_am'] ?? '') ?></textarea></div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-white fw-bold"><i class="fas fa-bullseye me-2 text-danger"></i>School Mission</div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sm-en" type="button">EN</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sm-om" type="button">OM</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sm-am" type="button">AM</button></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="sm-en"><textarea name="school_mission_en" class="form-control" rows="4"><?= e($settings['school_mission_en'] ?? '') ?></textarea></div>
              <div class="tab-pane fade" id="sm-om"><textarea name="school_mission_om" class="form-control" rows="4"><?= e($settings['school_mission_om'] ?? '') ?></textarea></div>
              <div class="tab-pane fade" id="sm-am"><textarea name="school_mission_am" class="form-control" rows="4"><?= e($settings['school_mission_am'] ?? '') ?></textarea></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Departments -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-sitemap me-2 text-info"></i>Departments
            <small class="text-muted fw-normal ms-2">— comma-separated list</small>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">English</label>
                <textarea name="departments_en" class="form-control" rows="3"
                          placeholder="Natural Science, Mathematics, ..."><?= e($settings['departments_en'] ?? '') ?></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Afaan Oromoo</label>
                <textarea name="departments_om" class="form-control" rows="3"><?= e($settings['departments_om'] ?? '') ?></textarea>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">አማርኛ</label>
                <textarea name="departments_am" class="form-control" rows="3"><?= e($settings['departments_am'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Core Values -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold d-flex align-items-center justify-content-between">
            <span><i class="fas fa-star me-2 text-warning"></i>Core Values (6 cards on About page)</span>
            <span class="badge bg-primary">Multilingual</span>
          </div>
          <div class="card-body">
            <div class="row g-4">
              <?php for ($i = 1; $i <= 6; $i++): ?>
              <div class="col-md-6 col-lg-4">
                <div class="border rounded p-3" style="background:#f8fafc">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <div style="width:36px;height:36px;background:#1B3A6B;color:#fff;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem">
                      <i class="fas fa-<?= e($settings["cv{$i}_icon"] ?? 'star') ?>"></i>
                    </div>
                    <span class="fw-bold text-muted small">Value <?= $i ?></span>
                  </div>

                  <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Icon (Font Awesome name)</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text"><i class="fas fa-icons"></i></span>
                      <input type="text" name="cv<?= $i ?>_icon"
                             class="form-control form-control-sm cv-icon-input"
                             value="<?= e($settings["cv{$i}_icon"] ?? 'star') ?>"
                             placeholder="e.g. award, heart, users"
                             data-idx="<?= $i ?>">
                    </div>
                    <div class="form-text">
                      Common: award, balance-scale, users, lightbulb, heart, shield-alt, star, graduation-cap
                    </div>
                  </div>

                  <ul class="nav nav-tabs nav-sm mb-2">
                    <li class="nav-item"><button class="nav-link py-1 active" data-bs-toggle="tab" data-bs-target="#cv<?= $i ?>-en" type="button" style="font-size:.78rem">EN</button></li>
                    <li class="nav-item"><button class="nav-link py-1" data-bs-toggle="tab" data-bs-target="#cv<?= $i ?>-om" type="button" style="font-size:.78rem">OM</button></li>
                    <li class="nav-item"><button class="nav-link py-1" data-bs-toggle="tab" data-bs-target="#cv<?= $i ?>-am" type="button" style="font-size:.78rem">AM</button></li>
                  </ul>
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="cv<?= $i ?>-en">
                      <input type="text" name="cv<?= $i ?>_name_en" class="form-control form-control-sm mb-2"
                             placeholder="Value name (EN)" value="<?= e($settings["cv{$i}_name_en"] ?? '') ?>">
                      <textarea name="cv<?= $i ?>_desc_en" class="form-control form-control-sm" rows="2"
                                placeholder="Short description (EN)"><?= e($settings["cv{$i}_desc_en"] ?? '') ?></textarea>
                    </div>
                    <div class="tab-pane fade" id="cv<?= $i ?>-om">
                      <input type="text" name="cv<?= $i ?>_name_om" class="form-control form-control-sm mb-2"
                             placeholder="Maqaa (OM)" value="<?= e($settings["cv{$i}_name_om"] ?? '') ?>">
                      <textarea name="cv<?= $i ?>_desc_om" class="form-control form-control-sm" rows="2"
                                placeholder="Ibsa gabaabaa (OM)"><?= e($settings["cv{$i}_desc_om"] ?? '') ?></textarea>
                    </div>
                    <div class="tab-pane fade" id="cv<?= $i ?>-am">
                      <input type="text" name="cv<?= $i ?>_name_am" class="form-control form-control-sm mb-2"
                             placeholder="ስም (AM)" value="<?= e($settings["cv{$i}_name_am"] ?? '') ?>">
                      <textarea name="cv<?= $i ?>_desc_am" class="form-control form-control-sm" rows="2"
                                placeholder="አጭር መግለጫ (AM)"><?= e($settings["cv{$i}_desc_am"] ?? '') ?></textarea>
                    </div>
                  </div>
                </div>
              </div>
              <?php endfor; ?>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary px-5">
        <i class="fas fa-save me-2"></i>Save About Content
      </button>
      <a href="<?= websiteUrl('about') ?>" target="_blank" class="btn btn-outline-success">
        <i class="fas fa-external-link-alt me-1"></i> View Live
      </a>
    </div>
  </form>
</div>

<script>
// Live icon preview
document.querySelectorAll('.cv-icon-input').forEach(function(input) {
  input.addEventListener('input', function() {
    var icon = this.value.trim();
    var iconEl = this.closest('.border').querySelector('.fas');
    iconEl.className = 'fas fa-' + (icon || 'star');
  });
});
</script>
