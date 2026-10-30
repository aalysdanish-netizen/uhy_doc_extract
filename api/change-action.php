<?php
/**
 * api/change-action.php - POST {document_id, action, change_id?, new_value?}
 * action: accept | reject | edit | accept_all
 */
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/PreparationService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function act_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('document.prepare', $user)) {
    act_fail(401, 'Please sign in again.');
}
if (!is_post() || !csrf_verify()) {
    act_fail(403, 'Your session expired. Please refresh the page.');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$action     = $_POST['action'] ?? '';
$userId     = (int)$user['user_id'];

if ($action === 'accept_all') {
    $n = PreparationService::acceptAllPending($documentId, $userId);
    echo json_encode(['ok' => true, 'accepted' => $n, 'pending' => PreparationService::pendingCount($documentId)]);
    exit;
}

$changeId = (int)($_POST['change_id'] ?? 0);
$newValue = $_POST['new_value'] ?? null;

if (!in_array($action, ['accept', 'reject', 'edit'], true)) {
    act_fail(400, 'Unknown action.');
}
if ($action === 'edit' && trim((string)$newValue) === '') {
    act_fail(422, 'Please enter a value.');
}

$ok = PreparationService::resolveChange($changeId, $documentId, $action, $newValue, $userId);
if (!$ok) {
    act_fail(404, 'That change could not be found.');
}

echo json_encode(['ok' => true, 'pending' => PreparationService::pendingCount($documentId)]);