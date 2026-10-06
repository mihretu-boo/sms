<?php $title = Lang::field($article, 'title'); ?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1 style="font-size:1.6rem"><?= e(Lang::field($article, 'title')) ?></h1>
    <nav aria-label="breadcrumb" class="mt-2">
      <ol class="breadcrumb justify-content-center mb-0" style="background:transparent">
        <li class="breadcrumb-item"><a href="<?= websiteUrl() ?>"><?= t('nav_home') ?></a></li>
        <li class="breadcrumb-item"><a href="<?= websiteUrl('news') ?>"><?= t('nav_news') ?></a></li>
        <li class="breadcrumb-item active"><?= e(truncate(Lang::field($article, 'title'), 40)) ?></li>
      </ol>
    </nav>
  </div>
</div>

<section class="section-py">
  <div class="container">
    <div class="row g-5">

      <!-- Main Article -->
      <div class="col-lg-8">
        <span class="cat-badge mb-3 d-inline-block"><?= e(ucfirst($article['category'])) ?></span>

        <h1 style="font-size:1.9rem;font-weight:800;color:#1a1a2e;margin-bottom:16px">
          <?= e(Lang::field($article, 'title')) ?>
        </h1>

        <div class="article-meta">
          <span><i class="fas fa-calendar-alt"></i><?= formatDate($article['published_at'] ?: $article['created_at']) ?></span>
          <span><i class="fas fa-eye"></i><?= (int)$article['views'] ?> views</span>
        </div>

        <?php if ($article['image'] && file_exists(ROOT . '/' . $article['image'])): ?>
        <img src="<?= uploadUrl($article['image']) ?>" class="article-img" alt="">
        <?php endif; ?>

        <div class="article-body">
          <?= Lang::field($article, 'content') ?: '<p>' . e(Lang::field($article,'excerpt')) . '</p>' ?>
        </div>

        <!-- Share -->
        <div class="article-share d-flex align-items-center gap-3 flex-wrap">
          <strong><?= t('footer_follow') ?>:</strong>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/site/news/' . $article['id']) ?>"
             target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
            <i class="fab fa-facebook-f me-1"></i> Facebook
          </a>
          <a href="https://t.me/share/url?url=<?= urlencode(BASE_URL . '/site/news/' . $article['id']) ?>"
             target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
            <i class="fab fa-telegram me-1"></i> Telegram
          </a>
        </div>

        <div class="mt-4">
          <a href="<?= websiteUrl('news') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i><?= t('news_back') ?>
          </a>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4">
        <!-- Related Articles -->
        <?php if (!empty($relatedArticles)): ?>
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header" style="background:var(--primary);color:#fff;font-weight:700">
            <i class="fas fa-layer-group me-2"></i> Related Articles
          </div>
          <div class="card-body p-0">
            <?php foreach ($relatedArticles as $rel): ?>
            <a href="<?= url('site/news/' . $rel['id']) ?>"
               class="d-flex gap-3 p-3 border-bottom text-decoration-none" style="color:inherit">
              <?php if ($rel['image'] && file_exists(ROOT . '/' . $rel['image'])): ?>
              <img src="<?= uploadUrl($rel['image']) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:4px;flex-shrink:0" alt="">
              <?php else: ?>
              <div style="width:64px;height:64px;background:#eee;border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-newspaper text-muted"></i>
              </div>
              <?php endif; ?>
              <div>
                <div style="font-size:.9rem;font-weight:600;color:#1a1a2e;line-height:1.3">
                  <?= e(truncate(Lang::field($rel,'title'), 60)) ?>
                </div>
                <div style="font-size:.8rem;color:#888;margin-top:4px">
                  <?= formatDate($rel['published_at'] ?: $rel['created_at']) ?>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- CTA Box -->
        <div style="background:linear-gradient(135deg,#1a6b3c,#144f2d);color:#fff;border-radius:8px;padding:24px;text-align:center">
          <i class="fas fa-graduation-cap" style="font-size:2.5rem;margin-bottom:12px;opacity:.7;display:block"></i>
          <h5 style="font-weight:800"><?= t('section_cta') ?></h5>
          <p style="font-size:.9rem;opacity:.85;margin:8px 0 16px"><?= t('cta_sub') ?></p>
          <a href="<?= websiteUrl('contact') ?>" class="btn-accent-custom"><?= t('btn_contact_us') ?></a>
        </div>
      </div>

    </div>
  </div>
</section>
