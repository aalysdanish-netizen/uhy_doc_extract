<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/UserService.php';

$admin = require_permission('admin.users');

$id       = (int)($_GET['id'] ?? 0);
$existing = UserService::find($id);
if (!$existing) {
    flash_set('warning', 'That user could not be found.');
    redirect('admin/users/index.php');
}

$mode   = 'edit';
$isSelf = $id === (int)$admin['user_id'];
$errors = [];
$form   = [
    'name'       => $existing['name'],
    'username'   => $existing['username'],
    'role'       => $existing['role'],
    'staff_type' => $existing['staff_type'] ?? 'staff',
    'department' => $existing['department'] ?? 'Other',
    'status'     => $existing['status'],
];

if (is_post()) {
    if (!csrf_verify()) {
        $errors['general'] = 'Your session expired. Please try again.';
    } else {
        foreach (['name', 'role', 'staff_type', 'department', 'status'] as $key) {
            $form[$key] = trim((string)($_POST[$key] ?? ''));
        }
        $errors = UserService::validate($form, $existing, (int)$admin['user_id']);

        if (!$errors) {
            $changed = UserService::update($id, $form, $existing, (int)$admin['user_id']);
            $avatarChanged = UserService::maybeUploadAvatar($id, $_FILES['avatar'] ?? null, $existing['avatar_path'] ?? null);
            $changed = $changed || $avatarChanged;
            flash_set($changed ? 'success' : 'info', $changed ? 'User details saved.' : 'No changes were made.');
            redirect('admin/users/view.php?id=' . $id);
        }
    }
}

$pageTitle    = 'Edit User';
$activeNav    = 'admin-users';
$extraScripts = ['assets/js/users.js'];
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('admin/users/view.php?id=' . $id)) ?>"><i class="bi bi-arrow-left"></i> Back to profile</a>
        <h1>Edit <?= e($existing['name']) ?></h1>
        <p class="df-page-sub"><?= e($existing['email']) ?></p>
    </div>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="df-alert df-alert-danger"><?= e($errors['general']) ?></div>
<?php endif; ?>

<div class="df-card df-form-card">
    <?php require __DIR__ . '/../../includes/user-form.php'; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>