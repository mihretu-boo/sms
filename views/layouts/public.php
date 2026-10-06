<!DOCTYPE html>
<html lang="<?= e(Lang::current()) ?>" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? getSetting('school_name_short','SJASSMS')) ?> | <?= e(getSetting('school_name', 'Shalaka Jatan Ali Secondary School')) ?></title>
<meta name="description" content="<?= e(getWebsiteSetting('tagline_' . Lang::current(), getWebsiteSetting('tagline_en', 'Excellence in Education'))) ?>">

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Public CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/public.css">
</head>
<body>

<!-- ===== UTILITY BAR ===== -->
<div class="utility-bar d-none d-md-block">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between">
      <div class="util-contact d-flex align-items-center gap-0">
        <?php if ($uPhone = getWebsiteSetting('contact_phone','')): ?>
        <a href="tel:<?= e(preg_replace('/[^+\d]/','',$uPhone)) ?>">
          <i class="fas fa-phone-alt"></i><?= e($uPhone) ?>
        </a>
        <span class="util-sep">|</span>
        <?php endif; ?>
        <?php if ($uEmail = getWebsiteSetting('contact_email','')): ?>
        <a href="mailto:<?= e($uEmail) ?>">
          <i class="fas fa-envelope"></i><?= e($uEmail) ?>
        </a>
        <?php endif; ?>
      </div>
      <!-- Language Switcher -->
      <div class="lang-pill">
        <?php foreach (['en'=>'EN','om'=>'OM','am'=>'አማ'] as $code => $label): ?>
        <a href="<?= url('site/lang/' . $code) ?>"
           class="<?= Lang::current() === $code ? 'active' : '' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<!-- ===== END UTILITY BAR ===== -->

<!-- ===== MAIN NAVBAR ===== -->
<nav class="pub-navbar navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?= websiteUrl() ?>">
      <div class="logo-circle"><i class="fas fa-graduation-cap"></i></div>
      <div>
        <div class="brand-name"><?= e(getSetting('school_name_short','SJASSMS')) ?></div>
        <div class="brand-sub"><?= e(getSetting('school_name','Shalaka Jatan Ali SS')) ?></div>
      </div>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#pubNav">
      <i class="fas fa-bars text-dark"></i>
    </button>
    <div class="collapse navbar-collapse" id="pubNav">
      <ul class="navbar-nav mx-auto gap-0">
        <?php
        $currentPath = trim(str_replace(BASE_PATH, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');
        $navLinks = [
          ['url'=>'site',         'key'=>'nav_home'],
          ['url'=>'site/about',   'key'=>'nav_about'],
          ['url'=>'site/news',    'key'=>'nav_news'],
          ['url'=>'site/gallery', 'key'=>'nav_gallery'],
          ['url'=>'site/contact', 'key'=>'nav_contact'],
        ];
        foreach ($navLinks as $link):
          if ($link['url'] === 'site') {
            $active = ($currentPath === 'site') ? 'active' : '';
          } else {
            $active = (strpos($currentPath, $link['url']) === 0) ? 'active' : '';
          }
        ?>
        <li class="nav-item">
          <a class="nav-link <?= $active ?>" href="<?= url($link['url']) ?>"><?= t($link['key']) ?></a>
        </li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
        <!-- Mobile lang switcher (hidden on md+) -->
        <div class="lang-pill d-md-none">
          <?php foreach (['en'=>'EN','om'=>'OM','am'=>'አማ'] as $code => $label): ?>
          <a href="<?= url('site/lang/' . $code) ?>"
             class="<?= Lang::current() === $code ? 'active' : '' ?>">
            <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
        <!-- Portal / Dashboard -->
        <?php if (Auth::check()): ?>
        <a href="<?= url('dashboard') ?>" class="nav-link btn-portal ms-1">
          <i class="fas fa-tachometer-alt me-1"></i><?= t('nav_admin') ?>
        </a>
        <?php else: ?>
        <a href="<?= url('login') ?>" class="nav-link btn-portal ms-1">
          <i class="fas fa-sign-in-alt me-1"></i><?= t('nav_portal') ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<!-- ===== END NAVBAR ===== -->

<!-- Flash messages -->
<?php if (isset($_SESSION['contact_success']) && $_SESSION['contact_success']): unset($_SESSION['contact_success']); ?>
<div class="alert alert-success alert-dismissible text-center mb-0 rounded-0" role="alert">
  <i class="fas fa-check-circle me-2"></i><?= t('contact_success') ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (isset($_SESSION['contact_error'])): $err = $_SESSION['contact_error']; unset($_SESSION['contact_error']); ?>
<div class="alert alert-danger alert-dismissible text-center mb-0 rounded-0" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i><?= e($err) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Page Content -->
<?= $content ?>

<!-- ===== FOOTER ===== -->
<footer class="pub-footer">
  <div class="container">
    <div class="row g-5">

      <!-- Brand column -->
      <div class="col-lg-4 col-md-6">
        <div class="footer-brand d-flex align-items-center gap-3 mb-3">
          <div class="logo-circle"><i class="fas fa-graduation-cap"></i></div>
          <div>
            <div style="color:#fff;font-weight:800;font-size:1rem;line-height:1.2"><?= e(getSetting('school_name_short','SJASSMS')) ?></div>
            <div style="font-size:.78rem;opacity:.55;line-height:1.3"><?= e(getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?></div>
          </div>
        </div>
        <p style="font-size:.88rem;margin-bottom:20px">
          <?= e(getWebsiteSetting('tagline_' . Lang::current(), getWebsiteSetting('tagline_en', 'Inspiring Excellence, Building Futures.'))) ?>
        </p>
        <div class="footer-social">
          <?php if ($fb = getWebsiteSetting('facebook_url','')): ?>
          <a href="<?= e($fb) ?>" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <?php endif; ?>
          <?php if ($yt = getWebsiteSetting('youtube_url','')): ?>
          <a href="<?= e($yt) ?>" target="_blank" rel="noopener" title="YouTube"><i class="fab fa-youtube"></i></a>
          <?php endif; ?>
          <?php if ($tg = getWebsiteSetting('telegram_url','')): ?>
          <a href="<?= e($tg) ?>" target="_blank" rel="noopener" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
          <?php endif; ?>
          <?php if ($tw = getWebsiteSetting('twitter_url','')): ?>
          <a href="<?= e($tw) ?>" target="_blank" rel="noopener" title="Twitter/X"><i class="fab fa-twitter"></i></a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Navigation column -->
      <div class="col-lg-2 col-md-3 col-6">
        <h5><?= t('footer_quick_links') ?></h5>
        <ul class="footer-links">
          <li><a href="<?= websiteUrl() ?>"><i class="fas fa-chevron-right"></i><?= t('nav_home') ?></a></li>
          <li><a href="<?= websiteUrl('about') ?>"><i class="fas fa-chevron-right"></i><?= t('nav_about') ?></a></li>
          <li><a href="<?= websiteUrl('news') ?>"><i class="fas fa-chevron-right"></i><?= t('nav_news') ?></a></li>
          <li><a href="<?= websiteUrl('gallery') ?>"><i class="fas fa-chevron-right"></i><?= t('nav_gallery') ?></a></li>
          <li><a href="<?= websiteUrl('contact') ?>"><i class="fas fa-chevron-right"></i><?= t('nav_contact') ?></a></li>
        </ul>
      </div>

      <!-- Portals column -->
      <div class="col-lg-2 col-md-3 col-6">
        <h5><?= t('footer_portals') ?></h5>
        <ul class="footer-links">
          <li><a href="<?= url('login') ?>"><i class="fas fa-chevron-right"></i><?= t('ql_student_portal') ?></a></li>
          <li><a href="<?= url('login') ?>"><i class="fas fa-chevron-right"></i><?= t('ql_parent_portal') ?></a></li>
          <li><a href="<?= url('login') ?>"><i class="fas fa-chevron-right"></i>Staff Portal</a></li>
          <li><a href="<?= websiteUrl('news') ?>?cat=exam"><i class="fas fa-chevron-right"></i><?= t('ql_exam_results') ?></a></li>
          <li><a href="<?= websiteUrl('news') ?>?cat=announcement"><i class="fas fa-chevron-right"></i><?= t('ql_timetable') ?></a></li>
        </ul>
      </div>

      <!-- Contact column -->
      <div class="col-lg-4 col-md-6">
        <h5><?= t('footer_contact') ?></h5>
        <?php if ($addr = getWebsiteSetting('contact_address','')): ?>
        <div class="footer-contact-item">
          <i class="fas fa-map-marker-alt"></i>
          <span><?= e($addr) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($phone = getWebsiteSetting('contact_phone','')): ?>
        <div class="footer-contact-item">
          <i class="fas fa-phone-alt"></i>
          <a href="tel:<?= e(preg_replace('/[^+\d]/','',$phone)) ?>" style="color:inherit"><?= e($phone) ?></a>
        </div>
        <?php endif; ?>
        <?php if ($email = getWebsiteSetting('contact_email','')): ?>
        <div class="footer-contact-item">
          <i class="fas fa-envelope"></i>
          <a href="mailto:<?= e($email) ?>" style="color:inherit"><?= e($email) ?></a>
        </div>
        <?php endif; ?>
        <div class="footer-contact-item">
          <i class="fas fa-clock"></i>
          <span><?= t('contact_hours_value') ?></span>
        </div>
      </div>

    </div><!-- /.row -->
  </div>
  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= e(getSetting('school_name_short','SJASSMS')) ?>.
    <?= t('footer_rights') ?> &mdash; <?= t('footer_designed') ?>
  </div>
</footer>
<!-- ===== END FOOTER ===== -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Lightbox modal -->
<div class="modal fade lightbox-modal" id="lightboxModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body p-0 position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2 z-3" data-bs-dismiss="modal"></button>
        <img id="lightboxImg" src="" alt="" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>

<!-- Video modal -->
<div class="modal fade" id="videoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-black border-0">
      <div class="modal-body p-0 position-relative">
        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2 z-3" data-bs-dismiss="modal"></button>
        <div class="ratio ratio-16x9">
          <iframe id="videoFrame" src="" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Gallery lightbox
document.querySelectorAll('.gallery-item[data-src]').forEach(function(el) {
  el.addEventListener('click', function() {
    document.getElementById('lightboxImg').src = this.dataset.src;
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
  });
});
// Video modal: stop playback on close
var videoModal = document.getElementById('videoModal');
if (videoModal) {
  videoModal.addEventListener('hide.bs.modal', function() {
    document.getElementById('videoFrame').src = '';
  });
}
// Gallery filter (category tabs)
document.querySelectorAll('[data-filter]').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    var filter = this.dataset.filter;
    document.querySelectorAll('.gallery-item').forEach(function(item) {
      item.closest('.col-6, .col-md-4, .col-lg-3')
        .style.display = (filter === 'all' || item.dataset.cat === filter) ? '' : 'none';
    });
  });
});
</script>
</body>
</html>
