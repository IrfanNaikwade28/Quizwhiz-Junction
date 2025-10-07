<?php
// Minimal session utilities for a simple college project

class Init {
    public static function startSession(): void {
        // Skip session handling during CLI runs (tests) to avoid header warnings
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            // Use default PHP session settings
            session_start();
        }
    }

    public static function regenerate(): void {
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }

    public static function destroy(): void {
        if (PHP_SAPI === 'cli') {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear session array and destroy session
            $_SESSION = [];
            @session_unset();
            @session_destroy();
        }
    }
}

?>
