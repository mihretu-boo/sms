<?php $title = 'News Articles'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="fas fa-newspaper me-2 text-primary"></i>News Articles</h4>
    <div class="d-flex gap-2">
      <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> CMS
      </a>
      <a href="<?= url('website/news/create') ?>" class="btn btn-sm btn-primary">
        <i class="fas fa-plus me-1"></i> New Article
      </a>
    </div>
  </div>

  <?php foreach (['success','error'] as $type):
    if ($msg = \Flash::get($type)):
  ?>
  <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?> alert-dismissible">
    <?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; endforeach; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="newsTable">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Title (EN)</th>
              <th>Category</th>
              <th>Published</th>
              <th>Date</th>
              <th>Views</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($articles)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No articles yet. <a href="<?= url('website/news/create') ?>">Create the first one</a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($articles as $a): ?>
            <tr>
              <td><?= $a['id'] ?></td>
              <td>
                <div class="fw-semibold"><?= e(truncate($a['title_en'] ?? 'Untitled', 50)) ?></div>
                <?php if ($a['author_name']): ?>
                <small class="text-muted"><?= e($a['author_name']) ?></small>
                <?php endif; ?>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e(ucfirst($a['category'])) ?></span></td>
              <td>
                <?php if ($a['is_published']): ?>
                <span class="badge bg-success">Published</span>
                <?php else: ?>
                <span class="badge bg-secondary">Draft</span>
                <?php endif; ?>
              </td>
              <td><?= formatDate($a['published_at'] ?: $a['created_at']) ?></td>
              <td><?= (int)$a['views'] ?></td>
              <td class="text-end">
                <a href="<?= url('site/news/' . $a['id']) ?>" target="_blank"
                   class="btn btn-sm btn-outline-secondary" title="Preview">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="<?= url('website/news/edit/' . $a['id']) ?>"
                   class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <form method="POST" action="<?= url('website/news/delete/' . $a['id']) ?>"
                      class="d-inline" onsubmit="return confirm('Delete this article?')">
                  <?= csrfField() ?>
                  <button class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
      <small class="text-muted">Total: <?= $total ?> articles</small>
      <nav><?= paginationLinks(['current_page'=>$page,'total_pages'=>$totalPages,'total'=>$total,'per_page'=>15,'offset'=>0,'data'=>[]]) ?></nav>
    </div>
    <?php endif; ?>
  </div>
</div>
