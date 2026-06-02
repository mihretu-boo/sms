<?php

function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset(string $path): string {
    return ASSETS_URL . '/' . ltrim($path, '/');
}

function uploadUrl(string $path): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function e(string|null $str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function old(string $key, mixed $default = ''): mixed {
    return $_SESSION['old_input'][$key] ?? $default;
}

function clearOld(): void {
    unset($_SESSION['old_input']);
}

function setOld(array $data): void {
    $_SESSION['old_input'] = $data;
}

function csrfField(): string {
    $token = Auth::csrf();
    return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
}

function selected(mixed $current, mixed $value): string {
    return $current == $value ? 'selected' : '';
}

function checked(mixed $current, mixed $value): string {
    return $current == $value ? 'checked' : '';
}

function formatDate(string|null $date, string $format = 'd M Y'): string {
    if (!$date) return '-';
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

function formatMoney(float|null $amount, string $currency = 'ETB'): string {
    if ($amount === null) return '-';
    return $currency . ' ' . number_format($amount, 2);
}

function calcGrade(float $percentage): array {
    foreach (GRADE_SCALE as $grade) {
        if ($percentage >= $grade['min'] && $percentage <= $grade['max']) {
            return $grade;
        }
    }
    return ['letter' => 'F', 'point' => 0.00, 'desc' => 'Fail'];
}

function calcGradeFromMarks(float $obtained, float $total): array {
    $percentage = $total > 0 ? ($obtained / $total) * 100 : 0;
    return calcGrade($percentage);
}

function getGpaClass(float $gpa): string {
    if ($gpa >= 3.75) return 'text-success fw-bold';
    if ($gpa >= 3.0)  return 'text-primary fw-bold';
    if ($gpa >= 2.0)  return 'text-warning';
    if ($gpa >= 1.0)  return 'text-orange';
    return 'text-danger';
}

function getAttendanceBadge(string $status): string {
    return match($status) {
        'present' => '<span class="badge bg-success">Present</span>',
        'absent'  => '<span class="badge bg-danger">Absent</span>',
        'late'    => '<span class="badge bg-warning text-dark">Late</span>',
        'excused' => '<span class="badge bg-info">Excused</span>',
        default   => '<span class="badge bg-secondary">Unknown</span>',
    };
}

function getStatusBadge(string $status): string {
    return match(strtolower($status)) {
        'active','present','paid','approved','returned' => '<span class="badge bg-success">' . ucfirst($status) . '</span>',
        'inactive','absent','unpaid','rejected'          => '<span class="badge bg-danger">' . ucfirst($status) . '</span>',
        'pending','partial','late'                       => '<span class="badge bg-warning text-dark">' . ucfirst($status) . '</span>',
        'suspended','terminated','overdue'               => '<span class="badge bg-danger">' . ucfirst($status) . '</span>',
        default => '<span class="badge bg-secondary">' . ucfirst($status) . '</span>',
    };
}

function getRoleLabel(string $role): string {
    return match($role) {
        'super_admin'     => 'Super Administrator',
        'principal'       => 'Principal',
        'vice_principal'  => 'Vice Principal',
        'registrar'       => 'Registrar',
        'teacher'         => 'Teacher',
        'dept_head'       => 'Department Head',
        'student'         => 'Student',
        'parent'          => 'Parent/Guardian',
        'finance_officer' => 'Finance Officer',
        default           => ucfirst(str_replace('_', ' ', $role)),
    };
}

function getRoleBadge(string $role): string {
    $color = match($role) {
        'super_admin'     => 'danger',
        'principal'       => 'primary',
        'vice_principal'  => 'info',
        'registrar'       => 'success',
        'teacher'         => 'warning',
        'dept_head'       => 'purple',
        'student'         => 'secondary',
        'parent'          => 'dark',
        'finance_officer' => 'green',
        default           => 'secondary',
    };
    return '<span class="badge bg-' . $color . '">' . getRoleLabel($role) . '</span>';
}

function timeAgo(string $datetime): string {
    try {
        $time = new DateTime($datetime);
        $now  = new DateTime();
        $diff = $now->diff($time);

        if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'Just now';
    } catch (Exception $e) {
        return $datetime;
    }
}

function generateStudentId(int $year, int $seq): string {
    return 'STU-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
}

function generateEmployeeId(int $seq): string {
    return 'EMP-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
}

function generateReceiptNo(): string {
    return 'REC-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function truncate(string $str, int $length = 50): string {
    return strlen($str) > $length ? substr($str, 0, $length) . '...' : $str;
}

function getSetting(string $key, mixed $default = ''): string {
    static $settings = null;
    if ($settings === null) {
        try {
            $db   = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

function getUnreadNotifications(): array {
    if (!Auth::check()) return [];
    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([Auth::id()]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getUnreadMessages(): int {
    if (!Auth::check()) return 0;
    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND read_at IS NULL AND is_deleted_receiver = 0");
        $stmt->execute([Auth::id()]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getActiveAnnouncements(): array {
    try {
        $db   = getDB();
        $role = Auth::role() ?? 'student';
        $stmt = $db->prepare("SELECT * FROM announcements WHERE status = 'active' AND start_date <= CURDATE() AND end_date >= CURDATE() AND (target_role = 'all' OR target_role = ?) ORDER BY priority DESC, created_at DESC LIMIT 5");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function photoUrl(string|null $photo, string $placeholder = 'user'): string {
    if ($photo && file_exists(ROOT . '/' . $photo)) {
        return BASE_URL . '/' . $photo;
    }
    return ASSETS_URL . '/images/placeholders/' . $placeholder . '.png';
}

function paginationLinks(array $pager, string $baseUrl = ''): string {
    if ($pager['total_pages'] <= 1) return '';

    $current = $pager['current_page'];
    $total   = $pager['total_pages'];
    $base    = $baseUrl ?: ($_SERVER['REQUEST_URI'] ?? '');
    $base    = preg_replace('/[?&]page=\d+/', '', $base);
    $sep     = strpos($base, '?') !== false ? '&' : '?';

    $html = '<nav aria-label="Page navigation"><ul class="pagination pagination-sm mb-0">';

    if ($current > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base . $sep . 'page=' . ($current - 1) . '">&laquo;</a></li>';
    }

    $start = max(1, $current - 2);
    $end   = min($total, $current + 2);

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $current ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base . $sep . 'page=' . $i . '">' . $i . '</a></li>';
    }

    if ($current < $total) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base . $sep . 'page=' . ($current + 1) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
