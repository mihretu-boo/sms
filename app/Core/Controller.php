<?php

class Controller {

    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $viewFile");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    protected function redirect(string $path, array $params = []): void {
        $url = BASE_URL . '/' . ltrim($path, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        header('Location: ' . $url);
        exit;
    }

    protected function redirectBack(): void {
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard';
        header('Location: ' . $referer);
        exit;
    }

    protected function json(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function requireAuth(array $allowedRoles = []): void {
        if (!Auth::check()) {
            Flash::set('error', 'Please login to access this page.');
            $this->redirect('login');
        }

        if (!empty($allowedRoles) && !in_array(Auth::user()['role'], $allowedRoles)) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    protected function requireGuest(): void {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
    }

    protected function post(string $key, mixed $default = null): mixed {
        return isset($_POST[$key]) ? $this->sanitize($_POST[$key]) : $default;
    }

    protected function get(string $key, mixed $default = null): mixed {
        return isset($_GET[$key]) ? $this->sanitize($_GET[$key]) : $default;
    }

    protected function file(string $key): array|null {
        return $_FILES[$key] ?? null;
    }

    private function sanitize(mixed $value): mixed {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        return htmlspecialchars(strip_tags(trim((string)$value)), ENT_QUOTES, 'UTF-8');
    }

    protected function validateCsrf(): void {
        $token = $this->post('_csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            Flash::set('error', 'Invalid request. Please try again.');
            $this->redirectBack();
        }
    }

    protected function uploadFile(string $field, string $dir, array $allowedTypes = []): string|false {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $file     = $_FILES[$field];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            Flash::set('error', 'Invalid file type.');
            return false;
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            Flash::set('error', 'File too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.');
            return false;
        }

        $uploadDir = UPLOADS_PATH . '/' . $dir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('', true) . '.' . strtolower($ext);
        $dest     = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/' . $dir . '/' . $filename;
        }

        return false;
    }

    protected function paginate(array $data, int $total, int $perPage = PER_PAGE): array {
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $totalPages = (int)ceil($total / $perPage);
        return [
            'data'        => $data,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => $totalPages,
            'offset'      => ($page - 1) * $perPage,
        ];
    }
}
