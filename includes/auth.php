<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

// Separate admin session support
function current_admin(): ?array {
    return $_SESSION['admin'] ?? null;
}

function require_login(): void {
    if (!current_user()) {
        header('Location: ' . url('login.php'));
        exit;
    }
}

function is_admin(): bool {
    // Backward compatibility: either user has is_admin flag OR dedicated admin session is present
    if (current_admin()) return true;
    return (current_user()['is_admin'] ?? 0) == 1;
}

function require_admin(): void {
    // If not logged in, send to login first.
    if (!current_user() && !current_admin()) {
        header('Location: ' . url('admin_login.php'));
        exit;
    }
    if (!is_admin()) {
        // If NO admin exists at all, offer bootstrap promotion button
        $countAdmins = db()->query('SELECT COUNT(*) FROM users WHERE is_admin=1')->fetchColumn();
        if ((int)$countAdmins === 0) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bootstrap_admin']) && verify_csrf($_POST['csrf'] ?? '')) {
                db()->prepare('UPDATE users SET is_admin=1 WHERE id=?')->execute([current_user()['id']]);
                // refresh user session data
                $u = db()->prepare('SELECT id,name,email,points,avatar_seed,is_admin FROM users WHERE id=?');
                $u->execute([current_user()['id']]);
                $_SESSION['user'] = $u->fetch();
                header('Location: ' . url('admin/index.php'));
                exit;
            }
            http_response_code(200);
            echo '<!DOCTYPE html><html><head><title>Bootstrap Admin</title><meta charset="utf-8"/><style>body{font-family:Inter,Arial,sans-serif;background:#1E1B2E;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0} .card{background:#2B2542;padding:40px;border-radius:20px;box-shadow:0 4px 24px -2px rgba(124,58,237,.35);max-width:460px} h1{margin:0 0 14px;font-size:30px} p{margin:0 0 18px;line-height:1.5;color:#cfc9e8} form{margin-top:10px} button{background:#7C3AED;color:#fff;border:none;padding:14px 24px;border-radius:16px;font-weight:600;cursor:pointer} button:hover{background:#A78BFA}</style></head><body><div class="card"><h1>Initialize Admin Role</h1><p>No admin accounts exist yet. Promote your current account to become the initial administrator.</p><form method="post"><input type="hidden" name="csrf" value="' . h(csrf_token()) . '"><input type="hidden" name="bootstrap_admin" value="1"><button>Become Admin</button></form><p style="margin-top:20px"><a href="' . url('dashboard.php') . '" style="color:#A78BFA;text-decoration:none">Return to Dashboard</a></p></div></body></html>';
            exit;
        }
        http_response_code(403);
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title><meta charset="utf-8"/><style>body{font-family:Inter,Arial,sans-serif;background:#1E1B2E;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0} .card{background:#2B2542;padding:40px;border-radius:20px;box-shadow:0 4px 24px -2px rgba(124,58,237,.35);max-width:420px} h1{margin:0 0 10px;font-size:28px} p{margin:0 0 18px;line-height:1.4;color:#cfc9e8} a{display:inline-block;background:#7C3AED;color:#fff;text-decoration:none;padding:12px 22px;border-radius:14px;font-weight:600} a:hover{background:#A78BFA}</style></head><body><div class="card"><h1>403 Forbidden</h1><p>You don\'t have permission to access the admin area.<br/>If you believe this is an error, set <code>is_admin = 1</code> for your user in the <code>users</code> table.</p><a href="' . url('dashboard.php') . '">Back to Dashboard</a></div></body></html>';
        exit;
    }
}

function login(string $email, string $password): bool {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        regenerate_csrf_token();
        return true;
    }
    return false;
}

function register_user(string $name, string $email, string $password): array {
    $errors = [];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password too short (min 6).';
    }
    if ($errors) return ['ok' => false, 'errors' => $errors];
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'errors' => ['Email already registered.']];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, points, avatar_seed, created_at) VALUES (?, ?, ?, 0, ?, NOW())');
    $avatarSeed = substr(bin2hex(random_bytes(6)), 0, 12);
    $stmt->execute([$name, $email, $hash, $avatarSeed]);
    $id = db()->lastInsertId();
    $user = db()->query("SELECT id, name, email, points, avatar_seed, is_admin FROM users WHERE id = " . (int)$id)->fetch();
    session_regenerate_id(true);
    $_SESSION['user'] = $user;
    regenerate_csrf_token();
    return ['ok' => true];
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// Admin session helpers
function admin_login_account(string $usernameOrEmail, string $password): bool {
    $stmt = db()->prepare('SELECT * FROM admin_accounts WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        unset($admin['password_hash']);
        $_SESSION['admin'] = $admin;
        regenerate_csrf_token();
        return true;
    }
    return false;
}

function admin_logout(): void {
    unset($_SESSION['admin']);
}

?>
