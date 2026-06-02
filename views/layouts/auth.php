<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title ?? 'Login') ?> | <?= e(getSetting('school_name_short','SJASSMS')) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/login.css">
</head>
<body class="auth-body">

<div class="auth-bg">
  <div class="auth-particles"></div>
</div>

<div class="auth-wrapper">
  <div class="auth-container">
    <!-- Logo / Header -->
    <div class="auth-header text-center mb-4">
      <div class="auth-logo-wrap mx-auto mb-3">
        <i class="fas fa-graduation-cap fa-3x text-white"></i>
      </div>
      <h4 class="text-white fw-bold mb-1"><?= e(getSetting('school_name_short','SJASSMS')) ?></h4>
      <p class="text-white-50 small mb-0"><?= e(getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?></p>
    </div>

    <!-- Card -->
    <div class="auth-card card shadow-lg border-0">
      <div class="card-body p-4 p-md-5">

        <!-- Flash messages -->
        <?= Flash::render() ?>

        <!-- Page content -->
        <?= $content ?>

      </div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-3">
      <small class="text-white-50">&copy; <?= date('Y') ?> <?= e(getSetting('school_name','SJASS')) ?> — All rights reserved</small>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
