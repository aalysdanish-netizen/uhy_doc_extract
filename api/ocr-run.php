<?php
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';
require_once __DIR__ . '/../services/OcrService.php';
require_once __DIR__ . '/../services/FieldExtractor.php';
require_once __DIR__ . '/../services/PreparationService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function api_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('document.prepare', $user)) {
    api_fail(401, 'Please sign in again.');
}
if (!is_post() || !csrf_verify()) {
    api_fail(403, 'Your session expired. Please refresh the page.');
}

$versionId = (int)($_POST['version_id'] ?? 0);

$result = PreparationService::runOcr($versionId, (int)$user['user_id']);
if (!$result['ok']) {
    api_fail(422, $result['error']);
}

$fields = [];
foreach (PreparationService::extractionFields($result['extraction_id']) as $fid => $row) {
    $fields[] = [
        'field_id'     => $fid,
        'label'        => $row['label'],
        'value'        => (int)$row['is_confirmed'] === 1 ? $row['confirmed_value'] : $row['extracted_value'],
        'is_confirmed' => (int)$row['is_confirmed'] === 1,
    ];
}

echo json_encode([
    'ok'            => true,
    'extraction_id' => $result['extraction_id'],
    'raw_text'      => PreparationService::rawText($result['extraction_id']),
    'fields'        => $fields,
]);