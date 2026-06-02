<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-graduation-cap text-primary me-2"></i>Exams</h4>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('exams/marks') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen me-1"></i>Enter Marks</a>
    <a href="<?= url('exams/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>New Exam</a>
  </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2">
      <div class="col-md-3"><select name="class_id" class="form-select"><option value="">All Classes</option><?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>" <?= selected($classId,$c['id']) ?>>Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><select name="type" class="form-select"><option value="">All Types</option><?php foreach (['assignment','quiz','project','mid_exam','final_exam','national_prep'] as $t): ?><option value="<?= $t ?>" <?= selected($type,$t) ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option><?php endforeach; ?></select></div>
      <div class="col-auto"><button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button><a href="<?= url('exams') ?>" class="btn btn-outline-secondary ms-1">Clear</a></div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small" id="examsTable">
        <thead class="table-light">
          <tr><th>Title</th><th>Type</th><th>Class</th><th>Subject</th><th>Date</th><th>Total Marks</th><th>Pass Marks</th><th>Created By</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($exams)): ?>
          <tr><td class="text-center py-4 text-muted">No exams found</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
          <?php else: foreach ($exams as $e): ?>
          <tr>
            <td class="fw-semibold"><?= e($e['title']) ?></td>
            <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$e['type'])) ?></span></td>
            <td>Grade <?= e($e['grade']) ?>-<?= e($e['section']) ?></td>
            <td><?= e($e['subject_name']) ?></td>
            <td><?= $e['exam_date'] ? formatDate($e['exam_date']) : '<span class="text-muted">—</span>' ?></td>
            <td><?= e($e['total_marks']) ?></td>
            <td><?= e($e['pass_marks']) ?></td>
            <td class="text-muted small"><?= e($e['created_by_name']) ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <a href="<?= url('exams/marks?exam_id='.$e['id']) ?>" class="btn btn-outline-primary btn-xs" title="Enter Marks"><i class="fas fa-pen"></i></a>
                <a href="<?= url('exams/edit/'.$e['id']) ?>" class="btn btn-outline-secondary btn-xs" title="Edit"><i class="fas fa-edit"></i></a>
                <form action="<?= url('exams/delete/'.$e['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete exam and all its marks?')">
                  <?= csrfField() ?>
                  <button class="btn btn-outline-danger btn-xs" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
