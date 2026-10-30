<?php
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/CommunityService.php';
require_once __DIR__ . '/../services/UploadService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function comm_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('community.use', $user)) {
    comm_fail(401, 'Please sign in again.');
}
if (!is_post() || !csrf_verify()) {
    comm_fail(403, 'Your session expired. Please refresh the page.');
}

$channelId = (int)($_POST['channel_id'] ?? 0);
$parentId  = (int)($_POST['parent_post_id'] ?? 0) ?: null;
$body      = trim((string)($_POST['body'] ?? ''));

$channel = CommunityService::findChannel($channelId);
if (!$channel) {
    comm_fail(404, 'Channel not found.');
}
if ($body === '' && empty($_FILES['files']['name'][0])) {
    comm_fail(422, 'Write a message or attach a file.');
}
if (mb_strlen($body) > 4000) {
    comm_fail(422, 'That message is too long.');
}

$stored = [];
if (!empty($_FILES['files']['name'][0])) {
    $count = count($_FILES['files']['name']);
    for ($i = 0; $i < min($count, 5); $i++) {
        $file = [
            'name' => $_FILES['files']['name'][$i], 'type' => $_FILES['files']['type'][$i],
            'tmp_name' => $_FILES['files']['tmp_name'][$i], 'error' => $_FILES['files']['error'][$i], 'size' => $_FILES['files']['size'][$i],
        ];
        [$err, $ext] = UploadService::validate($file);
        if ($err) {
            comm_fail(422, $err);
        }
        $stored[] = UploadService::store($file, $ext);
    }
}

$postId = CommunityService::create($channelId, (int)$user['user_id'], $body, $parentId, $stored);
$post = CommunityService::findPost($postId);

echo json_encode([
    'ok'   => true,
    'post' => [
        'post_id' => $postId, 'body' => $post['body'], 'author_name' => $post['author_name'], 'author_username' => $post['author_username'],
        'created_at' => $post['created_at'], 'attachments' => CommunityService::attachments($postId),
    ],
]);