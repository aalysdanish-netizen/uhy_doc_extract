<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/DocumentTypeService.php';

$admin = require_permission('admin.templates');

$error = '';
$name  = '';

// "Add type" form: create it, then go straight to its edit page to define fields
if (is_post()) {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $name  = trim(preg_replace('/\s+/', ' ', (string)($_POST['name'] ?? '')));
        $error = DocumentTypeService::nameError($name, null) ?? '';
        if ($error === '') {
            $newId = DocumentTypeService::create($name, (int)$admin['user_id']);
            flash_set('success', '"' . $name . '" was created. Now add the fields to look for.');
            redirect('admin/document-types/edit.php?id=' . $newId);
        }
    }
}

$types = DocumentTypeService::all();

$pageTitle = 'Document Types';
$activeNav = 'admin-types';
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>Document Types</h1>
        <p class="df-page-sub">Categories of documents, and the fields the system looks for in each one.</p>
    </div>
</div>

<div class="df-card" style="margin-bottom:20px">
    <h3 class="df-card-title">Add a document type</h3>
    <form method="post" class="df-inline-form" novalidate>
        <?= csrf_field() ?>
        <div class="df-field">
            <input class="df-input<?= $error ? ' is-invalid' : '' ?>" type="text" name="name" value="<?= e($name) ?>"
                   placeholder="e.g. Tax Computation" maxlength="100" aria-label="Type name" required>
            <?php if ($error): ?><small class="df-error"><i class="bi bi-exclamation-circle"></i> <?= e($error) ?></small><?php endif; ?>
        </div>
        <button type="submit" class="df-btn df-btn-primary"><i class="bi bi-plus-lg"></i> Add Type</button>
    </form>
</div>

<div class="df-card df-card-flush">
    <div class="df-table-wrap">
        <table class="df-table df-table-cards">
            <thead><tr><th>Type</th><th>Fields</th><th>Documents</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($types as $t): ?>
                <tr>
                    <td class="df-td-title" data-label="Type">
                        <div class="df-user-cell">
                            <span class="df-doc-icon df-doc-icon-sm df-doc-image"><i class="bi bi-tag"></i></span>
                            <strong><?= e($t['name']) ?></strong>
                        </div>
                    </td>
                    <td data-label="Fields"><?= (int)$t['field_count'] ?></td>
                    <td data-label="Documents"><?= (int)$t['doc_count'] ?></td>
                    <td data-label="Status"><?= status_badge((int)$t['is_active'] === 1 ? 'active' : 'inactive') ?></td>
                    <td class="df-td-actions" data-label="Actions">
                        <div class="df-actions">
                            <a class="df-action" href="<?= e(url('admin/document-types/edit.php?id=' . (int)$t['type_id'])) ?>"><i class="bi bi-pencil"></i><span>Edit</span></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>