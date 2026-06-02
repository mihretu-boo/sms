<?php

class Flash {

    public static function set(string $type, string $message): void {
        $_SESSION['flash'][$type] = $message;
    }

    public static function get(string $type): string|null {
        $msg = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $msg;
    }

    public static function has(string $type): bool {
        return !empty($_SESSION['flash'][$type]);
    }

    public static function render(): string {
        $html = '';
        $types = ['success', 'error', 'warning', 'info'];
        foreach ($types as $type) {
            if (self::has($type)) {
                $msg     = htmlspecialchars(self::get($type), ENT_QUOTES);
                $bsType  = $type === 'error' ? 'danger' : $type;
                $icon    = match($type) {
                    'success' => 'check-circle',
                    'error'   => 'exclamation-circle',
                    'warning' => 'exclamation-triangle',
                    default   => 'info-circle',
                };
                $html .= <<<HTML
<div class="alert alert-{$bsType} alert-dismissible fade show" role="alert">
  <i class="fas fa-{$icon} me-2"></i>{$msg}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
HTML;
            }
        }
        return $html;
    }
}
