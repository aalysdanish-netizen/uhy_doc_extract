<?php
/**
 * notifications/open.php?id=123
 * Marks the notification as read, then sends the user to the related page.
 * (Pages for tasks/chat/documents arrive in later phases; the notification is still marked read.)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
$user   = require_login();
$userId = (int)$user['user_id'];

$id = (int)($_GET['id'] ?? 0);
$n  = NotificationService::find($id, $userId);      // only finds YOUR notifications

if ($n === null) {
    flash_set('warning', 'That notification could not be found.');
    redirect('notifications/index.php');
}

NotificationService::markRead($id, $userId);
redirect(NotificationService::path($n));