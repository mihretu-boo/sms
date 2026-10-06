<?php $title = 'Website Pages'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-file-alt me-2 text-warning"></i>Website Pages</h4>
    <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> CMS Dashboard
    </a>
  </div>

  <?php if ($flash = \Flash::get('success')): ?>
  <div class="alert alert-success alert-dismissible"><i class="fas fa-check-circle me-2"></i><?= e($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($pages as $page): ?>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white">
          <strong class="text-capitalize"><?= e(ucfirst($page['slug'])) ?> Page</strong>
          <span class="badge bg-success ms-2">Published</span>
        </div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-3">
            <tr>
              <td class="text-muted" style="width:80px">EN</td>
              <td><?= e(truncate($page['title_en'] ?? '', 60)) ?></td>
            </tr>
            <tr>
              <td class="text-muted">OM</td>
              <td><?= e(truncate($page['title_om'] ?? '', 60)) ?></td>
            </tr>
            <tr>
              <td class="text-muted">አማ</td>
              <td><?= e(truncate($page['title_am'] ?? '', 60)) ?></td>
            </tr>
          </table>
          <div class="text-muted small mb-3">
            <i class="fas fa-clock me-1"></i>Updated: <?= formatDate($page['updated_at'], 'd M Y H:i') ?>
          </div>
          <div class="d-flex gap-2">
            <a href="<?= url('website/pages/edit/' . $page['slug']) ?>" class="btn btn-primary btn-sm">
              <i class="fas fa-edit me-1"></i> Edit Content
            </a>
            <a href="<?= websiteUrl($page['slug'] === 'home' ? '' : $page['slug']) ?>" target="_blank"
               class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-eye me-1"></i> Preview
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
