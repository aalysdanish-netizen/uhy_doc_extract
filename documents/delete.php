<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';

$user = require_login();

if (!is_post() || !csrf_verify()) {
    flash_set('danger', 'Your session expired. Please try again.');
    redirect('documents/library.php');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$doc = DocumentService::find($documentId);

if (!$doc) {
    flash_set('warning', 'That document could not be found.');
    redirect('documents/library.php');
}
if (!DocumentService::canDelete($doc, $user)) {
    flash_set('danger', 'You do not have permission to delete this document.');
    redirect('documents/view.php?id=' . $documentId);
}

$result = DocumentService::softDelete($documentId, (int)$user['user_id']);
flash_set($result['ok'] ? 'success' : 'danger', $result['ok'] ? ($result['name'] . ' was deleted.') : $result['error']);
redirect('documents/library.php');