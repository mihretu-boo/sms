<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exam Results — <?= htmlspecialchars($schoolName) ?></title>
<style>
  body{margin:0;padding:0;background:#F0F4F8;font-family:'Segoe UI',Arial,sans-serif}
  .wrapper{max-width:600px;margin:0 auto;padding:24px 16px}
  .card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);overflow:hidden}
  .header{background:linear-gradient(135deg,#1B5E20,#2E7D32);padding:36px 40px;text-align:center}
  .header h1{color:#fff;margin:0 0 4px;font-size:22px;font-weight:700}
  .header p{color:rgba(255,255,255,.8);margin:0;font-size:13px}
  .body{padding:36px 40px}
  .greeting{font-size:16px;color:#1a1a2e;font-weight:600;margin-bottom:16px}
  .message{font-size:14px;color:#555;line-height:1.75;margin-bottom:24px}
  .result-table{width:100%;border-collapse:collapse;margin-bottom:24px;font-size:14px}
  .result-table th{background:#F1F5F9;padding:10px 12px;text-align:left;color:#475569;font-weight:600;border:1px solid #E2E8F0}
  .result-table td{padding:10px 12px;border:1px solid #E2E8F0;color:#334155}
  .result-table tr:hover td{background:#F8FAFC}
  .grade-a{color:#1B5E20;font-weight:700}
  .grade-b{color:#1565C0;font-weight:700}
  .grade-c{color:#E65100;font-weight:700}
  .grade-f{color:#C62828;font-weight:700}
  .gpa-box{background:linear-gradient(135deg,#1B3A6B,#2A5298);border-radius:10px;padding:20px 24px;text-align:center;margin-bottom:24px;color:#fff}
  .gpa-num{font-size:3rem;font-weight:800;line-height:1}
  .gpa-label{font-size:13px;opacity:.8;margin-top:4px}
  .btn-wrap{text-align:center;margin:28px 0}
  .btn{display:inline-block;background:linear-gradient(135deg,#1B5E20,#2E7D32);color:#fff!important;text-decoration:none;padding:14px 40px;border-radius:8px;font-size:15px;font-weight:700}
  .footer{background:#F8F9FA;border-top:1px solid #EEE;padding:20px 40px;text-align:center}
  .footer p{font-size:12px;color:#9E9E9E;margin:4px 0}
</style>
</head>
<body>
<div class="wrapper">
  <div class="card">
    <div class="header">
      <div style="width:64px;height:64px;background:rgba(255,255,255,.15);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:30px">📊</div>
      <h1><?= htmlspecialchars($schoolName) ?></h1>
      <p><?= htmlspecialchars($examName ?? 'Exam Results') ?></p>
    </div>
    <div class="body">
      <div class="greeting">Dear <?= htmlspecialchars($recipientName) ?>,</div>
      <div class="message">
        The results for <strong><?= htmlspecialchars($examName ?? 'the recent exam') ?></strong>
        have been published for <strong><?= htmlspecialchars($studentName) ?></strong> (Grade <?= htmlspecialchars($grade ?? '') ?>).
        <?php if (!empty($academicTerm)): ?>
        <span style="color:#64748b">— <?= htmlspecialchars($academicTerm) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($gpa)): ?>
      <div class="gpa-box">
        <div class="gpa-num"><?= htmlspecialchars($gpa) ?></div>
        <div class="gpa-label">Overall GPA / Average Score</div>
      </div>
      <?php endif; ?>

      <?php if (!empty($subjects) && is_array($subjects)): ?>
      <table class="result-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th>Score</th>
            <th>Grade</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($subjects as $sub): ?>
          <tr>
            <td><?= htmlspecialchars($sub['subject'] ?? '') ?></td>
            <td><?= htmlspecialchars($sub['score'] ?? '') ?>/<?= htmlspecialchars($sub['total'] ?? '100') ?></td>
            <td class="<?= strtolower(substr($sub['grade'] ?? 'F', 0, 1)) === 'a' ? 'grade-a' : (strtolower(substr($sub['grade'] ?? 'F', 0, 1)) === 'b' ? 'grade-b' : (strtolower(substr($sub['grade'] ?? 'F', 0, 1)) === 'c' ? 'grade-c' : 'grade-f')) ?>">
              <?= htmlspecialchars($sub['grade'] ?? '') ?>
            </td>
            <td><?= htmlspecialchars($sub['remarks'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>

      <div class="btn-wrap">
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="btn">📄 &nbsp;View Full Report Card</a>
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
