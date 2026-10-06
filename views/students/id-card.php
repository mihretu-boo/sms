<?php
require_once ROOT . '/views/students/_id_card_helper.php';

$schoolName  = getSetting('school_name','Shalaka Jatan Ali Secondary School');
$schoolShort = 'SJASS';
$schoolAddr  = getSetting('school_address','Yabelo, Borana Zone, Oromia, Ethiopia');
$logoUrl     = BASE_URL . '/assets/images/logo.png';
?>

<div class="d-flex align-items-center justify-content-between mb-4 no-print">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-id-card text-primary me-2"></i>Student ID Card</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('students/id-cards') ?>">ID Cards</a></li>
      <li class="breadcrumb-item active"><?= e($student['first_name'].' '.$student['last_name']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 no-print">
    <a href="<?= url('students/id-cards') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fas fa-arrow-left me-1"></i>Back
    </a>
    <a href="<?= url('students/'.$student['id'].'/edit') ?>" class="btn btn-sm btn-outline-warning">
      <i class="fas fa-edit me-1"></i>Edit Student
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-primary">
      <i class="fas fa-print me-1"></i>Print Card
    </button>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-lg-6">

    <!-- Card preview -->
    <div class="text-center mb-4">
      <?php renderIdCard($student, $principal, $pTitle, $schoolName, $schoolShort, $schoolAddr); ?>
    </div>

    <!-- Card back -->
    <div class="text-center mb-4 no-print">
      <?php renderIdCardBack($student, $principal, $pTitle, $schoolName, $schoolShort, $schoolAddr); ?>
    </div>

    <div class="alert alert-info small no-print">
      <i class="fas fa-info-circle me-1"></i>
      Click <strong>Print Card</strong> to print on standard ID card stock (CR80 — 3.375" × 2.125").
      Set printer to <strong>no margins</strong> and <strong>actual size</strong>.
    </div>
  </div>
</div>

<style>
<?php echo idCardCss(); ?>

@media print {
  .no-print, header, aside, nav, footer, .sidebar, .topbar,
  .breadcrumb, h4, .alert { display: none !important; }
  body, html { background: #fff !important; }
  .id-card-wrap { page-break-after: always; }
}
</style>
