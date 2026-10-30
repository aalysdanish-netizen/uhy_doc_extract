<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/ActivityService.php';

$user   = require_permission('history.view');
$userId = (int)$user['user_id'];

$category = array_key_exists($_GET['category'] ?? '', ActivityService::categories()) ? $_GET['category'] : 'all';
$q        = trim($_GET['q'] ?? '');

$perPage = 25;
$total   = ActivityService::count($userId, $category, $q, '', '');
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$items   = ActivityService::search($userId, $category, $q, '', '', $perPage, ($page - 1) * $perPage);

$link = function (array $extra) use ($category, $q): string {
    $params = array_filter(array_merge(['category' => $category, 'q' => $q], $extra), fn($v) => $v !== '' && $v !== null);
    return url('history/index.php' . ($params ? '?' . http_build_query($params) : ''));
};

$pageTitle = 'History';
$activeNav = 'history';
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>History</h1>
        <p class="df-page-sub">A timeline of your activity in the system.</p>
    </div>
</div>

<form method="get" class="df-search" role="search">
    <?php if ($category !== 'all'): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search your activity" aria-label="Search history">
    <button type="submit" class="df-btn df-btn-primary df-btn-sm">Search</button>
</form>

<div class="df-tabs">
    <?php foreach (ActivityService::categories() as $key => $cat): ?>
        <a class="df-tab<?= $category === $key ? ' is-active' : '' ?>" href="<?= e($link(['category' => $key, 'page' => ''])) ?>"><?= e($cat['label']) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($items): ?>
    <div class="df-card">
        <ul class="df-timeline df-timeline-wide">
            <?php foreach ($items as $a): [$label, $icon] = ActivityService::actionMeta($a['action']); ?>
                <li>
                    <span class="df-timeline-icon"><i class="bi <?= e($icon) ?>"></i></span>
                    <span class="df-timeline-text">
                        <strong><?= e($a['description'] ?: $label) ?></strong>
                        <?php if ($a['old_value'] || $a['new_value']): ?>
                            <span class="df-timeline-detail"><?= e($a['old_value']) ?><?= $a['old_value'] && $a['new_value'] ? ' → ' : '' ?><?= e($a['new_value']) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="df-timeline-time"><?= e(format_datetime($a['created_at'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($pages > 1): ?>
        <div class="df-pager">
            <?php if ($page > 1): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i> Previous</a><?php endif; ?>
            <span class="df-muted">Page <?= $page ?> of <?= $pages ?></span>
            <?php if ($page < $pages): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page + 1])) ?>">Next <i class="bi bi-chevron-right"></i></a><?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-clock-history"></i></div>
            <h3>Nothing here yet</h3>
            <p>Your activity will appear here as you use the system.</p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>