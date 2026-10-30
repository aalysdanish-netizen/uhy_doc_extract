<?php
/**
 * services/CommunityService.php
 */
class CommunityService
{
    public static function channels(): array
    {
        return db()->query('SELECT * FROM community_channels WHERE is_active = 1 ORDER BY channel_id')->fetchAll();
    }

    public static function findChannel(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM community_channels WHERE channel_id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Top-level posts in a channel, newest first, with reply count and last-read state for the viewer. */
    public static function posts(int $channelId, int $viewerId, int $limit, int $offset): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.name AS author_name, u.username AS author_username, u.avatar_path AS author_avatar,
                    (SELECT COUNT(*) FROM community_posts r WHERE r.parent_post_id = p.post_id) AS reply_count,
                    (SELECT MAX(r.created_at) FROM community_posts r WHERE r.parent_post_id = p.post_id) AS last_reply_at
             FROM community_posts p
             JOIN users u ON u.user_id = p.user_id
             WHERE p.channel_id = ? AND p.parent_post_id IS NULL
             ORDER BY p.post_id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset
        );
        $stmt->execute([$channelId]);
        return $stmt->fetchAll();
    }

    public static function countPosts(int $channelId): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM community_posts WHERE channel_id = ? AND parent_post_id IS NULL');
        $stmt->execute([$channelId]);
        return (int)$stmt->fetchColumn();
    }

    public static function findPost(int $postId): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.name AS author_name, u.username AS author_username FROM community_posts p JOIN users u ON u.user_id = p.user_id WHERE p.post_id = ? LIMIT 1'
        );
        $stmt->execute([$postId]);
        return $stmt->fetch() ?: null;
    }

    public static function replies(int $postId): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.name AS author_name, u.username AS author_username, u.avatar_path AS author_avatar FROM community_posts p JOIN users u ON u.user_id = p.user_id
             WHERE p.parent_post_id = ? ORDER BY p.post_id ASC'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public static function attachments(int $postId): array
    {
        $stmt = db()->prepare('SELECT * FROM community_attachments WHERE post_id = ? ORDER BY attachment_id');
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    /** Creates a post or reply. $files: array of ['path','original','size'] already stored via UploadService. */
    public static function create(int $channelId, int $userId, string $body, ?int $parentPostId, array $files): int
    {
        db()->prepare('INSERT INTO community_posts (channel_id, user_id, parent_post_id, body) VALUES (?, ?, ?, ?)')
            ->execute([$channelId, $userId, $parentPostId, $body]);
        $postId = (int)db()->lastInsertId();

        $insertFile = db()->prepare('INSERT INTO community_attachments (post_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)');
        foreach ($files as $f) {
            $insertFile->execute([$postId, $f['original'], $f['path'], pathinfo($f['original'], PATHINFO_EXTENSION)]);
        }

        if ($parentPostId) {
            self::notifyOnReply($parentPostId, $channelId, $userId);
        }

        return $postId;
    }

    /** Notify the original poster (and anyone else who replied) that there's a new reply - not the replier themself. */
    private static function notifyOnReply(int $parentPostId, int $channelId, int $replierId): void
    {
        $parent = self::findPost($parentPostId);
        if (!$parent) {
            return;
        }
        $recipients = [(int)$parent['user_id']];

        $stmt = db()->prepare('SELECT DISTINCT user_id FROM community_posts WHERE parent_post_id = ?');
        $stmt->execute([$parentPostId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $uid) {
            $recipients[] = (int)$uid;
        }

        $channel = self::findChannel($channelId);
        foreach (array_unique($recipients) as $uid) {
            if ($uid === $replierId) {
                continue;
            }
            NotificationService::create($uid, 'message', 'New reply in ' . ($channel['name'] ?? 'Community'),
                mb_substr($parent['body'], 0, 80), 'channel', $channelId);
        }
    }

    /** Marks everything currently in the channel as read for this user. */
    public static function markChannelRead(int $channelId, int $userId): void
    {
        $latest = db()->prepare('SELECT MAX(post_id) FROM community_posts WHERE channel_id = ?');
        $latest->execute([$channelId]);
        $maxId = (int)$latest->fetchColumn();

        db()->prepare(
            'INSERT INTO community_reads (user_id, channel_id, last_read_post_id) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE last_read_post_id = GREATEST(last_read_post_id, VALUES(last_read_post_id))'
        )->execute([$userId, $channelId, $maxId]);
    }

    /** Unread count per channel_id, for badges next to each channel name. */
    public static function unreadPerChannel(int $userId): array
    {
        $stmt = db()->prepare(
            'SELECT p.channel_id, COUNT(*) AS n
             FROM community_posts p
             JOIN community_channels c ON c.channel_id = p.channel_id AND c.is_active = 1
             LEFT JOIN community_reads r ON r.channel_id = p.channel_id AND r.user_id = ?
             JOIN users me ON me.user_id = ?
             WHERE p.user_id <> ? AND p.post_id > COALESCE(r.last_read_post_id, 0) AND p.created_at >= me.created_at
             GROUP BY p.channel_id'
        );
        $stmt->execute([$userId, $userId, $userId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['channel_id']] = (int)$row['n'];
        }
        return $out;
    }
}