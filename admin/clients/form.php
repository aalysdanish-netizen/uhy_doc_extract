<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/ClientService.php';

$admin  = require_permission('admin.clients');
$id     = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;

$existing = null;
if ($isEdit) {
    $existing = ClientService::find($id);
    if (!$existing) {
        flash_set('warning', 'That client could not be found.');
        redirect('admin/clients/index.php');
    }
}

$errors = [];
$form = [
    'client_name'     => $existing['client_name'] ?? '',
    'registration_no' => $existing['registration_no'] ?? '',
    'contact_person'  => $existing['contact_person'] ?? '',
    'status'          => $existing['status'] ?? 'active',
];

if (is_post()) {
    if (!csrf_verify()) {
        $errors['general'] = 'Your session expired. Please try again.';
    } else {
        foreach ($form as $key => $default) {
            $form[$key] = trim((string)($_POST[$key] ?? ''));
        }
        $errors = ClientService::validate($form, $isEdit ? $id : null);

        if (!$errors) {
            if ($isEdit) {
                $changed = ClientService::update($id, $form, $existing, (int)$admin['user_id']);
                flash_set($changed ? 'success' : 'info', $changed ? 'Client saved.' : 'No changes were made.');
            } else {
                ClientService::create($form, (int)$admin['user_id']);
                flash_set('success', $form['client_name'] . ' was added.');
            }
            redirect('admin/clients/index.php');
        }
    }
}

$bad = fn(string $k): string => isset($errors[$k]) ? ' is-invalid' : '';
$err = fn(string $k): string => isset($errors[$k]) ? '<small class="df-error"><i class="bi bi-exclamation-circle"></i> ' . e($errors[$k]) . '</small>' : '';

$pageTitle = $isEdit ? 'Edit Client' : 'Add Client';
$activeNav = 'admin-clients';
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('admin/clients/index.php')) ?>"><i class="bi bi-arrow-left"></i> Clients</a>
        <h1><?= $isEdit ? 'Edit ' . e($existing['client_name']) : 'Add Client' ?></h1>
    </div>
</div>

<?php if (!empty($errors['general'])): ?><div class="df-alert df-alert-danger"><?= e($errors['general']) ?></div><?php endif; ?>

<div class="df-card df-form-card">
    <form method="post" novalidate data-loading-form>
        <?= csrf_field() ?>
        <div class="df-form-grid">
            <div class="df-field df-span-2">
                <label class="df-label" for="client_name">Client name</label>
                <input class="df-input<?= $bad('client_name') ?>" type="text" id="client_name" name="client_name"
                       value="<?= e($form['client_name']) ?>" placeholder="ABC Sdn Bhd" maxlength="150" required autofocus>
                <?= $err('client_name') ?>
            </div>
            <div class="df-field">
                <label class="df-label" for="registration_no">Registration no. <span class="df-muted">(optional)</span></label>
                <input class="df-input<?= $bad('registration_no') ?>" type="text" id="registration_no" name="registration_no"
                       value="<?= e($form['registration_no']) ?>" placeholder="202001000001" maxlength="50">
                <?= $err('registration_no') ?>
            </div>
            <div class="df-field">
                <label class="df-label" for="contact_person">Contact person <span class="df-muted">(optional)</span></label>
                <input class="df-input<?= $bad('contact_person') ?>" type="text" id="contact_person" name="contact_person"
                       value="<?= e($form['contact_person']) ?>" maxlength="120">
                <?= $err('contact_person') ?>
            </div>
            <div class="df-field">
                <label class="df-label" for="status">Status</label>
                <select class="df-input<?= $bad('status') ?>" id="status" name="status">
                    <option value="active"   <?= $form['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $form['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?= $err('status') ?>
            </div>
        </div>
        <div class="df-form-actions">
            <a class="df-btn df-btn-ghost" href="<?= e(url('admin/clients/index.php')) ?>">Cancel</a>
            <button type="submit" class="df-btn df-btn-primary"><span class="df-btn-label"><?= $isEdit ? 'Save Changes' : 'Add Client' ?></span></button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>