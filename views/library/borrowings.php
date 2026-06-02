<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-exchange-alt text-primary me-2"></i>Book Borrowings</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('library') ?>">Library</a></li><li class="breadcrumb-item active">Borrowings</li></ol></nav>
  </div>
  <a href="<?= url('library/books') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Books</a>
</div>

<!-- Status Tabs -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link <?= !$status?'active':'' ?>" href="<?= url('library/borrowings') ?>">All</a></li>
  <li class="nav-item"><a class="nav-link <?= $status==='borrowed'?'active':'' ?>" href="<?= url('library/borrowings?status=borrowed') ?>">Active</a></li>
  <li class="nav-item"><a class="nav-link <?= $status==='overdue'?'active':'' ?>" href="<?= url('library/borrowings?status=overdue') ?>">Overdue</a></li>
  <li class="nav-item"><a class="nav-link <?= $status==='returned'?'active':'' ?>" href="<?= url('library/borrowings?status=returned') ?>">Returned</a></li>
</ul>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="borrowingsTable">
        <thead class="table-light">
          <tr><th>Book</th><th>User</th><th>Borrowed</th><th>Due</th><th>Return</th><th>Fine</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($borrowings)): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted">No borrowing records found</td></tr>
          <?php else: foreach ($borrowings as $b): ?>
          <?php $isOverdue = $b['status']==='borrowed' && $b['days_overdue'] > 0; ?>
          <tr class="<?= $isOverdue?'table-danger':'' ?>">
            <td class="fw-semibold"><?= e($b['title']) ?><br><small class="text-muted"><?= e($b['author']) ?></small></td>
            <td><?= e($b['username']) ?></td>
            <td><?= formatDate($b['borrow_date']) ?></td>
            <td class="<?= $isOverdue?'text-danger fw-bold':'' ?>"><?= formatDate($b['due_date']) ?><?php if ($isOverdue): ?><br><small><?= $b['days_overdue'] ?> days overdue</small><?php endif; ?></td>
            <td><?= $b['return_date'] ? formatDate($b['return_date']) : '—' ?></td>
            <td><?= $b['fine'] > 0 ? '<span class="text-danger">'.formatMoney($b['fine']).'</span>' : '—' ?></td>
            <td><?= getStatusBadge($b['status']) ?></td>
            <td>
              <?php if ($b['status'] === 'borrowed' && Auth::can('students')): ?>
              <form action="<?= url('library/return/'.$b['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Confirm return?')">
                <?= csrfField() ?>
                <button class="btn btn-xs btn-success"><i class="fas fa-undo me-1"></i>Return</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
