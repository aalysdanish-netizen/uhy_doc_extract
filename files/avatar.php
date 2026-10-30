<?php
/**
 * files/avatar.php?u=<user_id>
 * Serves a user's profile photo. Publicly viewable to any logged-in user
 * (photos are not sensitive like documents), with caching for speed.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/UploadService.php';

require_login();   // must be signed in, but any role may view any avatar

$userId = (int)($_GET['u'] ?? 0);
$stmt = db()->prepare("SELECT avatar_path FROM users WHERE user_id = ? AND status = 'active'");
$stmt->execute([$userId]);
$path = $stmt->fetchColumn();

$abs = $path ? UploadService::absolutePath($path) : null;
if (!$abs) {
    http_response_code(404);
    exit;
}

session_write_close();
header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($abs));
header('Cache-Control: private, max-age=3600');
readfile($abs);
exit;