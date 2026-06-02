<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>403 — Access Denied</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>body{background:#F0F4F8;font-family:'Segoe UI',sans-serif;}</style>
</head>
<body>
<div class="min-vh-100 d-flex align-items-center justify-content-center">
  <div class="text-center">
    <div class="text-danger mb-3"><i class="fas fa-ban" style="font-size:5rem;"></i></div>
    <h4 class="mb-2">Access Denied</h4>
    <p class="text-muted mb-4">You don't have permission to access this page.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL . '/dashboard' : '/studentmanagement/dashboard' ?>" class="btn btn-primary">
      <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
    </a>
  </div>
</div>
</body>
</html>
