<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fee Reminder — <?= htmlspecialchars($schoolName) ?></title>
<style>
  body{margin:0;padding:0;background:#F0F4F8;font-family:'Segoe UI',Arial,sans-serif}
  .wrapper{max-width:600px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}
  .header{background:linear-gradient(135deg,#D84315,#E64A19);padding:36px 40px;text-align:center}
  .header h1{color:#fff;margin:0 0 4px;font-size:22px;font-weight:700}
  .header p{color:rgba(255,255,255,.8);margin:0;font-size:13px}
  .body{padding:36px 40px}
  .greeting{font-size:16px;color:#1a1a2e;font-weight:600;margin-bottom:16px}
  .message{font-size:14px;color:#555;line-height:1.75;margin-bottom:24px}
  .fee-box{background:#FFF3E0;border:1px solid #FFCC80;border-radius:8px;padding:20px 24px;margin-bottom:24px}
  .fee-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #FFE0B2;font-size:14px}
  .fee-row:last-child{border-bottom:none}
  .fee-label{color:#795548;font-weight:600}
  .fee-value{color:#D84315;font-weight:700}
  .due-badge{display:inline-block;background:#D84315;color:#fff;padding:6px 16px;border-radius:20px;font-size:14px;font-weight:700}
  .btn-wrap{text-align:center;margin:28px 0}
  .btn{display:inline-block;background:linear-gradient(135deg,#1B3A6B,#2A5298);color:#fff!important;text-decoration:none;padding:14px 40px;border-radius:8px;font-size:15px;font-weight:700}
  .warning-box{background:#FFEBEE;border-left:4px solid #C62828;border-radius:4px;padding:12px 16px;font-size:13px;color:#C62828;margin-bottom:20px}
  .footer{background:#F8F9FA;border-top:1px solid #EEE;padding:20px 40px;text-align:center}
  .footer p{font-size:12px;color:#9E9E9E;margin:4px 0}
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div style="width:64px;height:64px;background:rgba(255,255,255,.15);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:30px">💳</div>
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p>Fee Payment Reminder</p>
    </div>
    <div class="body">
      <div class="greeting">Dear <?= htmlspecialchars($parentName) ?>,</div>
      <div class="message">
        This is a reminder that a fee payment is due for your child
        <strong><?= htmlspecialchars($studentName) ?></strong>.
        Please make the payment before the due date to avoid any late charges.
      </div>

      <div class="fee-box">
        <div class="fee-row">
          <span class="fee-label">Student</span>
          <span class="fee-value"><?= htmlspecialchars($studentName) ?></span>
        </div>
        <div class="fee-row">
          <span class="fee-label">Fee Type</span>
          <span class="fee-value"><?= htmlspecialchars($feeType ?? 'School Fee') ?></span>
        </div>
        <div class="fee-row">
          <span class="fee-label">Amount Due</span>
          <span class="fee-value"><?= htmlspecialchars($currency ?? 'ETB') ?> <?= htmlspecialchars(number_format((float)($amount ?? 0), 2)) ?></span>
        </div>
        <div class="fee-row">
          <span class="fee-label">Due Date</span>
          <span class="fee-value"><span class="due-badge"><?= htmlspecialchars($dueDate ?? '') ?></span></span>
        </div>
        <?php if (!empty($academicTerm)): ?>
        <div class="fee-row">
          <span class="fee-label">Term</span>
          <span class="fee-value"><?= htmlspecialchars($academicTerm) ?></span>
        </div>
        <?php endif; ?>
      </div>

      <div class="warning-box">
        <strong>⚠ Note:</strong> Late payments may incur additional charges as per school policy.
        Please contact the finance office if you need assistance.
      </div>

      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">💳 &nbsp;View Payment Details</a>
      </div>

      <div style="font-size:12px;color:#9E9E9E;margin-top:20px">
        <p>Finance Office: <a href="mailto:<?= htmlspecialchars($adminEmail) ?>" style="color:#1B3A6B"><?= htmlspecialchars($adminEmail) ?></a></p>
        <p>Phone: <?= htmlspecialchars($schoolPhone ?? '') ?></p>
      </div>
    </div>
    <div class="footer">
      <p><strong><?= htmlspecialchars($schoolName) ?></strong></p>
      <p><?= htmlspecialchars($schoolAddress) ?></p>
      <p style="margin-top:8px;color:#BDBDBD">This is an automated message — please do not reply. &copy; <?= date('Y') ?></p>
    </div>
  </div>
</div>
</body>
</html>
