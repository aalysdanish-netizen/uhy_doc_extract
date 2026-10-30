<?php
/**
 * includes/user-form.php
 * Expects: $mode ('create' | 'edit'), $form (array), $errors (array), $isSelf (bool, edit only)
 */
$isEdit = $mode === 'edit';
$isSelf = $isSelf ?? false;

/** Small helper: prints an error message under a field */
$err = function (string $key) use ($errors): string {
    return isset($errors[$key]) ? '<small class="df-error"><i class="bi bi-exclamation-circle"></i> ' . e($errors[$key]) . '</small>' : '';
};
$bad = fn(string $key): string => isset($errors[$key]) ? ' is-invalid' : '';
?>
<form method="post" enctype="multipart/form-data" novalidate data-loading-form data-user-form>
    <?= csrf_field() ?>

    <div class="df-form-grid">

        <div class="df-field">
            <label class="df-label" for="name">Full name</label>
            <input class="df-input<?= $bad('name') ?>" type="text" id="name" name="name"
                   value="<?= e($form['name']) ?>" placeholder="Muhammad Alysdanish" maxlength="120" required>
            <?= $err('name') ?>
        </div>

        <div class="df-field">
            <label class="df-label" for="username">Username</label>
            <div class="df-input-group<?= $bad('username') ?>">
                <input class="df-input" type="text" id="username" name="username"
                       value="<?= e($form['username']) ?>" placeholder="alysdanish"
                       autocapitalize="none" autocorrect="off" spellcheck="false" maxlength="80"
                       <?= $isEdit ? 'readonly' : 'required data-username-input' ?>>
                <span class="df-input-suffix">@<?= e(EMAIL_DOMAIN) ?></span>
            </div>
            <?php if ($isEdit): ?>
                <small class="df-hint">The username is the login email, so it can't be changed.</small>
            <?php else: ?>
                <small class="df-hint">Login email: <strong data-email-preview>username@<?= e(EMAIL_DOMAIN) ?></strong></small>
            <?php endif; ?>
            <?= $err('username') ?>
        </div>

        <div class="df-field">
            <label class="df-label" for="role">Role</label>
            <select class="df-input<?= $bad('role') ?>" id="role" name="role" data-role-select <?= $isSelf ? 'disabled' : '' ?>>
                <option value="staff" <?= $form['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                <option value="admin" <?= $form['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <?php if ($isSelf): ?>
                <input type="hidden" name="role" value="<?= e($form['role']) ?>">
                <small class="df-hint">You can't change your own role.</small>
            <?php endif; ?>
            <?= $err('role') ?>
        </div>

        <div class="df-field" data-staff-type-field <?= $form['role'] === 'staff' ? '' : 'hidden' ?>>
            <label class="df-label" for="staff_type">Staff type</label>
            <select class="df-input<?= $bad('staff_type') ?>" id="staff_type" name="staff_type">
                <option value="staff"    <?= $form['staff_type'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                <option value="contract" <?= $form['staff_type'] === 'contract' ? 'selected' : '' ?>>Contract Staff</option>
                <option value="intern"   <?= $form['staff_type'] === 'intern' ? 'selected' : '' ?>>Intern</option>
            </select>
            <?= $err('staff_type') ?>
        </div>

        <div class="df-field">
            <label class="df-label" for="department">Department</label>
            <select class="df-input<?= $bad('department') ?>" id="department" name="department">
                <?php foreach (UserService::departments() as $d): ?>
                    <option value="<?= e($d) ?>" <?= $form['department'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $err('department') ?>
        </div>

        <div class="df-field">
            <label class="df-label" for="avatar">Profile photo <span class="df-muted">(optional)</span></label>
            <input class="df-input" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png">
            <?= $err('avatar') ?>
            <?php if ($isEdit && !empty($existing['avatar_path'])): ?>
                <small class="df-hint">A photo is already set. Choosing a new one replaces it.</small>
            <?php endif; ?>
        </div>

        <div class="df-field">
            <label class="df-label" for="status">Status</label>
            <select class="df-input<?= $bad('status') ?>" id="status" name="status" <?= $isSelf ? 'disabled' : '' ?>>
                <option value="active"   <?= $form['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
            <?php if ($isSelf): ?>
                <input type="hidden" name="status" value="<?= e($form['status']) ?>">
            <?php endif; ?>
            <?= $err('status') ?>
        </div>
    </div>

    <?php if (!$isEdit): ?>
        <div class="df-info-box">
            <i class="bi bi-key"></i>
            <div>
                <strong>No password needed.</strong>
                The account starts with the default password <code><?= e(DEFAULT_PASSWORD) ?></code>
                and the user must change it the first time they sign in.
            </div>
        </div>
    <?php endif; ?>

    <div class="df-form-actions">
        <a class="df-btn df-btn-ghost" href="<?= e(url('admin/users/index.php')) ?>">Cancel</a>
        <button type="submit" class="df-btn df-btn-primary">
            <span class="df-btn-label"><?= $isEdit ? 'Save Changes' : 'Create User' ?></span>
        </button>
    </div>
</form>