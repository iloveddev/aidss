<?php
/**
 * AID Session Manager
 * Handles secure session lifecycle.
 */

class AID_Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', AID_COOKIE_HTTPONLY);
            ini_set('session.cookie_secure', AID_COOKIE_SECURE);
            ini_set('session.cookie_samesite', AID_COOKIE_SAMESITE);
            ini_set('session.gc_maxlifetime', AID_SESSION_LIFETIME);
            
            session_start();
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function destroy() {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
}
