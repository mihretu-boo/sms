<?php $title = 'Homepage Sliders'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="fas fa-images me-2 text-info"></i>Homepage Sliders</h4>
    <a href="<?= url('website') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> CMS Dashboard
    </a>
  </div>

  <?php foreach (['success','error'] as $t): if ($m = \Flash::get($t)): ?>
  <div class="alert alert-<?= $t==='error'?'danger':'success' ?> alert-dismissible">
    <?= e($m) ?><button class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; endforeach; ?>

  <div class="row g-4">
    <!-- Upload form -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Add Slider</div>
        <div class="card-body">
          <form method="POST" action="<?= url('website/sliders/upload') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="mb-3">
              <label class="form-label fw-semibold">Background Image <span class="text-danger">*</span></label>
              <input type="file" name="image" class="form-control" accept="image/*" required>
              <small class="text-muted">Recommended: 1920×580px or similar wide format</small>
            </div>
            <hr>
            <div class="mb-2">
              <label class="form-label fw-semibold">Title (EN)</label>
              <input type="text" name="title_en" class="form-control" placeholder="English title">
            </div>
            <div class="mb-2">
              <label class="form-label fw-semibold">Subtitle (EN)</label>
              <input type="text" name="subtitle_en" class="form-control" placeholder="English subtitle">
            </div>
            <hr>
            <div class="mb-2">
              <label class="form-label fw-semibold">Title (OM)</label>
              <input type="text" name="title_om" class="form-control" placeholder="Afaan Oromoo">
            </div>
            <div class="mb-2">
              <label class="form-label fw-semibold">Subtitle (OM)</label>
              <input type="text" name="subtitle_om" class="form-control" placeholder="Afaan Oromoo">
            </div>
            <hr>
            <div class="mb-2">
              <label class="form-label fw-semibold">Title (አማ)</label>
              <input type="text" name="title_am" class="form-control" placeholder="አማርኛ">
            </div>
            <div class="mb-2">
              <label class="form-label fw-semibold">Subtitle (አማ)</label>
              <input type="text" name="subtitle_am" class="form-control" placeholder="አማርኛ">
            </div>
            <hr>
            <div class="mb-3">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?= count($sliders) + 1 ?>" min="0">
            </div>
            <button type="submit" class="btn btn-info text-white w-100">
              <i class="fas fa-upload me-1"></i> Add Slider
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Slider list -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Current Sliders (<?= count($sliders) ?>)</div>
        <div class="card-body">
          <?php if (empty($sliders)): ?>
          <div class="text-center text-muted py-5">
            <i class="fas fa-images" style="font-size:3rem;margin-bottom:12px;display:block"></i>
            No sliders yet.
          </div>
          <?php else: ?>
          <div class="d-flex flex-column gap-3">
            <?php foreach ($sliders as $s): ?>
            <div class="border rounded p-3 <?= $s['is_active'] ? '' : 'opacity-50' ?>">
              <div class="row align-items-center g-3">
                <div class="col-auto">
                  <img src="<?= uploadUrl($s['image']) ?>"
                       style="width:100px;height:56px;object-fit:cover;border-radius:4px" alt="">
                </div>
                <div class="col">
                  <div class="fw-semibold"><?= e($s['title_en'] ?: 'Untitled') ?></div>
                  <div class="text-muted small"><?= e(truncate($s['subtitle_en'] ?? '', 60)) ?></div>
                  <small class="text-muted">Order: <?= $s['sort_order'] ?></small>
                </div>
                <div class="col-auto d-flex gap-2">
                  <a href="<?= url('website/sliders/edit/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary"
                     title="Edit slider" aria-label="Edit slider">
                    <i class="fas fa-edit"></i>
                  </a>
                  <form method="POST" action="<?= url('website/sliders/toggle/' . $s['id']) ?>">
                    <?= csrfField() ?>
                    <button class="btn btn-sm <?= $s['is_active'] ? 'btn-success' : 'btn-outline-secondary' ?>"
                            title="<?= $s['is_active'] ? 'Active - click to hide' : 'Hidden - click to show' ?>">
                      <i class="fas fa-<?= $s['is_active'] ? 'eye' : 'eye-slash' ?>"></i>
                    </button>
                  </form>
                  <form method="POST" action="<?= url('website/sliders/delete/' . $s['id']) ?>"
                        onsubmit="return confirm('Delete slider?')">
                    <?= csrfField() ?>
                    <button class="btn btn-sm btn-outline-danger">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
