<?php
/**
 * SJASSMS Database Installer
 * Run this script ONCE to set up the database.
 * Delete or protect this file after installation.
 */
session_start();

$step = $_POST['step'] ?? 1;
$messages = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host   = trim($_POST['db_host'] ?? 'localhost');
    $name   = trim($_POST['db_name'] ?? 'sjassms');
    $user   = trim($_POST['db_user'] ?? 'root');
    $pass   = $_POST['db_pass'] ?? '';

    try {
        // Test connection
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE                => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,  // prevent "unbuffered query" errors
        ]);
        $messages[] = ['type'=>'success', 'text'=>'✓ Database connection successful'];

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
        $messages[] = ['type'=>'success', 'text'=>"✓ Database '$name' created/selected"];

        // Run SQL schema (use final merged database)
        $sqlFile = __DIR__ . '/database/sjassms_final.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);

            // ── Step 1: Strip ALL SQL line comments (-- ...)
            //    This is the critical fix: the old code checked if a statement
            //    *started* with '--' and skipped it, but comment blocks appear
            //    BEFORE CREATE TABLE / DROP TABLE statements after a ';' split,
            //    causing the whole statement including the SQL to be silently skipped.
            $sql = preg_replace('/--[^\n]*/m', '', $sql);

            // ── Step 2: Strip block comments (/* ... */)
            $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);

            // ── Step 3: Remove USE and CREATE DATABASE (already handled by PDO)
            $sql = preg_replace('/\bUSE\s+`[^`]+`\s*;?/m',              '', $sql);
            $sql = preg_replace('/\bCREATE\s+DATABASE\b.*?;/si',         '', $sql);
            $sql = preg_replace('/\bDROP\s+DATABASE\b.*?;/si',           '', $sql);

            // ── Step 4: Remove session-level statements not needed in PHP context
            $sql = preg_replace('/^\s*SET\s+SQL_MODE\s*=.*?;/mi',        '', $sql);
            $sql = preg_replace('/^\s*SET\s+time_zone\s*=.*?;/mi',       '', $sql);
            $sql = preg_replace('/^\s*SET\s+NAMES\s+\w+\s*;/mi',         '', $sql);
            $sql = preg_replace('/^\s*SET\s+AUTOCOMMIT\s*=.*?;/mi',      '', $sql);
            $sql = preg_replace('/^\s*START\s+TRANSACTION\s*;/mi',       '', $sql);
            $sql = preg_replace('/^\s*COMMIT\s*;/mi',                     '', $sql);

            // ── Step 5: Enable FK checks explicitly via PDO before executing
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            // ── Step 6: Split by semicolons and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            $executed   = 0;
            $warnings   = 0;

            foreach ($statements as $stmt) {
                if (empty($stmt)) continue;

                // Skip SELECT / SHOW statements — these are verification queries
                // in the SQL file and leave unbuffered results that break PDO
                $firstWord = strtoupper(substr(ltrim($stmt), 0, 6));
                if ($firstWord === 'SELECT' || $firstWord === 'SHOW  ') continue;

                try {
                    $pdo->exec($stmt);
                    $executed++;
                } catch (PDOException $e) {
                    $msg = $e->getMessage();
                    // Silently ignore: duplicate key, already-exists, already-dropped
                    if (strpos($msg, 'Duplicate entry')          !== false) continue;
                    if (strpos($msg, 'already exists')           !== false) continue;
                    if (strpos($msg, "Can't drop")               !== false) continue;
                    if (strpos($msg, 'Unknown table')            !== false) continue;

                    $warnings++;
                    $messages[] = ['type'=>'warning', 'text'=>'⚠ ' . substr($msg, 0, 150)];
                }
            }

            // ── Step 7: Re-enable FK checks
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

            if ($warnings === 0) {
                $messages[] = ['type'=>'success', 'text'=>"✓ Schema installed perfectly ($executed statements, 0 warnings)"];
            } else {
                $messages[] = ['type'=>'success', 'text'=>"✓ Schema installed ($executed statements, $warnings warnings — see above)"];
            }
        } else {
            $messages[] = ['type'=>'danger', 'text'=>'✗ SQL file not found: database/sjassms_final.sql'];
        }

        // Update config/database.php
        $configContent = "<?php\ndefine('DB_HOST', '$host');\ndefine('DB_NAME', '$name');\ndefine('DB_USER', '$user');\ndefine('DB_PASS', '" . addslashes($pass) . "');\ndefine('DB_CHARSET', 'utf8mb4');\n\nfunction getDB(): PDO {\n    static \$pdo = null;\n    if (\$pdo === null) {\n        try {\n            \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;\n            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [\n                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,\n                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n                PDO::ATTR_EMULATE_PREPARES   => false,\n            ]);\n        } catch (PDOException \$e) {\n            die('<div style=\"font-family:sans-serif;padding:30px;color:#c0392b;\"><h2>Database Error</h2><p>' . htmlspecialchars(\$e->getMessage()) . '</p></div>');\n        }\n    }\n    return \$pdo;\n}\n";
        file_put_contents(__DIR__ . '/config/database.php', $configContent);
        $messages[] = ['type'=>'success', 'text'=>'✓ Database configuration updated'];

        $success = true;
        $messages[] = ['type'=>'success', 'text'=>'✓ Installation complete! You can now login.'];

    } catch (PDOException $e) {
        $messages[] = ['type'=>'danger', 'text'=>'✗ Connection failed: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SJASSMS — Database Setup</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body { background: linear-gradient(135deg, #1565C0, #2E7D32); min-height: 100vh; font-family: 'Segoe UI', sans-serif; }
.install-card { max-width: 560px; margin: 40px auto; border-radius: 16px; }
.step-icon { width: 40px; height: 40px; background: #1565C0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 12px; flex-shrink: 0; }
</style>
</head>
<body>
<div class="container py-5">
  <div class="install-card card shadow-lg border-0">
    <div class="card-header bg-primary text-white text-center py-4">
      <i class="fas fa-graduation-cap fa-2x mb-2"></i>
      <h5 class="mb-0">SJASSMS Installation</h5>
      <small class="opacity-75">Shalaka Jatan Ali Secondary School Management System</small>
    </div>
    <div class="card-body p-4">
      <?php if (!empty($messages)): ?>
      <div class="mb-4">
        <?php foreach ($messages as $m): ?>
        <div class="alert alert-<?= $m['type'] ?> py-2 mb-1 small"><?= htmlspecialchars($m['text']) ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($success): ?>
      <div class="text-center py-3">
        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
        <h5 class="text-success">Installation Complete!</h5>
        <p class="text-muted">The database has been set up successfully.</p>
        <div class="card bg-light p-3 mb-3 text-start small">
          <strong>Default Login Accounts:</strong><br>
          <span class="badge bg-danger me-1 mt-1">admin / password</span>
          <span class="badge bg-primary me-1 mt-1">principal / password</span>
          <span class="badge bg-success me-1 mt-1">teacher1 / password</span>
          <span class="badge bg-info me-1 mt-1">student1 / password</span>
          <span class="badge bg-warning text-dark me-1 mt-1">parent1 / password</span>
          <span class="badge bg-secondary me-1 mt-1">finance / password</span>
        </div>
        <div class="alert alert-warning small"><i class="fas fa-exclamation-triangle me-1"></i><strong>Security:</strong> Please delete or move <code>install.php</code> after setup.</div>
        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/dashboard" class="btn btn-primary w-100">
          <i class="fas fa-arrow-right me-2"></i>Go to Login
        </a>
      </div>
      <?php else: ?>
      <h6 class="fw-bold mb-3"><i class="fas fa-database text-primary me-2"></i>Database Configuration</h6>
      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Database Host</label>
            <input type="text" name="db_host" class="form-control" value="localhost" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Database Name</label>
            <input type="text" name="db_name" class="form-control" value="sjassms" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Username</label>
            <input type="text" name="db_user" class="form-control" value="root" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="db_pass" class="form-control" placeholder="Leave blank for XAMPP default">
          </div>
        </div>

        <div class="alert alert-info mt-3 small">
          <i class="fas fa-info-circle me-1"></i>
          <strong>XAMPP Default:</strong> Host=localhost, User=root, Password=(empty).<br>
          This will create the <code>sjassms</code> database and all tables with sample data.
        </div>

        <button type="submit" name="step" value="2" class="btn btn-primary w-100 mt-2">
          <i class="fas fa-play me-2"></i>Install Database
        </button>
      </form>
      <?php endif; ?>
    </div>
    <div class="card-footer text-center text-muted small py-2">
      SJASSMS v1.0 — PHP <?= phpversion() ?> | MySQL
    </div>
  </div>
</div>
</body>
</html>
