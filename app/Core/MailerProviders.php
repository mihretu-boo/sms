<?php

/**
 * SJASSMS — Email Provider Presets
 * Centralised configuration for all supported email providers.
 * Admin selects a provider; the system auto-fills SMTP settings.
 * No code changes needed when switching providers.
 */
class MailerProviders {

    /**
     * All supported providers with their preset SMTP configurations.
     * Keys are stored in the settings table as smtp_provider.
     */
    public const PRESETS = [

        'google_workspace' => [
            'label'       => 'Google Workspace',
            'description' => 'Institutional email powered by Google (e.g. @bru.edu.et, @school.edu.et)',
            'icon'        => 'google',
            'color'       => '#4285F4',
            'badge_color' => 'primary',
            'host'        => 'smtp.gmail.com',
            'port'        => 587,
            'port_ssl'    => 465,
            'encryption'  => 'tls',
            'auth'        => true,
            'setup_steps' => [
                'Enter your institutional email address (e.g. you@bru.edu.et)',
                'Enter your email password',
                'If 2-Step Verification is ON → use a 16-char App Password instead',
            ],
            'app_password_url' => 'https://myaccount.google.com/apppasswords',
            'help_url'         => 'https://support.google.com/a/answer/176600',
            'note'             => 'Same infrastructure as Gmail. App Password required if 2FA is enabled.',
            'requires_app_password' => true,
        ],

        'gmail' => [
            'label'       => 'Gmail (Personal)',
            'description' => 'Personal Gmail accounts (@gmail.com)',
            'icon'        => 'google',
            'color'       => '#EA4335',
            'badge_color' => 'danger',
            'host'        => 'smtp.gmail.com',
            'port'        => 587,
            'port_ssl'    => 465,
            'encryption'  => 'tls',
            'auth'        => true,
            'setup_steps' => [
                'Enter your Gmail address (e.g. yourname@gmail.com)',
                'Enable 2-Step Verification on your Google Account',
                'Generate a 16-character App Password at myaccount.google.com/apppasswords',
                'Paste the App Password (NOT your regular password) in the password field',
            ],
            'app_password_url' => 'https://myaccount.google.com/apppasswords',
            'help_url'         => 'https://support.google.com/mail/answer/185833',
            'note'             => 'Regular Gmail password does not work. App Password is required.',
            'requires_app_password' => true,
        ],

        'outlook' => [
            'label'       => 'Outlook / Microsoft 365',
            'description' => 'Outlook.com, Hotmail, or Microsoft 365 / Office 365',
            'icon'        => 'microsoft',
            'color'       => '#0078D4',
            'badge_color' => 'info',
            'host'        => 'smtp.office365.com',
            'port'        => 587,
            'port_ssl'    => 587,
            'encryption'  => 'tls',
            'auth'        => true,
            'setup_steps' => [
                'Enter your Microsoft email address',
                'Enter your Microsoft account password',
                'If using Microsoft 365 with MFA → use an App Password from Microsoft account security settings',
            ],
            'app_password_url' => 'https://account.microsoft.com/security',
            'help_url'         => 'https://support.microsoft.com/en-us/office/pop-imap-and-smtp-settings',
            'note'             => 'Works with Outlook.com, Hotmail.com, and Microsoft 365 accounts.',
            'requires_app_password' => false,
        ],

        'yahoo' => [
            'label'       => 'Yahoo Mail',
            'description' => 'Yahoo Mail accounts (@yahoo.com)',
            'icon'        => 'yahoo',
            'color'       => '#6001D2',
            'badge_color' => 'purple',
            'host'        => 'smtp.mail.yahoo.com',
            'port'        => 587,
            'port_ssl'    => 465,
            'encryption'  => 'tls',
            'auth'        => true,
            'setup_steps' => [
                'Enter your Yahoo email address',
                'Go to Yahoo Account Security → Generate App Password',
                'Enter the generated App Password (NOT your regular password)',
            ],
            'app_password_url' => 'https://login.yahoo.com/account/security',
            'help_url'         => 'https://help.yahoo.com/kb/SLN4075.html',
            'note'             => 'Yahoo requires an App Password — regular passwords are not accepted.',
            'requires_app_password' => true,
        ],

        'custom' => [
            'label'       => 'Custom SMTP',
            'description' => 'Any SMTP server — enter your own host, port, and credentials',
            'icon'        => 'server',
            'color'       => '#6c757d',
            'badge_color' => 'secondary',
            'host'        => '',
            'port'        => 587,
            'port_ssl'    => 465,
            'encryption'  => 'tls',
            'auth'        => true,
            'setup_steps' => [
                'Get SMTP settings from your email provider or server admin',
                'Enter the SMTP hostname, port, and encryption type',
                'Enter your email address and password',
            ],
            'app_password_url' => null,
            'help_url'         => null,
            'note'             => 'Full control — enter all SMTP details manually.',
            'requires_app_password' => false,
        ],
    ];

    /**
     * Get a single provider preset by key.
     */
    public static function get(string $provider): array {
        return self::PRESETS[$provider] ?? self::PRESETS['custom'];
    }

    /**
     * Get current provider from settings, with fallback.
     */
    public static function current(): array {
        $key = getSetting('smtp_provider', 'custom');
        return self::get($key);
    }

    /**
     * Apply a provider's preset SMTP settings to the database.
     * Called when admin selects a new provider.
     * Preserves: smtp_user, smtp_pass (credentials stay the same).
     */
    public static function applyPreset(PDO $db, string $provider): void {
        $preset = self::get($provider);

        $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$provider, 'smtp_provider']);
        $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$preset['host'],       'smtp_host']);
        $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([(string)$preset['port'], 'smtp_port']);
        $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$preset['encryption'], 'smtp_encryption']);
        $db->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?")->execute([$preset['auth'] ? '1' : '0', 'smtp_auth']);
    }

    /**
     * Return preset config as JSON for the JavaScript provider-switcher.
     */
    public static function presetsJson(): string {
        $out = [];
        foreach (self::PRESETS as $key => $p) {
            $out[$key] = [
                'label'       => $p['label'],
                'host'        => $p['host'],
                'port'        => $p['port'],
                'port_ssl'    => $p['port_ssl'],
                'encryption'  => $p['encryption'],
                'note'        => $p['note'],
                'steps'       => $p['setup_steps'],
                'app_password_url'      => $p['app_password_url'],
                'requires_app_password' => $p['requires_app_password'],
            ];
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }
}
