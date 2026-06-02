<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="mb-0 fw-bold"><i class="fas fa-folder-open text-primary me-2"></i>Learning Materials</h4>
  </div>
  <?php if (Auth::hasRole(['super_admin','principal','teacher'])): ?>
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#materialModal"><i class="fas fa-upload me-1"></i>Upload Material</button>
  <?php endif; ?>
</div>

<div class="row g-3">
  <?php if (empty($materials)): ?>
  <div class="col-12">
    <div class="card border-0 shadow-sm text-center py-5"><div class="text-muted"><i class="fas fa-folder-open fa-3x mb-3"></i><br><h6>No materials available</h6></div></div>
  </div>
  <?php else: foreach ($materials as $m): ?>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="stat-icon bg-<?= match($m['file_type']??'pdf') {'pdf'=>'danger','doc'=>'primary','ppt'=>'warning','video'=>'success','link'=>'info',default=>'secondary'} ?>-light text-<?= match($m['file_type']??'pdf') {'pdf'=>'danger','doc'=>'primary','ppt'=>'warning','video'=>'success','link'=>'info',default=>'secondary'} ?> rounded-3" style="width:40px;height:40px">
            <i class="fas fa-<?= match($m['file_type']??'pdf') {'pdf'=>'file-pdf','doc'=>'file-word','ppt'=>'file-powerpoint','video'=>'video','link'=>'link',default=>'file'} ?>"></i>
          </div>
          <div>
            <div class="fw-semibold small"><?= e($m['title']) ?></div>
            <div class="text-muted" style="font-size:11px"><?= e($m['subject']??'') ?> | <?= $m['grade']??'' ?>-<?= $m['section']??'' ?></div>
          </div>
        </div>
        <?php if ($m['description']): ?><p class="text-muted small mb-2"><?= truncate(e($m['description']),80) ?></p><?php endif; ?>
        <div class="text-muted" style="font-size:11px"><?= e(($m['first_name']??'').' '.($m['last_name']??'')) ?> — <?= timeAgo($m['created_at']) ?></div>
      </div>
      <div class="card-footer bg-white border-top-0">
        <?php if ($m['file_path']): ?>
        <a href="<?= uploadUrl($m['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fas fa-download me-1"></i>Download</a>
        <?php elseif ($m['external_url']): ?>
        <a href="<?= e($m['external_url']) ?>" target="_blank" class="btn btn-xs btn-outline-info"><i class="fas fa-external-link-alt me-1"></i>Open Link</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<!-- Upload Modal -->
<?php if (Auth::hasRole(['super_admin','principal','teacher'])): ?>
<div class="modal fade" id="materialModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white"><h6 class="modal-title"><i class="fas fa-upload me-2"></i>Upload Material</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form action="<?= url('materials/upload') ?>" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Class</label><select name="class_id" class="form-select" id="classSelect">
              <?php
              $ayId = (int)getSetting('academic_year_id',1);
              $db = getDB();
              $cls = $db->prepare("SELECT * FROM classes WHERE academic_year_id=? ORDER BY grade, section"); $cls->execute([$ayId]);
              foreach ($cls->fetchAll() as $c): ?><option value="<?= $c['id'] ?>">Grade <?= e($c['grade']) ?>-<?= e($c['section']) ?></option><?php endforeach;
              ?>
            </select></div>
            <div class="col-md-6"><label class="form-label">Subject</label><select name="subject_id" class="form-select" id="subjectSelect"><option value="">Select Class first</option></select></div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">File Type</label><select name="file_type" class="form-select"><option value="pdf">PDF</option><option value="doc">Word Document</option><option value="ppt">PowerPoint</option><option value="video">Video</option><option value="link">External Link</option></select></div>
          </div>
          <div class="mb-3"><label class="form-label">File Upload</label><input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.avi"></div>
          <div class="mb-3"><label class="form-label">Or External URL</label><input type="url" name="external_url" class="form-control" placeholder="https://..."></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>
