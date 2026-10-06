<?php $title = 'Photo Gallery'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="fas fa-images me-2 text-success"></i>Photo Gallery</h4>
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
    <!-- Upload Form -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-bold">Upload New Image</div>
        <div class="card-body">
          <form method="POST" action="<?= url('website/gallery/upload') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="mb-3">
              <label class="form-label fw-semibold">Image <span class="text-danger">*</span></label>
              <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Caption (EN)</label>
              <input type="text" name="caption_en" class="form-control" placeholder="English caption">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Caption (OM)</label>
              <input type="text" name="caption_om" class="form-control" placeholder="Afaan Oromoo">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Caption (አማ)</label>
              <input type="text" name="caption_am" class="form-control" placeholder="አማርኛ">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Category</label>
              <select name="category_id" class="form-select">
                <option value="">-- None --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= e($cat['name_en']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="0" min="0">
            </div>
            <button type="submit" class="btn btn-success w-100">
              <i class="fas fa-upload me-1"></i> Upload Image
            </button>
          </form>
        </div>
      </div>

      <!-- Add Category -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Add Category</div>
        <div class="card-body">
          <form method="POST" action="<?= url('website/gallery/categories') ?>">
            <?= csrfField() ?>
            <div class="mb-2">
              <input type="text" name="name_en" class="form-control form-control-sm" placeholder="English name" required>
            </div>
            <div class="mb-2">
              <input type="text" name="name_om" class="form-control form-control-sm" placeholder="Afaan Oromoo">
            </div>
            <div class="mb-2">
              <input type="text" name="name_am" class="form-control form-control-sm" placeholder="አማርኛ">
            </div>
            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
              <i class="fas fa-plus me-1"></i> Add Category
            </button>
          </form>
          <?php if (!empty($categories)): ?>
          <hr>
          <small class="text-muted d-block mb-1">Existing Categories:</small>
          <?php foreach ($categories as $c): ?>
          <span class="badge bg-light text-dark border me-1 mb-1"><?= e($c['name_en']) ?></span>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Gallery Grid -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
          <span class="fw-bold">Gallery Images</span>
          <span class="badge bg-secondary ms-2"><?= count($images) ?> images</span>
        </div>
        <div class="card-body">
          <?php if (empty($images)): ?>
          <div class="text-center text-muted py-5">
            <i class="fas fa-images" style="font-size:3rem;margin-bottom:12px;display:block"></i>
            No images uploaded yet.
          </div>
          <?php else: ?>
          <div class="row g-3">
            <?php foreach ($images as $img): ?>
            <div class="col-6 col-md-4">
              <div class="position-relative">
                <img src="<?= uploadUrl($img['image']) ?>"
                     style="width:100%;height:140px;object-fit:cover;border-radius:6px" alt="">
                <div class="position-absolute top-0 end-0 p-1">
                  <form method="POST" action="<?= url('website/gallery/delete/' . $img['id']) ?>"
                        onsubmit="return confirm('Delete image?')">
                    <?= csrfField() ?>
                    <button class="btn btn-danger btn-sm" title="Delete">
                      <i class="fas fa-trash" style="font-size:.75rem"></i>
                    </button>
                  </form>
                </div>
                <?php if ($img['cat_name']): ?>
                <div class="position-absolute bottom-0 start-0 p-1">
                  <span class="badge bg-dark" style="font-size:.7rem"><?= e($img['cat_name']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($img['caption_en']): ?>
                <div class="mt-1 text-muted" style="font-size:.78rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                  <?= e($img['caption_en']) ?>
                </div>
                <?php endif; ?>
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
