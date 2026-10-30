<?php
/**
 * admin/users/actions.php
 * Handles Activate / Deactivate / Reset Password (POST only), then goes back.
 */
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/UserService.php';

$admin = require_permission('admin.users');

if (!is_post()) {
    redirect('admin/users/index.php');
}
if (!csrf_verify()) {
    flash_set('danger', 'Your session expired. Please try again.');
    redirect('admin/users/index.php');
}

$id     = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';
$dest   = (($_POST['return'] ?? '') === 'view') ? 'admin/users/view.php?id=' . $id : 'admin/users/index.php';

switch ($action) {
    case 'deactivate':
        $result = UserService::setStatus($id, 'inactive', (int)$admin['user_id']);
        break;
    case 'activate':
        $result = UserService::setStatus($id, 'active', (int)$admin['user_id']);
        break;
    case 'reset_password':
        $result = UserService::resetPassword($id, (int)$admin['user_id']);
        break;
    default:
        $result = ['ok' => false, 'message' => 'Unknown action.'];
}

flash_set($result['ok'] ? 'success' : 'danger', $result['message']);
redirect($dest);