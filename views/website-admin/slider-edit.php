<?php $title = 'Edit Slider'; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0"><i class="fas fa-edit me-2 text-info"></i>Edit Slider</h4>
    <a href="<?= url('website/sliders') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Sliders
    </a>
  </div>

  <form method="POST" action="<?= url('website/sliders/edit/' . $slider['id']) ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">Slider Content</div>
          <div class="card-body">
            <?php foreach ([
              'en' => ['English', 'English title', 'English subtitle'],
              'om' => ['Afaan Oromoo', 'Afaan Oromoo title', 'Afaan Oromoo subtitle'],
              'am' => ['Amharic', 'Amharic title', 'Amharic subtitle'],
            ] as $lang => $labels): ?>
            <section<?= $lang === 'en' ? '' : ' class="mt-4 pt-3 border-top"' ?>>
              <h6 class="fw-bold mb-3"><?= $labels[0] ?></h6>
              <div class="mb-3">
                <label class="form-label fw-semibold" for="title_<?= $lang ?>">Title</label>
                <input id="title_<?= $lang ?>" type="text" name="title_<?= $lang ?>" class="form-control"
                       value="<?= e($slider['title_' . $lang] ?? '') ?>" placeholder="<?= e($labels[1]) ?>">
              </div>
              <div>
                <label class="form-label fw-semibold" for="subtitle_<?= $lang ?>">Subtitle</label>
                <input id="subtitle_<?= $lang ?>" type="text" name="subtitle_<?= $lang ?>" class="form-control"
                       value="<?= e($slider['subtitle_' . $lang] ?? '') ?>" placeholder="<?= e($labels[2]) ?>">
              </div>
            </section>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white fw-bold">Background Image</div>
          <div class="card-body">
            <img src="<?= uploadUrl($slider['image']) ?>" class="img-fluid rounded mb-3 w-100" alt="Current slider image">
            <label class="form-label fw-semibold" for="image">Replace image</label>
            <input id="image" type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Leave empty to keep the current image. Recommended: 1920 × 580px.</small>
          </div>
        </div>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <label class="form-label fw-semibold" for="sort_order">Sort Order</label>
            <input id="sort_order" type="number" name="sort_order" class="form-control"
                   value="<?= (int)$slider['sort_order'] ?>" min="0">
            <small class="text-muted">Lower numbers appear first.</small>
          </div>
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-info text-white"><i class="fas fa-save me-1"></i> Save Changes</button>
          <a href="<?= url('website/sliders') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>
    </div>
  </form>
</div>
