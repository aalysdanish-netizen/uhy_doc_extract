<?php
/**
 * includes/badges.php
 * Unread counts for the sidebar and the notification bell.
 * Everything is calculated from the database on every call - nothing is hard-coded.
 *
 * Returns: ['tasks' => 2, 'community' => 4, 'chat' => 3, 'notifications' => 3]
 */

function get_badge_counts(int $userId): array
{
    $count = function (string $sql, array $params): int {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    };

    // Personal chat: messages sent TO me that I have not read
    $chat = $count(
        'SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0',
        [$userId]
    );

    // My tasks: tasks assigned to me that I have not opened yet
    $tasks = $count(
        "SELECT COUNT(*) FROM tasks
         WHERE assigned_to = ? AND seen_at IS NULL AND status <> 'completed'",
        [$userId]
    );

    // Community: posts by OTHER people that are newer than the last post I saw in
    // each channel. Posts written before my account existed are ignored.
    $community = $count(
        'SELECT COUNT(*)
         FROM community_posts p
         JOIN community_channels c ON c.channel_id = p.channel_id AND c.is_active = 1
         LEFT JOIN community_reads r ON r.channel_id = p.channel_id AND r.user_id = ?
         JOIN users me ON me.user_id = ?
         WHERE p.user_id <> ?
           AND p.post_id > COALESCE(r.last_read_post_id, 0)
           AND p.created_at >= me.created_at',
        [$userId, $userId, $userId]
    );

    // Bell: unread notifications
    $notifications = $count(
        'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
        [$userId]
    );

    return [
        'tasks'         => $tasks,
        'community'     => $community,
        'chat'          => $chat,
        'notifications' => $notifications,
    ];
}

/** 0 -> '' (badge hidden), 7 -> '7', 250 -> '99+' */
function badge_text(int $count): string
{
    if ($count <= 0) {
        return '';
    }
    return $count > 99 ? '99+' : (string)$count;
}