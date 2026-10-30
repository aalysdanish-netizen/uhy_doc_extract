<?php
/**
 * includes/bootstrap.php
 * Every page starts with:  require_once __DIR__ . '/../includes/bootstrap.php';
 * It loads settings, starts the session and loads all helper files.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Show errors only while developing
ini_set('display_errors', APP_DEBUG ? '1' : '0');
error_reporting(E_ALL);

// ---- Secure session -------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_name('UHYDF_SESSION');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

// ---- Inactivity timeout --------------------------------------------
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    $_SESSION = [];
    session_regenerate_id(true);
}
$_SESSION['last_activity'] = time();

// ---- Never let the browser cache dynamic pages (prevents stale content after redirects) ----
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// ---- Load helpers ---------------------------------------------------
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/../services/AuditService.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';   // NEW: can('...')
require_once __DIR__ . '/badges.php';        // NEW: unread counts
require_once __DIR__ . '/ui.php';            // NEW: small display helpers + menu definition