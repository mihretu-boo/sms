<?php $title = $article ? 'Edit Article' : 'New Article'; $isEdit = (bool)$article; ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">
      <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> me-2 text-primary"></i>
      <?= $isEdit ? 'Edit Article' : 'New Article' ?>
    </h4>
    <a href="<?= url('website/news') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back to Articles
    </a>
  </div>

  <form method="POST" action="<?= e($formAction) ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-4">

      <!-- Left: Content Tabs -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" id="langTabs">
              <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-en">
                English <span class="badge bg-danger ms-1">Required</span>
              </a></li>
              <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-om">Afaan Oromoo</a></li>
              <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-am">አማርኛ</a></li>
            </ul>
          </div>
          <div class="card-body tab-content">
            <?php
            $langs = [
              'en' => ['label_title'=>'Title', 'label_excerpt'=>'Excerpt / Short Description', 'label_content'=>'Full Content'],
              'om' => ['label_title'=>'Mata-Duree', 'label_excerpt'=>'Gabaabina', 'label_content'=>'Qabiyyee Guutuu'],
              'am' => ['label_title'=>'ርዕስ', 'label_excerpt'=>'አጭር መግለጫ', 'label_content'=>'ሙሉ ይዘት'],
            ];
            foreach ($langs as $code => $lbl):
              $active = $code === 'en' ? 'show active' : '';
            ?>
            <div class="tab-pane fade <?= $active ?>" id="tab-<?= $code ?>">
              <div class="mb-3">
                <label class="form-label fw-semibold"><?= $lbl['label_title'] ?></label>
                <input type="text" name="title_<?= $code ?>" class="form-control"
                       value="<?= e($article['title_' . $code] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold"><?= $lbl['label_excerpt'] ?></label>
                <textarea name="excerpt_<?= $code ?>" class="form-control" rows="3"><?= e($article['excerpt_' . $code] ?? '') ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold"><?= $lbl['label_content'] ?></label>
                <textarea name="content_<?= $code ?>" class="form-control" rows="15"><?= $article['content_' . $code] ?? '' ?></textarea>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Right: Meta -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white fw-bold">Publish Settings</div>
          <div class="card-body">
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_published" value="1"
                       id="is_published" <?= ($article['is_published'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="is_published">Published</label>
              </div>
              <small class="text-muted">Uncheck to save as draft</small>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Publish Date</label>
              <input type="date" name="published_at" class="form-control"
                     value="<?= e($article['published_at'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Category</label>
              <select name="category" class="form-select">
                <?php
                $cats = ['news'=>'News','event'=>'Event','announcement'=>'Announcement','achievement'=>'Achievement','sport'=>'Sport'];
                foreach ($cats as $val => $lbl):
                ?>
                <option value="<?= $val ?>" <?= selected($article['category'] ?? 'news', $val) ?>>
                  <?= $lbl ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white fw-bold">Featured Image</div>
          <div class="card-body">
            <?php if (!empty($article['image']) && file_exists(ROOT . '/' . $article['image'])): ?>
            <img src="<?= uploadUrl($article['image']) ?>" class="img-fluid rounded mb-3" alt="">
            <?php endif; ?>
            <input type="file" name="image" class="form-control" accept="image/*">
            <small class="text-muted">JPG, PNG, WebP. Max 10MB.</small>
          </div>
        </div>

        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> <?= $isEdit ? 'Update Article' : 'Create Article' ?>
          </button>
          <a href="<?= url('website/news') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>
    </div>
  </form>
</div>
