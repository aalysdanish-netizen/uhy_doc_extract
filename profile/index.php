<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/ActivityService.php';

require_once __DIR__ . '/../services/AvatarService.php';
require_once __DIR__ . '/../services/UploadService.php';

$user   = require_login();
$userId = (int)$user['user_id'];
$avatarError = '';

if (is_post() && csrf_verify()) {
    if (($_POST['action'] ?? '') === 'remove_avatar') {
        AvatarService::remove($user['avatar_path'] ?? null);
        db()->prepare('UPDATE users SET avatar_path = NULL WHERE user_id = ?')->execute([$userId]);
        AuditService::log($userId, 'avatar_updated', 'user', $userId, 'Removed profile photo');
        flash_set('success', 'Profile photo removed.');
        redirect('profile/index.php');
    } elseif (($_POST['action'] ?? '') === 'upload_avatar') {
        [$avatarError, $src] = AvatarService::validate($_FILES['avatar'] ?? null);
        if (!$avatarError) {
            try {
                $relative = AvatarService::storeForUser($userId, $src, $user['avatar_path'] ?? null);
                db()->prepare('UPDATE users SET avatar_path = ? WHERE user_id = ?')->execute([$relative, $userId]);
                AuditService::log($userId, 'avatar_updated', 'user', $userId, 'Updated profile photo');
                flash_set('success', 'Profile photo updated.');
                redirect('profile/index.php');
            } catch (Throwable $e) {
                error_log('Avatar upload failed: ' . $e->getMessage());
                $avatarError = 'Could not save the photo. Please try again.';
            }
        }
    }
}

$scalar = function (string $sql, array $p = []): int {
    $s = db()->prepare($sql);
    $s->execute($p);
    return (int)$s->fetchColumn();
};
$docCount  = $scalar('SELECT COUNT(*) FROM documents WHERE uploaded_by = ? AND is_deleted = 0', [$userId]);
$openTasks = $scalar("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status <> 'completed'", [$userId]);
$doneTasks = $scalar("SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'completed'", [$userId]);

$activity = ActivityService::search($userId, 'all', '', '', '', 8, 0);

$pageTitle = 'My Profile';
$activeNav = 'profile';
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>My Profile</h1>
        <p class="df-page-sub">Your account details and recent activity.</p>
    </div>
</div>

<div class="df-grid df-grid-profile">
    <div class="df-stack">
        <section class="df-card df-profile-card">
            <?= render_avatar($user, 'lg') ?>
            <h2><?= e($user['name']) ?></h2>
            <p class="df-muted df-break"><?= e($user['email']) ?></p>
            <div class="df-profile-badges">
                <?= $user['role'] === 'admin' ? '<span class="df-badge df-badge-purple">Admin</span>' : '<span class="df-badge df-badge-info">' . e(role_label($user)) . '</span>' ?>
            </div>

            <?php if ($avatarError): ?><div class="df-alert df-alert-danger" style="text-align:left;margin-top:14px"><?= e($avatarError) ?></div><?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="df-avatar-form" data-loading-form>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upload_avatar">
                <label class="df-btn df-btn-outline df-btn-block df-avatar-picker">
                    <i class="bi bi-camera"></i> <span id="avatarFileLabel">Change Photo</span>
                    <input type="file" name="avatar" id="avatarFileInput" accept="image/jpeg,image/png" hidden>
                </label>
                <button type="submit" class="df-btn df-btn-primary df-btn-block" id="avatarUploadBtn" hidden>
                    <span class="df-btn-label">Upload Photo</span>
                </button>
            </form>
            <?php if (!empty($user['avatar_path'])): ?>
                <form method="post" style="margin-top:8px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="remove_avatar">
                    <button type="submit" class="df-btn df-btn-ghost df-btn-block">Remove Photo</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="df-card">
            <h3 class="df-card-title">Details</h3>
            <dl class="df-details">
                <div><dt>Department</dt><dd><?= e($user['department'] ?? '-') ?></dd></div>
                <div><dt>Role</dt><dd><?= e(ucfirst($user['role'])) ?></dd></div>
                <?php if ($user['staff_type']): ?><div><dt>Staff type</dt><dd><?= e(ucfirst($user['staff_type'])) ?></dd></div><?php endif; ?>
            </dl>
            <a class="df-btn df-btn-outline df-btn-block" style="margin-top:16px" href="<?= e(url('auth/change-password.php')) ?>">
                <i class="bi bi-key"></i> Change Password
            </a>
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
            <div class="df-card-header df-card-header-plain">
                <h3 class="df-card-title">Recent Activity</h3>
                <a class="df-link" href="<?= e(url('history/index.php')) ?>">View all</a>
            </div>
            <?php if ($activity): ?>
                <ul class="df-timeline">
                    <?php foreach ($activity as $a): [$label] = ActivityService::actionMeta($a['action']); ?>
                        <li><span class="df-timeline-text"><?= e($a['description'] ?: $label) ?></span><span class="df-timeline-time"><?= e(time_ago($a['created_at'])) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="df-muted" style="margin:0">No activity yet.</p>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('avatarFileInput');
    var label = document.getElementById('avatarFileLabel');
    var uploadBtn = document.getElementById('avatarUploadBtn');
    if (!input) return;
    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
            uploadBtn.hidden = false;
        }
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>