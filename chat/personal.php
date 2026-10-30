<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/PersonalChatService.php';

$user   = require_permission('chat.use');
$userId = (int)$user['user_id'];

$contacts = PersonalChatService::contacts($userId);
$otherId  = (int)($_GET['with'] ?? 0);
$other = $otherId ? PersonalChatService::otherPerson($otherId) : null;
if ($otherId && !$other) {
    $otherId = 0;
}
if ($otherId) {
    PersonalChatService::markRead($userId, $otherId);
}
$messages = $otherId ? PersonalChatService::conversation($userId, $otherId) : [];

$pageTitle    = 'Personal Chat';
$activeNav    = 'chat';
$extraScripts = ['assets/js/chat-common.js', 'assets/js/personal-chat.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div><h1>Personal Chat</h1><p class="df-page-sub">Message a colleague directly and share files.</p></div>
</div>

<div class="df-chat-layout" data-personal-chat data-current-user-id="<?= $userId ?>" data-open-with="<?= $otherId ?>">
    <!-- Contacts -->
    <aside class="df-chat-side">
        <div class="df-search df-chat-search">
            <i class="bi bi-search"></i>
            <input type="search" id="staffSearch" placeholder="Search staff to message…" autocomplete="off">
        </div>
        <div id="staffResults" class="df-chat-search-results" hidden></div>

        <div id="contactList">
            <?php foreach ($contacts as $c):
                $cid = (int)$c['user_id'];
                if ($c['last_at']) {
                    $preview = $c['last_message'] !== null && $c['last_message'] !== ''
                        ? mb_strimwidth((string)$c['last_message'], 0, 40, '…')
                        : 'Sent a file';
                } else {
                    $preview = $c['department'] ?: 'No conversation yet';
                }
            ?>
                <a class="df-chat-channel<?= $cid === $otherId ? ' is-active' : '' ?>" href="<?= e(url('chat/personal.php?with=' . $cid)) ?>">
                    <?= render_avatar($c, 'sm') ?>
                    <span class="df-chat-channel-name">
                        <strong><?= e($c['name']) ?> <span class="df-username">@<?= e($c['username']) ?></span></strong>
                        <small><?= e($preview) ?></small>
                    </span>
                    <?php if ((int)$c['unread'] > 0): ?><span class="df-nav-badge"><?= e(badge_text((int)$c['unread'])) ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <?php if (!$contacts): ?>
                <p class="df-muted" style="padding:16px">No other active staff yet.</p>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Conversation -->
    <section class="df-chat-main<?= $otherId ? '' : ' df-chat-main-empty' ?>">
        <?php if ($otherId): ?>
            <div class="df-chat-header">
                <button type="button" class="df-icon-btn df-chat-back" data-chat-back aria-label="Back"><i class="bi bi-arrow-left"></i></button>
                <?= render_avatar($other, 'sm') ?>
                <div><strong><?= e($other['name']) ?></strong><small class="df-username">@<?= e($other['username']) ?></small></div>
            </div>

            <div class="df-messages" id="messageList">
                <?php foreach ($messages as $m): $mine = (int)$m['sender_id'] === $userId; $atts = PersonalChatService::attachments((int)$m['message_id']); ?>
                    <div class="df-bubble-row <?= $mine ? 'is-mine' : 'is-theirs' ?>">
                        <div class="df-bubble">
                            <?php if ($m['message']): ?><p><?= nl2br(e($m['message'])) ?></p><?php endif; ?>
                            <?php foreach ($atts as $a): [$ic, $icon] = doc_icon($a['file_type']); ?>
                                <a class="df-doc-chip" href="<?= e(url('files/chat-download.php?type=personal&id=' . (int)$a['attachment_id'])) ?>">
                                    <span class="df-doc-icon df-doc-icon-sm <?= e($ic) ?>"><i class="bi <?= e($icon) ?>"></i></span><?= e($a['file_name']) ?>
                                </a>
                            <?php endforeach; ?>
                            <span class="df-bubble-time"><?= e(date('g:i A', strtotime($m['created_at']))) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form id="messageForm" class="df-composer" data-receiver-id="<?= $otherId ?>">
                <?= csrf_field() ?>
                <textarea class="df-input" name="body" rows="1" placeholder="Type a message…" maxlength="4000"></textarea>
                <div class="df-composer-row">
                    <label class="df-attach-btn"><i class="bi bi-paperclip"></i><input type="file" name="files[]" multiple accept="image/*,.pdf,.docx,.xlsx" hidden></label>
                    <span class="df-attach-list"></span>
                    <button type="submit" class="df-btn df-btn-primary df-btn-sm"><i class="bi bi-send"></i> Send</button>
                </div>
            </form>
        <?php else: ?>
            <div class="df-empty"><div class="df-empty-icon"><i class="bi bi-chat-dots"></i></div><h3>Select a conversation</h3><p>Pick a colleague from the list, or search for someone new.</p></div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>