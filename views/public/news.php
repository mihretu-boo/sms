<?php $title = t('nav_news'); ?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1><?= t('section_news') ?></h1>
    <nav aria-label="breadcrumb" class="mt-2">
      <ol class="breadcrumb justify-content-center mb-0" style="background:transparent">
        <li class="breadcrumb-item"><a href="<?= websiteUrl() ?>"><?= t('nav_home') ?></a></li>
        <li class="breadcrumb-item active"><?= t('nav_news') ?></li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py">
  <div class="container">

    <!-- Category filter -->
    <?php if (!empty($categories)): ?>
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
      <a href="<?= websiteUrl('news') ?>"
         class="btn btn-sm <?= !$cat ? 'btn-primary' : 'btn-outline-secondary' ?>" style="<?= !$cat ? 'background:var(--primary);border-color:var(--primary)' : '' ?>">
        <?= t('news_all') ?>
      </a>
      <?php foreach ($categories as $c): ?>
      <a href="<?= websiteUrl('news?cat=' . urlencode($c)) ?>"
         class="btn btn-sm <?= $cat === $c ? 'btn-primary' : 'btn-outline-secondary' ?>" style="<?= $cat === $c ? 'background:var(--primary);border-color:var(--primary)' : '' ?>">
        <?= e(ucfirst($c)) ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($articles)): ?>
    <div class="text-center py-5">
      <i class="fas fa-newspaper text-muted" style="font-size:3rem;margin-bottom:16px;display:block"></i>
      <p class="text-muted"><?= t('news_no_items') ?></p>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($articles as $article): ?>
      <div class="col-md-6 col-lg-4">
        <div class="news-card">
          <?php if ($article['image'] && file_exists(ROOT . '/' . $article['image'])): ?>
          <img src="<?= uploadUrl($article['image']) ?>" class="news-img" alt="">
          <?php else: ?>
          <div class="news-img-placeholder"><i class="fas fa-newspaper"></i></div>
          <?php endif; ?>
          <div class="card-body">
            <div class="news-cat"><?= e(ucfirst($article['category'])) ?></div>
            <h5 class="card-title"><?= e(Lang::field($article, 'title')) ?></h5>
            <p class="card-text">
              <?= e(truncate(Lang::field($article, 'excerpt') ?: strip_tags(Lang::field($article, 'content')), 100)) ?>
            </p>
            <div class="news-meta">
              <i class="fas fa-calendar-alt"></i>
              <?= formatDate($article['published_at'] ?: $article['created_at']) ?>
            </div>
            <a href="<?= url('site/news/' . $article['id']) ?>" class="btn-primary-custom mt-3" style="font-size:.85rem;padding:7px 18px">
              <?= t('btn_read_more') ?>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5 d-flex justify-content-center">
      <ul class="pagination">
        <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="<?= websiteUrl('news?page=' . ($page - 1) . ($cat ? '&cat=' . urlencode($cat) : '')) ?>">
            &laquo;
          </a>
        </li>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= websiteUrl('news?page=' . $i . ($cat ? '&cat=' . urlencode($cat) : '')) ?>">
            <?= $i ?>
          </a>
        </li>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
          <a class="page-link" href="<?= websiteUrl('news?page=' . ($page + 1) . ($cat ? '&cat=' . urlencode($cat) : '')) ?>">
            &raquo;
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
