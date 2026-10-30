<?php
/**
 * files/chat-download.php?type=personal|community&id=<attachment_id>
 * Personal: only the sender or receiver may download. Community: any logged-in staff.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/UploadService.php';

$user = require_login();
$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

if ($type === 'personal') {
    $stmt = db()->prepare('SELECT a.*, m.sender_id, m.receiver_id FROM message_attachments a JOIN messages m ON m.message_id = a.message_id WHERE a.attachment_id = ?');
    $stmt->execute([$id]);
    $att = $stmt->fetch();
    if (!$att || ((int)$att['sender_id'] !== (int)$user['user_id'] && (int)$att['receiver_id'] !== (int)$user['user_id'])) {
        http_response_code(404);
        exit('File not found.');
    }
} elseif ($type === 'community') {
    $stmt = db()->prepare('SELECT * FROM community_attachments WHERE attachment_id = ?');
    $stmt->execute([$id]);
    $att = $stmt->fetch();
    if (!$att) {
        http_response_code(404);
        exit('File not found.');
    }
} else {
    http_response_code(400);
    exit('Invalid request.');
}

$abs = UploadService::absolutePath($att['file_path']);
if (!$abs) {
    http_response_code(404);
    exit('File not found.');
}

$ext = strtolower(pathinfo($att['file_name'], PATHINFO_EXTENSION));
$mime = UploadService::mimeFor($ext);
$ascii = preg_replace('/[^A-Za-z0-9._ -]+/', '_', $att['file_name']);

session_write_close();
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($abs));
header('Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($att['file_name']));
header('X-Content-Type-Options: nosniff');
readfile($abs);
exit;