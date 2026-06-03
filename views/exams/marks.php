<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-pen-alt text-primary me-2"></i>Enter Marks</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('exams') ?>">Exams</a></li><li class="breadcrumb-item active">Enter Marks</li></ol></nav>
  </div>
  <a href="<?= url('exams/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>New Exam</a>
</div>

<div class="row g-4">
  <!-- Exam selector -->
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-list text-primary me-2"></i>Select Exam</h6>
      </div>
      <div class="card-body p-0" style="max-height:500px;overflow-y:auto">
        <?php if (empty($exams)): ?>
        <div class="text-center py-4 text-muted small">No exams in current semester</div>
        <?php else: foreach ($exams as $e): ?>
        <a href="<?= url('exams/marks?exam_id='.$e['id']) ?>" class="d-block px-3 py-2 border-bottom text-decoration-none <?= $examId == $e['id'] ? 'bg-primary text-white' : 'text-dark' ?>">
          <div class="fw-semibold small"><?= htmlspecialchars($e['title']) ?></div>
          <div class="<?= $examId == $e['id'] ? 'text-white-50' : 'text-muted' ?>" style="font-size:11px">
            Grade <?= e($e['grade']) ?>-<?= e($e['section']) ?> | <?= e($e['subject_name']) ?>
            <span class="ms-1 badge <?= $examId == $e['id'] ? 'bg-white text-primary' : 'bg-light text-dark' ?>"><?= ucfirst(str_replace('_',' ',$e['type'])) ?></span>
          </div>
        </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Marks entry -->
  <div class="col-md-8">
    <?php if ($exam && !empty($students)): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-3">
        <div class="row g-2 small">
          <div class="col-md-4"><strong>Exam:</strong> <?= e($exam['title']) ?></div>
          <div class="col-md-3"><strong>Class:</strong> Grade <?= e($exam['grade']) ?>-<?= e($exam['section']) ?></div>
          <div class="col-md-3"><strong>Subject:</strong> <?= e($exam['subject_name']) ?></div>
          <div class="col-md-2"><strong>Total:</strong> <?= e($exam['total_marks']) ?> marks</div>
        </div>
      </div>
    </div>

    <form action="<?= url('exams/marks/save') ?>" method="POST" id="marksForm">
      <?= csrfField() ?>
      <input type="hidden" name="exam_id" value="<?= e($examId) ?>">

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
          <h6 class="mb-0 fw-semibold"><?= count($students) ?> Students</h6>
          <div>
            <span class="text-muted small">Pass Mark: <strong class="text-success"><?= e($exam['pass_marks']) ?></strong></span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr><th>#</th><th>Student</th><th>Student ID</th><th>Marks (/ <?= e($exam['total_marks']) ?>)</th><th>Grade</th><th>Remarks</th></tr>
              </thead>
              <tbody>
                <?php foreach ($students as $i => $s): ?>
                <?php $pct = $s['marks_obtained'] !== null && $exam['total_marks'] > 0 ? ($s['marks_obtained']/$exam['total_marks'])*100 : 0; ?>
                <tr>
                  <td class="text-muted small"><?= $i+1 ?></td>
                  <td>
                    <div class="fw-semibold small"><?= e($s['first_name'].' '.$s['last_name']) ?></div>
                  </td>
                  <td><span class="badge bg-light text-dark small font-monospace"><?= e($s['student_id']) ?></span></td>
                  <td style="width:140px">
                    <input type="number" name="marks[<?= $s['id'] ?>]" class="form-control form-control-sm marks-input"
                           min="0" max="<?= e($exam['total_marks']) ?>" step="0.5"
                           value="<?= $s['marks_obtained'] !== null ? e($s['marks_obtained']) : '' ?>"
                           placeholder="0-<?= e($exam['total_marks']) ?>"
                           data-total="<?= e($exam['total_marks']) ?>"
                           data-pass="<?= e($exam['pass_marks']) ?>"
                           data-idx="<?= $s['id'] ?>">
                  </td>
                  <td>
                    <?php
                    $gl = $s['grade_letter'] ?? '';
                    $glColor = $gl ? ('bg-' . (['A'=>'success','B'=>'primary','C'=>'info','D'=>'warning','F'=>'danger'][$gl[0]] ?? 'secondary')) : 'bg-light text-muted';
                    ?>
                    <span class="grade-badge-<?= $s['id'] ?> badge <?= $glColor ?>">
                      <?= e($gl ?: '—') ?>
                    </span>
                  </td>
                  <td>
                    <input type="text" name="remarks[<?= $s['id'] ?>]" class="form-control form-control-sm" placeholder="Optional" style="max-width:120px">
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end py-3">
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save All Marks</button>
        </div>
      </div>
    </form>
    <?php elseif ($examId && empty($students)): ?>
    <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No students found in this class.</div>
    <?php else: ?>
    <div class="card border-0 shadow-sm h-100 d-flex align-items-center justify-content-center" style="min-height:300px">
      <div class="text-center text-muted py-5">
        <i class="fas fa-hand-pointer fa-3x mb-3"></i><br>
        <h6>Select an exam from the list to enter marks</h6>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
const grades = <?= json_encode(GRADE_SCALE) ?>;

function getGrade(pct) {
  for (var g of grades) { if (pct >= g.min && pct <= g.max) return g; }
  return {letter:'F', point:0};
}

document.querySelectorAll('.marks-input').forEach(function(input) {
  input.addEventListener('input', function() {
    var val   = parseFloat(this.value) || 0;
    var total = parseFloat(this.dataset.total) || 100;
    var pct   = total > 0 ? (val/total)*100 : 0;
    var grade = getGrade(pct);
    var badge = document.querySelector('.grade-badge-' + this.dataset.idx);
    if (badge) {
      badge.textContent = grade.letter;
      badge.className = 'grade-badge-' + this.dataset.idx + ' badge bg-' +
        ({'A':'success','B':'primary','C':'info','D':'warning','F':'danger'}[grade.letter[0]] || 'secondary');
    }
  });
});
</script>
