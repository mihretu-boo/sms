<div class="container py-4" style="max-width:600px">
  <!-- School Header -->
  <div class="print-header text-center mb-4 pb-3">
    <h5 class="fw-bold text-primary mb-1"><?= e(getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?></h5>
    <div class="text-muted small"><?= e(getSetting('school_address','Yabelo, Borana Zone, Oromia')) ?></div>
    <div class="text-muted small">Tel: <?= e(getSetting('school_phone','')) ?> | Email: <?= e(getSetting('school_email','')) ?></div>
    <h5 class="fw-bold mt-3 mb-0">PAYMENT RECEIPT</h5>
  </div>

  <!-- Receipt Details -->
  <div class="row mb-3">
    <div class="col-6">
      <strong>Receipt No:</strong> <?= e($payment['receipt_no']) ?><br>
      <strong>Date:</strong> <?= formatDate($payment['payment_date'], 'd M Y') ?><br>
      <strong>Method:</strong> <?= ucfirst(str_replace('_',' ',$payment['payment_method'])) ?>
      <?php if ($payment['transaction_ref']): ?><br><strong>Ref:</strong> <?= e($payment['transaction_ref']) ?><?php endif; ?>
    </div>
    <div class="col-6 text-end">
      <strong>Cashier:</strong> <?= e($payment['recorded_by_name'] ?? '') ?><br>
      <strong>Time:</strong> <?= date('H:i') ?>
    </div>
  </div>

  <hr>

  <!-- Student Info -->
  <table class="table table-sm table-borderless mb-3">
    <tr><td class="fw-semibold" width="40%">Student Name</td><td><?= e($payment['first_name'].' '.$payment['last_name']) ?></td></tr>
    <tr><td class="fw-semibold">Student ID</td><td><?= e($payment['stud_no']) ?></td></tr>
    <tr><td class="fw-semibold">Class</td><td>Grade <?= e($payment['grade']??'—') ?>-<?= e($payment['section']??'') ?></td></tr>
    <?php if ($payment['fee_name']): ?><tr><td class="fw-semibold">Fee Type</td><td><?= e($payment['fee_name']) ?></td></tr><?php endif; ?>
    <?php if ($payment['notes']): ?><tr><td class="fw-semibold">Notes</td><td><?= e($payment['notes']) ?></td></tr><?php endif; ?>
  </table>

  <hr>

  <!-- Amount -->
  <div class="text-center my-4">
    <div class="fw-bold text-muted small mb-1">AMOUNT PAID</div>
    <div class="display-5 fw-bold text-success"><?= formatMoney($payment['amount']) ?></div>
  </div>

  <hr>

  <!-- Footer -->
  <div class="row small text-muted mt-4">
    <div class="col-6">
      <div class="fw-semibold mb-4">Received by:</div>
      <div style="border-top: 1px solid #333; padding-top: 4px;"><?= e($payment['recorded_by_name'] ?? '') ?></div>
    </div>
    <div class="col-6 text-end">
      <div class="fw-semibold mb-4">Student/Parent Signature:</div>
      <div style="border-top: 1px solid #333; padding-top: 4px;">______________________</div>
    </div>
  </div>

  <div class="text-center mt-4 text-muted" style="font-size:11px">
    This is a computer-generated receipt. Please keep it for your records.<br>
    Printed on: <?= date('d M Y H:i') ?>
  </div>
</div>
