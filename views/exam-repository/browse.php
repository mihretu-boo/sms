<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-search text-primary me-2"></i>Browse Examinations</h4>
    <p class="text-muted mb-0 small"><?= number_format($total) ?> examination(s) found</p>
  </div>
  <?php if (Auth::can('academics') || Auth::hasRole(['teacher','dept_head'])): ?>
  <a href="<?= url('exam-repository/upload') ?>" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Upload Exam</a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" action="<?= url('exam-repository/browse') ?>" class="row g-2 align-items-end">
      <div class="col-md-3">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
          <input type="text" name="search" class="form-control border-start-0" value="<?= e($search) ?>" placeholder="Search title, tags...">
        </div>
      </div>
      <div class="col-md-2">
        <select name="grade" class="form-select">
          <option value="">All Grades</option>
          <?php foreach (['9','10','11','12'] as $g): ?>
          <option value="<?= $g ?>" <?= selected($grade,$g) ?>>Grade <?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="subject_id" class="form-select">
          <option value="">All Subjects</option>
          <?php foreach ($subjects as $s): ?>
          <option value="<?= $s['id'] ?>" <?= selected($subId,$s['id']) ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <optgroup label="Internal">
            <?php foreach (['quiz'=>'Quiz','test'=>'Test','assignment'=>'Assignment','mid_semester'=>'Mid Exam','final'=>'Final Exam','practical'=>'Practical'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= selected($type,$v) ?>><?= $l ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="External">
            <?php foreach (['regional'=>'Regional','national'=>'National','mock'=>'Mock','entrance'=>'Entrance'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= selected($type,$v) ?>><?= $l ?></option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('exam-repository/browse') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- Folder-style navigation tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
  <a href="<?= url('exam-repository/browse') ?>" class="btn btn-sm <?= !$grade ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
  <?php foreach (['9'=>'Grade 9','10'=>'Grade 10','11'=>'Grade 11','12'=>'Grade 12'] as $g=>$l): ?>
  <a href="<?= url('exam-repository/browse?grade='.$g) ?>" class="btn btn-sm <?= $grade==$g ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>

<!-- Results Grid -->
<?php if (empty($exams)): ?>
<div class="card border-0 shadow-sm text-center py-5">
  <div class="text-muted"><i class="fas fa-folder-open fa-3x mb-3"></i><br><h6>No examinations found</h6><p class="small">Try different filter options</p></div>
</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($exams as $e): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100 exam-card">
      <div class="card-body">
        <!-- File type indicator + Grade badge -->
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-light rounded">
              <i class="fas <?= ExamRepositoryController::fileIcon($e['file_mime'] ?? '') ?> fa-lg"></i>
            </div>
            <div>
              <span class="badge bg-light text-dark border"><?= $e['grade']==='all'?'All Grades':'Grade '.$e['grade'] ?></span>
              <span class="badge bg-<?= $e['category_type']==='external'?'warning text-dark':'info' ?> ms-1"><?= ucfirst($e['category_type']) ?></span>
            </div>
          </div>
          <?= getStatusBadge($e['status']) ?>
        </div>

        <!-- Title -->
        <h6 class="fw-bold mb-1"><?= e(truncate($e['title'], 60)) ?></h6>

        <!-- Meta -->
        <div class="small text-muted mb-2">
          <?php if ($e['subject_name']): ?><i class="fas fa-book me-1"></i><?= e($e['subject_name']) ?><br><?php endif; ?>
          <i class="fas fa-tag me-1"></i><?= ucfirst(str_replace('_',' ',$e['exam_type'])) ?>
          &nbsp;|&nbsp;<i class="fas fa-signal me-1"></i><?= ucfirst($e['difficulty']) ?>
        </div>

        <?php if ($e['description']): ?>
        <p class="text-muted small mb-2"><?= e(truncate($e['description'], 80)) ?></p>
        <?php endif; ?>

        <!-- Tags -->
        <?php if ($e['tags']): ?>
        <div class="d-flex flex-wrap gap-1 mb-2">
          <?php foreach (array_slice(array_filter(explode(',', $e['tags'])),0,3) as $tag): ?>
          <span class="badge bg-light text-muted border" style="font-size:10px"><?= e(trim($tag)) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Footer info -->
        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top small text-muted">
          <span><i class="fas fa-download me-1"></i><?= $e['download_count'] ?></span>
          <span><?= e($e['uploader']) ?></span>
          <span><?= formatDate($e['created_at'],'d M Y') ?></span>
        </div>
      </div>
      <div class="card-footer bg-white border-top-0 pt-0">
        <div class="d-flex gap-1">
          <a href="<?= url('exam-repository/view/'.$e['id']) ?>" class="btn btn-sm btn-outline-primary flex-fill">
            <i class="fas fa-eye me-1"></i>View
          </a>
          <?php if ($e['status']==='approved'): ?>
          <a href="<?= url('exam-repository/download/'.$e['id']) ?>" class="btn btn-sm btn-success flex-fill">
            <i class="fas fa-download me-1"></i>Download
          </a>
          <?php endif; ?>
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

<style>
.exam-card { transition: transform 0.2s, box-shadow 0.2s; }
.exam-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important; }
</style>
