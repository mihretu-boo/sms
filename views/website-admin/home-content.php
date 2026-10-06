<?php $title = 'Home Page Content — Website CMS'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1 fw-bold"><i class="fas fa-home me-2 text-primary"></i>Home Page Content</h4>
      <p class="text-muted mb-0 small">School tagline, statistics, and call-to-action section</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= websiteUrl() ?>" target="_blank" class="btn btn-sm btn-outline-success">
        <i class="fas fa-eye me-1"></i> View Home Page
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

  <form method="POST" action="<?= url('website/home-content/save') ?>">
    <?= csrfField() ?>
    <div class="row g-4">

      <!-- Tagline / Slogan -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-quote-right me-2 text-info"></i>School Tagline / Slogan
            <small class="text-muted fw-normal ms-2">— shown in the navbar, footer, and meta description</small>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">English</label>
                <input type="text" name="tagline_en" class="form-control"
                       value="<?= e($settings['tagline_en'] ?? '') ?>"
                       placeholder="e.g. Inspiring Excellence, Building Futures.">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Afaan Oromoo</label>
                <input type="text" name="tagline_om" class="form-control"
                       value="<?= e($settings['tagline_om'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">አማርኛ</label>
                <input type="text" name="tagline_am" class="form-control"
                       value="<?= e($settings['tagline_am'] ?? '') ?>">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-chart-bar me-2 text-success"></i>Statistics Strip
            <small class="text-muted fw-normal ms-2">— displayed at the bottom of the hero slide</small>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-6 col-md-3">
                <label class="form-label fw-semibold"><i class="fas fa-users text-primary me-1"></i>Students</label>
                <input type="number" name="stat_students" class="form-control form-control-lg text-center fw-bold"
                       value="<?= e($settings['stat_students'] ?? '0') ?>" min="0">
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label fw-semibold"><i class="fas fa-chalkboard-teacher text-success me-1"></i>Teachers</label>
                <input type="number" name="stat_teachers" class="form-control form-control-lg text-center fw-bold"
                       value="<?= e($settings['stat_teachers'] ?? '0') ?>" min="0">
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label fw-semibold"><i class="fas fa-award text-warning me-1"></i>Years Active</label>
                <input type="number" name="stat_years" class="form-control form-control-lg text-center fw-bold"
                       value="<?= e($settings['stat_years'] ?? '0') ?>" min="0">
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label fw-semibold"><i class="fas fa-door-open text-info me-1"></i>Classes</label>
                <input type="number" name="stat_classes" class="form-control form-control-lg text-center fw-bold"
                       value="<?= e($settings['stat_classes'] ?? '0') ?>" min="0">
              </div>
            </div>
            <div class="mt-3 p-3 rounded" style="background:linear-gradient(135deg,#0F2548,#2A5298)">
              <div class="row text-center g-0 text-white">
                <?php foreach ([
                  ['stat_students','Students'],['stat_teachers','Teachers'],['stat_years','Years of Excellence']
                ] as [$k,$l]): ?>
                <div class="col-4" style="border-right:1px solid rgba(255,255,255,.15)">
                  <div style="font-size:1.4rem;font-weight:800;color:#E8A020"><?= e($settings[$k] ?? '0') ?>+</div>
                  <div style="font-size:.72rem;opacity:.75;text-transform:uppercase;letter-spacing:.04em"><?= $l ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-bullhorn me-2 text-danger"></i>Call-to-Action Banner
            <small class="text-muted fw-normal ms-2">— blue banner at the bottom of the home page</small>
          </div>
          <div class="card-body">
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cta-en" type="button"><i class="fas fa-flag-usa me-1"></i> English</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cta-om" type="button">OM</button></li>
              <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cta-am" type="button">AM</button></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="cta-en">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Heading</label>
                  <input type="text" name="cta_title_en" class="form-control"
                         value="<?= e($settings['cta_title_en'] ?? 'Ready to Join Our School?') ?>">
                </div>
                <div>
                  <label class="form-label fw-semibold">Subtitle</label>
                  <input type="text" name="cta_subtitle_en" class="form-control"
                         value="<?= e($settings['cta_subtitle_en'] ?? '') ?>">
                </div>
              </div>
              <div class="tab-pane fade" id="cta-om">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Mata Duree</label>
                  <input type="text" name="cta_title_om" class="form-control"
                         value="<?= e($settings['cta_title_om'] ?? '') ?>">
                </div>
                <div>
                  <label class="form-label fw-semibold">Ibsa</label>
                  <input type="text" name="cta_subtitle_om" class="form-control"
                         value="<?= e($settings['cta_subtitle_om'] ?? '') ?>">
                </div>
              </div>
              <div class="tab-pane fade" id="cta-am">
                <div class="mb-3">
                  <label class="form-label fw-semibold">ርዕስ</label>
                  <input type="text" name="cta_title_am" class="form-control"
                         value="<?= e($settings['cta_title_am'] ?? '') ?>">
                </div>
                <div>
                  <label class="form-label fw-semibold">ንዑስ ርዕስ</label>
                  <input type="text" name="cta_subtitle_am" class="form-control"
                         value="<?= e($settings['cta_subtitle_am'] ?? '') ?>">
                </div>
              </div>
            </div>
            <div class="mt-3 p-4 text-center rounded text-white" style="background:linear-gradient(135deg,#1B3A6B,#2A5298)">
              <div style="font-size:1.3rem;font-weight:800;margin-bottom:6px"><?= e($settings['cta_title_en'] ?? 'Ready to Join Our School?') ?></div>
              <div style="opacity:.85;font-size:.9rem"><?= e($settings['cta_subtitle_en'] ?? '') ?></div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="mt-4 d-flex gap-2">
      <button type="submit" class="btn btn-primary px-5">
        <i class="fas fa-save me-2"></i>Save Home Content
      </button>
      <a href="<?= websiteUrl() ?>" target="_blank" class="btn btn-outline-success">
        <i class="fas fa-external-link-alt me-1"></i> View Live
      </a>
    </div>
  </form>
</div>
