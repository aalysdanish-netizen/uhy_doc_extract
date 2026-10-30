<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$user   = require_login();
$userId = (int)$user['user_id'];

// "Mark all as read" button
if (is_post() && csrf_verify() && ($_POST['action'] ?? '') === 'mark_all') {
    NotificationService::markAllRead($userId);
    flash_set('success', 'All notifications marked as read.');
    redirect('notifications/index.php');
}

$filters = NotificationService::filters();
$filter  = $_GET['filter'] ?? 'all';
if (!isset($filters[$filter])) {
    $filter = 'all';
}

$perPage = 20;
$total   = NotificationService::countFor($userId, $filter);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$items   = NotificationService::listFor($userId, $filter, $perPage, ($page - 1) * $perPage);

$pageTitle = 'Notifications';
$activeNav = '';
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>All Notifications</h1>
        <p class="df-page-sub">Your full notification history. Reading a notification never deletes it.</p>
    </div>
    <div class="df-page-actions">
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all">
            <button type="submit" class="df-btn df-btn-outline"><i class="bi bi-check2-all"></i> Mark all as read</button>
        </form>
    </div>
</div>

<div class="df-tabs">
    <?php foreach ($filters as $key => $f): ?>
        <a class="df-tab<?= $filter === $key ? ' is-active' : '' ?>"
           href="<?= e(url('notifications/index.php?filter=' . $key)) ?>"><?= e($f['label']) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($items): ?>
    <div class="df-card df-card-flush">
        <div class="df-notif-list df-notif-list-page">
            <?php foreach ($items as $n): ?>
                <?= render_notification_item($n) ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="df-pager">
            <?php if ($page > 1): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('notifications/index.php?filter=' . $filter . '&page=' . ($page - 1))) ?>">
                    <i class="bi bi-chevron-left"></i> Newer
                </a>
            <?php endif; ?>
            <span class="df-muted">Page <?= $page ?> of <?= $pages ?></span>
            <?php if ($page < $pages): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('notifications/index.php?filter=' . $filter . '&page=' . ($page + 1))) ?>">
                    Older <i class="bi bi-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-bell"></i></div>
            <h3>No notifications here</h3>
            <p>When something happens - a new message, task or review - it will appear here.</p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>