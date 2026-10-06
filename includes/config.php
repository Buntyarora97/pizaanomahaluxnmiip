<?php
// ============================================================
// MAHALUXMI TRADING HOUSE - CONFIG
// Configure MySQL connection values with MTH_DB_* environment variables.
// ============================================================
define('DB_DRIVER', strtolower((string)(getenv('MTH_DB_DRIVER') ?: 'mysql')));
define('DB_HOST', getenv('MTH_DB_HOST') ?: '');
define('DB_NAME', getenv('MTH_DB_NAME') ?: '');
define('DB_USER', getenv('MTH_DB_USER') ?: '');
define('DB_PASS', getenv('MTH_DB_PASS') ?: '');

// Set MTH_SITE_URL on production if the site is behind a proxy. Otherwise
// detect the current host so the same files work on cPanel and Replit.
$detectedProtocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$detectedUrl = !empty($_SERVER['HTTP_HOST']) ? $detectedProtocol . '://' . $_SERVER['HTTP_HOST'] : '';
define('SITE_URL', rtrim((string)(getenv('MTH_SITE_URL') ?: $detectedUrl), '/'));
define('SITE_NAME', 'Mahaluxmi Trading House Pvt. Ltd.');

define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');

// Razorpay (server side only - secret never in JS)
define('RAZORPAY_KEY_ID', '');      // or read from site_settings
define('RAZORPAY_KEY_SECRET', '');

define('CURRENCY', 'INR');
// Project-specific name avoids a collision with PHP's built-in intl constant.
define('MTH_CURRENCY_SYMBOL', '₹');

// Environment
define('APP_ENV', getenv('MTH_APP_ENV') ?: 'production'); // production | development
if (APP_ENV === 'development') { error_reporting(E_ALL); ini_set('display_errors', '1'); }
else { error_reporting(0); ini_set('display_errors', '0'); }

date_default_timezone_set('Asia/Kolkata');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => isset($_SERVER['HTTPS'])]);
if (session_status() === PHP_SESSION_NONE) session_start();
