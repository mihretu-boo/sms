<?php $title = 'Website Settings'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-cog me-2 text-secondary"></i>Website Settings</h4>
    <div class="d-flex gap-2">
      <a href="<?= websiteUrl() ?>" target="_blank" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-external-link-alt me-1"></i> View Site
      </a>
      <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> CMS Dashboard
      </a>
    </div>
  </div>

  <?php if ($m = \Flash::get('success')): ?>
  <div class="alert alert-success alert-dismissible"><i class="fas fa-check-circle me-2"></i><?= e($m) ?>
    <button class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <form method="POST" action="<?= url('website/settings/save') ?>">
    <?= csrfField() ?>
    <div class="row g-4">

      <!-- Taglines -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">Tagline / Slogan</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">English</label>
              <input type="text" name="tagline_en" class="form-control"
                     value="<?= e($settings['tagline_en'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Afaan Oromoo</label>
              <input type="text" name="tagline_om" class="form-control"
                     value="<?= e($settings['tagline_om'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">አማርኛ</label>
              <input type="text" name="tagline_am" class="form-control"
                     value="<?= e($settings['tagline_am'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">Statistics (Homepage)</div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label fw-semibold"><i class="fas fa-users me-1 text-primary"></i>Students</label>
                <input type="number" name="stat_students" class="form-control"
                       value="<?= e($settings['stat_students'] ?? '0') ?>">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold"><i class="fas fa-chalkboard-teacher me-1 text-success"></i>Teachers</label>
                <input type="number" name="stat_teachers" class="form-control"
                       value="<?= e($settings['stat_teachers'] ?? '0') ?>">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold"><i class="fas fa-award me-1 text-warning"></i>Years</label>
                <input type="number" name="stat_years" class="form-control"
                       value="<?= e($settings['stat_years'] ?? '0') ?>">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold"><i class="fas fa-door-open me-1 text-info"></i>Classes</label>
                <input type="number" name="stat_classes" class="form-control"
                       value="<?= e($settings['stat_classes'] ?? '0') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">Contact Information</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-map-marker-alt me-1 text-danger"></i>Address</label>
              <input type="text" name="contact_address" class="form-control"
                     value="<?= e($settings['contact_address'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-phone me-1 text-success"></i>Phone</label>
              <input type="text" name="contact_phone" class="form-control"
                     value="<?= e($settings['contact_phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold"><i class="fas fa-envelope me-1 text-primary"></i>Email</label>
              <input type="email" name="contact_email" class="form-control"
                     value="<?= e($settings['contact_email'] ?? '') ?>">
            </div>
            <hr class="my-3">
            <label class="form-label fw-semibold"><i class="fas fa-clock me-1 text-secondary"></i>Office Hours</label>
            <div class="row g-2">
              <div class="col-md-4">
                <input type="text" name="office_hours_en" class="form-control form-control-sm"
                       placeholder="EN: Mon–Fri 8AM–5PM"
                       value="<?= e($settings['office_hours_en'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <input type="text" name="office_hours_om" class="form-control form-control-sm"
                       placeholder="OM"
                       value="<?= e($settings['office_hours_om'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <input type="text" name="office_hours_am" class="form-control form-control-sm"
                       placeholder="AM"
                       value="<?= e($settings['office_hours_am'] ?? '') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Social -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">Social Media Links</div>
          <div class="card-body">
            <?php
            $socials = [
              'facebook_url'  => ['icon'=>'fab fa-facebook-f',  'label'=>'Facebook URL', 'color'=>'#1877f2'],
              'twitter_url'   => ['icon'=>'fab fa-twitter',     'label'=>'Twitter / X URL','color'=>'#1da1f2'],
              'youtube_url'   => ['icon'=>'fab fa-youtube',     'label'=>'YouTube URL',  'color'=>'#ff0000'],
              'telegram_url'  => ['icon'=>'fab fa-telegram',    'label'=>'Telegram URL', 'color'=>'#2ca5e0'],
            ];
            foreach ($socials as $key => $s):
            ?>
            <div class="mb-3">
              <label class="form-label fw-semibold">
                <i class="<?= $s['icon'] ?> me-1" style="color:<?= $s['color'] ?>"></i><?= $s['label'] ?>
              </label>
              <input type="url" name="<?= $key ?>" class="form-control"
                     value="<?= e($settings[$key] ?? '') ?>" placeholder="https://">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Principal Message (moved to dedicated page) -->
      <div class="col-12">
        <div class="card border-0 shadow-sm border-primary" style="border-left:4px solid #1B3A6B!important">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <span class="fw-bold"><i class="fas fa-user-tie me-2 text-primary"></i>Principal's Message</span>
            <a href="<?= url('website/principal') ?>" class="btn btn-primary btn-sm">
              <i class="fas fa-external-link-alt me-1"></i>Edit on Dedicated Page
            </a>
          </div>
          <div class="card-body">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Principal Name</label>
                <input type="text" name="principal_name" class="form-control" value="<?= e($settings['principal_name'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Year Founded (E.C.)</label>
                <input type="text" name="school_founded" class="form-control" value="<?= e($settings['school_founded'] ?? '1999') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Principal Title (EN)</label>
                <input type="text" name="principal_title_en" class="form-control" value="<?= e($settings['principal_title_en'] ?? 'Principal') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Principal Title (OM)</label>
                <input type="text" name="principal_title_om" class="form-control" value="<?= e($settings['principal_title_om'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Principal Title (AM)</label>
                <input type="text" name="principal_title_am" class="form-control" value="<?= e($settings['principal_title_am'] ?? '') ?>">
              </div>
            </div>
            <ul class="nav nav-tabs mb-3" id="pmTabs">
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pm-en">English</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pm-om">Afaan Oromoo</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pm-am">አማርኛ</button></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="pm-en">
                <textarea name="principal_message_en" class="form-control" rows="4"><?= e($settings['principal_message_en'] ?? '') ?></textarea>
              </div>
              <div class="tab-pane fade" id="pm-om">
                <textarea name="principal_message_om" class="form-control" rows="4"><?= e($settings['principal_message_om'] ?? '') ?></textarea>
              </div>
              <div class="tab-pane fade" id="pm-am">
                <textarea name="principal_message_am" class="form-control" rows="4"><?= e($settings['principal_message_am'] ?? '') ?></textarea>
              </div>
            </div>
            <hr>
            <div class="row g-3 mt-1">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Vice Principal Name</label>
                <input type="text" name="vice_principal_name" class="form-control" value="<?= e($settings['vice_principal_name'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Vice Principal Title (EN)</label>
                <input type="text" name="vice_principal_title_en" class="form-control" value="<?= e($settings['vice_principal_title_en'] ?? 'Vice Principal') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- School History -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold"><i class="fas fa-landmark me-2 text-warning"></i>School History</div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sh-en">English</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sh-om">Afaan Oromoo</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sh-am">አማርኛ</button></li>
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
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sv-en">EN</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sv-om">OM</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sv-am">AM</button></li>
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
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sm-en">EN</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sm-om">OM</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sm-am">AM</button></li>
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
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold"><i class="fas fa-sitemap me-2 text-info"></i>Departments <small class="text-muted fw-normal">(comma-separated)</small></div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">English</label>
              <textarea name="departments_en" class="form-control" rows="3"><?= e($settings['departments_en'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Afaan Oromoo</label>
              <textarea name="departments_om" class="form-control" rows="3"><?= e($settings['departments_om'] ?? '') ?></textarea>
            </div>
            <div class="mb-0">
              <label class="form-label fw-semibold">አማርኛ</label>
              <textarea name="departments_am" class="form-control" rows="3"><?= e($settings['departments_am'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Google Map -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold"><i class="fas fa-map-marked-alt me-2 text-danger"></i>Google Map</div>
          <div class="card-body">
            <label class="form-label fw-semibold">Embed URL <small class="text-muted">(Google Maps → Share → Embed → iframe src="…")</small></label>
            <input type="url" name="google_map_embed" class="form-control mb-2"
                   value="<?= e($settings['google_map_embed'] ?? '') ?>"
                   placeholder="https://www.google.com/maps/embed?pb=...">
            <div class="form-text">Paste only the <code>src</code> URL from the Google Maps embed iframe code.</div>
          </div>
        </div>
      </div>

    </div><!-- /.row -->

    <div class="mt-4">
      <button type="submit" class="btn btn-primary px-5">
        <i class="fas fa-save me-1"></i> Save Settings
      </button>
    </div>
  </form>
</div>
