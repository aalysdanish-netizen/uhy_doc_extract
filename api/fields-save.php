<?php
/**
 * api/fields-save.php - POST {extraction_id, rows: JSON string of {field_id: {label, value}}}
 */
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/PreparationService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('document.prepare', $user)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Please sign in again.']);
    exit;
}
if (!is_post() || !csrf_verify()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Your session expired. Please refresh the page.']);
    exit;
}

$extractionId = (int)($_POST['extraction_id'] ?? 0);
$rows = json_decode((string)($_POST['rows'] ?? '[]'), true);
if (!is_array($rows)) {
    $rows = [];
}

if ($extractionId <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Nothing to save.']);
    exit;
}

PreparationService::saveFields($extractionId, $rows, (int)$user['user_id']);
echo json_encode(['ok' => true]);