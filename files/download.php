<?php
/**
 * files/download.php?v=<version_id>[&inline=1]
 * The ONLY way to open a stored file. Checks login + permission first.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';

$user = require_permission('document.library');

$version = DocumentService::findVersion((int)($_GET['v'] ?? 0));
$abs     = $version ? UploadService::absolutePath($version['file_path']) : null;

if (!$version || !$abs) {
    http_response_code(404);
    exit('File not found.');
}
if ((int)($version['is_deleted'] ?? 0) === 1 && $user['role'] !== 'admin') {
    http_response_code(404);
    exit('File not found.');
}

$ext    = strtolower((string)$version['file_type']);
$mime   = UploadService::mimeFor($ext);
$inline = !empty($_GET['inline']) && in_array($ext, ['jpg', 'png', 'pdf'], true);   // only safe types display in the browser

$filename = $version['original_name'] ?: ($version['document_name'] . '.' . $ext);
$filename = preg_replace('/[\r\n"]+/', '', $filename);
$ascii    = preg_replace('/[^A-Za-z0-9._ -]+/', '_', $filename);

// Log real downloads (not every image preview)
if (!$inline) {
    AuditService::log((int)$user['user_id'], 'file_downloaded', 'document', (int)$version['document_id'],
        'Downloaded ' . $filename . ' (version ' . $version['version_no'] . ')');
}

session_write_close();      // do not block the user's other requests while the file streams

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($abs));
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=600');
readfile($abs);
exit;