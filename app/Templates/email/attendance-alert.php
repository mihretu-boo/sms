<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Alert — <?= htmlspecialchars($schoolName) ?></title>
<style>
  body{margin:0;padding:0;background:#F0F4F8;font-family:'Segoe UI',Arial,sans-serif}
  .wrapper{max-width:600px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}
  .header{background:linear-gradient(135deg,#F57F17,#F9A825);padding:36px 40px;text-align:center}
  .header h1{color:#fff;margin:0 0 4px;font-size:22px;font-weight:700}
  .header p{color:rgba(255,255,255,.85);margin:0;font-size:13px}
  .body{padding:36px 40px}
  .greeting{font-size:16px;color:#1a1a2e;font-weight:600;margin-bottom:16px}
  .message{font-size:14px;color:#555;line-height:1.75;margin-bottom:24px}
  .stat-row{display:flex;gap:16px;margin-bottom:24px}
  .stat-card{flex:1;text-align:center;padding:20px 16px;border-radius:8px;border:1px solid #E0E0E0}
  .stat-num{font-size:2rem;font-weight:800}
  .stat-lbl{font-size:12px;color:#888;margin-top:4px}
  .stat-present{background:#E8F5E9;border-color:#C8E6C9}.stat-present .stat-num{color:#2E7D32}
  .stat-absent{background:#FFEBEE;border-color:#FFCDD2}.stat-absent .stat-num{color:#C62828}
  .stat-rate{background:#FFF8E1;border-color:#FFE082}.stat-rate .stat-num{color:#F57F17}
  .alert-box{background:#FFF8E1;border-left:4px solid #F57F17;border-radius:4px;padding:16px 20px;font-size:14px;color:#795548;margin-bottom:24px}
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
      <div style="width:64px;height:64px;background:rgba(255,255,255,.2);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:30px">⚠️</div>
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p>Attendance Alert</p>
    </div>
    <div class="body">
      <div class="greeting">Dear <?= htmlspecialchars($parentName) ?>,</div>
      <div class="message">
        This is an important notice regarding the attendance of your child,
        <strong><?= htmlspecialchars($studentName) ?></strong> (Grade <?= htmlspecialchars($grade ?? '') ?>).
        Their attendance has fallen below the required threshold of <strong><?= htmlspecialchars($threshold ?? '75') ?>%</strong>.
      </div>

      <div class="stat-row">
        <div class="stat-card stat-present">
          <div class="stat-num"><?= htmlspecialchars($presentDays ?? '0') ?></div>
          <div class="stat-lbl">Days Present</div>
        </div>
        <div class="stat-card stat-absent">
          <div class="stat-num"><?= htmlspecialchars($absentDays ?? '0') ?></div>
          <div class="stat-lbl">Days Absent</div>
        </div>
        <div class="stat-card stat-rate">
          <div class="stat-num"><?= htmlspecialchars($attendanceRate ?? '0') ?>%</div>
          <div class="stat-lbl">Attendance Rate</div>
        </div>
      </div>

      <div class="alert-box">
        <strong>📋 Action Required:</strong><br>
        Please ensure your child attends school regularly. If there are medical or personal reasons for the absences,
        kindly submit a written excuse to the school administration.
        <br><br>
        Consistent absence may affect your child's academic performance and end-of-term assessment.
      </div>

      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">📊 &nbsp;View Full Attendance Report</a>
      </div>

      <div style="font-size:12px;color:#9E9E9E;margin-top:20px">
        <p>For concerns, contact the class teacher or school administration at
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
