<div class="d-flex align-items-center justify-content-between mb-4">
  <div><h4 class="mb-0 fw-bold"><i class="fas fa-tags text-primary me-2"></i>Inventory Categories</h4></div>
  <a href="<?= url('inventory') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Inventory</a>
</div>

<div class="row g-3">
  <?php foreach ($categories as $cat): ?>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-4">
      <div class="fw-bold"><?= e($cat['name']) ?></div>
      <div class="text-muted small"><?= $cat['item_count'] ?> items</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm mt-4">
  <div class="card-header bg-white border-bottom py-3"><h6 class="mb-0 fw-semibold">Add Category</h6></div>
  <div class="card-body">
    <form action="<?= url('inventory/categories') ?>" method="POST" class="row g-3">
      <?= csrfField() ?>
      <div class="col-md-5"><input type="text" name="name" class="form-control" placeholder="Category name" required></div>
      <div class="col-md-5"><input type="text" name="description" class="form-control" placeholder="Description"></div>
      <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i>Add</button></div>
    </form>
  </div>
</div>
