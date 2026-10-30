<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';

$user = require_login();
if (!is_post() || !csrf_verify()) {
    flash_set('danger', 'Your session expired. Please try again.');
    redirect('documents/library.php');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$note       = trim((string)($_POST['note'] ?? ''));
$doc = DocumentService::find($documentId);
if (!$doc || !DocumentService::canReview($doc, $user)) {
    flash_set('danger', 'You do not have permission to review this document.');
    redirect('documents/view.php?id=' . $documentId);
}

$result = DocumentService::returnForChanges($documentId, $note, (int)$user['user_id']);
flash_set($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Document returned for changes.' : $result['error']);
redirect('documents/view.php?id=' . $documentId);