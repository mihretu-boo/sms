<?php
$title    = getSetting('school_name_short','SJASSMS');
$pName    = getWebsiteSetting('principal_name','');
$pMsg     = getWebsiteSetting('principal_message_' . Lang::current(), getWebsiteSetting('principal_message_en',''));
$pTitle   = getWebsiteSetting('principal_title_' . Lang::current(), getWebsiteSetting('principal_title_en','School Principal'));
$pPhoto   = getWebsiteSetting('principal_photo','');
$statStu  = (int)getWebsiteSetting('stat_students', 0);
$statTch  = (int)getWebsiteSetting('stat_teachers', 0);
$statYrs  = (int)getWebsiteSetting('stat_years', 0);
$lang     = Lang::current();
$ctaTitle = getWebsiteSetting('cta_title_' . $lang, getWebsiteSetting('cta_title_en', 'Ready to Join Our School?'));
$ctaSub   = getWebsiteSetting('cta_subtitle_' . $lang, getWebsiteSetting('cta_subtitle_en', ''));
$catClss  = [
  'academic'     => 'cat-academic',
  'announcement' => 'cat-announcement',
  'exam'         => 'cat-exam',
  'event'        => 'cat-event',
  'news'         => 'cat-news',
];
function catClass(string $c): string {
  return ['academic'=>'cat-academic','announcement'=>'cat-announcement',
          'exam'=>'cat-exam','event'=>'cat-event','news'=>'cat-news'][$c] ?? 'cat-news';
}
?>

<!-- ===== HERO ===== -->
<section class="hero-section">
  <?php if (!empty($sliders)): ?>
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <?php if (count($sliders) > 1): ?>
    <div class="carousel-indicators">
      <?php foreach ($sliders as $i => $s): ?>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>"
              <?= $i === 0 ? 'class="active"' : '' ?>></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="carousel-inner">
      <?php foreach ($sliders as $i => $s): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <div class="hero-slide">
          <?php if (!empty($s['image'])): ?>
          <div class="hero-bg" style="background-image:url('<?= e(BASE_URL . '/' . $s['image']) ?>')"></div>
          <?php endif; ?>
          <div class="container">
            <div class="hero-content">
              <div class="hero-eyebrow">
                <i class="fas fa-star"></i>
                <?= e(getSetting('school_name_short','SJASSMS')) ?> &mdash; <?= e(getWebsiteSetting('tagline_' . Lang::current(), getWebsiteSetting('tagline_en','Excellence in Education'))) ?>
              </div>
              <h1><?= e(Lang::field($s, 'title') ?: getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?></h1>
              <p><?= e(Lang::field($s, 'subtitle') ?: getWebsiteSetting('tagline_' . Lang::current(), getWebsiteSetting('tagline_en',''))) ?></p>
              <div class="hero-ctas">
                <a href="<?= websiteUrl('about') ?>" class="btn-accent-custom"><?= t('btn_learn_more') ?></a>
                <a href="<?= websiteUrl('contact') ?>" class="btn-outline-custom"><?= t('btn_contact_us') ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (count($sliders) > 1): ?>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <!-- Default hero if no sliders -->
  <div class="hero-slide">
    <div class="container">
      <div class="hero-content">
        <div class="hero-eyebrow">
          <i class="fas fa-star"></i> <?= t('tagline_en') ?: 'Excellence in Education' ?>
        </div>
        <h1><?= e(getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?></h1>
        <p><?= e(getWebsiteSetting('tagline_' . Lang::current(), getWebsiteSetting('tagline_en', 'Empowering students with knowledge, values, and skills to shape a better tomorrow.'))) ?></p>
        <div class="hero-ctas">
          <a href="<?= websiteUrl('about') ?>" class="btn-accent-custom"><?= t('btn_learn_more') ?></a>
          <a href="<?= websiteUrl('contact') ?>" class="btn-outline-custom"><?= t('btn_contact_us') ?></a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Stat strip inside hero -->
  <div class="hero-stats-strip">
    <div class="container">
      <div class="row g-0">
        <div class="col-4 hero-stat-item">
          <div class="hero-stat-num"><?= $statStu > 0 ? number_format($statStu) . '+' : '1,200+' ?></div>
          <div class="hero-stat-label"><?= t('stat_students') ?></div>
        </div>
        <div class="col-4 hero-stat-item">
          <div class="hero-stat-num"><?= $statTch > 0 ? $statTch . '+' : '80+' ?></div>
          <div class="hero-stat-label"><?= t('stat_teachers') ?></div>
        </div>
        <div class="col-4 hero-stat-item">
          <div class="hero-stat-num"><?= $statYrs > 0 ? $statYrs . '+' : '25+' ?></div>
          <div class="hero-stat-label"><?= t('stat_years') ?></div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ===== END HERO ===== -->


<!-- ===== LATEST NEWS ===== -->
<?php if (!empty($news)): ?>
<section class="section-py" style="background:var(--light-bg)">
  <div class="container">
    <div class="section-title">
      <span class="section-label"><i class="fas fa-newspaper me-1"></i><?= t('nav_news') ?></span>
      <h2><?= t('section_news') ?></h2>
      <div class="title-line"></div>
      <p><?= t('section_news_sub') ?></p>
    </div>
    <div class="row g-4">
      <?php foreach ($news as $article): ?>
      <div class="col-md-4">
        <a href="<?= websiteUrl('news/' . $article['id']) ?>" class="text-decoration-none">
          <div class="news-card">
            <?php if (!empty($article['image'])): ?>
            <img src="<?= e(BASE_URL . '/' . $article['image']) ?>"
                 alt="<?= e(Lang::field($article,'title')) ?>" class="news-img">
            <?php else: ?>
            <div class="news-img-placeholder"><i class="fas fa-newspaper"></i></div>
            <?php endif; ?>
            <div class="news-body">
              <span class="cat-tag <?= catClass($article['category'] ?? 'news') ?>">
                <?= t('cat_' . ($article['category'] ?? 'news')) ?>
              </span>
              <div class="card-title"><?= e(Lang::field($article,'title')) ?></div>
              <?php if ($ex = Lang::field($article,'excerpt')): ?>
              <div class="card-text"><?= e(mb_strimwidth($ex, 0, 110, '…')) ?></div>
              <?php endif; ?>
              <div class="news-meta">
                <span><i class="far fa-calendar-alt"></i>
                  <?= $article['published_at'] ? date('M j, Y', strtotime($article['published_at'])) : '' ?>
                </span>
                <?php if (!empty($article['views'])): ?>
                <span><i class="far fa-eye"></i> <?= number_format($article['views']) ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-5">
      <a href="<?= websiteUrl('news') ?>" class="btn-outline-primary-custom">
        <?= t('btn_view_all_news') ?> <i class="fas fa-arrow-right ms-1"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- ===== END LATEST NEWS ===== -->


<!-- ===== EVENTS + PRINCIPAL ===== -->
<section class="section-py">
  <div class="container">
    <div class="row g-5 align-items-start">

      <!-- Events timeline -->
      <div class="col-lg-6">
        <div class="section-title text-start mb-4">
          <span class="section-label"><i class="fas fa-calendar-alt me-1"></i><?= t('section_events') ?></span>
          <h2 class="mb-2"><?= t('section_events') ?></h2>
          <div class="title-line ms-0"></div>
        </div>
        <?php if (!empty($events)): ?>
        <?php foreach ($events as $ev): ?>
        <div class="event-card">
          <?php if (!empty($ev['published_at'])): ?>
          <div class="event-date-box">
            <div class="eday"><?= date('d', strtotime($ev['published_at'])) ?></div>
            <div class="emon"><?= date('M', strtotime($ev['published_at'])) ?></div>
          </div>
          <?php else: ?>
          <div class="event-date-box">
            <div class="eday"><i class="fas fa-calendar"></i></div>
            <div class="emon">TBD</div>
          </div>
          <?php endif; ?>
          <div>
            <div class="event-title"><?= e(Lang::field($ev,'title')) ?></div>
            <?php if ($exc = Lang::field($ev,'excerpt')): ?>
            <div class="event-meta"><?= e(mb_strimwidth($exc, 0, 80, '…')) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="mt-4">
          <a href="<?= websiteUrl('news') ?>?cat=event" class="btn-outline-primary-custom">
            <?= t('btn_view_all_news') ?> <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
        <?php else: ?>
        <div class="text-muted" style="padding:40px 0">
          <i class="fas fa-calendar-times fa-2x mb-3 d-block opacity-25"></i>
          <?= t('events_no_upcoming') ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Principal message pull-quote -->
      <?php if ($pName && $pMsg): ?>
      <div class="col-lg-6">
        <div class="section-title text-start mb-4">
          <span class="section-label"><i class="fas fa-user-tie me-1"></i><?= t('section_principal') ?></span>
          <h2 class="mb-2"><?= t('section_principal') ?></h2>
          <div class="title-line ms-0"></div>
        </div>
        <div class="principal-quote-wrap">
          <div class="principal-quote-mark">&ldquo;</div>
          <div class="principal-message">
            <?= e(mb_strimwidth($pMsg, 0, 420, '…')) ?>
          </div>
          <div class="principal-byline">
            <div class="principal-avatar">
              <?php if ($pPhoto): ?>
              <img src="<?= e(BASE_URL . '/' . $pPhoto) ?>" alt="<?= e($pName) ?>">
              <?php else: ?>
              <i class="fas fa-user-tie"></i>
              <?php endif; ?>
            </div>
            <div>
              <div class="principal-name"><?= e($pName) ?></div>
              <div class="principal-role"><?= e($pTitle) ?></div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</section>
<!-- ===== END EVENTS + PRINCIPAL ===== -->


<!-- ===== QUICK LINKS ===== -->
<div class="quick-link-row">
  <div class="container">
    <div class="row g-0">
      <div class="col-6 col-md-3">
        <a href="<?= websiteUrl('news') ?>?cat=exam" class="quick-link-item">
          <div class="quick-link-icon"><i class="fas fa-file-alt"></i></div>
          <div class="quick-link-label"><?= t('ql_timetable') ?></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="<?= websiteUrl('news') ?>?cat=academic" class="quick-link-item">
          <div class="quick-link-icon"><i class="fas fa-book-open"></i></div>
          <div class="quick-link-label"><?= t('ql_library') ?></div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="<?= url('login') ?>" class="quick-link-item">
          <div class="quick-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
          <div class="quick-link-label">Staff Portal</div>
        </a>
      </div>
      <div class="col-6 col-md-3">
        <a href="<?= url('login') ?>" class="quick-link-item">
          <div class="quick-link-icon"><i class="fas fa-user-graduate"></i></div>
          <div class="quick-link-label"><?= t('ql_student_portal') ?></div>
        </a>
      </div>
    </div>
  </div>
</div>
<!-- ===== END QUICK LINKS ===== -->


<!-- ===== GALLERY PREVIEW ===== -->
<section class="section-py" style="background:var(--light-bg)">
  <div class="container">
    <div class="section-title">
      <span class="section-label"><i class="fas fa-images me-1"></i><?= t('nav_gallery') ?></span>
      <h2><?= t('section_gallery') ?></h2>
      <div class="title-line"></div>
    </div>
    <div class="row g-3">
      <?php
      $galleryCategories = [
        ['icon'=>'school',     'label'=>t('gallery_school_life')  ?: 'School Life',   'cls'=>'gt-1'],
        ['icon'=>'running',    'label'=>t('gallery_sports')       ?: 'Sports',         'cls'=>'gt-2'],
        ['icon'=>'calendar',   'label'=>t('gallery_events')       ?: 'Events',         'cls'=>'gt-3'],
        ['icon'=>'graduation-cap','label'=>t('gallery_graduation')?: 'Graduation',     'cls'=>'gt-4'],
        ['icon'=>'trophy',     'label'=>t('gallery_competitions') ?: 'Competitions',   'cls'=>'gt-5'],
        ['icon'=>'images',     'label'=>t('gallery_photos')       ?: 'Photos',         'cls'=>'gt-6'],
      ];
      $galItems = array_values($gallery ?? []);
      for ($gi = 0; $gi < 6; $gi++):
        $catMeta = $galleryCategories[$gi] ?? $galleryCategories[5];
        if (!empty($galItems[$gi]['image'])):
      ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= websiteUrl('gallery') ?>" class="gallery-tile d-block">
          <img src="<?= e(BASE_URL . '/' . $galItems[$gi]['image']) ?>"
               alt="<?= e(Lang::field($galItems[$gi],'caption')) ?>">
          <div class="gt-overlay"><i class="fas fa-expand-alt"></i></div>
        </a>
      </div>
      <?php else: ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="<?= websiteUrl('gallery') ?>" class="gt-placeholder <?= $catMeta['cls'] ?> d-block">
          <i class="fas fa-<?= $catMeta['icon'] ?>"></i>
          <span><?= $catMeta['label'] ?></span>
        </a>
      </div>
      <?php
        endif;
      endfor;
      ?>
    </div>
    <div class="text-center mt-5">
      <a href="<?= websiteUrl('gallery') ?>" class="btn-primary-custom">
        <i class="fas fa-images me-2"></i><?= t('btn_view_gallery') ?>
      </a>
    </div>
  </div>
</section>
<!-- ===== END GALLERY PREVIEW ===== -->


<!-- ===== CTA BANNER ===== -->
<section class="cta-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7 text-center">
        <h2><?= e($ctaTitle) ?></h2>
        <p><?= e($ctaSub ?: t('section_cta')) ?></p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="<?= websiteUrl('contact') ?>" class="btn-accent-custom">
            <i class="fas fa-envelope me-2"></i><?= t('btn_contact_us') ?>
          </a>
          <a href="<?= websiteUrl('about') ?>" class="btn-outline-custom">
            <i class="fas fa-info-circle me-2"></i><?= t('btn_learn_more') ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- ===== END CTA ===== -->
