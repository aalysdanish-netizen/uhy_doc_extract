<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/PreparationService.php';

$user = require_permission('document.submit');

if (!is_post() || !csrf_verify()) {
    flash_set('danger', 'Your session expired. Please try again.');
    redirect('documents/prepare.php');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$result = PreparationService::submitForReview($documentId, (int)$user['user_id']);

flash_set($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Document submitted for review.' : $result['error']);
redirect('documents/view.php?id=' . $documentId);