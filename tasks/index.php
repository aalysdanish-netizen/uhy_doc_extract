<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/TaskService.php';

$user   = require_permission('task.view');
$userId = (int)$user['user_id'];

$scope  = ($user['role'] === 'admin' && ($_GET['scope'] ?? 'mine') === 'all') ? 'all' : 'mine';
$status = TaskService::statuses()[$_GET['status'] ?? ''] ? $_GET['status'] : '';
$q      = trim($_GET['q'] ?? '');

$perPage = 20;
$total   = TaskService::count($userId, $scope, $status, $q);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$tasks   = TaskService::search($userId, $scope, $status, $q, $perPage, ($page - 1) * $perPage);

$link = function (array $extra) use ($scope, $status, $q): string {
    $params = array_filter(array_merge(['scope' => $scope, 'status' => $status, 'q' => $q], $extra), fn($v) => $v !== '' && $v !== null);
    return url('tasks/index.php' . ($params ? '?' . http_build_query($params) : ''));
};

$pageTitle = 'My Tasks';
$activeNav = 'tasks';
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>My Tasks</h1>
        <p class="df-page-sub">What needs doing, and what's already done.</p>
    </div>
</div>

<?php if ($user['role'] === 'admin'): ?>
    <div class="df-tabs">
        <a class="df-tab<?= $scope === 'mine' ? ' is-active' : '' ?>" href="<?= e($link(['scope' => 'mine'])) ?>">My Tasks</a>
        <a class="df-tab<?= $scope === 'all' ? ' is-active' : '' ?>" href="<?= e($link(['scope' => 'all'])) ?>">Everyone's Tasks</a>
    </div>
<?php endif; ?>

<form method="get" class="df-search" role="search">
    <input type="hidden" name="scope" value="<?= e($scope) ?>">
    <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search title or client" aria-label="Search tasks">
    <button type="submit" class="df-btn df-btn-primary df-btn-sm">Search</button>
</form>

<div class="df-tabs">
    <a class="df-tab<?= $status === '' ? ' is-active' : '' ?>" href="<?= e($link(['status' => ''])) ?>">Open</a>
    <?php foreach (TaskService::statuses() as $key => $label): if ($key === 'completed') continue; ?>
        <a class="df-tab<?= $status === $key ? ' is-active' : '' ?>" href="<?= e($link(['status' => $key])) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
    <a class="df-tab<?= $status === 'completed' ? ' is-active' : '' ?>" href="<?= e($link(['status' => 'completed'])) ?>">Completed</a>
</div>

<?php if ($tasks): ?>
    <div class="df-stack">
        <?php foreach ($tasks as $t): [$dueText, $dueClass] = due_label($t['due_date']); $isNew = $t['seen_at'] === null; ?>
            <a class="df-task-row df-task-<?= e($t['priority']) ?><?= $isNew ? ' is-new' : '' ?>" href="<?= e(url('tasks/view.php?id=' . (int)$t['task_id'])) ?>">
                <div class="df-task-row-main">
                    <strong><?= e($t['title']) ?><?php if ($isNew): ?><span class="df-new-dot" title="New"></span><?php endif; ?></strong>
                    <small><?= e($t['client_name'] ?? '-') ?><?= $t['document_name'] ? ' · ' . e($t['document_name']) . ' (' . (int)$t['document_year'] . ')' : '' ?><?= $scope === 'all' ? ' · ' . e($t['assignee_name']) : '' ?></small>
                </div>
                <div class="df-task-row-meta">
                    <span class="<?= e($dueClass) ?>"><?= e($dueText) ?></span>
                    <?= status_badge($t['priority']) ?>
                    <?= status_badge($t['status']) ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <div class="df-pager">
            <?php if ($page > 1): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i> Previous</a><?php endif; ?>
            <span class="df-muted">Page <?= $page ?> of <?= $pages ?> · <?= $total ?> tasks</span>
            <?php if ($page < $pages): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page + 1])) ?>">Next <i class="bi bi-chevron-right"></i></a><?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-emoji-smile"></i></div>
            <h3>Nothing here</h3>
            <p>No tasks match this view right now.</p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>