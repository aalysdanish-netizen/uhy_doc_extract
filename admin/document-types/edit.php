<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/DocumentTypeService.php';

$admin = require_permission('admin.templates');

$id   = (int)($_GET['id'] ?? 0);
$type = DocumentTypeService::find($id);
if (!$type) {
    flash_set('warning', 'That document type could not be found.');
    redirect('admin/document-types/index.php');
}

$errors = [];

if (is_post()) {
    if (!csrf_verify()) {
        $errors['general'] = 'Your session expired. Please try again.';
    } else {
        $errors = DocumentTypeService::save($id, $_POST, (int)$admin['user_id']);
        if (!$errors) {
            flash_set('success', 'Document type saved.');
            redirect('admin/document-types/edit.php?id=' . $id);
        }
        // On error, show what the user typed instead of the saved values
        $type['name']      = trim((string)($_POST['name'] ?? $type['name']));
        $type['is_active'] = !empty($_POST['is_active']) ? 1 : 0;
    }
}

$fields = DocumentTypeService::fields($id);

$pageTitle = 'Edit Document Type';
$activeNav = 'admin-types';
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('admin/document-types/index.php')) ?>"><i class="bi bi-arrow-left"></i> Document types</a>
        <h1>Edit <?= e($type['name']) ?></h1>
    </div>
</div>

<?php foreach (['general', 'fields'] as $k): if (!empty($errors[$k])): ?>
    <div class="df-alert df-alert-danger"><i class="bi bi-exclamation-circle"></i> <?= e($errors[$k]) ?></div>
<?php endif; endforeach; ?>

<form method="post" novalidate data-loading-form>
    <?= csrf_field() ?>

    <div class="df-card df-form-card" style="margin-bottom:20px">
        <h3 class="df-card-title">Type details</h3>
        <div class="df-form-grid">
            <div class="df-field">
                <label class="df-label" for="name">Name</label>
                <input class="df-input<?= isset($errors['name']) ? ' is-invalid' : '' ?>" type="text" id="name" name="name"
                       value="<?= e($type['name']) ?>" maxlength="100" required>
                <?php if (isset($errors['name'])): ?><small class="df-error"><i class="bi bi-exclamation-circle"></i> <?= e($errors['name']) ?></small><?php endif; ?>
            </div>
            <div class="df-field">
                <span class="df-label">Availability</span>
                <label class="df-check">
                    <input type="checkbox" name="is_active" value="1" <?= (int)$type['is_active'] === 1 ? 'checked' : '' ?>>
                    Active (can be chosen when uploading)
                </label>
            </div>
        </div>
    </div>

    <div class="df-card df-form-card">
        <h3 class="df-card-title">Fields to look for</h3>
        <p class="df-muted" style="margin-top:-8px">
            OCR and comparison use these fields. In "Look for these words", list the labels that appear before the value
            in the document, separated by <code>|</code>. Example: <code>Address|Registered Address</code>.
            Staff can always correct the result.
        </p>

        <div class="df-fields-head">
            <span>Field label</span><span>Look for these words</span><span>Order</span><span>Remove</span>
        </div>

        <?php foreach ($fields as $fid => $f): ?>
            <div class="df-field-row">
                <div>
                    <label class="df-mlabel">Field label</label>
                    <input class="df-input" type="text" name="field_label[<?= (int)$fid ?>]" value="<?= e($f['label']) ?>" maxlength="100">
                    <small class="df-hint">Key: <code><?= e($f['field_key']) ?></code></small>
                </div>
                <div>
                    <label class="df-mlabel">Look for these words</label>
                    <input class="df-input" type="text" name="field_pattern[<?= (int)$fid ?>]" value="<?= e($f['ocr_pattern'] ?? '') ?>" maxlength="255">
                </div>
                <div>
                    <label class="df-mlabel">Order</label>
                    <input class="df-input" type="number" name="field_order[<?= (int)$fid ?>]" value="<?= (int)$f['sort_order'] ?>">
                </div>
                <div class="df-field-remove">
                    <label class="df-check"><input type="checkbox" name="field_delete[<?= (int)$fid ?>]" value="1"> <span>Remove</span></label>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="df-field-row df-field-new">
            <div>
                <label class="df-mlabel">New field</label>
                <input class="df-input" type="text" name="new_label" placeholder="+ New field label" maxlength="100"
                       value="<?= e($_POST['new_label'] ?? '') ?>">
            </div>
            <div>
                <label class="df-mlabel">Look for these words</label>
                <input class="df-input" type="text" name="new_pattern" placeholder="e.g. Tax No|TIN" maxlength="255"
                       value="<?= e($_POST['new_pattern'] ?? '') ?>">
            </div>
            <div></div><div></div>
        </div>

        <div class="df-form-actions">
            <a class="df-btn df-btn-ghost" href="<?= e(url('admin/document-types/index.php')) ?>">Cancel</a>
            <button type="submit" class="df-btn df-btn-primary"><span class="df-btn-label">Save Changes</span></button>
        </div>
    </div>
</form>

<?php require __DIR__ . '/../../includes/footer.php'; ?>