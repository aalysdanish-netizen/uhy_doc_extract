<?php
/**
 * services/PersonalChatService.php
 */
class PersonalChatService
{
    /**
     * Every active staff member, so anyone can be messaged without a search step first.
     * People you've already chatted with float to the top (most recent first);
     * everyone else follows, alphabetically, with no message preview.
     */
    public static function contacts(int $userId): array
    {
        $stmt = db()->prepare(
            'SELECT u.user_id, u.name, u.username, u.department, u.avatar_path,
                    (SELECT m.message FROM messages m WHERE (m.sender_id=u.user_id AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=u.user_id) ORDER BY m.message_id DESC LIMIT 1) AS last_message,
                    (SELECT m.created_at FROM messages m WHERE (m.sender_id=u.user_id AND m.receiver_id=?) OR (m.sender_id=? AND m.receiver_id=u.user_id) ORDER BY m.message_id DESC LIMIT 1) AS last_at,
                    (SELECT COUNT(*) FROM messages m WHERE m.sender_id=u.user_id AND m.receiver_id=? AND m.is_read=0) AS unread
             FROM users u
             WHERE u.status = "active" AND u.user_id <> ?
             ORDER BY (last_at IS NULL) ASC, last_at DESC, u.name ASC'
        );
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    /** All other active staff, for starting a NEW conversation (search box). */
    public static function searchableStaff(int $userId, string $q): array
    {
        $sql = "SELECT user_id, name, username, department, avatar_path FROM users WHERE status = 'active' AND user_id <> ?";
        $p = [$userId];
        if ($q !== '') {
            $sql .= ' AND name LIKE ?';
            $p[] = '%' . addcslashes($q, '%_\\') . '%';
        }
        $sql .= ' ORDER BY name LIMIT 20';
        $stmt = db()->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public static function conversation(int $userId, int $otherId, int $limit = 100): array
    {
        $stmt = db()->prepare(
            'SELECT m.*, s.name AS sender_name, s.username AS sender_username FROM messages m JOIN users s ON s.user_id = m.sender_id
             WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.message_id DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$userId, $otherId, $otherId, $userId]);
        return array_reverse($stmt->fetchAll());
    }

    public static function markRead(int $userId, int $otherId): void
    {
        db()->prepare('UPDATE messages SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND is_read = 0')
            ->execute([$otherId, $userId]);
    }

    /** $files: already-stored via UploadService, each ['path','original','size']. */
    public static function send(int $senderId, int $receiverId, string $body, array $files): int
    {
        db()->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)')
            ->execute([$senderId, $receiverId, $body !== '' ? $body : null]);
        $messageId = (int)db()->lastInsertId();

        $insert = db()->prepare('INSERT INTO message_attachments (message_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)');
        foreach ($files as $f) {
            $insert->execute([$messageId, $f['original'], $f['path'], pathinfo($f['original'], PATHINFO_EXTENSION), $f['size']]);
        }

        NotificationService::create($receiverId, 'message', 'New message',
            'You have a new message', 'conversation', $senderId);

        return $messageId;
    }

    public static function attachments(int $messageId): array
    {
        $stmt = db()->prepare('SELECT * FROM message_attachments WHERE message_id = ?');
        $stmt->execute([$messageId]);
        return $stmt->fetchAll();
    }

    public static function otherPersonName(int $userId): ?string
    {
        $stmt = db()->prepare("SELECT name FROM users WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        $name = $stmt->fetchColumn();
        return $name !== false ? $name : null;
    }

    /** Full row (name + username) for the conversation header. */
    public static function otherPerson(int $userId): ?array
    {
        $stmt = db()->prepare("SELECT user_id, name, username, avatar_path FROM users WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}