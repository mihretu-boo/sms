<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Print') ?> | <?= e(getSetting('school_name_short','SJASSMS')) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  body { font-size: 12px; background: white; }
  @media print { .no-print { display: none !important; } body { margin: 0; } }
  .print-header { border-bottom: 2px solid #1565C0; margin-bottom: 20px; }
</style>
</head>
<body>
  <div class="no-print text-end p-2">
    <button onclick="window.print()" class="btn btn-sm btn-primary me-2"><i class="fas fa-print me-1"></i>Print</button>
    <button onclick="window.close()" class="btn btn-sm btn-secondary"><i class="fas fa-times me-1"></i>Close</button>
  </div>
  <?= $content ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
