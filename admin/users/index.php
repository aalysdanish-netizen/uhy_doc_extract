<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/UserService.php';

$admin = require_permission('admin.users');     // staff get a 403 here

// ---- Filters from the URL ----
$q      = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
if (!in_array($status, ['all', 'active', 'inactive'], true)) {
    $status = 'all';
}

// ---- Pagination ----
$perPage = 15;
$total   = UserService::count($q, $status);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$users   = UserService::search($q, $status, $perPage, ($page - 1) * $perPage);
$counts  = UserService::statusCounts();

/** Build a link that keeps the current search */
$link = function (array $extra) use ($q, $status): string {
    $params = array_merge(['q' => $q, 'status' => $status === 'all' ? '' : $status], $extra);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return url('admin/users/index.php' . ($params ? '?' . http_build_query($params) : ''));
};

$pageTitle    = 'User Management';
$activeNav    = 'admin-users';
$modalReturn  = 'list';
$extraScripts = ['assets/js/users.js'];
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>User Management</h1>
        <p class="df-page-sub">Create accounts, manage access and reset passwords.</p>
    </div>
    <div class="df-page-actions">
        <a class="df-btn df-btn-primary" href="<?= e(url('admin/users/create.php')) ?>">
            <i class="bi bi-plus-lg"></i> Create User
        </a>
    </div>
</div>

<!-- Search -->
<form method="get" class="df-search" role="search">
    <?php if ($status !== 'all'): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email or department" aria-label="Search users">
    <?php if ($q !== ''): ?>
        <a class="df-search-clear" href="<?= e($link(['q' => ''])) ?>" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
    <?php endif; ?>
    <button type="submit" class="df-btn df-btn-primary df-btn-sm">Search</button>
</form>

<!-- Status tabs -->
<div class="df-tabs">
    <?php foreach (['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $key => $label): ?>
        <a class="df-tab<?= $status === $key ? ' is-active' : '' ?>" href="<?= e($link(['status' => $key === 'all' ? '' : $key, 'page' => ''])) ?>">
            <?= e($label) ?> <span class="df-tab-count"><?= (int)$counts[$key] ?></span>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($users): ?>
    <div class="df-card df-card-flush">
        <div class="df-table-wrap">
            <table class="df-table df-table-cards">
                <thead>
                    <tr>
                        <th>Name</th><th>Email</th><th>Role</th><th>Staff Type</th>
                        <th>Department</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): $isMe = (int)$u['user_id'] === (int)$admin['user_id']; ?>
                    <tr>
                        <td class="df-td-title" data-label="Name">
                            <div class="df-user-cell">
                                <?= render_avatar($u, 'sm') ?>
                                <strong><?= e($u['name']) ?><?= $isMe ? ' <span class="df-you">You</span>' : '' ?></strong>
                            </div>
                        </td>
                        <td data-label="Email" class="df-break"><?= e($u['email']) ?></td>
                        <td data-label="Role">
                            <?= $u['role'] === 'admin'
                                ? '<span class="df-badge df-badge-purple">Admin</span>'
                                : '<span class="df-badge df-badge-info">Staff</span>' ?>
                        </td>
                        <td data-label="Staff Type"><?= $u['staff_type'] ? e(['staff' => 'Staff', 'contract' => 'Contract Staff', 'intern' => 'Intern'][$u['staff_type']] ?? ucfirst($u['staff_type'])) : '<span class="df-muted">-</span>' ?></td>
                        <td data-label="Department"><?= e($u['department'] ?? '-') ?></td>
                        <td data-label="Status"><?= status_badge($u['status']) ?></td>
                        <td class="df-td-actions" data-label="Actions">
                            <div class="df-actions">
                                <a class="df-action" href="<?= e(url('admin/users/view.php?id=' . (int)$u['user_id'])) ?>" title="View">
                                    <i class="bi bi-eye"></i><span>View</span>
                                </a>
                                <a class="df-action" href="<?= e(url('admin/users/edit.php?id=' . (int)$u['user_id'])) ?>" title="Edit">
                                    <i class="bi bi-pencil"></i><span>Edit</span>
                                </a>
                                <button type="button" class="df-action" title="Reset Password"
                                        data-user-action="reset_password" data-user-id="<?= (int)$u['user_id'] ?>" data-user-name="<?= e($u['name']) ?>">
                                    <i class="bi bi-key"></i><span>Reset</span>
                                </button>
                                <?php if ($u['status'] === 'active' && !$isMe): ?>
                                    <button type="button" class="df-action df-action-danger" title="Deactivate"
                                            data-user-action="deactivate" data-user-id="<?= (int)$u['user_id'] ?>" data-user-name="<?= e($u['name']) ?>">
                                        <i class="bi bi-person-x"></i><span>Deactivate</span>
                                    </button>
                                <?php elseif ($u['status'] === 'inactive'): ?>
                                    <button type="button" class="df-action df-action-success" title="Activate"
                                            data-user-action="activate" data-user-id="<?= (int)$u['user_id'] ?>" data-user-name="<?= e($u['name']) ?>">
                                        <i class="bi bi-person-check"></i><span>Activate</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="df-pager">
            <?php if ($page > 1): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i> Previous</a>
            <?php endif; ?>
            <span class="df-muted">Page <?= $page ?> of <?= $pages ?> · <?= $total ?> users</span>
            <?php if ($page < $pages): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page + 1])) ?>">Next <i class="bi bi-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-people"></i></div>
            <h3><?= ($q !== '' || $status !== 'all') ? 'No users match your search' : 'No users yet' ?></h3>
            <p><?= ($q !== '' || $status !== 'all') ? 'Try a different search or clear the filters.' : 'Create the first account to get started.' ?></p>
            <?php if ($q !== '' || $status !== 'all'): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('admin/users/index.php')) ?>">Clear filters</a>
            <?php else: ?>
                <a class="df-btn df-btn-primary df-btn-sm" href="<?= e(url('admin/users/create.php')) ?>">Create User</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/user-action-modal.php'; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>