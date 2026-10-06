<?php

class Lang {

    private static array $strings = [];
    private static string $current = 'en';
    private static array $supported = ['en', 'om', 'am'];

    public static function init(): void {
        // Detect language: URL param → session → default
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$supported)) {
            $_SESSION['site_lang'] = $_GET['lang'];
        }
        self::$current = $_SESSION['site_lang'] ?? 'en';
        self::load(self::$current);
    }

    public static function set(string $lang): void {
        if (in_array($lang, self::$supported)) {
            self::$current = $lang;
            $_SESSION['site_lang'] = $lang;
            self::load($lang);
        }
    }

    public static function current(): string {
        return self::$current;
    }

    public static function supported(): array {
        return self::$supported;
    }

    private static function load(string $lang): void {
        if (isset(self::$strings[$lang])) return;
        $file = ROOT . '/lang/' . $lang . '.php';
        self::$strings[$lang] = file_exists($file) ? require $file : [];
    }

    public static function get(string $key, array $replace = []): string {
        self::load(self::$current);
        $str = self::$strings[self::$current][$key]
            ?? self::$strings['en'][$key]
            ?? $key;

        foreach ($replace as $k => $v) {
            $str = str_replace(':' . $k, $v, $str);
        }
        return $str;
    }

    // Return the translated field name for multilingual DB columns (e.g. title_en)
    public static function col(string $base): string {
        return $base . '_' . self::$current;
    }

    // Get text from a multilingual DB row, falling back to English
    public static function field(array $row, string $base): string {
        $col    = $base . '_' . self::$current;
        $colEn  = $base . '_en';
        return $row[$col] ?? $row[$colEn] ?? '';
    }
}

// Global shortcut
function t(string $key, array $replace = []): string {
    return e(Lang::get($key, $replace));
}

// Raw (unescaped) translation — only for HTML content stored in DB
function tr(string $key): string {
    return Lang::get($key);
}
