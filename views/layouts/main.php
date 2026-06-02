<!DOCTYPE html>
<html lang="<?= e(Auth::user()['lang'] ?? 'en') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e(Auth::csrf()) ?>">
<title><?= e($title ?? 'Dashboard') ?> | <?= e(getSetting('school_name_short','SJASSMS')) ?></title>

<!-- Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Custom styles -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
</head>
<body class="sidebar-mini">

<!-- Wrapper -->
<div class="wrapper d-flex">

  <!-- ===== SIDEBAR ===== -->
  <nav id="sidebar" class="sidebar">
    <!-- Logo -->
    <div class="sidebar-header">
      <div class="d-flex align-items-center gap-2 px-3 py-3">
        <div class="school-logo-circle">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="sidebar-brand">
          <div class="brand-name"><?= e(getSetting('school_name_short','SJASSMS')) ?></div>
          <div class="brand-sub">School System</div>
        </div>
        <button class="btn btn-link sidebar-toggle ms-auto text-white p-0" id="sidebarToggle">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>

    <!-- User info -->
    <div class="sidebar-user px-3 py-2">
      <div class="d-flex align-items-center gap-2">
        <?php $photo = Auth::user()['photo'] ?? null; ?>
        <img src="<?= photoUrl($photo, 'user') ?>" class="sidebar-avatar" alt="User">
        <div class="sidebar-user-info">
          <div class="sidebar-username"><?= e(Auth::user()['username'] ?? '') ?></div>
          <div class="sidebar-role"><?= getRoleLabel(Auth::role() ?? 'student') ?></div>
        </div>
      </div>
    </div>

    <hr class="sidebar-divider">

    <!-- Navigation -->
    <ul class="nav flex-column sidebar-nav px-2" id="sidebarNav">
      <?php
      $role    = Auth::role();
      $navItems = match($role) {
          'student' => [
              ['icon'=>'tachometer-alt','label'=>'Dashboard','url'=>'dashboard'],
              ['icon'=>'book-open','label'=>'My Grades','url'=>'exams/report-cards'],
              ['icon'=>'calendar-check','label'=>'My Attendance','url'=>'attendance/report'],
              ['icon'=>'clock','label'=>'Timetable','url'=>'timetable'],
              ['icon'=>'tasks','label'=>'Assignments','url'=>'assignments'],
              ['icon'=>'book','label'=>'Materials','url'=>'materials'],
              ['icon'=>'book-reader','label'=>'Library','url'=>'library'],
              ['icon'=>'bullhorn','label'=>'Announcements','url'=>'communication/announcements'],
              ['icon'=>'envelope','label'=>'Messages','url'=>'communication/messages'],
              ['icon'=>'bell','label'=>'Notifications','url'=>'communication/notifications'],
              ['icon'=>'user','label'=>'My Profile','url'=>'profile'],
          ],
          'parent' => [
              ['icon'=>'tachometer-alt','label'=>'Dashboard','url'=>'dashboard'],
              ['icon'=>'book-open','label'=>'Academic Progress','url'=>'exams/report-cards'],
              ['icon'=>'calendar-check','label'=>'Attendance','url'=>'attendance/report'],
              ['icon'=>'money-bill-wave','label'=>'Fee Status','url'=>'finance/fees'],
              ['icon'=>'bullhorn','label'=>'Announcements','url'=>'communication/announcements'],
              ['icon'=>'envelope','label'=>'Messages','url'=>'communication/messages'],
              ['icon'=>'user','label'=>'My Profile','url'=>'profile'],
          ],
          'teacher' => [
              ['icon'=>'tachometer-alt','label'=>'Dashboard','url'=>'dashboard'],
              ['icon'=>'calendar-check','label'=>'Attendance','url'=>'attendance','children'=>[
                  ['label'=>'Take Attendance','url'=>'attendance/take'],
                  ['label'=>'Attendance Report','url'=>'attendance/report'],
              ]],
              ['icon'=>'graduation-cap','label'=>'Exams & Marks','url'=>'exams','children'=>[
                  ['label'=>'View Exams','url'=>'exams'],
                  ['label'=>'Enter Marks','url'=>'exams/marks'],
              ]],
              ['icon'=>'clock','label'=>'Timetable','url'=>'timetable'],
              ['icon'=>'tasks','label'=>'Assignments','url'=>'assignments'],
              ['icon'=>'folder-open','label'=>'Materials','url'=>'materials'],
              ['icon'=>'bullhorn','label'=>'Announcements','url'=>'communication/announcements'],
              ['icon'=>'envelope','label'=>'Messages','url'=>'communication/messages'],
              ['icon'=>'file-medical-alt','label'=>'Leave Request','url'=>'staff/leaves'],
              ['icon'=>'user','label'=>'My Profile','url'=>'profile'],
          ],
          'finance_officer' => [
              ['icon'=>'tachometer-alt','label'=>'Dashboard','url'=>'dashboard'],
              ['icon'=>'money-bill-wave','label'=>'Finance','url'=>'finance','children'=>[
                  ['label'=>'Fee Collection','url'=>'finance/fees'],
                  ['label'=>'Payments','url'=>'finance/payments'],
                  ['label'=>'Expenses','url'=>'finance/expenses'],
                  ['label'=>'Payroll','url'=>'finance/payroll'],
                  ['label'=>'Reports','url'=>'finance/reports'],
              ]],
              ['icon'=>'chart-bar','label'=>'Reports','url'=>'reports/financial'],
              ['icon'=>'user','label'=>'My Profile','url'=>'profile'],
          ],
          default => [
              ['icon'=>'tachometer-alt','label'=>'Dashboard','url'=>'dashboard'],
              ['icon'=>'users','label'=>'Students','url'=>'students','children'=>[
                  ['label'=>'All Students','url'=>'students'],
                  ['label'=>'Add Student','url'=>'students/create'],
                  ['label'=>'Admissions','url'=>'students/admissions'],
                  ['label'=>'Promotions','url'=>'students/promotions'],
                  ['label'=>'Transfers','url'=>'students/transfers'],
              ]],
              ['icon'=>'chalkboard-teacher','label'=>'Staff','url'=>'staff','children'=>[
                  ['label'=>'All Staff','url'=>'staff'],
                  ['label'=>'Add Staff','url'=>'staff/create'],
                  ['label'=>'Attendance','url'=>'staff/attendance'],
                  ['label'=>'Leave Requests','url'=>'staff/leaves'],
              ]],
              ['icon'=>'calendar-check','label'=>'Attendance','url'=>'attendance','children'=>[
                  ['label'=>'Take Attendance','url'=>'attendance/take'],
                  ['label'=>'Report','url'=>'attendance/report'],
                  ['label'=>'Analytics','url'=>'attendance/analytics'],
              ]],
              ['icon'=>'book','label'=>'Academics','url'=>'academics','children'=>[
                  ['label'=>'Academic Years','url'=>'academics/years'],
                  ['label'=>'Classes','url'=>'academics/classes'],
                  ['label'=>'Subjects','url'=>'academics/subjects'],
                  ['label'=>'Departments','url'=>'academics/departments'],
                  ['label'=>'Assign Subjects','url'=>'academics/assign-subjects'],
              ]],
              ['icon'=>'graduation-cap','label'=>'Exams & Marks','url'=>'exams','children'=>[
                  ['label'=>'Exams','url'=>'exams'],
                  ['label'=>'Enter Marks','url'=>'exams/marks'],
                  ['label'=>'Report Cards','url'=>'exams/report-cards'],
                  ['label'=>'GPA Calculator','url'=>'exams/gpa'],
              ]],
              ['icon'=>'clock','label'=>'Timetable','url'=>'timetable'],
              ['icon'=>'tasks','label'=>'Assignments','url'=>'assignments'],
              ['icon'=>'book-reader','label'=>'Library','url'=>'library','children'=>[
                  ['label'=>'Books','url'=>'library/books'],
                  ['label'=>'Borrowings','url'=>'library/borrowings'],
              ]],
              ['icon'=>'money-bill-wave','label'=>'Finance','url'=>'finance','children'=>[
                  ['label'=>'Fees','url'=>'finance/fees'],
                  ['label'=>'Payments','url'=>'finance/payments'],
                  ['label'=>'Expenses','url'=>'finance/expenses'],
                  ['label'=>'Payroll','url'=>'finance/payroll'],
              ]],
              ['icon'=>'bullhorn','label'=>'Communication','url'=>'communication','children'=>[
                  ['label'=>'Announcements','url'=>'communication/announcements'],
                  ['label'=>'Messages','url'=>'communication/messages'],
                  ['label'=>'Notifications','url'=>'communication/notifications'],
              ]],
              ['icon'=>'exclamation-triangle','label'=>'Discipline','url'=>'discipline'],
              ['icon'=>'warehouse','label'=>'Inventory','url'=>'inventory'],
              ['icon'=>'bed','label'=>'Hostel','url'=>'hostel'],
              ['icon'=>'bus','label'=>'Transport','url'=>'transport'],
              ['icon'=>'users-cog','label'=>'Clubs','url'=>'clubs'],
              ['icon'=>'chart-bar','label'=>'Reports','url'=>'reports','children'=>[
                  ['label'=>'Academic','url'=>'reports/academic'],
                  ['label'=>'Attendance','url'=>'reports/attendance'],
                  ['label'=>'Financial','url'=>'reports/financial'],
                  ['label'=>'Staff','url'=>'reports/staff'],
                  ['label'=>'Annual Report','url'=>'reports/annual'],
              ]],
              ['icon'=>'cog','label'=>'Settings','url'=>'settings','children'=>[
                  ['label'=>'General Settings','url'=>'settings'],
                  ['label'=>'User Management','url'=>'settings/users'],
                  ['label'=>'Audit Logs','url'=>'settings/audit'],
                  ['label'=>'Backup','url'=>'settings/backup'],
              ]],
          ]
      };

      $currentPath = trim(str_replace(BASE_PATH, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');

      foreach ($navItems as $item):
          $hasChildren = !empty($item['children']);
          $isActive    = strpos($currentPath, $item['url']) === 0;
          $menuId      = 'menu_' . md5($item['url']);
      ?>
      <li class="nav-item <?= $hasChildren ? 'has-dropdown' : '' ?>">
        <?php if ($hasChildren): ?>
        <a class="nav-link sidebar-link <?= $isActive ? 'active' : '' ?>" data-bs-toggle="collapse" href="#<?= $menuId ?>" role="button" aria-expanded="<?= $isActive ? 'true' : 'false' ?>">
          <i class="fas fa-<?= $item['icon'] ?> nav-icon"></i>
          <span class="nav-label"><?= e($item['label']) ?></span>
          <i class="fas fa-chevron-right nav-arrow"></i>
        </a>
        <div class="collapse <?= $isActive ? 'show' : '' ?>" id="<?= $menuId ?>">
          <ul class="nav flex-column sub-menu">
            <?php foreach ($item['children'] as $child): ?>
            <li class="nav-item">
              <a class="nav-link sidebar-sublink <?= $currentPath === $child['url'] ? 'active' : '' ?>" href="<?= url($child['url']) ?>">
                <i class="fas fa-circle sub-dot"></i>
                <?= e($child['label']) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php else: ?>
        <a class="nav-link sidebar-link <?= $currentPath === $item['url'] ? 'active' : '' ?>" href="<?= url($item['url']) ?>">
          <i class="fas fa-<?= $item['icon'] ?> nav-icon"></i>
          <span class="nav-label"><?= e($item['label']) ?></span>
        </a>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>

    <!-- Sidebar footer -->
    <div class="sidebar-footer px-3 py-2 mt-auto">
      <a href="<?= url('logout') ?>" class="btn btn-sm btn-outline-light w-100">
        <i class="fas fa-sign-out-alt me-1"></i> <span class="nav-label">Logout</span>
      </a>
    </div>
  </nav>
  <!-- ===== END SIDEBAR ===== -->

  <!-- Main Content -->
  <div class="main-content flex-fill">

    <!-- Top Navbar -->
    <nav class="topbar navbar navbar-expand-lg">
      <div class="container-fluid px-3">
        <!-- Mobile sidebar toggle -->
        <button class="btn btn-link text-dark d-lg-none me-2 p-0" id="mobileSidebarToggle">
          <i class="fas fa-bars fa-lg"></i>
        </button>

        <!-- Breadcrumb / Page Title -->
        <div class="topbar-title d-none d-md-block">
          <h6 class="mb-0 fw-semibold text-dark"><?= e($title ?? 'Dashboard') ?></h6>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
          <!-- Search -->
          <div class="topbar-search d-none d-md-block">
            <div class="input-group input-group-sm">
              <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
              <input type="text" class="form-control bg-light border-0" placeholder="Search..." id="globalSearch" style="width:200px">
            </div>
          </div>

          <!-- Current Date -->
          <span class="badge bg-light text-dark d-none d-md-inline-flex align-items-center gap-1">
            <i class="fas fa-calendar text-primary"></i>
            <?= date('D, d M Y') ?>
          </span>

          <!-- Notifications -->
          <?php $notifs = getUnreadNotifications(); ?>
          <div class="dropdown">
            <button class="btn btn-link position-relative p-1 text-dark" data-bs-toggle="dropdown" title="Notifications">
              <i class="fas fa-bell fa-lg"></i>
              <?php if (!empty($notifs)): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px">
                <?= count($notifs) ?><span class="visually-hidden">notifications</span>
              </span>
              <?php endif; ?>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" style="width:320px">
              <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <span class="fw-semibold">Notifications</span>
                <?php if (!empty($notifs)): ?>
                <a href="<?= url('communication/notifications/read-all') ?>" class="text-primary small" onclick="return confirm('Mark all as read?')">Mark all read</a>
                <?php endif; ?>
              </div>
              <div class="notification-list" style="max-height:300px;overflow-y:auto">
                <?php if (empty($notifs)): ?>
                <div class="text-center py-4 text-muted">
                  <i class="fas fa-bell-slash fa-2x mb-2"></i><br>No new notifications
                </div>
                <?php else: foreach ($notifs as $n): ?>
                <a href="<?= url(ltrim($n['link'] ?? 'communication/notifications', '/')) ?>" class="dropdown-item px-3 py-2 border-bottom notif-item <?= $n['is_read'] ? '' : 'bg-light-blue' ?>" data-id="<?= $n['id'] ?>">
                  <div class="d-flex gap-2 align-items-start">
                    <div class="notif-icon text-<?= $n['type'] ?>"><i class="fas fa-<?= $n['icon'] ?? 'bell' ?>"></i></div>
                    <div class="flex-fill">
                      <div class="fw-semibold small"><?= e($n['title']) ?></div>
                      <div class="text-muted" style="font-size:12px"><?= truncate(e($n['message']), 60) ?></div>
                      <div class="text-muted" style="font-size:11px"><?= timeAgo($n['created_at']) ?></div>
                    </div>
                  </div>
                </a>
                <?php endforeach; endif; ?>
              </div>
              <div class="px-3 py-2 text-center border-top">
                <a href="<?= url('communication/notifications') ?>" class="text-primary small">View all notifications</a>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <?php $msgCount = getUnreadMessages(); ?>
          <a href="<?= url('communication/messages') ?>" class="btn btn-link position-relative p-1 text-dark" title="Messages">
            <i class="fas fa-envelope fa-lg"></i>
            <?php if ($msgCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size:9px"><?= $msgCount ?></span>
            <?php endif; ?>
          </a>

          <!-- User dropdown -->
          <div class="dropdown">
            <button class="btn btn-link d-flex align-items-center gap-2 p-1 text-dark" data-bs-toggle="dropdown">
              <?php $uPhoto = Auth::user()['photo'] ?? null; ?>
              <img src="<?= photoUrl($uPhoto, 'user') ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
              <span class="d-none d-md-inline small fw-semibold"><?= e(Auth::user()['username'] ?? '') ?></span>
              <i class="fas fa-chevron-down small text-muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><h6 class="dropdown-header"><?= getRoleLabel(Auth::role() ?? '') ?></h6></li>
              <li><a class="dropdown-item" href="<?= url('profile') ?>"><i class="fas fa-user me-2"></i>My Profile</a></li>
              <li><a class="dropdown-item" href="<?= url('profile') ?>"><i class="fas fa-cog me-2"></i>Account Settings</a></li>
              <?php if (Auth::isAdmin()): ?>
              <li><a class="dropdown-item" href="<?= url('settings') ?>"><i class="fas fa-wrench me-2"></i>System Settings</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?= url('logout') ?>">
                  <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
    <!-- End Topbar -->

    <!-- Page Content -->
    <div class="page-content">
      <!-- Flash messages -->
      <?= Flash::render() ?>

      <!-- Active announcements bar -->
      <?php
      $urgentAnns = array_filter(getActiveAnnouncements(), fn($a) => $a['priority'] === 'urgent');
      if (!empty($urgentAnns)):
          $ann = reset($urgentAnns);
      ?>
      <div class="alert alert-warning alert-dismissible fade show mb-3 py-2" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Urgent:</strong> <?= e($ann['title']) ?> — <?= truncate(e($ann['content']), 100) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <!-- View content injected here -->
      <?= $content ?>
    </div>
    <!-- End Page Content -->

    <!-- Footer -->
    <footer class="page-footer text-center py-2">
      <small class="text-muted">
        &copy; <?= date('Y') ?> <?= e(getSetting('school_name','Shalaka Jatan Ali Secondary School')) ?> — <?= APP_FULL_NAME ?> v<?= APP_VERSION ?>
      </small>
    </footer>
  </div>
  <!-- End Main Content -->

</div>
<!-- End Wrapper -->

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Custom JS -->
<script src="<?= ASSETS_URL ?>/js/app.js"></script>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF_TOKEN = '<?= Auth::csrf() ?>';
</script>

<?php if (isset($pageScript)): ?>
<script><?= $pageScript ?></script>
<?php endif; ?>

</body>
</html>
