<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-plus-circle text-success me-2"></i>Record Payment</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('finance/payments') ?>">Payments</a></li><li class="breadcrumb-item active">Record</li></ol></nav>
  </div>
  <a href="<?= url('finance/payments') ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4 justify-content-center">
  <div class="col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white py-3">
        <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Payment Details</h6>
      </div>
      <div class="card-body">
        <form action="<?= url('finance/payments/create') ?>" method="POST">
          <?= csrfField() ?>

          <!-- Student Search -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-graduate text-muted"></i></span>
              <input type="text" id="studentSearch" class="form-control border-start-0" placeholder="Type student name or ID..." autocomplete="off">
            </div>
            <input type="hidden" name="student_id" id="studentId" value="<?= e($stuId) ?>">
            <div id="studentSuggestions" class="list-group mt-1 shadow" style="display:none; position:absolute; z-index:1000; width:400px; max-height:200px; overflow-y:auto"></div>
          </div>

          <?php if ($student): ?>
          <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
            <img src="<?= photoUrl($student['photo'],'student') ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover">
            <div>
              <div class="fw-semibold"><?= e($student['first_name'].' '.$student['last_name']) ?></div>
              <div class="text-muted small"><?= e($student['student_id']) ?></div>
            </div>
          </div>
          <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

          <!-- Pending Fees -->
          <?php if (!empty($fees)): ?>
          <div class="mb-4">
            <label class="form-label fw-semibold">Select Fee <small class="text-muted">(Optional)</small></label>
            <select name="student_fee_id" class="form-select" id="feeSelect">
              <option value="">General Payment (Not linked to specific fee)</option>
              <?php foreach ($fees as $f): ?>
              <option value="<?= $f['id'] ?>" data-amount="<?= $f['amount'] ?>">
                <?= e($f['fee_name']) ?> — <?= formatMoney($f['amount']) ?> (Due: <?= formatDate($f['due_date']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Amount (ETB) <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">ETB</span>
                <input type="number" name="amount" class="form-control" id="amountInput" min="1" step="0.01" placeholder="0.00" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
              <input type="date" name="payment_date" class="form-control flatpickr" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-select" required>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="telebirr">Telebirr</option>
                <option value="cbe_birr">CBE Birr</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Transaction Reference</label>
              <input type="text" name="transaction_ref" class="form-control" placeholder="Bank ref, Telebirr code...">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Notes</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= url('finance/payments') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save me-2"></i>Record Payment</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
var searchTimeout;
document.getElementById('studentSearch').addEventListener('input', function() {
  clearTimeout(searchTimeout);
  var q = this.value;
  if (q.length < 2) { document.getElementById('studentSuggestions').style.display='none'; return; }
  searchTimeout = setTimeout(function() {
    fetch(BASE_URL + '/api/students?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(data => {
        var box = document.getElementById('studentSuggestions');
        box.innerHTML = '';
        if (data.length === 0) { box.style.display='none'; return; }
        data.forEach(function(s) {
          var a = document.createElement('a');
          a.href = '#';
          a.className = 'list-group-item list-group-item-action';
          a.innerHTML = '<strong>' + s.first_name + ' ' + s.last_name + '</strong> <span class="text-muted small">(' + s.student_id + ')</span>';
          a.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('studentSearch').value = s.first_name + ' ' + s.last_name;
            document.getElementById('studentId').value = s.id;
            box.style.display='none';
            window.location.href = BASE_URL + '/finance/payments/create?student_id=' + s.id;
          });
          box.appendChild(a);
        });
        box.style.display='block';
      });
  }, 300);
});

document.addEventListener('click', function(e) {
  if (!e.target.closest('#studentSearch')) document.getElementById('studentSuggestions').style.display='none';
});

var feeSelect = document.getElementById('feeSelect');
if (feeSelect) {
  feeSelect.addEventListener('change', function() {
    var amt = this.options[this.selectedIndex].dataset.amount;
    if (amt) document.getElementById('amountInput').value = amt;
  });
}
</script>
