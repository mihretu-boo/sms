<?php
$title    = t('nav_about');
$lang     = Lang::current();
$ctaTitle = getWebsiteSetting('cta_title_' . $lang, getWebsiteSetting('cta_title_en', 'Ready to Join Our School?'));
$ctaSub   = getWebsiteSetting('cta_subtitle_' . $lang, getWebsiteSetting('cta_subtitle_en', ''));
$history  = getWebsiteSetting('school_history_' . $lang, getWebsiteSetting('school_history_en',''));
$vision   = getWebsiteSetting('school_vision_'  . $lang, getWebsiteSetting('school_vision_en', ''));
$mission  = getWebsiteSetting('school_mission_' . $lang, getWebsiteSetting('school_mission_en',''));
$founded  = getWebsiteSetting('school_founded','1999');
$pName    = getWebsiteSetting('principal_name','');
$pTitle   = getWebsiteSetting('principal_title_' . $lang, getWebsiteSetting('principal_title_en','Principal'));
$pPhoto   = getWebsiteSetting('principal_photo','');
$vpName   = getWebsiteSetting('vice_principal_name','');
$vpTitle  = getWebsiteSetting('vice_principal_title_' . $lang, getWebsiteSetting('vice_principal_title_en','Vice Principal'));
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header">
  <div class="container">
    <h1><?= e(Lang::field($aboutPage,'title') ?: t('section_about')) ?></h1>
    <p><?= e(getWebsiteSetting('tagline_'.$lang, getWebsiteSetting('tagline_en',''))) ?></p>
    <nav aria-label="breadcrumb" class="mt-2">
      <ol class="breadcrumb justify-content-center mb-0" style="background:transparent">
        <li class="breadcrumb-item"><a href="<?= websiteUrl() ?>"><?= t('nav_home') ?></a></li>
        <li class="breadcrumb-item active"><?= t('nav_about') ?></li>
      </ol>
    </nav>
  </div>
</div>

<!-- ===== SCHOOL HISTORY ===== -->
<?php if ($history): ?>
<section class="history-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div style="color:var(--accent);font-weight:700;font-size:.85rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">
          <i class="fas fa-landmark me-1"></i><?= t('about_history') ?>
        </div>
        <h2 style="font-size:1.9rem;font-weight:800;color:#1a1a2e;margin-bottom:20px"><?= t('about_history_detail') ?></h2>
        <div class="history-timeline">
          <div class="history-item">
            <div class="history-dot"></div>
            <div class="history-year"><?= t('about_founded') ?> — <?= e($founded) ?> E.C.</div>
            <p class="history-text"><?= e($history) ?></p>
          </div>
          <div class="history-item">
            <div class="history-dot"></div>
            <div class="history-year"><?= $lang==='om'?'Har\'aa':'Today' ?></div>
            <p class="history-text">
              <?php if ($lang==='om'): ?>Barattootni <?= e(getWebsiteSetting('stat_students','1200')) ?>+ fi barsiisotni <?= e(getWebsiteSetting('stat_teachers','65')) ?>+ wajjin, manni barumsaan guddina barnootaa ol-aanaa naannoo keessatti itti fufee jira.
              <?php elseif ($lang==='am'): ?>ከ<?= e(getWebsiteSetting('stat_students','1200')) ?>+ ተማሪዎች እና <?= e(getWebsiteSetting('stat_teachers','65')) ?>+ መምህራን ጋር ትምህርት ቤቱ በክልሉ ውስጥ የትምህርት ልህቀት አዕምሮ ሆኖ ቀጥሏል።
              <?php else: ?>With <?= e(getWebsiteSetting('stat_students','1200')) ?>+ students and <?= e(getWebsiteSetting('stat_teachers','65')) ?>+ teachers, the school continues to be a pillar of educational excellence in the region.<?php endif; ?>
            </p>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="row g-3">
          <?php
          $milestones = [
            ['icon'=>'graduation-cap','num'=>getWebsiteSetting('stat_students','1200').'+','lbl'=>t('stat_students'),'color'=>'#1a6b3c'],
            ['icon'=>'chalkboard-teacher','num'=>getWebsiteSetting('stat_teachers','65').'+','lbl'=>t('stat_teachers'),'color'=>'#e8a020'],
            ['icon'=>'award','num'=>getWebsiteSetting('stat_years','25').'+','lbl'=>t('stat_years'),'color'=>'#1565C0'],
            ['icon'=>'door-open','num'=>getWebsiteSetting('stat_classes','32').'+','lbl'=>t('stat_classes'),'color'=>'#7B1FA2'],
          ];
          foreach ($milestones as $m): ?>
          <div class="col-6">
            <div style="background:#fff;border-radius:10px;padding:28px 20px;text-align:center;border:1px solid var(--border)">
              <div style="width:54px;height:54px;border-radius:12px;background:<?= $m['color'] ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin:0 auto 12px">
                <i class="fas fa-<?= $m['icon'] ?>"></i>
              </div>
              <div style="font-size:1.8rem;font-weight:800;color:<?= $m['color'] ?>"><?= e($m['num']) ?></div>
              <div style="font-size:.88rem;color:var(--muted);margin-top:4px"><?= $m['lbl'] ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== VISION & MISSION ===== -->
<?php if ($vision || $mission): ?>
<section class="section-py bg-white">
  <div class="container">
    <div class="section-title">
      <h2><?= t('section_vision_mission') ?></h2>
      <div class="title-line"></div>
    </div>
    <div class="row g-4">
      <?php if ($vision): ?>
      <div class="col-md-6">
        <div class="vm-card vm-card-vision h-100">
          <div class="vm-card-icon"><i class="fas fa-eye"></i></div>
          <h3><?= t('about_vision') ?></h3>
          <p><?= e($vision) ?></p>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($mission): ?>
      <div class="col-md-6">
        <div class="vm-card vm-card-mission h-100">
          <div class="vm-card-icon"><i class="fas fa-bullseye"></i></div>
          <h3><?= t('about_mission') ?></h3>
          <p><?= e($mission) ?></p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== ABOUT CONTENT (from DB) ===== -->
<?php if (!empty($aboutPage) && Lang::field($aboutPage,'content')): ?>
<section class="section-py" style="background:#f8f9fa">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">
        <div class="article-body">
          <?= Lang::field($aboutPage,'content') ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== CORE VALUES ===== -->
<section class="section-py <?= !empty($aboutPage) && Lang::field($aboutPage,'content') ? 'bg-white' : '' ?>" style="<?= empty($aboutPage) || !Lang::field($aboutPage,'content') ? 'background:#f8f9fa' : '' ?>">
  <div class="container">
    <div class="section-title">
      <h2><?= t('about_values') ?></h2>
      <div class="title-line"></div>
    </div>
    <?php
    $values = [];
    for ($i = 1; $i <= 6; $i++) {
        $name = getWebsiteSetting("cv{$i}_name_{$lang}", getWebsiteSetting("cv{$i}_name_en", ''));
        $desc = getWebsiteSetting("cv{$i}_desc_{$lang}", getWebsiteSetting("cv{$i}_desc_en", ''));
        $icon = getWebsiteSetting("cv{$i}_icon", 'star');
        if ($name) $values[] = compact('icon','name','desc');
    }
    ?>
    <div class="row g-4">
      <?php foreach ($values as $v): ?>
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon"><i class="fas fa-<?= e($v['icon']) ?>"></i></div>
          <h5><?= e($v['name']) ?></h5>
          <p><?= e($v['desc']) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== ADMINISTRATION ===== -->
<?php if ($pName || $vpName): ?>
<section class="section-py bg-white">
  <div class="container">
    <div class="section-title">
      <h2><?= t('about_administration') ?></h2>
      <div class="title-line"></div>
    </div>
    <div class="row g-4 justify-content-center">
      <?php if ($pName): ?>
      <div class="col-md-6 col-lg-4">
        <div class="admin-card">
          <div class="admin-avatar">
            <?php if ($pPhoto): ?>
            <img src="<?= e(BASE_URL . '/' . $pPhoto) ?>" alt="<?= e($pName) ?>">
            <?php else: ?>
            <i class="fas fa-user-tie"></i>
            <?php endif; ?>
          </div>
          <div class="admin-name"><?= e($pName) ?></div>
          <div class="admin-role"><?= e($pTitle) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($vpName): ?>
      <div class="col-md-6 col-lg-4">
        <div class="admin-card">
          <div class="admin-avatar" style="background:linear-gradient(135deg,var(--accent),var(--accent-dk))">
            <i class="fas fa-user-tie"></i>
          </div>
          <div class="admin-name"><?= e($vpName) ?></div>
          <div class="admin-role"><?= e($vpTitle) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== DEPARTMENTS ===== -->
<?php if (!empty($departments)): ?>
<section class="section-py" style="background:#f8f9fa">
  <div class="container">
    <div class="section-title">
      <h2><?= t('about_departments') ?></h2>
      <div class="title-line"></div>
    </div>
    <div class="row g-3">
      <?php
      $deptIconList = ['flask','globe-africa','calculator','language','running','laptop-code','microscope','book'];
      foreach ($departments as $idx => $dept): ?>
      <div class="col-md-6 col-lg-4">
        <div class="dept-card">
          <div class="dept-icon"><i class="fas fa-<?= $deptIconList[$idx % count($deptIconList)] ?>"></i></div>
          <div class="dept-name"><?= e($dept) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== STATS BAR ===== -->
<div class="stats-bar">
  <div class="container">
    <div class="row text-center g-0">
      <?php
      $stats = [
        ['key'=>'stat_students','setting'=>'stat_students','icon'=>'users'],
        ['key'=>'stat_teachers','setting'=>'stat_teachers','icon'=>'chalkboard-teacher'],
        ['key'=>'stat_years',   'setting'=>'stat_years',   'icon'=>'award'],
        ['key'=>'stat_classes', 'setting'=>'stat_classes', 'icon'=>'door-open'],
      ];
      foreach ($stats as $i => $s): ?>
      <div class="col-6 col-md-3 stat-item py-2 <?= $i > 0 ? 'stat-divider' : '' ?>">
        <div class="stat-number"><?= e(getWebsiteSetting($s['setting'],'0')) ?>+</div>
        <div class="stat-label"><?= t($s['key']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ===== CTA ===== -->
<section class="cta-section">
  <div class="container">
    <h2><?= e($ctaTitle) ?></h2>
    <p><?= e($ctaSub ?: t('section_cta')) ?></p>
    <a href="<?= websiteUrl('contact') ?>" class="btn-accent-custom me-3"><?= t('btn_contact_us') ?></a>
    <a href="<?= websiteUrl('news') ?>"    class="btn-outline-custom"><?= t('nav_news') ?></a>
  </div>
</section>
