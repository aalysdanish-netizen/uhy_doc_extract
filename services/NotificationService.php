<?php
/**
 * services/NotificationService.php
 * Creates and reads notifications. Notifications are NEVER deleted -
 * "reading" only flips is_read to 1, so history is kept.
 *
 * Creating one from anywhere in the system:
 *   NotificationService::create($userId, 'task', 'New task assigned', 'Company Profile - ABC Sdn Bhd', 'task', $taskId);
 *
 * reference_type / reference_id decide where a click goes:
 *   'conversation' + sender's user_id  -> Personal Chat with that person
 *   'task'         + task_id           -> that task
 *   'document'     + document_id       -> that document
 *   'channel'      + channel_id        -> that community channel
 */
class NotificationService
{
    public static function create(
        int $userId,
        string $type,                 // message | task | document | system
        string $title,
        ?string $message = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        $stmt = db()->prepare(
            'INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $type, $title, $message, $referenceType, $referenceId]);
    }

    /** Latest notifications (for the bell panel). */
    public static function recent(int $userId, int $limit = 6): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM notifications WHERE user_id = ?
             ORDER BY created_at DESC, notification_id DESC LIMIT ' . (int)$limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Filter tabs on the All Notifications page -> database type */
    public static function filters(): array
    {
        return [
            'all'       => ['label' => 'All',       'type' => null],
            'messages'  => ['label' => 'Messages',  'type' => 'message'],
            'tasks'     => ['label' => 'Tasks',     'type' => 'task'],
            'documents' => ['label' => 'Documents', 'type' => 'document'],
            'system'    => ['label' => 'System',    'type' => 'system'],
        ];
    }

    public static function listFor(int $userId, string $filter, int $limit, int $offset): array
    {
        $type   = self::filters()[$filter]['type'] ?? null;
        $sql    = 'SELECT * FROM notifications WHERE user_id = ?';
        $params = [$userId];
        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY created_at DESC, notification_id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countFor(int $userId, string $filter): int
    {
        $type   = self::filters()[$filter]['type'] ?? null;
        $sql    = 'SELECT COUNT(*) FROM notifications WHERE user_id = ?';
        $params = [$userId];
        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = $type;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** Find one notification - only if it belongs to this user. */
    public static function find(int $notificationId, int $userId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM notifications WHERE notification_id = ? AND user_id = ?');
        $stmt->execute([$notificationId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public static function markRead(int $notificationId, int $userId): void
    {
        db()->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?')
            ->execute([$notificationId, $userId]);
    }

    public static function markAllRead(int $userId): int
    {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }

    /** Where should clicking this notification go? (path inside the project) */
    public static function path(array $n): string
    {
        $id = (int)($n['reference_id'] ?? 0);

        switch ($n['reference_type'] ?? '') {
            case 'conversation':
                return $id ? 'chat/personal.php?with=' . $id : 'chat/personal.php';
            case 'task':
                return $id ? 'tasks/view.php?id=' . $id : 'tasks/index.php';
            case 'document':
                return $id ? 'documents/view.php?id=' . $id : 'documents/library.php';
            case 'channel':
                return $id ? 'chat/community.php?channel=' . $id : 'chat/community.php';
        }
        return 'notifications/index.php';
    }

    public static function icon(string $type): string
    {
        return [
            'message'  => 'bi-chat-dots',
            'task'     => 'bi-check2-square',
            'document' => 'bi-file-earmark-text',
            'system'   => 'bi-megaphone',
        ][$type] ?? 'bi-bell';
    }
}