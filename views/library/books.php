<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-book text-primary me-2"></i>Library — Books</h4>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item"><a href="<?= url('library') ?>">Library</a></li><li class="breadcrumb-item active">Books</li></ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('library/borrowings') ?>" class="btn btn-sm btn-outline-primary">Borrowings</a>
    <?php if (Auth::can('students')): ?>
    <a href="<?= url('library/books/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Book</a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
          <input type="text" class="form-control border-start-0" name="search" value="<?= e($search) ?>" placeholder="Title, author, ISBN...">
        </div>
      </div>
      <div class="col-md-3">
        <select name="category" class="form-select">
          <option value="">All Categories</option>
          <?php foreach ($cats as $c): ?>
          <option value="<?= e($c) ?>" <?= selected($category, $c) ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="<?= url('library/books') ?>" class="btn btn-outline-secondary ms-1">Clear</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white border-bottom d-flex justify-content-between py-3">
    <h6 class="mb-0 fw-semibold">Books Catalog</h6>
    <span class="text-muted small">Showing <?= count($books) ?> of <?= $total ?></span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Total</th><th>Available</th><th>Location</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($books)): ?>
          <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-book fa-2x mb-2"></i><br>No books found</td></tr>
          <?php else: foreach ($books as $b): ?>
          <tr>
            <td class="fw-semibold"><?= e($b['title']) ?></td>
            <td><?= e($b['author']) ?></td>
            <td><span class="badge bg-light text-dark"><?= e($b['category'] ?? '—') ?></span></td>
            <td class="text-muted font-monospace"><?= e($b['isbn'] ?? '—') ?></td>
            <td><?= $b['copies_total'] ?></td>
            <td>
              <span class="badge bg-<?= $b['copies_available'] > 0 ? 'success' : 'danger' ?>">
                <?= $b['copies_available'] ?>
              </span>
            </td>
            <td class="text-muted"><?= e($b['location'] ?? '—') ?></td>
            <td>
              <div class="btn-group btn-group-sm">
                <?php if (Auth::can('students')): ?>
                <a href="<?= url('library/books/edit/'.$b['id']) ?>" class="btn btn-outline-secondary btn-xs"><i class="fas fa-edit"></i></a>
                <?php endif; ?>
                <?php if ($b['copies_available'] > 0 && Auth::can('students')): ?>
                <button class="btn btn-outline-success btn-xs" onclick="showBorrowModal(<?= $b['id'] ?>, '<?= addslashes(e($b['title'])) ?>')" title="Issue Book"><i class="fas fa-hand-holding-medical"></i></button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php if ($pages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between">
    <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
    <?= paginationLinks(['current_page'=>$page,'total_pages'=>$pages]) ?>
  </div>
  <?php endif; ?>
</div>

<!-- Borrow Modal -->
<div class="modal fade" id="borrowModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white"><h6 class="modal-title"><i class="fas fa-book me-2"></i>Issue Book: <span id="bookTitle"></span></h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('library/borrow') ?>" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="book_id" id="borrowBookId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">User (Student/Staff) <span class="text-danger">*</span></label>
            <input type="text" id="borrowUserSearch" class="form-control" placeholder="Type to search user..." autocomplete="off">
            <input type="hidden" name="user_id" id="borrowUserId" required>
            <div id="borrowUserResults" class="list-group mt-1 shadow" style="display:none;position:absolute;z-index:1000;width:90%;max-height:200px;overflow-y:auto"></div>
          </div>
          <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i>Due date will be set to <?= getSetting('max_borrow_days',14) ?> days from today.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i>Issue Book</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showBorrowModal(bookId, title) {
  document.getElementById('borrowBookId').value = bookId;
  document.getElementById('bookTitle').textContent = title;
  new bootstrap.Modal(document.getElementById('borrowModal')).show();
}

var borrowTimeout;
document.getElementById('borrowUserSearch').addEventListener('input', function() {
  clearTimeout(borrowTimeout);
  var q = this.value;
  if (q.length < 2) { document.getElementById('borrowUserResults').style.display='none'; return; }
  borrowTimeout = setTimeout(function() {
    fetch(BASE_URL + '/api/students?q=' + encodeURIComponent(q))
      .then(r => r.json())
      .then(function(data) {
        var box = document.getElementById('borrowUserResults');
        box.innerHTML = '';
        data.forEach(function(s) {
          var a = document.createElement('a');
          a.href='#'; a.className='list-group-item list-group-item-action';
          a.innerHTML = '<strong>' + s.first_name + ' ' + s.last_name + '</strong> <small class="text-muted">(' + s.student_id + ')</small>';
          a.addEventListener('click', function(e) {
            e.preventDefault();
            // Use user_id from student record via API — simplified: use student record user_id
            document.getElementById('borrowUserSearch').value = s.first_name + ' ' + s.last_name;
            document.getElementById('borrowUserId').value = s.id; // Using student user_id
            box.style.display='none';
          });
          box.appendChild(a);
        });
        box.style.display = data.length ? 'block' : 'none';
      });
  }, 300);
});
</script>
