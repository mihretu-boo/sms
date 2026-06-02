<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <!-- ID Card Front -->
      <div class="card shadow border-0" style="border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#1565C0,#0D47A1)">
        <div class="card-body text-white text-center p-0">
          <!-- Header -->
          <div class="py-3 px-3" style="background:rgba(0,0,0,0.2)">
            <div class="fw-bold" style="font-size:13px"><?= e(getSetting('school_name','SJASS')) ?></div>
            <div style="font-size:10px;opacity:.8">Borana Zone, Oromia, Ethiopia</div>
          </div>
          <!-- Photo -->
          <div class="py-3">
            <img src="<?= photoUrl($student['photo'],'student') ?>" class="rounded-circle" width="80" height="80" style="object-fit:cover;border:3px solid white">
          </div>
          <!-- Name -->
          <div class="px-3 pb-2">
            <div class="fw-bold" style="font-size:16px"><?= e($student['first_name'].' '.$student['last_name']) ?></div>
            <div style="font-size:11px;opacity:.8">Grade <?= e($student['grade']??'') ?> — Section <?= e($student['section']??'') ?></div>
          </div>
          <!-- ID Badge -->
          <div class="py-2 px-4" style="background:rgba(255,255,255,0.15)">
            <div class="fw-bold font-monospace" style="font-size:14px;letter-spacing:2px"><?= e($student['student_id']) ?></div>
          </div>
          <!-- Footer -->
          <div class="py-2" style="font-size:10px;opacity:.7">STUDENT IDENTIFICATION CARD</div>
          <div class="py-2 px-3" style="background:rgba(46,125,50,0.8);font-size:10px">
            Academic Year: <?= e(getSetting('academic_year_name','2024-2025')) ?><br>
            Admission: <?= formatDate($student['admission_date']) ?>
          </div>
        </div>
      </div>

      <div class="text-center mt-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2"><i class="fas fa-print me-1"></i>Print</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
      </div>
    </div>
  </div>
</div>
