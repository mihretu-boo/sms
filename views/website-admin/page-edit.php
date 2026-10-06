<?php $title = 'Edit Page: ' . ucfirst($page['slug']); ?>

<div class="container-fluid px-4 py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">
      <i class="fas fa-edit me-2 text-warning"></i>Edit: <span class="text-capitalize"><?= e($page['slug']) ?></span> Page
    </h4>
    <a href="<?= url('website/pages') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i> Back
    </a>
  </div>

  <form method="POST" action="<?= url('website/pages/save/' . $page['slug']) ?>">
    <?= csrfField() ?>

    <!-- Tabs for languages -->
    <ul class="nav nav-tabs mb-0" id="langTabs">
      <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-en">
        <img src="https://flagcdn.com/16x12/gb.png" class="me-1" alt="">English
      </a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-om">
        Afaan Oromoo
      </a></li>
      <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-am">
        አማርኛ
      </a></li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white shadow-sm mb-3">
      <!-- English -->
      <div class="tab-pane fade show active" id="tab-en">
        <div class="mb-3">
          <label class="form-label fw-semibold">Title (English)</label>
          <input type="text" name="title_en" class="form-control" value="<?= e($page['title_en'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Content (English)</label>
          <textarea name="content_en" class="form-control" rows="12" style="font-family:monospace"><?= e($page['content_en'] ?? '') ?></textarea>
          <small class="text-muted">Basic HTML allowed: &lt;p&gt; &lt;h2&gt; &lt;h3&gt; &lt;h4&gt; &lt;b&gt; &lt;ul&gt; &lt;li&gt;</small>
        </div>
      </div>
      <!-- Afaan Oromo -->
      <div class="tab-pane fade" id="tab-om">
        <div class="mb-3">
          <label class="form-label fw-semibold">Mata-Duree (Afaan Oromoo)</label>
          <input type="text" name="title_om" class="form-control" value="<?= e($page['title_om'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Qabiyyee (Afaan Oromoo)</label>
          <textarea name="content_om" class="form-control" rows="12" style="font-family:monospace"><?= e($page['content_om'] ?? '') ?></textarea>
        </div>
      </div>
      <!-- Amharic -->
      <div class="tab-pane fade" id="tab-am">
        <div class="mb-3">
          <label class="form-label fw-semibold">ርዕስ (አማርኛ)</label>
          <input type="text" name="title_am" class="form-control" value="<?= e($page['title_am'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">ይዘት (አማርኛ)</label>
          <textarea name="content_am" class="form-control" rows="12" style="font-family:monospace"><?= e($page['content_am'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Save Page
      </button>
      <a href="<?= url('website/pages') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
