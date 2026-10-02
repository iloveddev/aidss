<?php
/**
 * AID Core Configuration
 * Centralized configuration for the platform.
 */

define('AID_VERSION', '1.0.0');
define('AID_ROOT', dirname(__FILE__));

// Default paths
define('AID_PATH_CORE', AID_ROOT . '/core');
define('AID_PATH_API', AID_ROOT . '/api');
define('AID_PATH_ADMIN', AID_ROOT . '/admin');
define('AID_PATH_SDK', AID_ROOT . '/sdk');
define('AID_PATH_INSTALL', AID_ROOT . '/install');
define('AID_PATH_STORAGE', AID_ROOT . '/storage');
define('AID_PATH_LOGS', AID_ROOT . '/logs');
define('AID_PATH_ASSETS', AID_ROOT . '/assets');

// Security Defaults
define('AID_SESSION_LIFETIME', 3600); // 1 hour
define('AID_COOKIE_SECURE', true);
define('AID_COOKIE_HTTPONLY', true);
define('AID_COOKIE_SAMESITE', 'Lax');

// Environment
$env = 'production'; // Default
if (file_exists(AID_ROOT . '/.env')) {
    // In a real shared hosting environment, we might use a simple PHP config file
    // instead of a .env parser to avoid dependencies.
}
