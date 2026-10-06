<?php $title = 'Website CMS'; ?>

<div class="container-fluid px-4 py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h4 class="mb-1 fw-bold"><i class="fas fa-globe me-2 text-primary"></i>Website CMS</h4>
      <p class="text-muted mb-0 small">Manage your public website content</p>
    </div>
    <a href="<?= websiteUrl() ?>" target="_blank" class="btn btn-outline-primary btn-sm">
      <i class="fas fa-external-link-alt me-1"></i> View Public Site
    </a>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <?php
    $cards = [
      ['label'=>'News Articles',       'value'=>$stats['news'],     'icon'=>'newspaper',   'color'=>'primary'],
      ['label'=>'Gallery Images',       'value'=>$stats['gallery'],  'icon'=>'images',       'color'=>'success'],
      ['label'=>'Contact Messages',     'value'=>$stats['messages'], 'icon'=>'envelope',     'color'=>'info'],
      ['label'=>'Unread Messages',      'value'=>$stats['unread'],   'icon'=>'bell',         'color'=>'warning'],
      ['label'=>'Active Sliders',       'value'=>$stats['sliders'],  'icon'=>'images',       'color'=>'secondary'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="col-6 col-md-4 col-lg-2-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center py-4">
          <div class="mb-2">
            <span class="text-<?= $c['color'] ?>" style="font-size:1.8rem"><i class="fas fa-<?= $c['icon'] ?>"></i></span>
          </div>
          <div class="fw-bold" style="font-size:1.6rem;color:#1a1a2e"><?= $c['value'] ?></div>
          <div class="text-muted small"><?= $c['label'] ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick Actions -->
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Quick Actions</div>
        <div class="card-body">
          <div class="row g-2">
            <div class="col-6 col-md-4 col-lg-2">
              <a href="<?= url('website/home-content') ?>" class="btn btn-outline-primary btn-sm w-100 py-2">
                <i class="fas fa-home d-block mb-1" style="font-size:1.3rem"></i>Home Page
              </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <a href="<?= url('website/principal') ?>" class="btn btn-outline-info btn-sm w-100 py-2">
                <i class="fas fa-user-tie d-block mb-1" style="font-size:1.3rem"></i>Principal
              </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <a href="<?= url('website/about-content') ?>" class="btn btn-outline-success btn-sm w-100 py-2">
                <i class="fas fa-school d-block mb-1" style="font-size:1.3rem"></i>About Page
              </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <a href="<?= url('website/news/create') ?>" class="btn btn-outline-warning btn-sm w-100 py-2">
                <i class="fas fa-newspaper d-block mb-1" style="font-size:1.3rem"></i>New Article
              </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <a href="<?= url('website/gallery') ?>" class="btn btn-outline-secondary btn-sm w-100 py-2">
                <i class="fas fa-images d-block mb-1" style="font-size:1.3rem"></i>Gallery
              </a>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
              <a href="<?= url('website/settings') ?>" class="btn btn-outline-dark btn-sm w-100 py-2">
                <i class="fas fa-cog d-block mb-1" style="font-size:1.3rem"></i>Contact & Social
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Messages -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-bold"><i class="fas fa-envelope me-2 text-info"></i>Recent Contact Messages</span>
      <a href="<?= url('website/contact-messages') ?>" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
      <?php if (empty($recent)): ?>
      <p class="text-muted text-center py-4">No messages yet.</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $msg): ?>
            <tr>
              <td class="fw-semibold"><?= e($msg['name']) ?></td>
              <td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td>
              <td><?= e(truncate($msg['subject'], 40)) ?></td>
              <td><?= formatDate($msg['created_at'], 'd M Y H:i') ?></td>
              <td>
                <?php if ($msg['is_read']): ?>
                <span class="badge bg-secondary">Read</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark">New</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<style>.col-lg-2-4 { width: 20%; } @media(max-width:992px){.col-lg-2-4{width:50%}} @media(max-width:576px){.col-lg-2-4{width:100%}}</style>
