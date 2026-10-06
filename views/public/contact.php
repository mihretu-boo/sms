<?php $title = t('nav_contact'); ?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1><?= t('contact_title') ?></h1>
    <p><?= t('contact_subtitle') ?></p>
    <nav aria-label="breadcrumb" class="mt-2">
      <ol class="breadcrumb justify-content-center mb-0" style="background:transparent">
        <li class="breadcrumb-item"><a href="<?= websiteUrl() ?>"><?= t('nav_home') ?></a></li>
        <li class="breadcrumb-item active"><?= t('nav_contact') ?></li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py">
  <div class="container">
    <div class="row g-5">

      <!-- Contact Form -->
      <div class="col-lg-7">
        <div class="contact-card">
          <h4 style="font-weight:800;color:#1a1a2e;margin-bottom:24px">
            <i class="fas fa-paper-plane me-2" style="color:#1a6b3c"></i><?= t('contact_title') ?>
          </h4>
          <form method="POST" action="<?= url('site/contact') ?>" novalidate>
            <?= csrfField() ?>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold"><?= t('contact_name') ?> <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                       placeholder="<?= t('contact_name') ?>"
                       value="<?= e(old('name')) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold"><?= t('contact_email') ?> <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control"
                       placeholder="<?= t('contact_email') ?>"
                       value="<?= e(old('email')) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold"><?= t('contact_phone') ?></label>
                <input type="tel" name="phone" class="form-control"
                       placeholder="+251 000 000 000"
                       value="<?= e(old('phone')) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold"><?= t('contact_subject') ?> <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control"
                       placeholder="<?= t('contact_subject') ?>"
                       value="<?= e(old('subject')) ?>" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold"><?= t('contact_message') ?> <span class="text-danger">*</span></label>
                <textarea name="message" class="form-control" rows="6"
                          placeholder="<?= t('contact_message') ?>" required><?= e(old('message')) ?></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn-primary-custom w-100" style="border:none;cursor:pointer;padding:13px">
                  <i class="fas fa-paper-plane me-2"></i><?= t('contact_send') ?>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-5">
        <h4 style="font-weight:800;color:#1a1a2e;margin-bottom:28px">
          <?= t('footer_contact') ?>
        </h4>

        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div class="contact-info-text">
            <h6><?= t('contact_address') ?></h6>
            <p><?= e(getWebsiteSetting('contact_address','Shalaka, Ethiopia')) ?></p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-phone-alt"></i></div>
          <div class="contact-info-text">
            <h6><?= t('contact_phone_lbl') ?></h6>
            <p><?= e(getWebsiteSetting('contact_phone','')) ?></p>
          </div>
        </div>

        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-envelope"></i></div>
          <div class="contact-info-text">
            <h6><?= t('contact_email_lbl') ?></h6>
            <p><?= e(getWebsiteSetting('contact_email','')) ?></p>
          </div>
        </div>

        <!-- Social Links -->
        <h6 class="fw-bold mt-4 mb-3"><?= t('footer_follow') ?></h6>
        <div class="social-links">
          <?php if ($fb = getWebsiteSetting('facebook_url','')): ?>
          <a href="<?= e($fb) ?>" target="_blank" rel="noopener" title="Facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <?php endif; ?>
          <?php if ($tw = getWebsiteSetting('twitter_url','')): ?>
          <a href="<?= e($tw) ?>" target="_blank" rel="noopener" title="Twitter">
            <i class="fab fa-twitter"></i>
          </a>
          <?php endif; ?>
          <?php if ($yt = getWebsiteSetting('youtube_url','')): ?>
          <a href="<?= e($yt) ?>" target="_blank" rel="noopener" title="YouTube">
            <i class="fab fa-youtube"></i>
          </a>
          <?php endif; ?>
          <?php if ($tg = getWebsiteSetting('telegram_url','')): ?>
          <a href="<?= e($tg) ?>" target="_blank" rel="noopener" title="Telegram">
            <i class="fab fa-telegram"></i>
          </a>
          <?php endif; ?>
        </div>

        <!-- Office Hours -->
        <?php $lang = Lang::current(); $officeHours = getWebsiteSetting('office_hours_' . $lang, getWebsiteSetting('office_hours_en', t('contact_hours_value'))); ?>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="fas fa-clock"></i></div>
          <div class="contact-info-text">
            <h6><?= t('contact_hours') ?></h6>
            <p><?= e($officeHours) ?></p>
          </div>
        </div>

        <!-- Google Map -->
        <h6 class="fw-bold mt-4 mb-3"><i class="fas fa-map-marked-alt me-1 text-primary"></i><?= t('contact_map') ?></h6>
        <?php $mapEmbed = getWebsiteSetting('google_map_embed',''); ?>
        <?php if ($mapEmbed): ?>
        <iframe src="<?= e($mapEmbed) ?>" class="map-embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <?php else: ?>
        <div style="height:230px;background:linear-gradient(135deg,#e8f5e9,#e3f2fd);border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px dashed #ccc">
          <i class="fas fa-map-marked-alt" style="font-size:2.5rem;color:#1a6b3c;opacity:.4;margin-bottom:10px"></i>
          <p class="text-muted small mb-1"><?= e(getWebsiteSetting('contact_address','Shalaka, Oromia, Ethiopia')) ?></p>
          <small class="text-muted" style="font-size:.75rem">
            <?= Lang::current()==='om' ? 'Kaartaan too\'annoo sirnaan saagamuu qaba.' : (Lang::current()==='am' ? 'ካርታ ዊጃ ወጥቶ ቀርቧል፤ ኤምቤድ URL ያስፈልጋል።' : 'Configure Google Map embed URL in Site Settings.') ?>
          </small>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>
