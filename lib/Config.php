<?php

class Config {
    // Toggle password hashing. Per user request, default to false.
    public static function usePasswordHashing(): bool {
        $env = getenv('USE_PASSWORD_HASHING');
        if ($env !== false) {
            $val = strtolower(trim($env));
            return in_array($val, ['1','true','yes','on'], true);
        }
        return false; // default disabled
    }

    // Toggle CSRF verification globally (default disabled for college project)
    public static function enableCsrf(): bool {
        $env = getenv('ENABLE_CSRF');
        if ($env !== false) {
            $val = strtolower(trim($env));
            return !in_array($val, ['0','false','no','off'], true);
        }
        return false; // default disabled
    }
}

?>
