<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($title ?? 'Announcement') ?> — <?= htmlspecialchars($schoolName) ?></title>
<style>
  body{margin:0;padding:0;background:#F0F4F8;font-family:'Segoe UI',Arial,sans-serif}
  .wrapper{max-width:600px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}
  .header{background:linear-gradient(135deg,#1B3A6B,#2A5298);padding:36px 40px;text-align:center}
  .header h1{color:#fff;margin:0 0 4px;font-size:22px;font-weight:700}
  .header p{color:rgba(255,255,255,.75);margin:0;font-size:13px}
  .body{padding:36px 40px}
  .announcement-title{font-size:20px;font-weight:800;color:#1B3A6B;margin-bottom:8px}
  .meta{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px}
  .meta-badge{background:#EFF6FF;color:#1B3A6B;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
  .content{font-size:14px;color:#444;line-height:1.8;margin-bottom:24px;border-left:4px solid #E8A020;padding-left:16px}
  .btn-wrap{text-align:center;margin:28px 0}
  .btn{display:inline-block;background:linear-gradient(135deg,#1B3A6B,#2A5298);color:#fff!important;text-decoration:none;padding:14px 40px;border-radius:8px;font-size:15px;font-weight:700}
  .footer{background:#F8F9FA;border-top:1px solid #EEE;padding:20px 40px;text-align:center}
  .footer p{font-size:12px;color:#9E9E9E;margin:4px 0}
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div style="width:64px;height:64px;background:rgba(255,255,255,.15);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:30px">📢</div>
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p>School Announcement</p>
    </div>
    <div class="body">
      <div class="announcement-title"><?= htmlspecialchars($title ?? 'School Notice') ?></div>

      <div class="meta">
        <?php if (!empty($category)): ?>
        <span class="meta-badge">📁 <?= htmlspecialchars($category) ?></span>
        <?php endif; ?>
        <?php if (!empty($publishedAt)): ?>
        <span class="meta-badge">📅 <?= htmlspecialchars($publishedAt) ?></span>
        <?php endif; ?>
        <?php if (!empty($audience)): ?>
        <span class="meta-badge">👥 <?= htmlspecialchars($audience) ?></span>
        <?php endif; ?>
      </div>

      <div class="content">
        <?= nl2br(htmlspecialchars($body ?? '')) ?>
      </div>

      <?php if (!empty($loginUrl)): ?>
      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">🔔 &nbsp;View in Portal</a>
      </div>
      <?php endif; ?>
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
