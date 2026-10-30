<?php
/**
 * api/set-template.php - POST {document_id, version_id}
 * Sets (or clears with version_id=0) which previous-year version is being compared against,
 * and returns everything the right-hand panel needs to render it.
 */
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';
require_once __DIR__ . '/../services/PreparationService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function tpl_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('document.prepare', $user)) {
    tpl_fail(401, 'Please sign in again.');
}
if (!is_post() || !csrf_verify()) {
    tpl_fail(403, 'Your session expired. Please refresh the page.');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$versionId  = (int)($_POST['version_id'] ?? 0);

$doc = DocumentService::find($documentId);
if (!$doc) {
    tpl_fail(404, 'Document not found.');
}

if ($versionId > 0 && !DocumentService::isValidTemplate($versionId, (int)$doc['client_id'], (int)$doc['type_id'])) {
    tpl_fail(422, 'That document does not belong to the same client and document type.');
}

db()->prepare('UPDATE documents SET prepared_from_version_id = ? WHERE document_id = ?')
    ->execute([$versionId > 0 ? $versionId : null, $documentId]);

$payload = ['ok' => true, 'version_id' => $versionId, 'extraction' => null, 'fields' => [], 'preview' => null];

if ($versionId > 0) {
    $version = DocumentService::findVersion($versionId);
    $ext     = strtolower((string)$version['file_type']);
    $isImage = in_array($ext, ['jpg', 'png'], true);

    $payload['preview'] = [
        'file_type'     => $ext,
        'is_image'      => $isImage,
        'original_name' => $version['original_name'],
        'download_url'  => url('files/download.php?v=' . $versionId),
        'inline_url'    => $isImage ? url('files/download.php?v=' . $versionId . '&inline=1') : null,
    ];

    $extraction = PreparationService::extractionFor($versionId);
    if ($extraction) {
        $payload['extraction'] = (int)$extraction['extraction_id'];
        $payload['raw_text']   = PreparationService::rawText((int)$extraction['extraction_id']);
        foreach (PreparationService::extractionFields((int)$extraction['extraction_id']) as $fid => $row) {
            $payload['fields'][] = [
                'field_id'     => $fid,
                'label'        => $row['label'],
                'value'        => (int)$row['is_confirmed'] === 1 ? $row['confirmed_value'] : $row['extracted_value'],
                'is_confirmed' => (int)$row['is_confirmed'] === 1,
            ];
        }
    }
}

echo json_encode($payload);