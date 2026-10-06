<?php $title = t('nav_gallery'); $lang = Lang::current(); ?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1><?= t('section_gallery') ?></h1>
    <nav aria-label="breadcrumb" class="mt-2">
      <ol class="breadcrumb justify-content-center mb-0" style="background:transparent">
        <li class="breadcrumb-item"><a href="<?= websiteUrl() ?>"><?= t('nav_home') ?></a></li>
        <li class="breadcrumb-item active"><?= t('nav_gallery') ?></li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py">
  <div class="container">

    <!-- Photo / Video Tabs -->
    <div class="d-flex justify-content-center gap-3 mb-5">
      <a href="<?= websiteUrl('gallery' . ($cat ? '?cat=' . $cat : '')) ?>"
         class="btn btn-<?= $mediaType === 'photo' ? 'primary' : 'outline-secondary' ?>" style="min-width:120px">
        <i class="fas fa-images me-2"></i><?= t('gallery_photos') ?>
      </a>
      <?php if ($videoCount > 0): ?>
      <a href="<?= websiteUrl('gallery?media=video' . ($cat ? '&cat=' . $cat : '')) ?>"
         class="btn btn-<?= $mediaType === 'video' ? 'primary' : 'outline-secondary' ?>" style="min-width:120px">
        <i class="fas fa-play-circle me-2"></i><?= t('gallery_videos') ?>
        <span class="badge bg-danger ms-1" style="font-size:.7rem"><?= $videoCount ?></span>
      </a>
      <?php endif; ?>
    </div>

    <!-- Category Filter -->
    <?php if (!empty($categories) && $mediaType === 'photo'): ?>
    <div class="gallery-filter text-center mb-5">
      <a href="<?= websiteUrl('gallery') ?>"
         class="btn btn-sm btn-outline-secondary <?= !$cat ? 'active' : '' ?>">
        <?= t('gallery_all') ?>
      </a>
      <?php foreach ($categories as $c): ?>
      <a href="<?= websiteUrl('gallery?cat=' . $c['id']) ?>"
         class="btn btn-sm btn-outline-secondary <?= $cat == $c['id'] ? 'active' : '' ?>">
        <?= e(Lang::field($c,'name')) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($mediaType === 'video'): ?>
    <!-- ===== VIDEO GRID ===== -->
    <?php if (empty($images)): ?>
    <div class="text-center py-5">
      <i class="fas fa-video-slash text-muted" style="font-size:3rem;margin-bottom:16px;display:block"></i>
      <p class="text-muted"><?= $lang==='om' ? 'Yeroo ammaa viidiyoolee hin jiran.' : ($lang==='am' ? 'እስካሁን ቪዲዮዎች የሉም።' : 'No videos available yet.') ?></p>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($images as $img): ?>
      <?php
      $vidUrl = $img['video_url'] ?? '';
      $ytId   = '';
      if ($vidUrl && preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/', $vidUrl, $m)) {
          $ytId = $m[1];
      }
      ?>
      <div class="col-md-6 col-lg-4">
        <?php if ($ytId): ?>
        <div class="video-item" data-bs-toggle="modal" data-bs-target="#videoModal" data-ytid="<?= e($ytId) ?>">
          <img src="https://img.youtube.com/vi/<?= e($ytId) ?>/hqdefault.jpg" alt="<?= e(Lang::field($img,'caption')) ?>" loading="lazy">
          <div class="video-play-btn"><i class="fas fa-play-circle"></i></div>
        </div>
        <?php else: ?>
        <div class="video-item">
          <img src="<?= uploadUrl($img['image']) ?>" alt="<?= e(Lang::field($img,'caption')) ?>" loading="lazy">
          <div class="video-play-btn"><i class="fas fa-play-circle"></i></div>
        </div>
        <?php endif; ?>
        <?php if (Lang::field($img,'caption')): ?>
        <div class="text-center mt-2" style="font-size:.83rem;color:#666"><?= e(Lang::field($img,'caption')) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- YouTube Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-black border-0">
          <div class="modal-header border-0 pb-0">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0">
            <div class="ratio ratio-16x9">
              <iframe id="ytFrame" src="" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
    document.getElementById('videoModal').addEventListener('show.bs.modal', function(e) {
      var ytId = e.relatedTarget.dataset.ytid;
      document.getElementById('ytFrame').src = 'https://www.youtube.com/embed/' + ytId + '?autoplay=1';
    });
    document.getElementById('videoModal').addEventListener('hide.bs.modal', function() {
      document.getElementById('ytFrame').src = '';
    });
    </script>

    <?php else: ?>
    <!-- ===== PHOTO GRID ===== -->
    <?php if (empty($images)): ?>
    <div class="text-center py-5">
      <i class="fas fa-images text-muted" style="font-size:3rem;margin-bottom:16px;display:block"></i>
      <p class="text-muted"><?= t('gallery_no_items') ?></p>
    </div>
    <?php else: ?>
    <div class="row g-3">
      <?php foreach ($images as $img): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="gallery-item"
             data-src="<?= uploadUrl($img['image']) ?>"
             data-cat="<?= (int)$img['category_id'] ?>">
          <img src="<?= uploadUrl($img['image']) ?>"
               alt="<?= e(Lang::field($img,'caption')) ?>"
               loading="lazy">
          <div class="gallery-overlay">
            <i class="fas fa-expand-arrows-alt"></i>
          </div>
        </div>
        <?php if (Lang::field($img,'caption')): ?>
        <div class="text-center mt-1" style="font-size:.82rem;color:#666"><?= e(Lang::field($img,'caption')) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</section>
