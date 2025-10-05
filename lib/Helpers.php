<?php
require_once __DIR__ . '/Init.php';
require_once __DIR__ . '/Config.php';

class Helpers {
    public static function csrfToken(): string {
        Init::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool {
        Init::startSession();
        if (!Config::enableCsrf()) {
            return true;
        }
        return $token && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function e(?string $str): string {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

?>
