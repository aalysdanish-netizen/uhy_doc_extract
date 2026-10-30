<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// true = this page may be opened even when a password change is required
$user   = require_login(true);
$forced = (int)$user['must_change_password'] === 1;
$error  = '';

if (is_post()) {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $error = validate_new_password($current, $new, $confirm) ?? '';

        if ($error === '') {
            if (change_user_password((int)$user['user_id'], $current, $new)) {
                session_regenerate_id(true);
                flash_set('success', 'Your password has been changed.');
                redirect('dashboard.php');
            }
            $error = 'Your current password is incorrect.';
        }
    }
}

$pageTitle = 'Change password';
require __DIR__ . '/../includes/auth-header.php';
?>

<h2 class="df-auth-title">Change your password</h2>
<p class="df-auth-sub">
    <?= $forced
        ? 'Hi ' . e($user['name']) . ', for your security please choose a new password before continuing.'
        : 'Choose a new password for your account.' ?>
</p>

<?php if ($error): ?>
    <div class="df-alert df-alert-danger"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></div>
<?php endif; ?>

<form method="post" novalidate data-loading-form>
    <?= csrf_field() ?>

    <div class="df-field">
        <label for="current_password" class="df-label">Current password</label>
        <div class="df-input-group">
            <input type="password" id="current_password" name="current_password" class="df-input"
                   autocomplete="current-password" required autofocus>
            <button type="button" class="df-input-btn" data-toggle-password="#current_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>

    <div class="df-field">
        <label for="new_password" class="df-label">New password</label>
        <div class="df-input-group">
            <input type="password" id="new_password" name="new_password" class="df-input"
                   autocomplete="new-password" required>
            <button type="button" class="df-input-btn" data-toggle-password="#new_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
        <small class="df-hint">At least 8 characters, with a letter and a number.</small>
    </div>

    <div class="df-field">
        <label for="confirm_password" class="df-label">Confirm new password</label>
        <div class="df-input-group">
            <input type="password" id="confirm_password" name="confirm_password" class="df-input"
                   autocomplete="new-password" required>
            <button type="button" class="df-input-btn" data-toggle-password="#confirm_password" aria-label="Show password"><i class="bi bi-eye"></i></button>
        </div>
    </div>

    <button type="submit" class="df-btn df-btn-primary df-btn-block">
        <span class="df-btn-label">Update Password</span>
    </button>

    <?php if (!$forced): ?>
        <a href="<?= e(url('dashboard.php')) ?>" class="df-btn df-btn-ghost df-btn-block">Cancel</a>
    <?php endif; ?>
</form>

<?php require __DIR__ . '/../includes/auth-footer.php'; ?>