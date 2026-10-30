<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/UserService.php';

$admin = require_permission('admin.users');

$mode   = 'create';
$errors = [];
$form   = ['name' => '', 'username' => '', 'role' => 'staff', 'staff_type' => 'staff', 'department' => 'Audit', 'status' => 'active'];

if (is_post()) {
    if (!csrf_verify()) {
        $errors['general'] = 'Your session expired. Please try again.';
    } else {
        foreach ($form as $key => $default) {
            $form[$key] = trim((string)($_POST[$key] ?? ''));
        }
        $errors = UserService::validate($form, null, (int)$admin['user_id']);

        if (!$errors) {
            $newId = UserService::create($form, (int)$admin['user_id']);
            UserService::maybeUploadAvatar($newId, $_FILES['avatar'] ?? null);
            flash_set('success', $form['name'] . ' was created. Login: ' . build_email($form['username']) . ' with the default password.');
            redirect('admin/users/view.php?id=' . $newId);
        }
    }
}

$pageTitle    = 'Create User';
$activeNav    = 'admin-users';
$extraScripts = ['assets/js/users.js'];
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('admin/users/index.php')) ?>"><i class="bi bi-arrow-left"></i> Users</a>
        <h1>Create User</h1>
        <p class="df-page-sub">Add a new team member. The company email is created automatically.</p>
    </div>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="df-alert df-alert-danger"><?= e($errors['general']) ?></div>
<?php endif; ?>

<div class="df-card df-form-card">
    <?php require __DIR__ . '/../../includes/user-form.php'; ?>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>