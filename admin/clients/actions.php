<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/ClientService.php';

$admin = require_permission('admin.clients');

if (!is_post()) {
    redirect('admin/clients/index.php');
}
if (!csrf_verify()) {
    flash_set('danger', 'Your session expired. Please try again.');
    redirect('admin/clients/index.php');
}

$id     = (int)($_POST['client_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($action === 'deactivate') {
    $result = ClientService::setStatus($id, 'inactive', (int)$admin['user_id']);
} elseif ($action === 'activate') {
    $result = ClientService::setStatus($id, 'active', (int)$admin['user_id']);
} else {
    $result = ['ok' => false, 'message' => 'Unknown action.'];
}

flash_set($result['ok'] ? 'success' : 'danger', $result['message']);
redirect('admin/clients/index.php');