<?php
/**
 * Shared helper for ID card rendering
 * Used by id-card.php, id-cards.php, id-card-bulk.php
 */

function idCardTheme(string $grade, string $stream): array {
    $s = strtolower($stream ?: 'general');
    switch ($grade) {
        case '9':
            return ['bg1'=>'#9C3D0A','bg2'=>'#C4591E','c1'=>'rgba(200,90,30,.35)','c2'=>'rgba(160,60,15,.25)','badge'=>'rgba(0,0,0,.25)'];
        case '10':
            return ['bg1'=>'#0F2548','bg2'=>'#1E4080','c1'=>'rgba(30,64,128,.45)','c2'=>'rgba(15,37,72,.35)','badge'=>'rgba(0,0,0,.3)'];
        case '11':
            if ($s === 'natural')
                return ['bg1'=>'#0B3318','bg2'=>'#1B5E20','c1'=>'rgba(27,94,32,.45)','c2'=>'rgba(11,51,24,.35)','badge'=>'rgba(0,0,0,.3)'];
            return ['bg1'=>'#3D0C6B','bg2'=>'#7B1FA2','c1'=>'rgba(123,31,162,.45)','c2'=>'rgba(61,12,107,.35)','badge'=>'rgba(0,0,0,.3)'];
        case '12':
            if ($s === 'natural')
                return ['bg1'=>'#003840','bg2'=>'#00695C','c1'=>'rgba(0,105,92,.45)','c2'=>'rgba(0,56,64,.35)','badge'=>'rgba(0,0,0,.3)'];
            return ['bg1'=>'#56001E','bg2'=>'#880E4F','c1'=>'rgba(136,14,79,.45)','c2'=>'rgba(86,0,30,.35)','badge'=>'rgba(0,0,0,.3)'];
        default:
            return ['bg1'=>'#1B3A6B','bg2'=>'#2A5298','c1'=>'rgba(42,82,152,.45)','c2'=>'rgba(27,58,107,.35)','badge'=>'rgba(0,0,0,.3)'];
    }
}

function idCardStreamCode(string $stream): string {
    return match(strtolower($stream)) {
        'social'  => 'SS',
        'natural' => 'NS',
        default   => 'GP',
    };
}

function idCardStreamLabel(string $stream): string {
    return match(strtolower($stream)) {
        'social'  => 'SOCIAL SCIENCE',
        'natural' => 'NATURAL SCIENCE',
        default   => 'GENERAL PROGRAM',
    };
}

function idCardNumber(array $s): string {
    $code  = idCardStreamCode($s['stream'] ?? 'general');
    $grade = str_pad($s['grade'] ?? '9', 2, '0', STR_PAD_LEFT);
    $seq   = str_pad($s['id'] ?? 1, 4, '0', STR_PAD_LEFT);
    return "$code-$grade-$seq";
}

function idCardInitials(array $s): string {
    $f = mb_substr($s['first_name'] ?? '?', 0, 1);
    $l = mb_substr($s['last_name']  ?? '?', 0, 1);
    return strtoupper($f . $l);
}

function idCardAcademicYear(array $s): string {
    if (!empty($s['academic_year'])) return $s['academic_year'];
    return date('Y') . '–' . (date('Y') + 1);
}

function idCardValidShort(array $s): string {
    $y = idCardAcademicYear($s);
    // "2025-2026" → "2025-26"
    if (preg_match('/(\d{4})[\-–](\d{4})/', $y, $m)) {
        return $m[1] . '–' . substr($m[2], 2);
    }
    return $y;
}

function renderIdCard(
    array  $s,
    string $principal,
    string $pTitle,
    string $schoolName,
    string $schoolShort,
    string $schoolAddr
): void {
    $grade   = $s['grade']   ?? '9';
    $section = $s['section'] ?? 'A';
    $stream  = $s['stream']  ?? 'general';
    $theme   = idCardTheme($grade, $stream);
    $code    = idCardStreamCode($stream);
    $label   = idCardStreamLabel($stream);
    $initials= idCardInitials($s);
    $cardNo  = idCardNumber($s);
    $ayShort = idCardValidShort($s);
    $name    = e($s['first_name'] . ' ' . $s['last_name']);
    $blood   = e($s['blood_type'] ?? '—');
    $homeroom= e($s['homeroom_teacher'] ?? '—');
    ?>
<div class="id-card-wrap">
<div class="id-card" style="background:linear-gradient(140deg,<?= $theme['bg1'] ?>,<?= $theme['bg2'] ?>)">

  <!-- decorative circles -->
  <div class="ic-circle ic-c1" style="background:<?= $theme['c1'] ?>"></div>
  <div class="ic-circle ic-c2" style="background:<?= $theme['c2'] ?>"></div>

  <!-- Header -->
  <div class="ic-header">
    <div>
      <div class="ic-school-abbr"><?= $schoolShort ?></div>
      <div class="ic-school-name"><?= strtoupper($schoolName) ?></div>
    </div>
    <div class="ic-grade-badge" style="background:<?= $theme['badge'] ?>">
      GRADE <?= $grade ?>
    </div>
  </div>

  <!-- Body -->
  <div class="ic-body">
    <!-- Avatar -->
    <?php if (!empty($s['photo']) && file_exists(ROOT.'/'.$s['photo'])): ?>
    <div class="ic-avatar ic-avatar-img">
      <img src="<?= BASE_URL.'/'.$s['photo'] ?>" alt="<?= $name ?>">
    </div>
    <?php else: ?>
    <div class="ic-avatar">
      <span><?= $initials ?></span>
    </div>
    <?php endif; ?>

    <!-- Info -->
    <div class="ic-info">
      <div class="ic-name"><?= $name ?></div>
      <div class="ic-stream-badge">
        <span class="ic-dot"></span><?= $label ?>
      </div>
      <div class="ic-fields">
        <div class="ic-field">
          <div class="ic-field-label">SECTION</div>
          <div class="ic-field-val"><?= $grade ?>-<?= $section ?></div>
        </div>
        <div class="ic-field">
          <div class="ic-field-label">YEAR</div>
          <div class="ic-field-val"><?= e(idCardAcademicYear($s)) ?></div>
        </div>
        <div class="ic-field">
          <div class="ic-field-label">HOMEROOM</div>
          <div class="ic-field-val"><?= $homeroom ?></div>
        </div>
        <div class="ic-field">
          <div class="ic-field-label">BLOOD</div>
          <div class="ic-field-val"><?= $blood ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="ic-footer">
    <div class="ic-card-no"><?= $cardNo ?></div>
    <div class="ic-barcode"><?php for($i=0;$i<22;$i++) echo '<span style="width:'.([1,2,1,3,1,2,1,1,3,2,1,2,1,3,1,2,3,1,2,1,3,2][$i%22]).'px"></span>'; ?></div>
    <div class="ic-valid">Valid <?= $ayShort ?></div>
  </div>

</div><!-- .id-card -->
</div><!-- .id-card-wrap -->
    <?php
}

function renderIdCardBack(
    array  $s,
    string $principal,
    string $pTitle,
    string $schoolName,
    string $schoolShort,
    string $schoolAddr
): void {
    $grade  = $s['grade']  ?? '9';
    $stream = $s['stream'] ?? 'general';
    $theme  = idCardTheme($grade, $stream);
    $cardNo = idCardNumber($s);
    $ayShort= idCardValidShort($s);
    ?>
<div class="id-card-wrap">
<div class="id-card id-card-back" style="background:linear-gradient(140deg,<?= $theme['bg1'] ?>,<?= $theme['bg2'] ?>)">
  <div class="ic-circle ic-c1" style="background:<?= $theme['c1'] ?>"></div>
  <div class="ic-circle ic-c2" style="background:<?= $theme['c2'] ?>"></div>

  <!-- Magnetic stripe -->
  <div class="ic-stripe"></div>

  <!-- Back body -->
  <div class="ic-back-body">
    <div class="ic-back-row">
      <span class="ic-back-label">Student ID</span>
      <span class="ic-back-val"><?= e($s['student_id'] ?? '') ?></span>
    </div>
    <div class="ic-back-row">
      <span class="ic-back-label">Admission No</span>
      <span class="ic-back-val"><?= e($s['admission_no'] ?? '') ?></span>
    </div>
    <div class="ic-back-row">
      <span class="ic-back-label">Date of Birth</span>
      <span class="ic-back-val"><?= e($s['dob'] ? date('d M Y', strtotime($s['dob'])) : '—') ?></span>
    </div>
    <div class="ic-back-row">
      <span class="ic-back-label">Emergency</span>
      <span class="ic-back-val"><?= e($s['emergency_contact_phone'] ?? '—') ?></span>
    </div>

    <!-- Authorization -->
    <div class="ic-sig-row">
      <div class="ic-sig-block" style="max-width:180px;margin:0 auto">
        <div class="ic-sig-line"></div>
        <div class="ic-sig-name"><?= e($principal) ?></div>
        <div class="ic-sig-title"><?= e($pTitle) ?></div>
      </div>
    </div>
  </div>

  <!-- Back footer -->
  <div class="ic-back-footer">
    <div><?= e($schoolName) ?> — <?= e($schoolAddr) ?></div>
    <div><?= $cardNo ?> · Valid <?= $ayShort ?></div>
  </div>
</div>
</div>
    <?php
}

function idCardCss(): string { return <<<CSS
/* ─── ID Card core ─────────────────────────── */
.id-card-wrap { display:inline-block; margin:8px; }

.id-card {
  position: relative;
  width: 400px;
  height: 252px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,.35);
  font-family: 'Segoe UI', Arial, sans-serif;
  color: #fff;
  user-select: none;
}

/* Decorative circles */
.ic-circle { position:absolute; border-radius:50%; pointer-events:none; }
.ic-c1 { width:220px; height:220px; top:-60px; right:-60px; }
.ic-c2 { width:160px; height:160px; bottom:-50px; left:60px; }

/* Header */
.ic-header {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 14px 16px 6px;
}
.ic-school-abbr {
  font-size: 20px;
  font-weight: 900;
  letter-spacing: 1px;
  line-height: 1;
  color: #fff;
}
.ic-school-name {
  font-size: 8px;
  letter-spacing: .06em;
  opacity: .7;
  margin-top: 2px;
  font-weight: 600;
}
.ic-grade-badge {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: .08em;
  padding: 4px 10px;
  border-radius: 20px;
  white-space: nowrap;
  border: 1px solid rgba(255,255,255,.25);
}

/* Body */
.ic-body {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 6px 16px 0;
}

/* Avatar */
.ic-avatar {
  flex-shrink: 0;
  width: 68px; height: 68px;
  border-radius: 12px;
  background: rgba(255,255,255,.18);
  border: 2px solid rgba(255,255,255,.3);
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 800; letter-spacing: 1px;
  overflow: hidden;
}
.ic-avatar-img img { width:100%; height:100%; object-fit:cover; }

/* Student info */
.ic-info { flex: 1; min-width: 0; }
.ic-name {
  font-size: 17px;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ic-stream-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 8.5px;
  font-weight: 700;
  letter-spacing: .07em;
  background: rgba(255,255,255,.18);
  border-radius: 20px;
  padding: 3px 9px;
  margin-bottom: 8px;
}
.ic-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: rgba(255,255,255,.9);
  flex-shrink: 0;
}
.ic-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3px 16px;
}
.ic-field-label {
  font-size: 7.5px;
  opacity: .6;
  font-weight: 700;
  letter-spacing: .06em;
  text-transform: uppercase;
  line-height: 1;
}
.ic-field-val {
  font-size: 10.5px;
  font-weight: 700;
  line-height: 1.3;
}

/* Footer */
.ic-footer {
  position: absolute;
  z-index: 2;
  bottom: 0; left: 0; right: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 7px 16px 10px;
  background: rgba(0,0,0,.18);
}
.ic-card-no {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .05em;
  font-family: monospace;
  opacity: .9;
}
.ic-barcode {
  display: flex;
  align-items: flex-end;
  gap: 1.5px;
  height: 22px;
  opacity: .7;
}
.ic-barcode span {
  display: inline-block;
  background: #fff;
  height: 100%;
  border-radius: 1px;
}
.ic-barcode span:nth-child(odd)  { height: 70%; }
.ic-barcode span:nth-child(3n)   { height: 90%; }
.ic-barcode span:nth-child(4n)   { height: 55%; }
.ic-valid {
  font-size: 9px;
  opacity: .75;
  font-weight: 600;
}

/* ── CARD BACK ── */
.id-card-back { background-size: cover; }
.ic-stripe {
  position: relative; z-index: 2;
  width: 100%; height: 36px;
  background: #111;
  margin-top: 28px;
}
.ic-back-body {
  position: relative; z-index: 2;
  padding: 8px 16px 0;
}
.ic-back-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 3px 0;
  border-bottom: 1px solid rgba(255,255,255,.1);
  font-size: 10px;
}
.ic-back-label { opacity: .6; font-weight: 600; }
.ic-back-val   { font-weight: 700; font-family: monospace; }
.ic-sig-row {
  display: flex;
  gap: 16px;
  margin-top: 8px;
}
.ic-sig-block  { flex: 1; text-align: center; }
.ic-sig-line   { border-bottom: 1px solid rgba(255,255,255,.5); margin-bottom: 3px; height: 18px; }
.ic-sig-name   { font-size: 8px; font-weight: 700; }
.ic-sig-title  { font-size: 7.5px; opacity: .65; }
.ic-back-footer {
  position: absolute; z-index: 2;
  bottom: 0; left: 0; right: 0;
  background: rgba(0,0,0,.2);
  padding: 5px 12px;
  font-size: 7.5px;
  opacity: .75;
  display: flex;
  justify-content: space-between;
}

/* Print */
@media print {
  .id-card { box-shadow: none; }
  .id-card-wrap { margin: 0; }
}
CSS;
}
