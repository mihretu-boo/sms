<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-chalkboard text-primary me-2"></i>Assign Subjects to Class</h4>
    <p class="text-muted small mb-0">Ethiopian curriculum — stream-aware subject assignment</p>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/years') ?>">Academic Years</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/classes') ?>">Classes</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/subjects') ?>">Subjects</a></li>
  <li class="nav-item"><a class="nav-link" href="<?= url('academics/departments') ?>">Departments</a></li>
  <li class="nav-item"><a class="nav-link active" href="<?= url('academics/assign-subjects') ?>">Assign Subjects</a></li>
</ul>

<!-- Class selector -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label small fw-semibold">Select Class</label>
        <select name="class_id" class="form-select" onchange="this.form.submit()">
          <option value="">— Choose a class —</option>
          <?php foreach ($classes as $cls):
            $sl = match($cls['stream']) {'natural'=>' [Natural]','social'=>' [Social]',default=>''};
          ?>
          <option value="<?= $cls['id'] ?>" <?= selected($classId,$cls['id']) ?>>
            Grade <?= e($cls['grade']) ?>-<?= e($cls['section']) ?><?= $sl ?> (<?= $cls['student_count'] ?> students)
          </option>
          <?php endforeach; ?>
        </select>
      </div>
    </form>
  </div>
</div>

<?php if ($classId && $classInfo): ?>

<?php
$streamInfo = match($classStream) {
    'natural' => ['label'=>'Natural Science','color'=>'success','icon'=>'flask',
                  'desc'=>'Afaan Oromo · English · Mathematics · Physics · Chemistry · Biology · Agriculture · IT (8 subjects)'],
    'social'  => ['label'=>'Social Science','color'=>'info','icon'=>'globe',
                  'desc'=>'Afaan Oromo · English · Mathematics · Geography · History · Economics · Citizenship Education · IT (8 subjects)'],
    default   => ['label'=>'General (All Students)','color'=>'primary','icon'=>'users',
                  'desc'=>'13 subjects: Afaan Oromo · Amharic · English · Mathematics · Biology · Chemistry · Physics · Geography · History · Citizenship Ed · IT · Economics · HPE'],
};
?>

<!-- Class Banner -->
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-<?= $streamInfo['color'] ?>">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h6 class="fw-bold mb-1">
          Grade <?= e($classInfo['grade']) ?>-<?= e($classInfo['section']) ?>
          &nbsp;<span class="badge bg-<?= $streamInfo['color'] ?>">
            <i class="fas fa-<?= $streamInfo['icon'] ?> me-1"></i><?= $streamInfo['label'] ?>
          </span>
        </h6>
        <div class="text-muted small"><?= $streamInfo['desc'] ?></div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <form action="<?= url('academics/assign-subjects/auto') ?>" method="POST" class="d-inline"
              onsubmit="return confirm('Auto-assign all <?= $streamInfo['label'] ?> subjects? Existing assignments for this class/semester will be replaced.')">
          <?= csrfField() ?>
          <input type="hidden" name="class_id"    value="<?= e($classId) ?>">
          <input type="hidden" name="semester_id" value="<?= e($semId) ?>">
          <button type="submit" class="btn btn-sm btn-<?= $streamInfo['color'] ?>">
            <i class="fas fa-magic me-1"></i>Auto-Assign Curriculum
          </button>
        </form>
        <a href="<?= url('timetable/create?class_id='.$classId) ?>" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-clock me-1"></i>Timetable
        </a>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($subjects)): ?>
<form action="<?= url('academics/assign-subjects') ?>" method="POST">
  <?= csrfField() ?>
  <input type="hidden" name="class_id"    value="<?= e($classId) ?>">
  <input type="hidden" name="semester_id" value="<?= e($semId) ?>">

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
      <h6 class="mb-0 fw-semibold">
        <i class="fas fa-list text-primary me-2"></i>Subjects — <?= count($subjects) ?> available
      </h6>
      <div class="d-flex align-items-center gap-3">
        <div class="form-check mb-0">
          <input type="checkbox" class="form-check-input" id="selectAllSubs"
                 onchange="document.querySelectorAll('.sub-cb').forEach(c=>c.checked=this.checked);updateCount()">
          <label class="form-check-label small" for="selectAllSubs">Select All</label>
        </div>
        <span class="badge bg-primary" id="selectedCount">0 selected</span>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th width="40">✓</th>
              <th>Subject Name</th>
              <th width="80">Code</th>
              <th width="110">Stream</th>
              <th>Assigned Teacher</th>
              <th width="80">Hrs/Week</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $prevStream = '__start__';
            // Sort: common first, then stream-specific
            usort($subjects, function($a,$b) {
              $order = ['all'=>0,'natural'=>1,'social'=>2];
              $ao = $order[$a['stream']??'all']; $bo = $order[$b['stream']??'all'];
              return $ao !== $bo ? $ao - $bo : strcmp($a['name'],$b['name']);
            });

            foreach ($subjects as $s):
              $isAssigned = isset($assigned[$s['id']]);
              $curStream  = $s['stream'] ?? 'all';

              if ($curStream !== $prevStream && in_array($classInfo['grade'], ['11','12'])):
                $hdr = match($curStream) {
                    'natural' => ['bg-success','fas fa-flask','Natural Science — Stream-Specific Subjects'],
                    'social'  => ['bg-info','fas fa-globe','Social Science — Stream-Specific Subjects'],
                    default   => ['bg-primary','fas fa-users','Common Subjects (Both Streams)'],
                };
            ?>
            <tr>
              <td colspan="6" class="py-2 fw-semibold text-white <?= $hdr[0] ?> small">
                <i class="<?= $hdr[1] ?> me-2"></i><?= $hdr[2] ?>
              </td>
            </tr>
            <?php $prevStream = $curStream; endif; ?>

            <tr class="<?= $isAssigned ? 'table-success bg-opacity-50' : '' ?>">
              <td>
                <input type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>"
                       class="form-check-input sub-cb"
                       <?= $isAssigned ? 'checked' : '' ?>
                       onchange="updateCount()">
              </td>
              <td>
                <div class="fw-semibold"><?= e($s['name']) ?></div>
                <?php if ($isAssigned && ($assigned[$s['id']]['teacher_first'] ?? '')): ?>
                <div class="text-success" style="font-size:11px"><i class="fas fa-check-circle me-1"></i>Assigned</div>
                <?php elseif ($isAssigned): ?>
                <div class="text-warning" style="font-size:11px"><i class="fas fa-exclamation-circle me-1"></i>No teacher assigned</div>
                <?php endif; ?>
              </td>
              <td><span class="font-monospace text-muted"><?= e($s['code']) ?></span></td>
              <td>
                <?php if ($s['stream'] === 'natural'): ?>
                <span class="badge bg-success-light text-success border border-success" style="font-size:10px"><i class="fas fa-flask me-1"></i>Natural</span>
                <?php elseif ($s['stream'] === 'social'): ?>
                <span class="badge bg-info-light text-info border border-info" style="font-size:10px"><i class="fas fa-globe me-1"></i>Social</span>
                <?php else: ?>
                <span class="badge bg-light text-muted border" style="font-size:10px"><i class="fas fa-users me-1"></i>Common</span>
                <?php endif; ?>
              </td>
              <td>
                <select name="teacher_id[<?= $s['id'] ?>]" class="form-select form-select-sm">
                  <option value="">— No Teacher —</option>
                  <?php foreach ($teachers as $t): ?>
                  <option value="<?= $t['id'] ?>" <?= selected($assigned[$s['id']]['teacher_id'] ?? '', $t['id']) ?>>
                    <?= e($t['first_name'].' '.$t['last_name']) ?>
                    <?php if ($t['dept_name']): ?><span class="text-muted">(<?= e($t['dept_name']) ?>)</span><?php endif; ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <input type="number" name="periods[<?= $s['id'] ?>]"
                       class="form-control form-control-sm text-center"
                       value="<?= $assigned[$s['id']]['periods_per_week'] ?? ($s['periods_week'] ?? 3) ?>"
                       min="1" max="10" style="width:55px">
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
      <div class="small text-muted">
        <i class="fas fa-lightbulb text-warning me-1"></i>
        <strong>Tip:</strong> Use "Auto-Assign Curriculum" to instantly assign all <?= $streamInfo['label'] ?> subjects.
      </div>
      <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-save me-2"></i>Save Assignments
      </button>
    </div>
  </div>
</form>

<?php else: ?>
<div class="alert alert-warning">
  <i class="fas fa-exclamation-triangle me-2"></i>
  No subjects defined for this grade/stream.
  <a href="<?= url('academics/subjects') ?>" class="alert-link">Add subjects first</a>.
</div>
<?php endif; ?>

<?php else: ?>
<div class="card border-0 shadow-sm text-center py-5">
  <div class="text-muted">
    <i class="fas fa-chalkboard fa-3x mb-3 text-primary opacity-50"></i><br>
    <h6>Select a class to manage subject assignments</h6>
    <p class="small text-muted">For Grade 11/12, the system automatically shows only the appropriate subjects based on the class stream (Natural or Social Science).</p>
  </div>
</div>
<?php endif; ?>

<script>
function updateCount() {
  var n = document.querySelectorAll('.sub-cb:checked').length;
  document.getElementById('selectedCount').textContent = n + ' selected';
}
updateCount();
</script>
