<?php
// Application Configuration
define('APP_NAME', 'SJASSMS');
define('APP_FULL_NAME', 'Shalaka Jatan Ali Secondary School Management System');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development | production

// Base URL - auto-detect
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_PATH', ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/\\'));
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_PATH);
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// File Paths
define('UPLOADS_PATH', ROOT . '/uploads');
define('VIEWS_PATH', ROOT . '/views');

// Session Config
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'SJASSMS_SESSION');

// Upload Config
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOC_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation']);

// Pagination
define('PER_PAGE', 20);

// Timezone
date_default_timezone_set('Africa/Addis_Ababa');

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Grade system (Ethiopian)
// A = 90-100 (4.0), A- = 85-89 (3.75), B+ = 80-84 (3.5), B = 75-79 (3.0)
// B- = 70-74 (2.75), C+ = 65-69 (2.5), C = 60-64 (2.0), C- = 55-59 (1.75)
// D = 50-54 (1.0), F = 0-49 (0.0)
define('GRADE_SCALE', [
    ['min' => 90, 'max' => 100, 'letter' => 'A',  'point' => 4.00, 'desc' => 'Excellent'],
    ['min' => 85, 'max' => 89,  'letter' => 'A-', 'point' => 3.75, 'desc' => 'Very Good'],
    ['min' => 80, 'max' => 84,  'letter' => 'B+', 'point' => 3.50, 'desc' => 'Good'],
    ['min' => 75, 'max' => 79,  'letter' => 'B',  'point' => 3.00, 'desc' => 'Above Average'],
    ['min' => 70, 'max' => 74,  'letter' => 'B-', 'point' => 2.75, 'desc' => 'Average'],
    ['min' => 65, 'max' => 69,  'letter' => 'C+', 'point' => 2.50, 'desc' => 'Below Average'],
    ['min' => 60, 'max' => 64,  'letter' => 'C',  'point' => 2.00, 'desc' => 'Satisfactory'],
    ['min' => 55, 'max' => 59,  'letter' => 'C-', 'point' => 1.75, 'desc' => 'Pass'],
    ['min' => 50, 'max' => 54,  'letter' => 'D',  'point' => 1.00, 'desc' => 'Minimum Pass'],
    ['min' => 0,  'max' => 49,  'letter' => 'F',  'point' => 0.00, 'desc' => 'Fail'],
]);

// Role permissions matrix
define('ROLE_PERMISSIONS', [
    'super_admin' => ['*'],
    'principal' => ['dashboard','students','staff','attendance','academics','exams','finance','library','communication','discipline','inventory','reports','settings.view'],
    'vice_principal' => ['dashboard','students','staff','attendance','academics','exams','discipline','reports'],
    'registrar' => ['dashboard','students','academics','reports'],
    'teacher' => ['dashboard','attendance.take','exams.marks','assignments','materials','timetable','communication.view'],
    'dept_head' => ['dashboard','staff','academics','exams','reports'],
    'student' => ['dashboard','grades','attendance.view','timetable','assignments','materials','communication.view'],
    'parent' => ['dashboard','grades','attendance.view','communication.view','finance.view'],
    'finance_officer' => ['dashboard','finance','reports.finance'],
]);

// Navigation menu per role
define('NAVIGATION', [
    'super_admin' => [
        ['icon'=>'tachometer-alt','label'=>'Dashboard','url'=>'/dashboard'],
        ['icon'=>'users','label'=>'Students','url'=>'/students','children'=>[
            ['label'=>'All Students','url'=>'/students'],
            ['label'=>'Add Student','url'=>'/students/create'],
            ['label'=>'Admissions','url'=>'/students/admissions'],
            ['label'=>'Promotions','url'=>'/students/promotions'],
            ['label'=>'Transfers','url'=>'/students/transfers'],
        ]],
        ['icon'=>'chalkboard-teacher','label'=>'Staff','url'=>'/staff','children'=>[
            ['label'=>'All Staff','url'=>'/staff'],
            ['label'=>'Add Staff','url'=>'/staff/create'],
            ['label'=>'Attendance','url'=>'/staff/attendance'],
            ['label'=>'Leave Requests','url'=>'/staff/leaves'],
            ['label'=>'Payroll','url'=>'/staff/payroll'],
        ]],
        ['icon'=>'calendar-check','label'=>'Attendance','url'=>'/attendance','children'=>[
            ['label'=>'Take Attendance','url'=>'/attendance/take'],
            ['label'=>'Attendance Report','url'=>'/attendance/report'],
            ['label'=>'Analytics','url'=>'/attendance/analytics'],
        ]],
        ['icon'=>'book','label'=>'Academics','url'=>'/academics','children'=>[
            ['label'=>'Academic Years','url'=>'/academics/years'],
            ['label'=>'Classes','url'=>'/academics/classes'],
            ['label'=>'Subjects','url'=>'/academics/subjects'],
            ['label'=>'Departments','url'=>'/academics/departments'],
        ]],
        ['icon'=>'graduation-cap','label'=>'Exams & Marks','url'=>'/exams','children'=>[
            ['label'=>'Exams','url'=>'/exams'],
            ['label'=>'Enter Marks','url'=>'/exams/marks'],
            ['label'=>'Report Cards','url'=>'/exams/report-cards'],
            ['label'=>'GPA Calculator','url'=>'/exams/gpa'],
        ]],
        ['icon'=>'archive','label'=>'Exam Repository','url'=>'/exam-repository','children'=>[
            ['label'=>'Dashboard','url'=>'/exam-repository'],
            ['label'=>'Browse Exams','url'=>'/exam-repository/browse'],
            ['label'=>'Upload Exam','url'=>'/exam-repository/upload'],
            ['label'=>'Manage','url'=>'/exam-repository/manage'],
            ['label'=>'Question Bank','url'=>'/exam-repository/question-bank'],
            ['label'=>'Reports','url'=>'/exam-repository/reports'],
        ]],
        ['icon'=>'clock','label'=>'Timetable','url'=>'/timetable'],
        ['icon'=>'tasks','label'=>'Assignments','url'=>'/assignments'],
        ['icon'=>'book-open','label'=>'Library','url'=>'/library','children'=>[
            ['label'=>'Books','url'=>'/library/books'],
            ['label'=>'Borrowings','url'=>'/library/borrowings'],
            ['label'=>'Return Book','url'=>'/library/return'],
        ]],
        ['icon'=>'money-bill-wave','label'=>'Finance','url'=>'/finance','children'=>[
            ['label'=>'Fee Collection','url'=>'/finance/fees'],
            ['label'=>'Payments','url'=>'/finance/payments'],
            ['label'=>'Expenses','url'=>'/finance/expenses'],
            ['label'=>'Payroll','url'=>'/finance/payroll'],
            ['label'=>'Reports','url'=>'/finance/reports'],
        ]],
        ['icon'=>'bullhorn','label'=>'Communication','url'=>'/communication','children'=>[
            ['label'=>'Announcements','url'=>'/communication/announcements'],
            ['label'=>'Messages','url'=>'/communication/messages'],
            ['label'=>'Notifications','url'=>'/communication/notifications'],
        ]],
        ['icon'=>'exclamation-triangle','label'=>'Discipline','url'=>'/discipline'],
        ['icon'=>'warehouse','label'=>'Inventory','url'=>'/inventory'],
        ['icon'=>'bed','label'=>'Hostel','url'=>'/hostel'],
        ['icon'=>'bus','label'=>'Transport','url'=>'/transport'],
        ['icon'=>'users-cog','label'=>'Clubs','url'=>'/clubs'],
        ['icon'=>'chart-bar','label'=>'Reports','url'=>'/reports','children'=>[
            ['label'=>'Academic Reports','url'=>'/reports/academic'],
            ['label'=>'Attendance Reports','url'=>'/reports/attendance'],
            ['label'=>'Financial Reports','url'=>'/reports/financial'],
            ['label'=>'Staff Reports','url'=>'/reports/staff'],
            ['label'=>'Annual Report','url'=>'/reports/annual'],
        ]],
        ['icon'=>'cog','label'=>'Settings','url'=>'/settings','children'=>[
            ['label'=>'General Settings','url'=>'/settings'],
            ['label'=>'User Management','url'=>'/settings/users'],
            ['label'=>'Roles & Permissions','url'=>'/settings/roles'],
            ['label'=>'Audit Logs','url'=>'/settings/audit'],
            ['label'=>'Backup','url'=>'/settings/backup'],
        ]],
    ],
]);
