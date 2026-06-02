<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-edit text-warning me-2"></i>Edit Book</h4></div>
  <a href="<?= url('library/books') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-dark py-3"><h6 class="mb-0">Edit: <?= e($book['title']) ?></h6></div>
      <div class="card-body">
        <form action="<?= url('library/books/edit/'.$book['id']) ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" value="<?= e($book['title']) ?>" required></div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Author <span class="text-danger">*</span></label><input type="text" name="author" class="form-control" value="<?= e($book['author']) ?>" required></div>
            <div class="col-md-6"><label class="form-label">Publisher</label><input type="text" name="publisher" class="form-control" value="<?= e($book['publisher']) ?>"></div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?= e($book['category']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Total Copies</label><input type="number" name="copies_total" class="form-control" value="<?= e($book['copies_total']) ?>" min="1"></div>
            <div class="col-md-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control" value="<?= e($book['location']) ?>"></div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-2"></i>Update Book</button>
            <a href="<?= url('library/books') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
