<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/ActivityService.php';

$admin = require_permission('admin.activity');

$category = array_key_exists($_GET['category'] ?? '', ActivityService::categories()) ? $_GET['category'] : 'all';
$q        = trim($_GET['q'] ?? '');
$staffId  = (int)($_GET['staff'] ?? 0);
$date     = fn(string $v): string => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
$from     = $date((string)($_GET['from'] ?? ''));
$to       = $date((string)($_GET['to'] ?? ''));

$viewerId = $staffId > 0 ? $staffId : null;

$perPage = 30;
$total   = ActivityService::count($viewerId, $category, $q, $from, $to);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$items   = ActivityService::search($viewerId, $category, $q, $from, $to, $perPage, ($page - 1) * $perPage);

$staffList = ActivityService::staffWithActivity();

$link = function (array $extra) use ($category, $q, $staffId, $from, $to): string {
    $params = array_filter(array_merge(['category' => $category, 'q' => $q, 'staff' => $staffId ?: '', 'from' => $from, 'to' => $to], $extra), fn($v) => $v !== '' && $v !== null);
    return url('admin/activity/index.php' . ($params ? '?' . http_build_query($params) : ''));
};

$pageTitle = 'System Activity';
$activeNav = 'admin-activity';
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>System Activity</h1>
        <p class="df-page-sub">A firm-wide log of every recorded action.</p>
    </div>
</div>

<form method="get" class="df-filters-form df-card" style="margin-bottom:20px">
    <div class="df-filter-grid">
        <div class="df-field df-filter-wide">
            <label class="df-label" for="q">Search</label>
            <input class="df-input" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Search description or user">
        </div>
        <div class="df-field">
            <label class="df-label" for="staff">User</label>
            <select class="df-input" id="staff" name="staff">
                <option value="">Everyone</option>
                <?php foreach ($staffList as $s): ?>
                    <option value="<?= (int)$s['user_id'] ?>" <?= $staffId === (int)$s['user_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="df-field">
            <label class="df-label" for="from">From</label>
            <input class="df-input" type="date" id="from" name="from" value="<?= e($from) ?>">
        </div>
        <div class="df-field">
            <label class="df-label" for="to">To</label>
            <input class="df-input" type="date" id="to" name="to" value="<?= e($to) ?>">
        </div>
    </div>
    <div class="df-form-actions df-filter-actions">
        <?php if ($q || $staffId || $from || $to): ?><a class="df-btn df-btn-ghost" href="<?= e(url('admin/activity/index.php?category=' . $category)) ?>">Clear</a><?php endif; ?>
        <button type="submit" class="df-btn df-btn-primary"><i class="bi bi-search"></i> Apply</button>
    </div>
</form>

<div class="df-tabs">
    <?php foreach (ActivityService::categories() as $key => $cat): ?>
        <a class="df-tab<?= $category === $key ? ' is-active' : '' ?>" href="<?= e($link(['category' => $key, 'page' => ''])) ?>"><?= e($cat['label']) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($items): ?>
    <div class="df-card df-card-flush">
        <div class="df-table-wrap">
            <table class="df-table df-table-cards">
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
                <tbody>
                <?php foreach ($items as $a): [$label, $icon] = ActivityService::actionMeta($a['action']); ?>
                    <tr>
                        <td data-label="When"><?= e(format_datetime($a['created_at'])) ?></td>
                        <td data-label="User"><?= e($a['user_name'] ?? 'System') ?></td>
                        <td data-label="Action"><i class="bi <?= e($icon) ?>"></i> <?= e($label) ?></td>
                        <td data-label="Details">
                            <?= e($a['description'] ?? '-') ?>
                            <?php if ($a['old_value'] || $a['new_value']): ?>
                                <br><small class="df-muted"><?= e($a['old_value']) ?><?= $a['old_value'] && $a['new_value'] ? ' → ' : '' ?><?= e($a['new_value']) ?></small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="df-pager">
            <?php if ($page > 1): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i> Previous</a><?php endif; ?>
            <span class="df-muted">Page <?= $page ?> of <?= $pages ?> · <?= $total ?> records</span>
            <?php if ($page < $pages): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page + 1])) ?>">Next <i class="bi bi-chevron-right"></i></a><?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-activity"></i></div>
            <h3>No activity found</h3>
            <p>Try adjusting the filters above.</p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/footer.php'; ?>