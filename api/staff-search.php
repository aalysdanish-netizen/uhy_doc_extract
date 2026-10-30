<?php
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/PersonalChatService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('chat.use', $user)) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
$staff = PersonalChatService::searchableStaff((int)$user['user_id'], $q);

$items = array_map(fn($s) => ['user_id' => (int)$s['user_id'], 'name' => $s['name'], 'username' => $s['username'], 'department' => $s['department'], 'initials' => user_initials($s['name'])], $staff);
echo json_encode(['ok' => true, 'items' => $items]);