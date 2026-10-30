<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/UserService.php';

$admin = require_permission('admin.users');

$id = (int)($_GET['id'] ?? 0);
$u  = UserService::find($id);
if (!$u) {
    flash_set('warning', 'That user could not be found.');
    redirect('admin/users/index.php');
}
$isMe = $id === (int)$admin['user_id'];

// Small stats
$scalar = function (string $sql, array $p): int {
    $s = db()->prepare($sql);
    $s->execute($p);
    return (int)$s->fetchColumn();
};
$docCount  = $scalar('SELECT COUNT(*) FROM documents WHERE uploaded_by = ?', [$id]);
$openTasks = $scalar("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status <> 'completed'", [$id]);
$doneTasks = $scalar("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'completed'", [$id]);

// Recent activity by this user (timeline)
$stmt = db()->prepare('SELECT description, action, created_at FROM audit_logs WHERE user_id = ? ORDER BY log_id DESC LIMIT 10');
$stmt->execute([$id]);
$activity = $stmt->fetchAll();

$pageTitle    = $u['name'];
$activeNav    = 'admin-users';
$modalReturn  = 'view';
$extraScripts = ['assets/js/users.js'];
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('admin/users/index.php')) ?>"><i class="bi bi-arrow-left"></i> Users</a>
        <h1>User Profile</h1>
    </div>
    <div class="df-page-actions">
        <a class="df-btn df-btn-outline" href="<?= e(url('admin/users/edit.php?id=' . $id)) ?>"><i class="bi bi-pencil"></i> Edit</a>
        <button type="button" class="df-btn df-btn-outline" data-user-action="reset_password" data-user-id="<?= $id ?>" data-user-name="<?= e($u['name']) ?>">
            <i class="bi bi-key"></i> Reset Password
        </button>
        <?php if ($u['status'] === 'active' && !$isMe): ?>
            <button type="button" class="df-btn df-btn-danger" data-user-action="deactivate" data-user-id="<?= $id ?>" data-user-name="<?= e($u['name']) ?>">
                <i class="bi bi-person-x"></i> Deactivate
            </button>
        <?php elseif ($u['status'] === 'inactive'): ?>
            <button type="button" class="df-btn df-btn-primary" data-user-action="activate" data-user-id="<?= $id ?>" data-user-name="<?= e($u['name']) ?>">
                <i class="bi bi-person-check"></i> Activate
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="df-grid df-grid-profile">

    <div class="df-stack">
        <section class="df-card df-profile-card">
            <?= render_avatar($u, 'lg') ?>
            <h2><?= e($u['name']) ?></h2>
            <p class="df-muted df-break"><?= e($u['email']) ?></p>
            <div class="df-profile-badges">
                <?= $u['role'] === 'admin' ? '<span class="df-badge df-badge-purple">Admin</span>' : '<span class="df-badge df-badge-info">' . e(role_label($u)) . '</span>' ?>
                <?= status_badge($u['status']) ?>
            </div>
        </section>

        <section class="df-card">
            <h3 class="df-card-title">Details</h3>
            <dl class="df-details">
                <div><dt>Department</dt><dd><?= e($u['department'] ?? '-') ?></dd></div>
                <div><dt>Role</dt><dd><?= e(ucfirst($u['role'])) ?></dd></div>
                <div><dt>Staff type</dt><dd><?= $u['staff_type'] ? e(['staff' => 'Staff', 'contract' => 'Contract Staff', 'intern' => 'Intern'][$u['staff_type']] ?? ucfirst($u['staff_type'])) : '-' ?></dd></div>
                <div><dt>Last sign in</dt><dd><?= e($u['last_login_at'] ? format_datetime($u['last_login_at']) : 'Never') ?></dd></div>
                <div><dt>Password</dt><dd><?= (int)$u['must_change_password'] === 1 ? '<span class="df-badge df-badge-warning">Must change at next sign in</span>' : 'Set by user' ?></dd></div>
                <div><dt>Created</dt><dd><?= e(format_datetime($u['created_at'])) ?></dd></div>
            </dl>
        </section>
    </div>

    <div class="df-stack">
        <div class="df-grid df-grid-3-tight">
            <div class="df-stat df-stat-purple"><span class="df-stat-icon"><i class="bi bi-file-earmark-text"></i></span>
                <div><div class="df-stat-value"><?= $docCount ?></div><div class="df-stat-label">Documents uploaded</div></div></div>
            <div class="df-stat df-stat-amber"><span class="df-stat-icon"><i class="bi bi-hourglass-split"></i></span>
                <div><div class="df-stat-value"><?= $openTasks ?></div><div class="df-stat-label">Open tasks</div></div></div>
            <div class="df-stat df-stat-green"><span class="df-stat-icon"><i class="bi bi-check2-circle"></i></span>
                <div><div class="df-stat-value"><?= $doneTasks ?></div><div class="df-stat-label">Tasks completed</div></div></div>
        </div>

        <section class="df-card">
            <h3 class="df-card-title">Recent activity</h3>
            <?php if ($activity): ?>
                <ul class="df-timeline">
                    <?php foreach ($activity as $a): ?>
                        <li>
                            <span class="df-timeline-text"><?= e($a['description'] ?: ucfirst(str_replace('_', ' ', $a['action']))) ?></span>
                            <span class="df-timeline-time"><?= e(time_ago($a['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="df-empty">
                    <div class="df-empty-icon"><i class="bi bi-clock-history"></i></div>
                    <h3>No activity yet</h3>
                    <p>Actions this user takes will appear here.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require __DIR__ . '/../../includes/user-action-modal.php'; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>