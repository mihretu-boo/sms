<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-book-reader text-primary me-2"></i>Library Dashboard</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Library</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('library/books') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-book me-1"></i>Books</a>
    <a href="<?= url('library/borrowings') ?>" class="btn btn-sm btn-primary"><i class="fas fa-exchange-alt me-1"></i>Borrowings</a>
  </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary-light text-primary rounded-3"><i class="fas fa-book fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($total_books) ?></div><div class="stat-label text-muted small">Total Books</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-light text-success rounded-3"><i class="fas fa-check-circle fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($available) ?></div><div class="stat-label text-muted small">Available</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-warning-light text-warning rounded-3"><i class="fas fa-hand-holding fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($borrowed) ?></div><div class="stat-label text-muted small">Borrowed</div></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-danger-light text-danger rounded-3"><i class="fas fa-exclamation-circle fa-lg"></i></div>
        <div><div class="stat-value"><?= number_format($overdue) ?></div><div class="stat-label text-muted small">Overdue</div></div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Borrowings -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold"><i class="fas fa-history text-primary me-2"></i>Recent Borrowings</h6>
    <a href="<?= url('library/borrowings') ?>" class="text-primary small">View All</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light"><tr><th>Book</th><th>User</th><th>Borrowed</th><th>Due</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($recent_borrows)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">No recent borrowings</td></tr>
          <?php else: foreach ($recent_borrows as $b): ?>
          <tr>
            <td class="fw-semibold"><?= e($b['title']) ?></td>
            <td><?= e($b['username']) ?></td>
            <td><?= formatDate($b['borrow_date']) ?></td>
            <td class="<?= $b['status']==='borrowed' && strtotime($b['due_date'])<time() ? 'text-danger fw-bold' : '' ?>">
              <?= formatDate($b['due_date']) ?>
            </td>
            <td><?= getStatusBadge($b['status']) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
