<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/CommunityService.php';
require_once __DIR__ . '/../services/UploadService.php';

$user   = require_permission('community.use');
$userId = (int)$user['user_id'];

$channels = CommunityService::channels();
if (!$channels) {
    $pageTitle = 'Community';
    $activeNav = 'community';
    require __DIR__ . '/../includes/header.php';
    ?>
    <div class="df-card"><div class="df-empty"><div class="df-empty-icon"><i class="bi bi-people"></i></div><h3>No channels yet</h3></div></div>
    <?php require __DIR__ . '/../includes/footer.php'; exit;
}

$channelId = (int)($_GET['channel'] ?? $channels[0]['channel_id']);
$channel = CommunityService::findChannel($channelId) ?? $channels[0];
$channelId = (int)$channel['channel_id'];

CommunityService::markChannelRead($channelId, $userId);   // opening a channel marks it read

$perPage = 20;
$total   = CommunityService::countPosts($channelId);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$posts   = CommunityService::posts($channelId, $userId, $perPage, ($page - 1) * $perPage);

$unread = CommunityService::unreadPerChannel($userId);

$pageTitle    = 'Community · ' . $channel['name'];
$activeNav    = 'community';
$extraScripts = ['assets/js/chat-common.js', 'assets/js/community.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div><h1>Community</h1><p class="df-page-sub">Discuss with the team. Posts are visible to everyone.</p></div>
</div>

<div class="df-chat-layout">
    <!-- Channel list -->
    <aside class="df-chat-side">
        <?php foreach ($channels as $c): $cid = (int)$c['channel_id']; $n = $unread[$cid] ?? 0; ?>
            <a class="df-chat-channel<?= $cid === $channelId ? ' is-active' : '' ?>" href="<?= e(url('chat/community.php?channel=' . $cid)) ?>">
                <span class="df-chat-channel-icon"><i class="bi bi-hash"></i></span>
                <span class="df-chat-channel-name"><?= e($c['name']) ?></span>
                <?php if ($n > 0 && $cid !== $channelId): ?><span class="df-nav-badge"><?= e(badge_text($n)) ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </aside>

    <!-- Posts -->
    <section class="df-chat-main">
        <div class="df-chat-header">
            <button type="button" class="df-icon-btn df-chat-back" data-chat-back aria-label="Back to channels"><i class="bi bi-arrow-left"></i></button>
            <div><strong># <?= e($channel['name']) ?></strong><small><?= e($channel['description']) ?></small></div>
        </div>

        <!-- New post composer -->
        <form id="postForm" class="df-composer" data-channel-id="<?= $channelId ?>">
            <?= csrf_field() ?>
            <textarea class="df-input" name="body" rows="2" placeholder="Share something with the team…" maxlength="4000"></textarea>
            <div class="df-composer-row">
                <label class="df-attach-btn"><i class="bi bi-paperclip"></i><input type="file" name="files[]" multiple accept="image/*,.pdf,.docx,.xlsx" hidden></label>
                <span class="df-attach-list"></span>
                <button type="submit" class="df-btn df-btn-primary df-btn-sm"><i class="bi bi-send"></i> Post</button>
            </div>
        </form>

        <div class="df-thread-list" id="threadList">
            <?php if ($posts): foreach ($posts as $p): $atts = CommunityService::attachments((int)$p['post_id']); ?>
                <article class="df-thread-post">
                    <div class="df-thread-head">
                        <?= render_avatar(['name' => $p['author_name'], 'user_id' => $p['user_id'], 'avatar_path' => $p['author_avatar'] ?? null], 'sm') ?>
                        <div><strong><?= e($p['author_name']) ?> <span class="df-username">@<?= e($p['author_username']) ?></span></strong><small><?= e(time_ago($p['created_at'])) ?></small></div>
                    </div>
                    <?php if ($p['body']): ?><p class="df-thread-body"><?= nl2br(e($p['body'])) ?></p><?php endif; ?>
                    <?php if ($atts): ?>
                        <div class="df-thread-attachments">
                            <?php foreach ($atts as $a): [$ic, $icon] = doc_icon($a['file_type']); ?>
                                <a class="df-doc-chip" href="<?= e(url('files/chat-download.php?type=community&id=' . (int)$a['attachment_id'])) ?>">
                                    <span class="df-doc-icon df-doc-icon-sm <?= e($ic) ?>"><i class="bi <?= e($icon) ?>"></i></span><?= e($a['file_name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <button type="button" class="df-link-btn df-reply-toggle" data-post-id="<?= (int)$p['post_id'] ?>">
                        <?= (int)$p['reply_count'] > 0 ? $p['reply_count'] . ' repl' . ($p['reply_count'] == 1 ? 'y' : 'ies') : 'Reply' ?>
                    </button>

                    <div class="df-thread-replies" data-replies="<?= (int)$p['post_id'] ?>" hidden>
                        <?php foreach (CommunityService::replies((int)$p['post_id']) as $r): $ratts = CommunityService::attachments((int)$r['post_id']); ?>
                             <div class="df-thread-reply">
                                <?= render_avatar(['name' => $r['author_name'], 'user_id' => $r['user_id'], 'avatar_path' => $r['author_avatar'] ?? null], 'sm') ?>
                                <div>
                                    <strong><?= e($r['author_name']) ?> <span class="df-username">@<?= e($r['author_username']) ?></span></strong> <small class="df-muted"><?= e(time_ago($r['created_at'])) ?></small>
                                    <?php if ($r['body']): ?><p class="df-thread-body"><?= nl2br(e($r['body'])) ?></p><?php endif; ?>
                                    <?php if ($ratts): foreach ($ratts as $a): [$ic, $icon] = doc_icon($a['file_type']); ?>
                                        <a class="df-doc-chip" href="<?= e(url('files/chat-download.php?type=community&id=' . (int)$a['attachment_id'])) ?>">
                                            <span class="df-doc-icon df-doc-icon-sm <?= e($ic) ?>"><i class="bi <?= e($icon) ?>"></i></span><?= e($a['file_name']) ?>
                                        </a>
                                    <?php endforeach; endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <form class="df-reply-form" data-parent-id="<?= (int)$p['post_id'] ?>" data-channel-id="<?= $channelId ?>">
                            <?= csrf_field() ?>
                            <input class="df-input" type="text" name="body" placeholder="Write a reply…" maxlength="4000">
                            <label class="df-attach-btn"><i class="bi bi-paperclip"></i><input type="file" name="files[]" multiple accept="image/*,.pdf,.docx,.xlsx" hidden></label>
                            <button type="submit" class="df-btn df-btn-primary df-btn-sm">Send</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; else: ?>
                <div class="df-empty"><div class="df-empty-icon"><i class="bi bi-chat-square-text"></i></div><h3>No posts yet</h3><p>Be the first to share something in this channel.</p></div>
            <?php endif; ?>
        </div>

        <?php if ($pages > 1): ?>
            <div class="df-pager">
                <?php if ($page < $pages): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('chat/community.php?channel=' . $channelId . '&page=' . ($page + 1))) ?>">Older posts</a><?php endif; ?>
                <?php if ($page > 1): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('chat/community.php?channel=' . $channelId . '&page=' . ($page - 1))) ?>">Newer posts</a><?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>