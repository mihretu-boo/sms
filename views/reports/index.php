<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chart-bar text-primary me-2"></i>Reports Center</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Reports</li></ol></nav>
  </div>
</div>

<div class="row g-4">
  <!-- Academic Reports -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card">
      <div class="card-body text-center py-5">
        <div class="report-icon bg-primary-light text-primary rounded-3 mx-auto mb-3">
          <i class="fas fa-graduation-cap fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Academic Reports</h6>
        <p class="text-muted small mb-3">Student performance, GPA distribution, subject analysis by class and semester</p>
        <a href="<?= url('reports/academic') ?>" class="btn btn-primary w-100">View Academic Report</a>
      </div>
    </div>
  </div>

  <!-- Attendance Reports -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card">
      <div class="card-body text-center py-5">
        <div class="report-icon bg-success-light text-success rounded-3 mx-auto mb-3">
          <i class="fas fa-calendar-check fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Attendance Reports</h6>
        <p class="text-muted small mb-3">Daily, weekly, monthly attendance rates by class and individual students</p>
        <a href="<?= url('reports/attendance') ?>" class="btn btn-success w-100">View Attendance Report</a>
      </div>
    </div>
  </div>

  <!-- Financial Reports -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card">
      <div class="card-body text-center py-5">
        <div class="report-icon bg-warning-light text-warning rounded-3 mx-auto mb-3">
          <i class="fas fa-money-bill-wave fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Financial Reports</h6>
        <p class="text-muted small mb-3">Income, expenses, fee collection status, payroll summaries</p>
        <a href="<?= url('reports/financial') ?>" class="btn btn-warning w-100">View Financial Report</a>
      </div>
    </div>
  </div>

  <!-- Staff Reports -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card">
      <div class="card-body text-center py-5">
        <div class="report-icon bg-info-light text-info rounded-3 mx-auto mb-3">
          <i class="fas fa-users fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Staff Reports</h6>
        <p class="text-muted small mb-3">Staff distribution by department, qualification, attendance and leave</p>
        <a href="<?= url('reports/staff') ?>" class="btn btn-info w-100 text-white">View Staff Report</a>
      </div>
    </div>
  </div>

  <!-- Annual Report -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card">
      <div class="card-body text-center py-5">
        <div class="report-icon bg-danger-light text-danger rounded-3 mx-auto mb-3">
          <i class="fas fa-file-alt fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Annual Report</h6>
        <p class="text-muted small mb-3">Comprehensive school annual report with all key metrics and achievements</p>
        <a href="<?= url('reports/annual') ?>" class="btn btn-danger w-100">View Annual Report</a>
      </div>
    </div>
  </div>

  <!-- Data Export -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card">
      <div class="card-body text-center py-5">
        <div class="report-icon bg-primary-light text-primary rounded-3 mx-auto mb-3">
          <i class="fas fa-download fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Data Export</h6>
        <p class="text-muted small mb-3">Export students, staff, and payment data to CSV for offline processing</p>
        <div class="d-flex flex-column gap-2">
          <a href="<?= url('reports/export?type=students') ?>" class="btn btn-outline-primary btn-sm">Export Students CSV</a>
          <a href="<?= url('reports/export?type=staff') ?>" class="btn btn-outline-primary btn-sm">Export Staff CSV</a>
          <a href="<?= url('reports/export?type=payments') ?>" class="btn btn-outline-primary btn-sm">Export Payments CSV</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Excel Roster & Report Cards -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100 report-card"
         style="border-top:4px solid #1A5E20 !important">
      <div class="card-body text-center py-5">
        <div class="report-icon rounded-3 mx-auto mb-3"
             style="background:rgba(26,94,32,.12);color:#1A5E20">
          <i class="fas fa-file-excel fa-2x"></i>
        </div>
        <h6 class="fw-bold mb-2">Excel Roster &amp; Report Cards</h6>
        <p class="text-muted small mb-3">
          Generate the official SJASS roster workbook with live student data,
          bilingual Afan Oromo/English labels, and optional printable Kard sheets.
        </p>
        <a href="<?= url('reports/excel') ?>"
           class="btn w-100 text-white fw-bold"
           style="background:#1A5E20">
          <i class="fas fa-file-excel me-1"></i>
          Open Excel Export
        </a>
        <div class="mt-2 d-flex gap-1 justify-content-center flex-wrap">
          <a href="<?= url('reports/excel-roster?grade=9') ?>"
             class="btn btn-sm btn-outline-secondary">Gr 9</a>
          <a href="<?= url('reports/excel-roster?grade=10') ?>"
             class="btn btn-sm btn-outline-secondary">Gr 10</a>
          <a href="<?= url('reports/excel-roster?grade=11') ?>"
             class="btn btn-sm btn-outline-secondary">Gr 11</a>
          <a href="<?= url('reports/excel-roster?grade=12') ?>"
             class="btn btn-sm btn-outline-secondary">Gr 12</a>
          <a href="<?= url('reports/excel-roster?grade=all') ?>"
             class="btn btn-sm btn-success">All</a>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
.report-card { transition: transform 0.2s, box-shadow 0.2s; }
.report-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important; }
.report-icon { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 16px; }
</style>
