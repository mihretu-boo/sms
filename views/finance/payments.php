<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave text-primary me-2"></i>Fee Payments</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Payments</li></ol></nav>
  </div>
  <a href="<?= url('finance/payments/create') ?>" class="btn btn-sm btn-success"><i class="fas fa-plus me-1"></i>Record Payment</a>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-success-light text-success rounded-3"><i class="fas fa-calendar-alt fa-lg"></i></div>
        <div><div class="fw-bold fs-5"><?= formatMoney($today_total) ?></div><div class="text-muted small">Today's Collections</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-primary-light text-primary rounded-3"><i class="fas fa-chart-line fa-lg"></i></div>
        <div><div class="fw-bold fs-5"><?= formatMoney($month_total) ?></div><div class="text-muted small">This Month's Collections</div></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="stat-icon bg-info-light text-info rounded-3"><i class="fas fa-list-ol fa-lg"></i></div>
        <div><div class="fw-bold fs-5"><?= number_format($total) ?></div><div class="text-muted small">Total Transactions</div></div>
      </div>
    </div>
  </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" name="search" value="<?= e($search) ?>" placeholder="Student name, ID, receipt no...">
        </div>
      </div>
      <div class="col-md-3">
        <input type="date" name="date" class="form-control flatpickr" value="<?= e($date) ?>" placeholder="Filter by date">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('finance/payments') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
        <a href="<?= url('reports/export?type=payments') ?>" class="btn btn-outline-info ms-1"><i class="fas fa-download me-1"></i>Export</a>
      </div>
    </form>
  </div>
</div>

<!-- Payments Table -->
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Receipt #</th><th>Student</th><th>Fee Type</th><th>Amount</th><th>Date</th><th>Method</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
          <tr><td colspan="7" class="text-center py-5 text-muted">No payments found</td></tr>
          <?php else: foreach ($payments as $p): ?>
          <tr>
            <td><span class="badge bg-light text-dark font-monospace small"><?= e($p['receipt_no']) ?></span></td>
            <td>
              <div class="fw-semibold small"><?= e($p['first_name'].' '.$p['last_name']) ?></div>
              <div class="text-muted" style="font-size:11px"><?= e($p['stud_no']) ?></div>
            </td>
            <td class="small"><?= e($p['fee_name'] ?? 'General') ?></td>
            <td class="fw-bold text-success"><?= formatMoney($p['amount']) ?></td>
            <td class="small"><?= formatDate($p['payment_date']) ?></td>
            <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$p['payment_method'])) ?></span></td>
            <td>
              <a href="<?= url('finance/payments/receipt/'.$p['id']) ?>" class="btn btn-xs btn-outline-primary" target="_blank" title="Receipt"><i class="fas fa-receipt"></i></a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between align-items-center">
    <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
    <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
  </div>
  <?php endif; ?>
</div>
