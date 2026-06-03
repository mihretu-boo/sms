<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Password Changed Successfully</title>
<style>
  body { margin:0; padding:0; background:#F0F4F8; font-family:'Segoe UI',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:0 auto; padding:24px 16px; }
  .card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08); overflow:hidden; }
  .header { background:linear-gradient(135deg,#1B5E20,#2E7D32); padding:32px 40px; text-align:center; }
  .header h1 { color:#fff; margin:0 0 4px; font-size:22px; font-weight:700; }
  .header p  { color:rgba(255,255,255,0.75); margin:0; font-size:13px; }
  .body { padding:36px 40px; }
  .success-icon { text-align:center; font-size:56px; margin-bottom:16px; }
  .message { font-size:14px; color:#555; line-height:1.7; margin-bottom:24px; }
  .info-row { display:flex; justify-content:space-between; background:#F5F5F5; padding:10px 14px; border-radius:6px; font-size:13px; margin-bottom:8px; }
  .btn-wrap { text-align:center; margin:28px 0; }
  .btn { display:inline-block; background:linear-gradient(135deg,#2E7D32,#388E3C); color:#fff !important;
         text-decoration:none; padding:14px 40px; border-radius:8px; font-size:15px; font-weight:700; }
  .alert { background:#FFEBEE; border-left:4px solid #C62828; border-radius:4px; padding:12px 16px; font-size:13px; color:#C62828; margin-bottom:20px; }
  .footer { background:#F8F9FA; border-top:1px solid #EEEEEE; padding:20px 40px; text-align:center; }
  .footer p { font-size:12px; color:#9E9E9E; margin:4px 0; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p>Password Changed Successfully</p>
    </div>
    <div class="body">
      <div class="success-icon">✅</div>

      <div class="message">
        <strong>Dear <?= htmlspecialchars($username) ?>,</strong><br><br>
        Your password for the <strong><?= htmlspecialchars($schoolName) ?> Management System</strong>
        has been successfully changed.
      </div>

      <div class="info-row"><span><strong>Account:</strong></span><span><?= htmlspecialchars($email) ?></span></div>
      <div class="info-row"><span><strong>Changed at:</strong></span><span><?= htmlspecialchars($changedAt) ?></span></div>
      <div class="info-row"><span><strong>IP Address:</strong></span><span><?= htmlspecialchars($ipAddress) ?></span></div>

      <br>

      <div class="alert">
        <strong>⚠ Wasn't you?</strong><br>
        If you did not make this change, your account may have been compromised.
        Contact the system administrator immediately at <strong><?= htmlspecialchars($adminEmail) ?></strong>.
      </div>

      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">🔐 &nbsp;Login to Your Account</a>
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
