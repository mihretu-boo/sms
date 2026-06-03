<?php

/**
 * SJASSMS — Native PHP SMTP Mailer
 * No external dependencies (no Composer required)
 * Supports: Gmail, Outlook/Hotmail, institutional SMTP
 * Encryption: TLS (STARTTLS on port 587), SSL (port 465), plain
 */
class Mailer {

    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromEmail;
    private string $fromName;
    private bool   $auth;
    private int    $timeout;
    private bool   $debug;
    /** @var resource|false */
    private $socket = false;
    private array  $log = [];

    public function __construct(array $config = []) {
        $this->host       = $config['host']       ?? getSetting('smtp_host',       'smtp.gmail.com');
        $this->port       = (int)($config['port'] ?? getSetting('smtp_port',       '587'));
        $this->encryption = $config['encryption'] ?? getSetting('smtp_encryption',  'tls');
        $this->username   = $config['username']   ?? getSetting('smtp_user',        '');
        $this->password   = $config['password']   ?? getSetting('smtp_pass',        '');
        // fromEmail: prefer explicit smtp_from_email, fall back to smtp_user, then school_email
        $fromEmail        = getSetting('smtp_from_email', '');
        if (empty($fromEmail)) $fromEmail = getSetting('smtp_user', '');
        if (empty($fromEmail)) $fromEmail = getSetting('school_email', '');
        $this->fromEmail  = $config['from_email'] ?? $fromEmail;
        $this->fromName   = $config['from_name']  ?? getSetting('smtp_from_name', getSetting('school_name', 'SJASSMS'));
        $this->auth       = (bool)($config['auth'] ?? getSetting('smtp_auth', '1'));
        $this->timeout    = (int)($config['timeout'] ?? getSetting('smtp_timeout', '30'));
        $this->debug      = (bool)($config['debug'] ?? false);
    }

    // ===== Public API =====

    /**
     * Send an email.
     * Returns true on success, throws \RuntimeException on failure.
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $plainBody = '',
        array  $replyTo   = []
    ): bool {
        if (empty($this->password)) {
            // SMTP password not set — log the email to file and return true (dev mode)
            // Admin must set smtp_pass in Settings → Email & SMTP
            $this->logFallback($to, $subject, $htmlBody,
                'SMTP password not set. Go to Settings → Email & SMTP to enter the password for ' . $this->username);
            return true;
        }

        try {
            $this->connect();
            $this->sendHello();
            $this->startTls();
            $this->authenticate();
            $this->sendMail($to, $subject, $htmlBody, $plainBody, $replyTo);
            $this->quit();
            return true;
        } catch (\Exception $e) {
            $this->logEntry('ERROR: ' . $e->getMessage());
            $this->close();
            // Fallback to log file
            $this->logFallback($to, $subject, $htmlBody, $e->getMessage());
            throw new \RuntimeException('Email send failed: ' . $e->getMessage());
        }
    }

    public function getLog(): array {
        return $this->log;
    }

    // ===== SMTP Protocol =====

    private function connect(): void {
        $this->log = [];
        $this->logEntry("Connecting to {$this->host}:{$this->port} ({$this->encryption})");

        $host = match($this->encryption) {
            'ssl' => 'ssl://' . $this->host,
            default => $this->host,
        };

        $errNo  = 0;
        $errStr = '';
        $this->socket = @stream_socket_client(
            "$host:{$this->port}",
            $errNo, $errStr,
            $this->timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$this->socket) {
            throw new \RuntimeException("Connection failed ({$errNo}): {$errStr}");
        }

        stream_set_timeout($this->socket, $this->timeout);
        $response = $this->readResponse();
        $this->logEntry("Server: $response");

        if (!str_starts_with($response, '220')) {
            throw new \RuntimeException("Unexpected greeting: $response");
        }
    }

    private function sendHello(): void {
        $domain = gethostname() ?: 'localhost';
        $resp   = $this->cmd("EHLO $domain");

        if (str_starts_with($resp, '421') || str_starts_with($resp, '5')) {
            $resp = $this->cmd("HELO $domain");
        }

        if (!str_starts_with($resp, '250')) {
            throw new \RuntimeException("EHLO rejected: $resp");
        }
    }

    private function startTls(): void {
        if ($this->encryption !== 'tls') return;

        $resp = $this->cmd('STARTTLS');
        if (!str_starts_with($resp, '220')) {
            throw new \RuntimeException("STARTTLS rejected: $resp");
        }

        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new \RuntimeException('TLS handshake failed.');
        }

        // Re-negotiate EHLO after TLS
        $this->sendHello();
    }

    private function authenticate(): void {
        if (!$this->auth || empty($this->username)) return;

        $resp = $this->cmd('AUTH LOGIN');
        if (!str_starts_with($resp, '334')) {
            throw new \RuntimeException("AUTH LOGIN rejected: $resp");
        }

        $resp = $this->cmd(base64_encode($this->username));
        if (!str_starts_with($resp, '334')) {
            throw new \RuntimeException("Username rejected: $resp");
        }

        $resp = $this->cmd(base64_encode($this->password));
        if (!str_starts_with($resp, '235')) {
            throw new \RuntimeException("Authentication failed. Check SMTP credentials. Response: $resp");
        }

        $this->logEntry('AUTH OK');
    }

    private function sendMail(
        string $to,
        string $subject,
        string $htmlBody,
        string $plainBody,
        array  $replyTo
    ): void {
        $fromEmail = $this->fromEmail ?: $this->username;
        $fromName  = $this->fromName;
        $boundary  = '----SJASSMS_' . md5(uniqid('', true));

        // MAIL FROM
        $resp = $this->cmd("MAIL FROM:<{$fromEmail}>");
        if (!str_starts_with($resp, '250')) {
            throw new \RuntimeException("MAIL FROM rejected: $resp");
        }

        // RCPT TO
        $resp = $this->cmd("RCPT TO:<{$to}>");
        if (!str_starts_with($resp, '250') && !str_starts_with($resp, '251')) {
            throw new \RuntimeException("RCPT TO rejected: $resp");
        }

        // DATA
        $resp = $this->cmd('DATA');
        if (!str_starts_with($resp, '354')) {
            throw new \RuntimeException("DATA rejected: $resp");
        }

        // Build headers + message
        $plain   = $plainBody ?: strip_tags($htmlBody);
        $headers = implode("\r\n", [
            "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>",
            "To: {$to}",
            "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
            "MIME-Version: 1.0",
            "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
            "X-Mailer: SJASSMS-Mailer",
            "Date: " . date('r'),
        ]);

        if (!empty($replyTo)) {
            $headers .= "\r\nReply-To: " . $replyTo[0];
        }

        $body  = "{$headers}\r\n\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($plain)) . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($htmlBody)) . "\r\n";
        $body .= "--{$boundary}--\r\n";
        $body .= ".";

        $resp = $this->cmd($body);
        if (!str_starts_with($resp, '250')) {
            throw new \RuntimeException("Message rejected: $resp");
        }

        $this->logEntry("Message accepted for $to");
    }

    private function quit(): void {
        $this->cmd('QUIT');
        $this->close();
    }

    private function close(): void {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = false;
        }
    }

    private function cmd(string $command): string {
        if (!$this->socket) {
            throw new \RuntimeException('No SMTP connection.');
        }

        // Don't log passwords
        $logCmd = str_starts_with($command, 'AUTH') || (strlen($command) > 20 && base64_decode($command, true) && str_contains($this->password, base64_decode($command)))
            ? '***'
            : (strlen($command) > 200 ? substr($command, 0, 80) . '...' : $command);

        $this->logEntry(">>> $logCmd");
        fwrite($this->socket, $command . "\r\n");
        $resp = $this->readResponse();
        $this->logEntry("<<< $resp");
        return $resp;
    }

    private function readResponse(): string {
        $data = '';
        while (($line = fgets($this->socket, 512)) !== false) {
            $data .= trim($line);
            // Multi-line response ends when 4th char is space, not hyphen
            if (isset($line[3]) && $line[3] === ' ') break;
            $data .= "\n";
        }
        return $data;
    }

    private function logEntry(string $msg): void {
        $this->log[] = date('H:i:s') . ' ' . $msg;
    }

    // ===== Fallback (dev mode — SMTP not configured) =====

    private function logFallback(string $to, string $subject, string $body, string $error = ''): void {
        $logDir  = ROOT . '/storage/email-logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

        $filename = $logDir . '/email_' . date('Ymd_His') . '_' . substr(md5($to), 0, 6) . '.html';
        $content  = "<!--\n";
        $content .= "To: $to\nSubject: $subject\n";
        $content .= $error ? "Error: $error\n" : "Note: SMTP not configured, email logged to file.\n";
        $content .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $content .= "-->\n" . $body;

        @file_put_contents($filename, $content);
    }

    // ===== Static: Template Rendering =====

    public static function renderTemplate(string $template, array $vars = []): string {
        $file = ROOT . '/app/Templates/email/' . $template . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("Email template not found: $template");
        }
        extract($vars);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    // ===== Static: Test Connection =====

    public static function test(): array {
        $result = ['success' => false, 'message' => '', 'log' => []];
        try {
            $mailer = new self();
            if (empty(getSetting('smtp_user'))) {
                $result['message'] = 'SMTP not configured. Set SMTP credentials in Settings → Email.';
                return $result;
            }
            $mailer->connect();
            $mailer->sendHello();
            $mailer->startTls();
            $mailer->authenticate();
            $mailer->quit();
            $result['success'] = true;
            $result['message'] = 'SMTP connection and authentication successful!';
            $result['log']     = $mailer->getLog();
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['log']     = isset($mailer) ? $mailer->getLog() : [];
        }
        return $result;
    }
}
