<?php
/**
 * includes/api-guard.php
 * Required at the very TOP of every api/*.php file, BEFORE bootstrap.php.
 */
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
set_time_limit(60);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
        }
        $debug = defined('APP_DEBUG') ? APP_DEBUG : true;
        echo json_encode([
            'ok'    => false,
            'error' => $debug
                ? ('Server error: ' . $error['message'] . ' in ' . basename($error['file']) . ':' . $error['line'])
                : 'A server error occurred. Please try again.',
        ]);
    }
});