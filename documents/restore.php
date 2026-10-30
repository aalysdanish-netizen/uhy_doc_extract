<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';

$user = require_role('admin');   // restoring is Admin-only

if (!is_post() || !csrf_verify()) {
    flash_set('danger', 'Your session expired. Please try again.');
    redirect('documents/library.php?deleted=1');
}

$documentId = (int)($_POST['document_id'] ?? 0);
$result = DocumentService::restore($documentId, (int)$user['user_id']);

flash_set($result['ok'] ? 'success' : 'danger', $result['ok'] ? ($result['name'] . ' was restored.') : $result['error']);
redirect('documents/library.php?deleted=1');