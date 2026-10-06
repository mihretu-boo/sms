<?php
require_once ROOT . '/views/students/_id_card_helper.php';
$schoolName  = getSetting('school_name','Shalaka Jatan Ali Secondary School');
$schoolShort = 'SJASS';
$schoolAddr  = getSetting('school_address','');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print ID Cards — <?= e($schoolName) ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#e8e8e8; font-family:'Segoe UI',sans-serif; }

.no-print {
  background:#fff;
  padding:12px 24px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  box-shadow:0 2px 8px rgba(0,0,0,.1);
  margin-bottom:24px;
}
.no-print h6 { margin:0; font-size:15px; font-weight:700; }

.cards-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
  padding: 24px;
}

.card-pair {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: center;
}

.card-label {
  font-size: 11px;
  color: #666;
  font-weight: 600;
  text-align: center;
}

<?php echo idCardCss(); ?>

@media print {
  .no-print { display:none !important; }
  body { background:#fff; }
  .cards-grid { padding:4mm; gap:6mm; }
  .id-card { box-shadow:none; border:0.5pt solid #ccc; }
  .card-label { display:none; }
  .card-pair { page-break-inside: avoid; }
}
</style>
</head>
<body>

<div class="no-print">
  <h6><i class="fas fa-print" style="color:#1B3A6B;margin-right:8px"></i>
    Print <?= count($students) ?> ID Card<?= count($students)!==1?'s':'' ?>
  </h6>
  <div style="display:flex;gap:8px">
    <button onclick="window.print()" style="background:#1B3A6B;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-weight:700;cursor:pointer;font-size:14px">
      🖨 Print All
    </button>
    <button onclick="window.close()" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-size:14px">
      ✕ Close
    </button>
  </div>
</div>

<div class="cards-grid">
  <?php foreach ($students as $stu): ?>
  <div class="card-pair">
    <div class="card-label">FRONT — <?= e($stu['first_name'].' '.$stu['last_name']) ?></div>
    <?php renderIdCard($stu, $principal, $pTitle, $schoolName, $schoolShort, $schoolAddr); ?>
    <div class="card-label">BACK</div>
    <?php renderIdCardBack($stu, $principal, $pTitle, $schoolName, $schoolShort, $schoolAddr); ?>
  </div>
  <?php endforeach; ?>
</div>

<script>
// Auto-open print dialog
window.addEventListener('load', function() {
  setTimeout(function() { window.print(); }, 400);
});
</script>
</body>
</html>
