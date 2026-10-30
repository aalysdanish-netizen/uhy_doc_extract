<?php
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/PreparationService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function cmp_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('document.prepare', $user)) {
    cmp_fail(401, 'Please sign in again.');
}
if (!is_post() || !csrf_verify()) {
    cmp_fail(403, 'Your session expired. Please refresh the page.');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$doc = DocumentService::find($documentId);
if (!$doc) {
    cmp_fail(404, 'Document not found.');
}

$currentExtraction = PreparationService::extractionFor((int)$doc['current_version_id']);
if (!$currentExtraction) {
    cmp_fail(422, 'Please extract information from the current document first.');
}
if (!$doc['prepared_from_version_id']) {
    cmp_fail(422, 'Please select a previous-year template first.');
}
$previousExtraction = PreparationService::extractionFor((int)$doc['prepared_from_version_id']);
if (!$previousExtraction) {
    cmp_fail(422, 'Please extract information from the previous-year document first.');
}

$current  = PreparationService::labeledValues((int)$currentExtraction['extraction_id']);
$previous = PreparationService::labeledValues((int)$previousExtraction['extraction_id']);

PreparationService::compare($documentId, $current, $previous, (int)$user['user_id']);

$changes = array_map(function ($c) {
    return [
        'change_id'     => (int)$c['change_id'],
        'field_key'     => $c['field_key'],
        'old_value'     => $c['old_value'],
        'new_value'     => $c['new_value'],
        'change_status' => $c['change_status'],
    ];
}, PreparationService::changesFor($documentId));

echo json_encode(['ok' => true, 'changes' => $changes]);