<?php
// Database configuration and app settings

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'quizwhiz_junction');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default; change in production

define('APP_NAME', 'Quizwhiz Junction');
define('APP_BASE_URL', '/Quizwhiz-Junction'); // Adjust if deployed in a different folder

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('qwz_session');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            ensure_default_admin($pdo);
            ensure_default_admin_account($pdo);
    }
    return $pdo;
}

    function ensure_default_admin(PDO $pdo): void {
        // Creates or updates a default admin account if no admins exist.
        try {
            $adminCount = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_admin=1')->fetchColumn();
            if ($adminCount === 0) {
                $email = 'admin@gmail.com';
                $name = 'Administrator';
                $passwordPlain = 'pass123';
                $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
                // Check if email exists
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
                $stmt->execute([$email]);
                $row = $stmt->fetch();
                if ($row) {
                    // Update existing user to admin and reset password
                    $upd = $pdo->prepare('UPDATE users SET password_hash=?, is_admin=1 WHERE id=?');
                    $upd->execute([$hash, $row['id']]);
                    ensure_admin_row($pdo, (int)$row['id']);
                } else {
                    $avatarSeed = substr(bin2hex(random_bytes(6)), 0, 12);
                    $ins = $pdo->prepare('INSERT INTO users (name, email, password_hash, points, avatar_seed, is_admin, created_at) VALUES (?, ?, ?, 0, ?, 1, NOW())');
                    $ins->execute([$name, $email, $hash, $avatarSeed]);
                    $newId = (int)$pdo->lastInsertId();
                    ensure_admin_row($pdo, $newId, true);
                }
            }
        } catch (Throwable $e) {
            // Fail silently; admin can still be created manually.
        }
    }

    function ensure_admin_row(PDO $pdo, int $userId, bool $superAdmin = false): void {
        try {
            $stmt = $pdo->prepare('SELECT user_id FROM admins WHERE user_id=?');
            $stmt->execute([$userId]);
            if ($stmt->fetch()) {
                $up = $pdo->prepare('UPDATE admins SET updated_at=NOW() WHERE user_id=?');
                $up->execute([$userId]);
            } else {
                $ins = $pdo->prepare('INSERT INTO admins (user_id, super_admin, status, notes, created_at) VALUES (?, ?, "active", NULL, NOW())');
                $ins->execute([$userId, $superAdmin ? 1 : 0]);
            }
        } catch (Throwable $e) {
            // Silently ignore if table missing or other issues.
        }
    }

    function ensure_default_admin_account(PDO $pdo): void {
        // Seed a dedicated admin login if none exists (separate from users table)
        try {
            $cnt = (int)$pdo->query('SELECT COUNT(*) FROM admin_accounts')->fetchColumn();
            if ($cnt === 0) {
                $username = 'admin';
                $email = 'admin@quizwhiz.local';
                $hash = password_hash('pass123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO admin_accounts (username,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
                $stmt->execute([$username,$email,$hash,'admin']);
            }
        } catch (Throwable $e) {
            // Ignore if table missing; installer or SQL upgrade will create it.
        }
    }

// Simple router helper
function url(string $path = ''): string {
    $base = rtrim(APP_BASE_URL, '/');
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

?>
