<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-database text-primary me-2"></i>Question Bank</h4>
    <p class="text-muted mb-0 small"><?= number_format($total) ?> questions in bank</p>
  </div>
  <?php if (Auth::can('academics') || Auth::hasRole(['teacher','dept_head'])): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
    <i class="fas fa-plus me-1"></i>Add Question
  </button>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <input type="text" name="search" class="form-control form-control-sm" value="<?= e($search) ?>" placeholder="Search question text...">
      </div>
      <div class="col-md-2">
        <select name="subject_id" class="form-select form-select-sm">
          <option value="">All Subjects</option>
          <?php foreach ($subjects as $s): ?>
          <option value="<?= $s['id'] ?>" <?= selected($subId,$s['id']) ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="grade" class="form-select form-select-sm">
          <option value="">All Grades</option>
          <?php foreach (['9','10','11','12'] as $g): ?>
          <option value="<?= $g ?>" <?= selected($grade,$g) ?>>Grade <?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="difficulty" class="form-select form-select-sm">
          <option value="">All Difficulty</option>
          <option value="easy" <?= selected($diff,'easy') ?>>Easy</option>
          <option value="medium" <?= selected($diff,'medium') ?>>Medium</option>
          <option value="hard" <?= selected($diff,'hard') ?>>Hard</option>
        </select>
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select form-select-sm">
          <option value="">All Types</option>
          <?php foreach (['mcq'=>'MCQ','true_false'=>'True/False','short_answer'=>'Short Answer','essay'=>'Essay','practical'=>'Practical'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= selected($type,$v) ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <a href="<?= url('exam-repository/question-bank') ?>" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Stats by difficulty -->
<div class="row g-3 mb-4">
  <?php
  $db = getDB();
  $diffStats = $db->query("SELECT difficulty, COUNT(*) as cnt FROM question_bank WHERE status='active' GROUP BY difficulty")->fetchAll(PDO::FETCH_KEY_PAIR);
  $typeStats  = $db->query("SELECT question_type, COUNT(*) as cnt FROM question_bank WHERE status='active' GROUP BY question_type")->fetchAll(PDO::FETCH_KEY_PAIR);
  ?>
  <?php foreach (['easy'=>['color'=>'success','icon'=>'star'], 'medium'=>['color'=>'warning','icon'=>'star-half-alt'], 'hard'=>['color'=>'danger','icon'=>'star']] as $d=>$meta): ?>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm text-center py-3">
      <div class="stat-icon bg-<?= $meta['color'] ?>-light text-<?= $meta['color'] ?> rounded-3 mx-auto mb-2" style="width:40px;height:40px">
        <i class="fas fa-<?= $meta['icon'] ?>"></i>
      </div>
      <div class="fw-bold fs-5"><?= $diffStats[$d] ?? 0 ?></div>
      <div class="text-muted small"><?= ucfirst($d) ?> Questions</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Questions List -->
<?php if (empty($questions)): ?>
<div class="card border-0 shadow-sm text-center py-5">
  <div class="text-muted"><i class="fas fa-database fa-3x mb-3"></i><br><h6>No questions in bank</h6><p class="small">Add questions using the button above</p></div>
</div>
<?php else: ?>
<div class="accordion" id="questionAccordion">
  <?php foreach ($questions as $i => $q):
    $diffColor = ['easy'=>'success','medium'=>'warning','hard'=>'danger'][$q['difficulty']] ?? 'secondary';
    $typeLabel  = ['mcq'=>'MCQ','true_false'=>'True/False','short_answer'=>'Short Answer','essay'=>'Essay','practical'=>'Practical'][$q['question_type']] ?? $q['question_type'];
  ?>
  <div class="accordion-item border-0 shadow-sm mb-2">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#q<?= $q['id'] ?>">
        <div class="d-flex align-items-center gap-2 w-100 me-2">
          <span class="badge bg-<?= $diffColor ?>" style="min-width:55px"><?= ucfirst($q['difficulty']) ?></span>
          <span class="badge bg-light text-dark border"><?= $typeLabel ?></span>
          <?php if ($q['subject_name']): ?><span class="badge bg-light text-dark border small"><?= e($q['subject_name']) ?></span><?php endif; ?>
          <span class="ms-2 text-dark small fw-semibold flex-fill"><?= e(truncate($q['question_text'], 80)) ?></span>
          <span class="text-muted small ms-2"><?= $q['marks'] ?> mark(s)</span>
        </div>
      </button>
    </h2>
    <div id="q<?= $q['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#questionAccordion">
      <div class="accordion-body bg-light rounded-bottom border-top">
        <div class="row g-3">
          <div class="col-md-8">
            <div class="fw-semibold mb-2">Question:</div>
            <div class="mb-3"><?= nl2br(e($q['question_text'])) ?></div>

            <?php if ($q['question_type'] === 'mcq' && $q['option_a']): ?>
            <div class="row g-2 mb-3">
              <?php foreach (['A','B','C','D'] as $opt):
                $key = 'option_'.strtolower($opt);
                if (!$q[$key]) continue;
                $isCorrect = strtoupper($q['correct_answer']) === $opt;
              ?>
              <div class="col-md-6">
                <div class="p-2 rounded border <?= $isCorrect ? 'bg-success text-white border-success' : 'bg-white' ?>">
                  <strong><?= $opt ?>.</strong> <?= e($q[$key]) ?>
                  <?php if ($isCorrect): ?><i class="fas fa-check ms-2"></i><?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php elseif ($q['question_type'] === 'true_false'): ?>
            <div class="mb-3"><strong>Answer:</strong> <span class="badge bg-success"><?= e($q['correct_answer']) ?></span></div>
            <?php elseif ($q['correct_answer']): ?>
            <div class="mb-3 p-2 bg-success-light rounded border-start border-3 border-success"><strong>Answer:</strong> <?= e($q['correct_answer']) ?></div>
            <?php endif; ?>

            <?php if ($q['explanation']): ?>
            <div class="alert alert-info py-2 mb-0 small"><strong>Explanation:</strong> <?= e($q['explanation']) ?></div>
            <?php endif; ?>
          </div>
          <div class="col-md-4">
            <div class="bg-white rounded border p-3 small">
              <div class="mb-1"><strong>Subject:</strong> <?= e($q['subject_name'] ?? '—') ?></div>
              <div class="mb-1"><strong>Grade:</strong> <?= $q['grade']==='all'?'All Grades':'Grade '.$q['grade'] ?></div>
              <div class="mb-1"><strong>Chapter:</strong> <?= e($q['chapter'] ?? '—') ?></div>
              <div class="mb-1"><strong>Marks:</strong> <?= $q['marks'] ?></div>
              <?php if ($q['learning_outcome']): ?><div class="mb-1"><strong>Outcome:</strong> <?= e($q['learning_outcome']) ?></div><?php endif; ?>
              <div><strong>Added by:</strong> <?= e($q['creator'] ?? '—') ?></div>
            </div>
            <?php if (Auth::can('academics') || Auth::hasRole(['teacher']) && $q['created_by']==Auth::id()): ?>
            <form action="<?= url('exam-repository/question-bank/delete/'.$q['id']) ?>" method="POST" class="mt-2" onsubmit="return confirm('Remove question from bank?')">
              <?= csrfField() ?>
              <button class="btn btn-sm btn-outline-danger w-100"><i class="fas fa-trash me-1"></i>Remove Question</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="d-flex justify-content-center mt-4">
  <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Add Question Modal -->
<?php if (Auth::can('academics') || Auth::hasRole(['teacher','dept_head'])): ?>
<div class="modal fade" id="addQuestionModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title"><i class="fas fa-plus me-2"></i>Add Question to Bank</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('exam-repository/question-bank') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label">Subject</label>
              <select name="subject_id" class="form-select">
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= $s['grade'] ?>)</option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label">Grade</label>
              <select name="grade" class="form-select">
                <option value="all">All</option>
                <?php foreach (['9','10','11','12'] as $g): ?><option value="<?= $g ?>">Gr. <?= $g ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Chapter / Topic</label>
              <input type="text" name="chapter" class="form-control" placeholder="e.g. Chapter 3">
            </div>
            <div class="col-md-2">
              <label class="form-label">Marks</label>
              <input type="number" name="marks" class="form-control" value="2" min="1">
            </div>
            <div class="col-md-4">
              <label class="form-label">Question Type <span class="text-danger">*</span></label>
              <select name="question_type" class="form-select" id="qTypeSelect">
                <option value="mcq">Multiple Choice (MCQ)</option>
                <option value="true_false">True / False</option>
                <option value="short_answer">Short Answer</option>
                <option value="essay">Essay</option>
                <option value="practical">Practical</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Difficulty <span class="text-danger">*</span></label>
              <select name="difficulty" class="form-select">
                <option value="easy">Easy</option>
                <option value="medium" selected>Medium</option>
                <option value="hard">Hard</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Learning Outcome</label>
              <input type="text" name="learning_outcome" class="form-control" placeholder="e.g. Solve linear equations">
            </div>
            <div class="col-12">
              <label class="form-label">Question Text <span class="text-danger">*</span></label>
              <textarea name="question_text" class="form-control" rows="3" required placeholder="Write the question here..."></textarea>
            </div>
            <!-- MCQ Options -->
            <div id="mcqOptions" class="col-12">
              <label class="form-label">Answer Options</label>
              <div class="row g-2">
                <?php foreach (['a'=>'A','b'=>'B','c'=>'C','d'=>'D'] as $k=>$l): ?>
                <div class="col-md-6">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text fw-bold"><?= $l ?>.</span>
                    <input type="text" name="option_<?= $k ?>" class="form-control" placeholder="Option <?= $l ?>">
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-md-6" id="correctAnswerDiv">
              <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
              <input type="text" name="correct_answer" class="form-control" placeholder="For MCQ: A / B / C / D. For others: write answer">
            </div>
            <div class="col-12">
              <label class="form-label">Explanation <small class="text-muted">(optional)</small></label>
              <textarea name="explanation" class="form-control" rows="2" placeholder="Explain why the answer is correct..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tags</label>
              <input type="text" name="tags" class="form-control" placeholder="algebra, chapter1, revision">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Add to Bank</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('qTypeSelect').addEventListener('change', function() {
  var mcq = document.getElementById('mcqOptions');
  var ca  = document.getElementById('correctAnswerDiv');
  if (this.value === 'mcq') { mcq.style.display=''; ca.querySelector('input').placeholder='A / B / C / D'; }
  else if (this.value === 'true_false') { mcq.style.display='none'; ca.querySelector('input').placeholder='True or False'; }
  else if (this.value === 'essay') { mcq.style.display='none'; ca.style.display='none'; }
  else { mcq.style.display='none'; ca.style.display=''; ca.querySelector('input').placeholder='Expected answer'; }
});
</script>
<?php endif; ?>
