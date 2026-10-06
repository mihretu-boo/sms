<?php
// $counts = ['9' => 42, '10' => 38, ...]
$total = array_sum($counts);
$gradeColors = ['9'=>'warning','10'=>'primary','11'=>'success','12'=>'danger'];
?>

<!-- Page header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold">
      <i class="fas fa-file-excel text-success me-2"></i>
      Excel Export — Roster &amp; Report Cards
    </h4>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
        <li class="breadcrumb-item active">Excel Export</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Info banner -->
<div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-start gap-3">
  <i class="fas fa-info-circle fa-lg mt-1 text-info flex-shrink-0"></i>
  <div>
    <strong>About this export:</strong>
    Generates a formatted Excel workbook (.xlsx) with class rosters for all grades.
    Each roster has <em>40 student slots</em> with Semester&nbsp;I, Semester&nbsp;II, and Average rows,
    auto-calculated totals, ranks, and a colour-coded Pass/Fail column.
    Optionally include individual <strong>Report Card</strong> sheets for each enrolled student.
  </div>
</div>

<!-- Grade summary cards -->
<div class="row g-3 mb-4">
  <?php foreach ([9,10,11,12] as $g): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-3">
        <div style="font-size:1.8rem;font-weight:800;color:var(--bs-<?= $gradeColors[$g] ?>)">
          <?= $counts[$g] ?? 0 ?>
        </div>
        <div class="small text-muted fw-semibold">Grade <?= $g ?></div>
        <div class="text-muted" style="font-size:11px">enrolled students</div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Export cards -->
<div class="row g-4">

  <!-- All Grades -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header border-0 py-3"
           style="background:linear-gradient(135deg,#1A5E20,#2E7D32)">
        <h5 class="mb-0 text-white fw-bold">
          <i class="fas fa-layer-group me-2"></i>
          All Grades Roster
        </h5>
        <small class="text-white opacity-75">Grades 9 – 12 in one workbook</small>
      </div>
      <div class="card-body">
        <ul class="list-unstyled small mb-4">
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Ros9 · Ros10 · Ros11 · Ros12 sheets</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Live student names from database</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>3 rows per student: Sem I / Sem II / Average</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Auto-calculated average, rank, Pass/Fail</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Colour-scale on averages, conditional formatting</li>
          <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Data validation dropdowns (Sex, Stream)</li>
          <li class="mb-2 text-muted"><i class="fas fa-circle me-2" style="font-size:8px"></i>Total students: <strong><?= $total ?></strong></li>
        </ul>

        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="kardAll" name="kard_all">
            <label class="form-check-label fw-semibold" for="kardAll">
              Include Report Card sheets
              <small class="text-muted d-block fw-normal">
                Adds one Kard sheet per enrolled student (may be large for many students)
              </small>
            </label>
          </div>
        </div>

        <a id="btnAll" href="<?= url('reports/excel-roster?grade=all') ?>"
           class="btn btn-success w-100 fw-bold" onclick="applyKard(this,'all')">
          <i class="fas fa-download me-2"></i>Download All Grades (.xlsx)
        </a>
      </div>
    </div>
  </div>

  <!-- Per-grade -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header border-0 py-3"
           style="background:linear-gradient(135deg,#0D47A1,#1565C0)">
        <h5 class="mb-0 text-white fw-bold">
          <i class="fas fa-filter me-2"></i>
          Single Grade Roster
        </h5>
        <small class="text-white opacity-75">Generate one grade at a time</small>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">
          Faster download, smaller file — useful when you only need one grade
          or want to print report cards for a single cohort.
        </p>

        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="kardSingle" name="kard_single">
            <label class="form-check-label fw-semibold" for="kardSingle">
              Include Report Card sheets
            </label>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <?php foreach ([9,10,11,12] as $g): ?>
          <div class="col-6">
            <a href="<?= url("reports/excel-roster?grade={$g}") ?>"
               class="btn btn-outline-<?= $gradeColors[$g] ?> w-100 grade-btn"
               data-grade="<?= $g ?>"
               onclick="applyKard(this,<?= $g ?>)">
              <i class="fas fa-download me-1"></i>
              Grade <?= $g ?>
              <span class="badge bg-<?= $gradeColors[$g] ?> ms-1">
                <?= $counts[$g] ?? 0 ?>
              </span>
            </a>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="alert alert-warning py-2 px-3 small mb-0">
          <i class="fas fa-clock me-1"></i>
          <strong>Generation time:</strong> ~5-10 seconds per grade.
          Large classes with report cards may take up to 30 s.
        </div>
      </div>
    </div>
  </div>

</div>

<!-- What's in the workbook -->
<div class="card border-0 shadow-sm mt-4">
  <div class="card-header border-0 bg-light py-3">
    <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-muted"></i>Workbook Sheet Guide</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th class="ps-4">Sheet</th>
            <th>Contents</th>
            <th>Grades</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="ps-4 fw-bold text-success">Baafata</td>
            <td>Table of contents with clickable links to every sheet</td>
            <td>—</td>
          </tr>
          <tr class="table-light">
            <td class="ps-4 fw-bold">Inf0</td>
            <td>School name, region, principal, academic year, pass mark</td>
            <td>—</td>
          </tr>
          <tr>
            <td class="ps-4 fw-bold text-warning">Ros9 · Ros10</td>
            <td>Class roster — 12 subjects (Afan Oromo, Amharic, English, Math, Physics, Chemistry, Biology, Geography, History, Citizenship, IT, HPE)</td>
            <td>9 · 10</td>
          </tr>
          <tr class="table-light">
            <td class="ps-4 fw-bold text-warning">Ros11 · Ros12</td>
            <td>Class roster — 10 subjects, NS/SS stream column. NS: Physics, Chemistry, Biology. SS: Geography, History, Economics</td>
            <td>11 · 12</td>
          </tr>
          <tr>
            <td class="ps-4 fw-bold text-primary">Kard sheets</td>
            <td>One printable report card per student — 5-component assessment per subject per semester, summary, attendance, behaviour, signatures</td>
            <td>All (optional)</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Spinner overlay -->
<div id="genOverlay"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);
            z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:16px">
  <div class="spinner-border text-light" style="width:3.5rem;height:3.5rem"></div>
  <div class="text-white fw-bold fs-5">Generating Excel workbook…</div>
  <div class="text-white-50 small">This may take up to 30 seconds</div>
</div>

<script>
function applyKard(link, grade) {
  var kardId = (grade === 'all') ? 'kardAll' : 'kardSingle';
  var kard   = document.getElementById(kardId).checked ? 1 : 0;
  var base   = '<?= url('reports/excel-roster') ?>?grade=' + grade + (kard ? '&kard=1' : '');
  link.href  = base;

  // Show spinner while generating
  document.getElementById('genOverlay').style.display = 'flex';
  setTimeout(function() {
    document.getElementById('genOverlay').style.display = 'none';
  }, 35000);
}
</script>
