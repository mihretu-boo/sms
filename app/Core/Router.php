<?php

class Router {

    private array $routes = [];

    public function __construct() {
        $this->registerRoutes();
    }

    private function registerRoutes(): void {
        // Auth
        $this->add('GET',  '',                    'AuthController', 'showLogin');
        $this->add('GET',  'login',               'AuthController', 'showLogin');
        $this->add('POST', 'login',               'AuthController', 'login');
        $this->add('GET',  'logout',              'AuthController', 'logout');
        $this->add('GET',  'forgot-password',          'AuthController', 'forgotPassword');
        $this->add('POST', 'forgot-password',          'AuthController', 'processForgotPassword');
        $this->add('GET',  'forgot-password/sent',     'AuthController', 'forgotPasswordSent');
        $this->add('GET',  'reset-password',           'AuthController', 'resetPassword');
        $this->add('POST', 'reset-password',           'AuthController', 'processReset');
        $this->add('GET',  'reset-password/success',   'AuthController', 'resetPasswordSuccess');

        // Dashboard
        $this->add('GET',  'dashboard',           'DashboardController', 'index');

        // Students
        $this->add('GET',  'students',            'StudentController', 'index');
        $this->add('GET',  'students/create',     'StudentController', 'create');
        $this->add('POST', 'students/create',     'StudentController', 'store');
        $this->add('GET',  'students/view/{id}',  'StudentController', 'view');
        $this->add('GET',  'students/edit/{id}',  'StudentController', 'edit');
        $this->add('POST', 'students/edit/{id}',  'StudentController', 'update');
        $this->add('POST', 'students/delete/{id}','StudentController', 'delete');
        $this->add('GET',  'students/admissions', 'StudentController', 'admissions');
        $this->add('GET',  'students/promotions', 'StudentController', 'promotions');
        $this->add('POST', 'students/promote',    'StudentController', 'promote');
        $this->add('GET',  'students/transfers',  'StudentController', 'transfers');
        $this->add('POST', 'students/transfers',  'StudentController', 'transfers');
        $this->add('GET',  'students/id-card/{id}',              'StudentController', 'idCard');
        $this->add('POST', 'students/create-parent-account/{id}','StudentController', 'createParentAccount');

        // Staff
        $this->add('GET',  'staff',               'StaffController', 'index');
        $this->add('GET',  'staff/create',        'StaffController', 'create');
        $this->add('POST', 'staff/create',        'StaffController', 'store');
        $this->add('GET',  'staff/view/{id}',     'StaffController', 'view');
        $this->add('GET',  'staff/edit/{id}',     'StaffController', 'edit');
        $this->add('POST', 'staff/edit/{id}',     'StaffController', 'update');
        $this->add('POST', 'staff/delete/{id}',   'StaffController', 'delete');
        $this->add('GET',  'staff/attendance',    'StaffController', 'attendance');
        $this->add('POST', 'staff/attendance',    'StaffController', 'saveAttendance');
        $this->add('GET',  'staff/leaves',        'StaffController', 'leaves');
        $this->add('POST', 'staff/leaves',        'StaffController', 'submitLeave');
        $this->add('POST', 'staff/leaves/approve/{id}', 'StaffController', 'approveLeave');
        $this->add('GET',  'staff/payroll',       'StaffController', 'payroll');
        $this->add('POST', 'staff/payroll',       'StaffController', 'processPayroll');

        // Attendance
        $this->add('GET',  'attendance',          'AttendanceController', 'index');
        $this->add('GET',  'attendance/take',     'AttendanceController', 'take');
        $this->add('POST', 'attendance/save',     'AttendanceController', 'save');
        $this->add('GET',  'attendance/report',   'AttendanceController', 'report');
        $this->add('GET',  'attendance/analytics','AttendanceController', 'analytics');

        // Academics
        $this->add('GET',  'academics',           'AcademicController', 'index');
        $this->add('GET',  'academics/years',     'AcademicController', 'years');
        $this->add('POST', 'academics/years',     'AcademicController', 'saveYear');
        $this->add('GET',  'academics/classes',   'AcademicController', 'classes');
        $this->add('POST', 'academics/classes',   'AcademicController', 'saveClass');
        $this->add('POST', 'academics/classes/delete/{id}', 'AcademicController', 'deleteClass');
        $this->add('GET',  'academics/subjects',  'AcademicController', 'subjects');
        $this->add('POST', 'academics/subjects',  'AcademicController', 'saveSubject');
        $this->add('POST', 'academics/subjects/delete/{id}', 'AcademicController', 'deleteSubject');
        $this->add('GET',  'academics/departments','AcademicController','departments');
        $this->add('POST', 'academics/departments','AcademicController','saveDepartment');
        $this->add('GET',  'academics/assign-subjects',       'AcademicController','assignSubjects');
        $this->add('POST', 'academics/assign-subjects',       'AcademicController','saveAssignments');
        $this->add('POST', 'academics/assign-subjects/auto',  'AcademicController','autoAssignSubjects');

        // Exams
        $this->add('GET',  'exams',               'ExamController', 'index');
        $this->add('GET',  'exams/create',        'ExamController', 'create');
        $this->add('POST', 'exams/create',        'ExamController', 'store');
        $this->add('GET',  'exams/edit/{id}',     'ExamController', 'edit');
        $this->add('POST', 'exams/edit/{id}',     'ExamController', 'update');
        $this->add('POST', 'exams/delete/{id}',   'ExamController', 'delete');
        $this->add('GET',  'exams/marks',         'ExamController', 'marks');
        $this->add('POST', 'exams/marks/save',    'ExamController', 'saveMarks');
        $this->add('GET',  'exams/report-cards',  'ExamController', 'reportCards');
        $this->add('GET',  'exams/report-card/{id}','ExamController','viewReportCard');
        $this->add('GET',  'exams/gpa',           'ExamController', 'gpa');

        // Timetable
        $this->add('GET',  'timetable',           'TimetableController', 'index');
        $this->add('GET',  'timetable/create',    'TimetableController', 'create');
        $this->add('POST', 'timetable/save',      'TimetableController', 'save');
        $this->add('POST', 'timetable/delete/{id}','TimetableController','delete');

        // Assignments
        $this->add('GET',  'assignments',         'AssignmentController', 'index');
        $this->add('GET',  'assignments/create',  'AssignmentController', 'create');
        $this->add('POST', 'assignments/create',  'AssignmentController', 'store');
        $this->add('GET',  'assignments/view/{id}','AssignmentController','view');
        $this->add('POST', 'assignments/submit/{id}','AssignmentController','submit');
        $this->add('GET',  'assignments/grade/{id}','AssignmentController','grade');
        $this->add('POST', 'assignments/grade/{id}','AssignmentController','saveGrade');
        $this->add('GET',  'materials',           'AssignmentController', 'materials');
        $this->add('POST', 'materials/upload',    'AssignmentController', 'uploadMaterial');

        // Library
        $this->add('GET',  'library',             'LibraryController', 'index');
        $this->add('GET',  'library/books',       'LibraryController', 'books');
        $this->add('GET',  'library/books/create','LibraryController', 'createBook');
        $this->add('POST', 'library/books/create','LibraryController', 'storeBook');
        $this->add('GET',  'library/books/edit/{id}','LibraryController','editBook');
        $this->add('POST', 'library/books/edit/{id}','LibraryController','updateBook');
        $this->add('GET',  'library/borrowings',  'LibraryController', 'borrowings');
        $this->add('POST', 'library/borrow',      'LibraryController', 'borrow');
        $this->add('POST', 'library/return/{id}', 'LibraryController', 'returnBook');

        // Finance
        $this->add('GET',  'finance',             'FinanceController', 'index');
        $this->add('GET',  'finance/fees',        'FinanceController', 'fees');
        $this->add('POST', 'finance/fees',        'FinanceController', 'saveFeeCategory');
        $this->add('POST', 'finance/fees/assign', 'FinanceController', 'assignFees');
        $this->add('GET',  'finance/payments',    'FinanceController', 'payments');
        $this->add('GET',  'finance/payments/create','FinanceController','createPayment');
        $this->add('POST', 'finance/payments/create','FinanceController','storePayment');
        $this->add('GET',  'finance/payments/receipt/{id}','FinanceController','receipt');
        $this->add('GET',  'finance/expenses',    'FinanceController', 'expenses');
        $this->add('POST', 'finance/expenses',    'FinanceController', 'saveExpense');
        $this->add('GET',  'finance/payroll',     'FinanceController', 'payroll');
        $this->add('POST', 'finance/payroll',     'FinanceController', 'processPayroll');
        $this->add('GET',  'finance/reports',     'FinanceController', 'reports');

        // Communication
        $this->add('GET',  'communication',              'CommunicationController', 'index');
        $this->add('GET',  'communication/announcements','CommunicationController', 'announcements');
        $this->add('POST', 'communication/announcements','CommunicationController', 'saveAnnouncement');
        $this->add('POST', 'communication/announcements/delete/{id}','CommunicationController','deleteAnnouncement');
        $this->add('GET',  'communication/messages',     'CommunicationController', 'messages');
        $this->add('POST', 'communication/messages/send','CommunicationController', 'sendMessage');
        $this->add('GET',  'communication/messages/view/{id}','CommunicationController','viewMessage');
        $this->add('GET',  'communication/notifications','CommunicationController', 'notifications');
        $this->add('POST', 'communication/notifications/read/{id}','CommunicationController','markRead');
        $this->add('POST', 'communication/notifications/read-all','CommunicationController','markAllRead');

        // Discipline
        $this->add('GET',  'discipline',          'DisciplineController', 'index');
        $this->add('POST', 'discipline/create',   'DisciplineController', 'store');
        $this->add('GET',  'discipline/view/{id}','DisciplineController', 'view');
        $this->add('POST', 'discipline/resolve/{id}','DisciplineController','resolve');

        // Inventory
        $this->add('GET',  'inventory',           'InventoryController', 'index');
        $this->add('POST', 'inventory/create',    'InventoryController', 'store');
        $this->add('POST', 'inventory/edit/{id}', 'InventoryController', 'update');
        $this->add('POST', 'inventory/delete/{id}','InventoryController','delete');
        $this->add('GET',  'inventory/categories','InventoryController', 'categories');
        $this->add('POST', 'inventory/categories','InventoryController', 'categories');

        // Hostel
        $this->add('GET',  'hostel',              'HostelController', 'index');
        $this->add('POST', 'hostel/allocate',     'HostelController', 'allocate');
        $this->add('POST', 'hostel/vacate/{id}',  'HostelController', 'vacate');

        // Transport
        $this->add('GET',  'transport',           'TransportController', 'index');
        $this->add('POST', 'transport/vehicles',  'TransportController', 'saveVehicle');
        $this->add('POST', 'transport/routes',    'TransportController', 'saveRoute');

        // Clubs
        $this->add('GET',  'clubs',               'ClubController', 'index');
        $this->add('POST', 'clubs/create',        'ClubController', 'store');
        $this->add('GET',  'clubs/view/{id}',     'ClubController', 'view');
        $this->add('POST', 'clubs/enroll',        'ClubController', 'enroll');

        // Reports
        $this->add('GET',  'reports',             'ReportController', 'index');
        $this->add('GET',  'reports/academic',    'ReportController', 'academic');
        $this->add('GET',  'reports/attendance',  'ReportController', 'attendance');
        $this->add('GET',  'reports/financial',   'ReportController', 'financial');
        $this->add('GET',  'reports/staff',       'ReportController', 'staff');
        $this->add('GET',  'reports/annual',      'ReportController', 'annual');
        $this->add('GET',  'reports/export',      'ReportController', 'export');

        // Settings
        $this->add('GET',  'settings',            'SettingsController', 'index');
        $this->add('POST', 'settings',            'SettingsController', 'save');
        $this->add('GET',  'settings/users',      'SettingsController', 'users');
        $this->add('POST', 'settings/users/create','SettingsController','createUser');
        $this->add('POST', 'settings/users/edit/{id}','SettingsController','editUser');
        $this->add('POST', 'settings/users/toggle/{id}','SettingsController','toggleUser');
        $this->add('POST', 'settings/users/reset-password/{id}','SettingsController','resetUserPassword');
        $this->add('GET',  'settings/roles',      'SettingsController', 'roles');
        $this->add('GET',  'settings/audit',      'SettingsController', 'audit');
        $this->add('GET',  'settings/backup',     'SettingsController', 'backup');
        $this->add('POST', 'settings/backup/create','SettingsController','createBackup');

        // Profile
        $this->add('GET',  'profile',             'ProfileController', 'index');
        $this->add('POST', 'profile',             'ProfileController', 'update');
        $this->add('POST', 'profile/password',    'ProfileController', 'changePassword');
        $this->add('POST', 'profile/photo',       'ProfileController', 'uploadPhoto');

        // Exam Repository
        $this->add('GET',  'exam-repository',                       'ExamRepositoryController', 'index');
        $this->add('GET',  'exam-repository/upload',                'ExamRepositoryController', 'upload');
        $this->add('POST', 'exam-repository/upload',                'ExamRepositoryController', 'store');
        $this->add('GET',  'exam-repository/browse',                'ExamRepositoryController', 'browse');
        $this->add('GET',  'exam-repository/manage',                'ExamRepositoryController', 'manage');
        $this->add('GET',  'exam-repository/view/{id}',             'ExamRepositoryController', 'view');
        $this->add('GET',  'exam-repository/download/{id}',         'ExamRepositoryController', 'download');
        $this->add('GET',  'exam-repository/edit/{id}',             'ExamRepositoryController', 'edit');
        $this->add('POST', 'exam-repository/edit/{id}',             'ExamRepositoryController', 'update');
        $this->add('POST', 'exam-repository/submit/{id}',           'ExamRepositoryController', 'submit');
        $this->add('POST', 'exam-repository/approve/{id}',          'ExamRepositoryController', 'approve');
        $this->add('POST', 'exam-repository/archive/{id}',          'ExamRepositoryController', 'archive');
        $this->add('POST', 'exam-repository/delete/{id}',           'ExamRepositoryController', 'delete');
        $this->add('GET',  'exam-repository/question-bank',         'ExamRepositoryController', 'questionBank');
        $this->add('POST', 'exam-repository/question-bank',         'ExamRepositoryController', 'storeQuestion');
        $this->add('POST', 'exam-repository/question-bank/delete/{id}','ExamRepositoryController','deleteQuestion');
        $this->add('GET',  'exam-repository/reports',               'ExamRepositoryController', 'reports');

        // Settings — Email & SMTP
        $this->add('POST', 'settings/smtp-test',          'SettingsController', 'smtpTest');
        $this->add('POST', 'settings/send-test-email',    'SettingsController', 'sendTestEmail');
        $this->add('POST', 'settings/switch-email-provider','SettingsController','switchEmailProvider');

        // API endpoints
        $this->add('GET',  'api/students',         'ApiController', 'students');
        $this->add('GET',  'api/staff',            'ApiController', 'staff');
        $this->add('GET',  'api/classes',          'ApiController', 'classes');
        $this->add('GET',  'api/subjects',         'ApiController', 'subjects');
        $this->add('GET',  'api/notifications',    'ApiController', 'notifications');
        $this->add('POST', 'api/notifications/read','ApiController', 'markNotifRead');
    }

    private function add(string $method, string $path, string $controller, string $action): void {
        $this->routes[] = compact('method', 'path', 'controller', 'action');
    }

    public function dispatch(): void {
        $method     = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];

        // Strip base path
        $basePath = BASE_PATH;
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        // Remove query string
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $path = trim($requestUri, '/');

        // Support POST method override
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if (strtoupper($route['method']) !== $method) continue;

            $params = $this->matchRoute($route['path'], $path);
            if ($params !== false) {
                $this->callController($route['controller'], $route['action'], $params);
                return;
            }
        }

        // 404 handler
        $this->handle404($path);
    }

    private function matchRoute(string $routePath, string $requestPath): array|false {
        // Build regex from route pattern
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches);
            return $matches;
        }
        return false;
    }

    private function callController(string $controllerName, string $action, array $params): void {
        $file = ROOT . '/app/Controllers/' . $controllerName . '.php';

        if (!file_exists($file)) {
            $this->handle404($controllerName);
            return;
        }

        require_once $file;

        if (!class_exists($controllerName)) {
            $this->handle404($controllerName);
            return;
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $action)) {
            $this->handle404($action);
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function handle404(string $path): void {
        http_response_code(404);
        require_once VIEWS_PATH . '/errors/404.php';
    }
}
