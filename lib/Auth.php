<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Init.php';
require_once __DIR__ . '/Config.php';

class Auth {
    // Authenticate user (role user). Returns array with user fields on success, null on failure.
    public static function loginUser(string $email, string $password): ?array {
        Init::startSession();
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT name, college, email, password FROM user WHERE email = ?", [$email]);
        if (!$user) return null;

        $stored = $user['password'];
        if (!Config::usePasswordHashing()) {
            // Plaintext mode by request
            if ($stored !== $password) return null;
        } else {
            // Hashed mode with legacy auto-upgrade
            $isLegacy = !preg_match('/^\$2y\$|^\$argon2/i', (string)$stored);
            if ($isLegacy) {
                if (!hash_equals(hash('sha256', $stored), hash('sha256', $password))) {
                    return null;
                }
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $db->run("UPDATE user SET password = ? WHERE email = ?", [$newHash, $email]);
                    $stored = $newHash;
                } catch (Throwable $e) { /* ignore */ }
            } else {
                if (!password_verify($password, $stored)) return null;
                if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                    $rehash = password_hash($password, PASSWORD_DEFAULT);
                    try { $db->run("UPDATE user SET password = ? WHERE email = ?", [$rehash, $email]); } catch (Throwable $e) {}
                }
            }
        }

        // Success
        $_SESSION['role'] = 'user';
        $_SESSION['email'] = $user['email'];
        $_SESSION['name']  = $user['name'];
        $_SESSION['college'] = $user['college'];
        Init::regenerate();
        return $user;
    }

    public static function registerUser(string $name, string $email, string $password, string $college): ?array {
        $db = Database::getInstance();
        // Validate basic email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
        $exists = $db->fetchOne("SELECT email FROM user WHERE email = ?", [$email]);
        if ($exists) return null;
    $toStore = Config::usePasswordHashing() ? password_hash($password, PASSWORD_DEFAULT) : $password;
    $db->run("INSERT INTO user(name, college, email, password) VALUES(?,?,?,?)", [$name, $college, $email, $toStore]);
        return ['name'=>$name,'college'=>$college,'email'=>$email];
    }

    // Authenticate admin with legacy upgrade similar to user
    public static function loginAdmin(string $email, string $password): bool {
        Init::startSession();
        $db = Database::getInstance();
        $admin = $db->fetchOne("SELECT email, password FROM admin WHERE email = ?", [$email]);
        if (!$admin) return false;
        $stored = $admin['password'];
        if (!Config::usePasswordHashing()) {
            if ($stored !== $password) return false;
        } else {
            $isLegacy = !preg_match('/^\$2y\$|^\$argon2/i', (string)$stored);
            if ($isLegacy) {
                if (!hash_equals(hash('sha256', $stored), hash('sha256', $password))) {
                    return false;
                }
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                try { $db->run("UPDATE admin SET password = ? WHERE email = ?", [$newHash, $email]); } catch (Throwable $e) {}
            } else {
                if (!password_verify($password, $stored)) return false;
                if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                    $rehash = password_hash($password, PASSWORD_DEFAULT);
                    try { $db->run("UPDATE admin SET password = ? WHERE email = ?", [$rehash, $email]); } catch (Throwable $e) {}
                }
            }
        }

        $_SESSION['role'] = 'admin';
        $_SESSION['email'] = $email;
        $_SESSION['name']  = 'Admin';
        $_SESSION['key']   = 'suryapinky'; // Keep compatibility with existing checks
        Init::regenerate();
        return true;
    }
}

?>
