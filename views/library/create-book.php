<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-primary me-2"></i>Add Book</h4></div>
  <a href="<?= url('library/books') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white py-3"><h6 class="mb-0"><i class="fas fa-book me-2"></i>Book Details</h6></div>
      <div class="card-body">
        <form action="<?= url('library/books/create') ?>" method="POST">
          <?= csrfField() ?>
          <div class="mb-3"><label class="form-label fw-semibold">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Author <span class="text-danger">*</span></label><input type="text" name="author" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">ISBN</label><input type="text" name="isbn" class="form-control" placeholder="Optional"></div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Publisher</label><input type="text" name="publisher" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Year</label><input type="number" name="publish_year" class="form-control" min="1900" max="<?= date('Y') ?>"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Language</label><select name="language" class="form-select"><option>English</option><option>Amharic</option><option>Afaan Oromoo</option></select></div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Category</label><input type="text" name="category" class="form-control" placeholder="e.g. Textbook, Reference"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Total Copies <span class="text-danger">*</span></label><input type="number" name="copies_total" class="form-control" value="1" min="1" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Location</label><input type="text" name="location" class="form-control" placeholder="e.g. Shelf A1"></div>
          </div>
          <div class="mb-4"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Add Book</button>
            <a href="<?= url('library/books') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
