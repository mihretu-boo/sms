<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Your Password</title>
<style>
  body { margin:0; padding:0; background:#F0F4F8; font-family:'Segoe UI',Arial,sans-serif; }
  .wrapper { max-width:600px; margin:0 auto; padding:24px 16px; }
  .card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08); overflow:hidden; }
  .header { background:linear-gradient(135deg,#0D47A1,#1565C0); padding:32px 40px; text-align:center; }
  .header img { width:60px; height:60px; margin-bottom:12px; }
  .header h1 { color:#fff; margin:0 0 4px; font-size:22px; font-weight:700; }
  .header p  { color:rgba(255,255,255,0.75); margin:0; font-size:13px; }
  .body { padding:36px 40px; }
  .greeting { font-size:16px; color:#333; margin-bottom:16px; }
  .message  { font-size:14px; color:#555; line-height:1.7; margin-bottom:24px; }
  .btn-wrap { text-align:center; margin:28px 0; }
  .btn { display:inline-block; background:linear-gradient(135deg,#1565C0,#1976D2); color:#fff !important;
         text-decoration:none; padding:14px 40px; border-radius:8px; font-size:15px;
         font-weight:700; letter-spacing:0.3px; }
  .url-box { background:#F5F5F5; border:1px solid #E0E0E0; border-radius:6px; padding:10px 14px;
             font-family:monospace; font-size:12px; color:#555; word-break:break-all; margin-bottom:20px; }
  .info-box { background:#E3F2FD; border-left:4px solid #1565C0; border-radius:4px;
              padding:12px 16px; font-size:13px; color:#1565C0; margin-bottom:20px; }
  .warning  { background:#FFF8E1; border-left:4px solid #F57F17; border-radius:4px;
              padding:12px 16px; font-size:13px; color:#795548; margin-bottom:20px; }
  .footer { background:#F8F9FA; border-top:1px solid #EEEEEE; padding:20px 40px; text-align:center; }
  .footer p { font-size:12px; color:#9E9E9E; margin:4px 0; }
  .footer strong { color:#666; }
  .divider { border:none; border-top:1px solid #EEEEEE; margin:24px 0; }
  @media (max-width:480px) {
    .body { padding:24px 20px; }
    .header { padding:24px 20px; }
    .footer { padding:16px 20px; }
  }
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <!-- Header -->
    <div class="header">
      <div style="width:60px;height:60px;background:rgba(255,255,255,0.15);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:28px">🎓</div>
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p>School Management System</p>
    </div>

    <!-- Body -->
    <div class="body">
      <div class="greeting">Dear <?= htmlspecialchars($username) ?>,</div>

      <div class="message">
        We received a request to reset the password for your account associated with
        <strong><?= htmlspecialchars($email) ?></strong>.
        <br><br>
        Click the button below to reset your password. This link will expire in
        <strong><?= (int)$expiryMinutes ?> minutes</strong>.
      </div>

      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($resetUrl) ?>" class="btn">
          🔒 &nbsp;Reset My Password
        </a>
      </div>

      <div class="info-box">
        <strong>⏱ Link expires:</strong> <?= htmlspecialchars($expiresAt) ?> (Ethiopia time)
      </div>

      <p style="font-size:13px;color:#777">If the button doesn't work, copy and paste the link below into your browser:</p>
      <div class="url-box"><?= htmlspecialchars($resetUrl) ?></div>

      <div class="warning">
        <strong>⚠ Didn't request this?</strong><br>
        If you did not request a password reset, please ignore this email. Your password will remain unchanged.
        Consider changing it if you suspect unauthorized access.
      </div>

      <hr class="divider">

      <div style="font-size:12px;color:#9E9E9E">
        <p>For security reasons, this link can only be used <strong>once</strong> and will expire automatically.</p>
        <p>If you need further assistance, contact the system administrator at
           <a href="mailto:<?= htmlspecialchars($adminEmail) ?>" style="color:#1565C0"><?= htmlspecialchars($adminEmail) ?></a>.
        </p>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <p><strong><?= htmlspecialchars($schoolName) ?></strong></p>
      <p><?= htmlspecialchars($schoolAddress) ?></p>
      <p style="margin-top:8px;color:#BDBDBD">
        This is an automated message — please do not reply.
        &copy; <?= date('Y') ?> <?= htmlspecialchars($schoolName) ?>
      </p>
    </div>
  </div>
</div>
</body>
</html>
