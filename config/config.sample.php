<?php
// Application Configuration
define('APP_NAME', 'Secure Auth System');
define('APP_URL', 'http://localhost/auth');
define('APP_TIMEZONE', 'UTC');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'auth_session');

// Security Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_SPECIAL_CHARS', true);
define('PASSWORD_REQUIRE_NUMBERS', true);
define('PASSWORD_REQUIRE_UPPERCASE', true);

// 2FA Configuration
define('2FA_ISSUER', 'Secure Auth System');
define('2FA_PERIOD', 30); // Time period for TOTP in seconds

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);
?> 