<?php
/**
 * api/badges.php - returns the current unread counts as JSON.
 * Not polled yet. Later, one line in assets/js/app.js turns polling on.
 */
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';


header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

echo json_encode(['ok' => true, 'counts' => get_badge_counts((int)$user['user_id']), ]);