<?php
/**
 * api/field-remove.php - POST {extraction_id, field_id}
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
$fieldId      = (int)($_POST['field_id'] ?? 0);

$ok = PreparationService::removeField($fieldId, $extractionId);
echo json_encode(['ok' => $ok]);