<?php

/**
 * SJASSMS — Database-backed Rate Limiter
 * Used to restrict password reset requests.
 */
class RateLimiter {

    private PDO $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Check if the identifier (email or IP) has exceeded the limit
     * within the rolling time window.
     *
     * @param string $identifier  Email address or IP
     * @param string $type        'email' or 'ip'
     * @param int    $maxRequests Max allowed requests per window
     * @param int    $windowMins  Window size in minutes
     * @return bool  true = allowed, false = rate-limited
     */
    public function attempt(string $identifier, string $type = 'email', int $maxRequests = 3, int $windowMins = 60): bool {
        $this->cleanup();

        $stmt = $this->db->prepare(
            "SELECT id, requests, window_start FROM password_reset_rate_limit
             WHERE identifier = ? AND type = ? LIMIT 1"
        );
        $stmt->execute([$identifier, $type]);
        $row = $stmt->fetch();

        if (!$row) {
            // First request — insert
            $this->db->prepare(
                "INSERT INTO password_reset_rate_limit (identifier, type, requests, window_start)
                 VALUES (?, ?, 1, NOW())"
            )->execute([$identifier, $type]);
            return true;
        }

        $windowStart = new DateTime($row['window_start']);
        $now         = new DateTime();
        $elapsed     = ($now->getTimestamp() - $windowStart->getTimestamp()) / 60; // minutes

        if ($elapsed >= $windowMins) {
            // Window expired — reset
            $this->db->prepare(
                "UPDATE password_reset_rate_limit SET requests = 1, window_start = NOW()
                 WHERE id = ?"
            )->execute([$row['id']]);
            return true;
        }

        if ($row['requests'] >= $maxRequests) {
            return false; // Rate limited
        }

        // Increment counter
        $this->db->prepare(
            "UPDATE password_reset_rate_limit SET requests = requests + 1 WHERE id = ?"
        )->execute([$row['id']]);

        return true;
    }

    /**
     * Get remaining attempts for an identifier.
     */
    public function remaining(string $identifier, string $type = 'email', int $maxRequests = 3, int $windowMins = 60): int {
        $stmt = $this->db->prepare(
            "SELECT requests, window_start FROM password_reset_rate_limit
             WHERE identifier = ? AND type = ? LIMIT 1"
        );
        $stmt->execute([$identifier, $type]);
        $row = $stmt->fetch();

        if (!$row) return $maxRequests;

        $elapsed = (time() - strtotime($row['window_start'])) / 60;
        if ($elapsed >= $windowMins) return $maxRequests;

        return max(0, $maxRequests - $row['requests']);
    }

    /**
     * Get seconds until the rate limit window resets.
     */
    public function retryAfter(string $identifier, string $type = 'email', int $windowMins = 60): int {
        $stmt = $this->db->prepare(
            "SELECT window_start FROM password_reset_rate_limit
             WHERE identifier = ? AND type = ? LIMIT 1"
        );
        $stmt->execute([$identifier, $type]);
        $row = $stmt->fetch();

        if (!$row) return 0;

        $elapsed  = time() - strtotime($row['window_start']);
        $resetAt  = ($windowMins * 60) - $elapsed;
        return max(0, $resetAt);
    }

    /**
     * Clear expired entries.
     */
    private function cleanup(): void {
        try {
            $this->db->exec(
                "DELETE FROM password_reset_rate_limit
                 WHERE window_start < DATE_SUB(NOW(), INTERVAL 2 HOUR)"
            );
        } catch (\Exception $e) {
            // Non-fatal
        }
    }
}
