<?php
/**
 * api/mark-read.php - marks notifications as read (used by "Mark all as read" in the bell).
 * POST fields: csrf_token, action = "all"  (or action = "one" with id)
 */
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';


header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_fail(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null) {
    json_fail(401, 'Not signed in.');
}
if (!is_post()) {
    json_fail(405, 'POST required.');
}
if (!csrf_verify()) {
    json_fail(403, 'Session expired. Please refresh the page.');
}

$userId = (int)$user['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'all') {
    NotificationService::markAllRead($userId);
} elseif ($action === 'one') {
    NotificationService::markRead((int)($_POST['id'] ?? 0), $userId);
} else {
    json_fail(400, 'Unknown action.');
}

echo json_encode(['ok' => true, 'counts' => get_badge_counts($userId)]);