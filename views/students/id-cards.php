<?php
require_once ROOT . '/views/students/_id_card_helper.php';

$schoolName  = getSetting('school_name','Shalaka Jatan Ali Secondary School');
$schoolShort = 'SJASS';
$schoolAddr  = getSetting('school_address','');

$streamColors = [
    'general' => 'primary', 'social' => 'purple', 'natural' => 'success'
];
$gradeColors = ['9'=>'warning','10'=>'primary','11'=>'indigo','12'=>'danger'];
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-id-card text-primary me-2"></i>ID Card Management</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
      <li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li>
      <li class="breadcrumb-item active">ID Cards</li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('students/create') ?>" class="btn btn-sm btn-success">
      <i class="fas fa-plus me-1"></i>Add Student
    </a>
    <button class="btn btn-sm btn-primary" id="btnBulkPrint" onclick="bulkPrint()" disabled>
      <i class="fas fa-print me-1"></i>Print Selected
    </button>
  </div>
</div>

<!-- Stats row -->
<?php
$byGrade = array_count_values(array_column($students,'grade'));
$total = count($students);
?>
<div class="row g-3 mb-4">
  <?php foreach(['9','10','11','12'] as $g): ?>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body py-3 text-center">
        <div style="font-size:1.6rem;font-weight:800;color:var(--bs-<?= $gradeColors[$g] ?? 'primary' ?>)">
          <?= $byGrade[$g] ?? 0 ?>
        </div>
        <div class="small text-muted fw-semibold">Grade <?= $g ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold mb-1">Grade</label>
        <select name="grade" class="form-select form-select-sm">
          <option value="">All Grades</option>
          <?php foreach(['9','10','11','12'] as $g): ?>
          <option value="<?= $g ?>" <?= $grade===$g?'selected':'' ?>>Grade <?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold mb-1">Section</label>
        <select name="section" class="form-select form-select-sm">
          <option value="">All Sections</option>
          <?php foreach(array_unique(array_column($classes,'section')) as $sec): ?>
          <option value="<?= $sec ?>" <?= $section===$sec?'selected':'' ?>><?= $sec ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold mb-1">Stream / Program</label>
        <select name="stream" class="form-select form-select-sm">
          <option value="">All Programs</option>
          <option value="general"  <?= $stream==='general' ?'selected':'' ?>>General Program</option>
          <option value="social"   <?= $stream==='social'  ?'selected':'' ?>>Social Science</option>
          <option value="natural"  <?= $stream==='natural' ?'selected':'' ?>>Natural Science</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold mb-1">Search</label>
        <input type="text" name="search" class="form-control form-control-sm"
               value="<?= e($search) ?>" placeholder="Name or ID…">
      </div>
      <div class="col-md-1">
        <button type="submit" class="btn btn-primary btn-sm w-100">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Mode toggle -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <div class="small text-muted">
    Showing <strong><?= $total ?></strong> student<?= $total!==1?'s':'' ?>
  </div>
  <div class="btn-group btn-group-sm">
    <button class="btn btn-outline-secondary active" id="btnTableView" onclick="setView('table')">
      <i class="fas fa-list"></i> Table
    </button>
    <button class="btn btn-outline-secondary" id="btnCardView" onclick="setView('cards')">
      <i class="fas fa-th"></i> Preview Cards
    </button>
  </div>
</div>

<!-- ── TABLE VIEW ─────────────────────────────── -->
<div id="tableView">
  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:40px">
              <input type="checkbox" id="selectAll" class="form-check-input" onchange="toggleAll(this)">
            </th>
            <th>Student</th>
            <th>Card No.</th>
            <th>Grade</th>
            <th>Section</th>
            <th>Program</th>
            <th>Blood</th>
            <th>Status</th>
            <th style="width:140px"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
          <tr><td colspan="9" class="text-center text-muted py-5">
            <i class="fas fa-id-card fa-2x mb-2 d-block opacity-25"></i>
            No students found. <a href="<?= url('students/create') ?>">Add a student</a>.
          </td></tr>
          <?php endif; ?>
          <?php foreach ($students as $stu):
            $th    = idCardTheme($stu['grade']??'9', $stu['stream']??'general');
            $cardNo= idCardNumber($stu);
            $stream= $stu['stream'] ?? 'general';
          ?>
          <tr>
            <td>
              <input type="checkbox" class="form-check-input stu-check" value="<?= $stu['id'] ?>"
                     onchange="updateBulkBtn()">
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <!-- Mini avatar -->
                <div class="rounded-2 d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                     style="width:36px;height:36px;background:linear-gradient(135deg,<?= $th['bg1'] ?>,<?= $th['bg2'] ?>);font-size:12px">
                  <?= idCardInitials($stu) ?>
                </div>
                <div>
                  <div class="fw-semibold small"><?= e($stu['first_name'].' '.$stu['last_name']) ?></div>
                  <div class="text-muted" style="font-size:11px"><?= e($stu['student_id']) ?></div>
                </div>
              </div>
            </td>
            <td><code class="small"><?= $cardNo ?></code></td>
            <td><span class="badge bg-secondary"><?= $stu['grade'] ?? '—' ?></span></td>
            <td><?= $stu['section'] ?? '—' ?></td>
            <td>
              <?php $sc = ['general'=>'primary','social'=>'warning','natural'=>'success']; ?>
              <span class="badge bg-<?= $sc[$stream] ?? 'secondary' ?>-subtle text-<?= $sc[$stream] ?? 'secondary' ?> border" style="font-size:10px">
                <?= idCardStreamLabel($stream) ?>
              </span>
            </td>
            <td><?= e($stu['blood_type'] ?? '—') ?></td>
            <td>
              <span class="badge bg-<?= $stu['status']==='active'?'success':'secondary' ?>-subtle text-<?= $stu['status']==='active'?'success':'secondary' ?> border" style="font-size:10px">
                <?= $stu['status'] ?? 'active' ?>
              </span>
            </td>
            <td class="text-end pe-3">
              <a href="<?= url('students/id-card/'.$stu['id']) ?>"
                 class="btn btn-outline-primary btn-sm" title="Preview Card">
                <i class="fas fa-id-card"></i>
              </a>
              <a href="<?= url('students/'.$stu['id'].'/edit') ?>"
                 class="btn btn-outline-secondary btn-sm" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ── CARD PREVIEW VIEW ─────────────────────── -->
<div id="cardView" style="display:none">
  <?php if (empty($students)): ?>
  <div class="text-center text-muted py-5">No students found.</div>
  <?php else: ?>
  <div style="display:flex;flex-wrap:wrap;gap:20px;justify-content:center">
    <?php foreach ($students as $stu): ?>
    <div style="cursor:pointer" onclick="window.location='<?= url('students/id-card/'.$stu['id']) ?>'">
      <?php renderIdCard($stu, $principal, $pTitle, $schoolName, $schoolShort, $schoolAddr); ?>
      <div class="text-center mt-1" style="font-size:11px;color:#888">
        <?= e($stu['first_name'].' '.$stu['last_name']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<style>
<?php echo idCardCss(); ?>
/* Scale down cards in grid preview */
#cardView .id-card {
  transform: scale(0.72);
  transform-origin: top left;
  margin-bottom: -72px;
}
#cardView .id-card-wrap {
  width: 290px;
  height: 182px;
  overflow: hidden;
  border-radius: 12px;
}
</style>

<script>
function toggleAll(cb) {
  document.querySelectorAll('.stu-check').forEach(c => c.checked = cb.checked);
  updateBulkBtn();
}
function updateBulkBtn() {
  var count = document.querySelectorAll('.stu-check:checked').length;
  var btn   = document.getElementById('btnBulkPrint');
  btn.disabled = count === 0;
  btn.innerHTML = '<i class="fas fa-print me-1"></i>Print Selected' + (count ? ' (' + count + ')' : '');
}
function bulkPrint() {
  var ids = Array.from(document.querySelectorAll('.stu-check:checked')).map(c => c.value);
  if (!ids.length) return;
  window.open('<?= url('students/id-cards/bulk-print') ?>?ids=' + ids.join(','), '_blank');
}
function setView(v) {
  document.getElementById('tableView').style.display = v==='table' ? '' : 'none';
  document.getElementById('cardView').style.display  = v==='cards' ? '' : 'none';
  document.getElementById('btnTableView').classList.toggle('active', v==='table');
  document.getElementById('btnCardView').classList.toggle('active', v==='cards');
}
</script>
