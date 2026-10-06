<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome — <?= htmlspecialchars($schoolName) ?></title>
<style>
  body{margin:0;padding:0;background:#F0F4F8;font-family:'Segoe UI',Arial,sans-serif}
  .wrapper{max-width:600px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}
  .header{background:linear-gradient(135deg,#0F2548,#1B3A6B);padding:36px 40px;text-align:center}
  .header h1{color:#fff;margin:0 0 4px;font-size:22px;font-weight:700}
  .header p{color:rgba(255,255,255,.75);margin:0;font-size:13px}
  .body{padding:36px 40px}
  .greeting{font-size:16px;color:#1a1a2e;font-weight:600;margin-bottom:16px}
  .message{font-size:14px;color:#555;line-height:1.75;margin-bottom:24px}
  .cred-box{background:#F0F4FF;border:1px solid #C7D7F5;border-radius:8px;padding:20px 24px;margin-bottom:24px}
  .cred-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #E2E8F0;font-size:14px}
  .cred-row:last-child{border-bottom:none}
  .cred-label{color:#64748b;font-weight:600}
  .cred-value{color:#1B3A6B;font-weight:700;font-family:monospace}
  .role-badge{display:inline-block;background:#E8A020;color:#fff;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:700;text-transform:capitalize}
  .btn-wrap{text-align:center;margin:28px 0}
  .btn{display:inline-block;background:linear-gradient(135deg,#1B3A6B,#2A5298);color:#fff!important;text-decoration:none;padding:14px 40px;border-radius:8px;font-size:15px;font-weight:700}
  .info-box{background:#FFF8E1;border-left:4px solid #E8A020;border-radius:4px;padding:12px 16px;font-size:13px;color:#795548;margin-bottom:20px}
  .footer{background:#F8F9FA;border-top:1px solid #EEE;padding:20px 40px;text-align:center}
  .footer p{font-size:12px;color:#9E9E9E;margin:4px 0}
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div style="width:64px;height:64px;background:rgba(255,255,255,.15);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:30px">👩‍🏫</div>
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p>Staff Management System</p>
    </div>
    <div class="body">
      <div class="greeting">Welcome aboard, <?= htmlspecialchars($staffName) ?>!</div>
      <div class="message">
        Your staff account has been created at <strong><?= htmlspecialchars($schoolName) ?></strong>.
        You have been assigned as <span class="role-badge"><?= htmlspecialchars($role) ?></span>.
        <br><br>
        Use the credentials below to log in to the School Management System.
      </div>

      <div class="cred-box">
        <div class="cred-row">
          <span class="cred-label">Username</span>
          <span class="cred-value"><?= htmlspecialchars($username) ?></span>
        </div>
        <div class="cred-row">
          <span class="cred-label">Temporary Password</span>
          <span class="cred-value"><?= htmlspecialchars($password) ?></span>
        </div>
        <div class="cred-row">
          <span class="cred-label">Role</span>
          <span class="cred-value"><?= htmlspecialchars($role) ?></span>
        </div>
        <div class="cred-row">
          <span class="cred-label">Department</span>
          <span class="cred-value"><?= htmlspecialchars($department ?? 'N/A') ?></span>
        </div>
      </div>

      <div class="info-box">
        <strong>⚠ Security:</strong> Please change your temporary password immediately after your first login.
        Do not share your credentials with anyone.
      </div>

      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">🔐 &nbsp;Login to Staff Portal</a>
      </div>

      <div style="font-size:12px;color:#9E9E9E;margin-top:20px">
        <p>For technical assistance, contact the system administrator at
          <a href="mailto:<?= htmlspecialchars($adminEmail) ?>" style="color:#1B3A6B"><?= htmlspecialchars($adminEmail) ?></a>.
        </p>
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
