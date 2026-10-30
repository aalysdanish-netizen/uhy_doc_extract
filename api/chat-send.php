<?php
require_once __DIR__ . '/../includes/api-guard.php';
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/PersonalChatService.php';
require_once __DIR__ . '/../services/UploadService.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function chat_fail(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
}

$user = current_user();
if ($user === null || (int)$user['must_change_password'] === 1 || !can('chat.use', $user)) {
    chat_fail(401, 'Please sign in again.');
}
if (!is_post() || !csrf_verify()) {
    chat_fail(403, 'Your session expired. Please refresh the page.');
}

$receiverId = (int)($_POST['receiver_id'] ?? 0);
$body       = trim((string)($_POST['body'] ?? ''));

if ($receiverId === (int)$user['user_id']) {
    chat_fail(422, 'You cannot message yourself.');
}
$receiverName = PersonalChatService::otherPersonName($receiverId);
if (!$receiverName) {
    chat_fail(404, 'That person could not be found.');
}
if ($body === '' && empty($_FILES['files']['name'][0])) {
    chat_fail(422, 'Write a message or attach a file.');
}
if (mb_strlen($body) > 4000) {
    chat_fail(422, 'That message is too long.');
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
            chat_fail(422, $err);
        }
        $stored[] = UploadService::store($file, $ext);
    }
}

$messageId = PersonalChatService::send((int)$user['user_id'], $receiverId, $body, $stored);

echo json_encode([
    'ok' => true,
    'message' => [
        'message_id' => $messageId, 'message' => $body, 'created_at' => date('Y-m-d H:i:s'),
        'attachments' => PersonalChatService::attachments($messageId),
    ],
]);