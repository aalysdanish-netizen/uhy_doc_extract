<?php
/**
 * api/templates.php?client_id=1&type_id=2
 * Returns earlier documents of that client + type that can be used as templates.
 */
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('document.capture', $user)) {
    http_response_code(401);
    echo json_encode(['ok' => false]);
    exit;
}

$items = DocumentService::templateOptions((int)($_GET['client_id'] ?? 0), (int)($_GET['type_id'] ?? 0));
echo json_encode(['ok' => true, 'items' => $items]);