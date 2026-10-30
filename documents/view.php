<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';

$user = require_permission('document.library');

$doc = DocumentService::find((int)($_GET['id'] ?? 0));
if (!$doc) {
    flash_set('warning', 'That document could not be found.');
    redirect('documents/library.php');
}
if ((int)$doc['is_deleted'] === 1 && $user['role'] !== 'admin') {
    flash_set('warning', 'This document has been deleted.');
    redirect('documents/library.php');
}

$versions = DocumentService::versions((int)$doc['document_id']);
$tasks    = DocumentService::tasksFor((int)$doc['document_id']);

$hasFile  = !empty($doc['file_path']);
$ext      = strtolower((string)$doc['file_type']);
$isImage  = in_array($ext, ['jpg', 'png'], true);
[$iconClass, $icon] = doc_icon($ext);
$fileUrl    = $hasFile ? url('files/download.php?v=' . (int)$doc['current_version_id']) : '';

$pageTitle    = $doc['document_name'];
$activeNav    = 'library';
$extraScripts = ['assets/js/confirm.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('documents/library.php?client=' . (int)$doc['client_id'])) ?>" onclick="if (history.length > 1) { history.back(); return false; ?>">
    <i class="bi bi-arrow-left"></i> Back
</a>
        <h1><?= e($doc['document_name']) ?> <span class="df-muted">(<?= (int)$doc['year'] ?>)</span></h1>
        <p class="df-page-sub"><?= e($doc['client_name']) ?> · <?= e($doc['type_name']) ?> · <?= status_badge($doc['status']) ?></p>
    </div>
    <div class="df-page-actions">
        <?php if ($hasFile && (int)$doc['is_deleted'] === 0): ?>
            <?php if (can('document.prepare') && in_array($doc['status'], ['draft', 'returned'], true)): ?>
                <a class="df-btn df-btn-primary" href="<?= e(url('documents/prepare.php?id=' . (int)$doc['document_id'])) ?>">
                    <i class="bi bi-arrow-left-right"></i> Continue Preparation
                </a>
            <?php endif; ?>
            <a class="df-btn df-btn-outline" href="<?= e($fileUrl) ?>"><i class="bi bi-download"></i> Download</a>

        <?php endif; ?>
        <?php if ((int)$doc['is_deleted'] === 1): ?>
            <form method="post" action="<?= e(url('documents/restore.php')) ?>">
                <?= csrf_field() ?><input type="hidden" name="document_id" value="<?= (int)$doc['document_id'] ?>">
                <button type="submit" class="df-btn df-btn-primary"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
            </form>
        <?php elseif (DocumentService::canDelete($doc, $user)): ?>
            <button type="button" class="df-btn df-btn-danger" data-confirm data-tone="danger"
                    data-title="Delete this document?" data-text="<?= e($doc['document_name']) ?> will be removed from the library. An Admin can restore it later if needed."
                    data-button="Delete" data-action="<?= e(url('documents/delete.php')) ?>"
                    data-fields="<?= e(json_encode(['document_id' => (int)$doc['document_id']])) ?>">
                <i class="bi bi-trash"></i> Delete
            </button>
        <?php endif; ?>
    </div>
            <?php if (DocumentService::canReview($doc, $user)): ?>
            <button type="button" class="df-btn df-btn-primary" data-confirm data-tone="success"
                    data-title="Approve this document?" data-text="<?= e($doc['document_name']) ?> will be marked Approved and its task closed."
                    data-button="Approve" data-action="<?= e(url('documents/approve.php')) ?>"
                    data-fields="<?= e(json_encode(['document_id' => (int)$doc['document_id']])) ?>">
                <i class="bi bi-check2-circle"></i> Approve
            </button>
            <button type="button" class="df-btn df-btn-outline" data-modal-open="#returnModal">
                <i class="bi bi-arrow-return-left"></i> Return for Changes
            </button>
        <?php endif; ?>
</div>

<?php if ((int)$doc['is_deleted'] === 1): ?>
    <div class="df-alert df-alert-danger">
        <i class="bi bi-trash3"></i>
        This document was deleted by <strong><?= e($doc['deleted_by_name'] ?? '-') ?></strong> on <?= e(format_datetime($doc['deleted_at'])) ?>.
        It is hidden from the library and cannot be edited until restored.
    </div>
<?php endif; ?>

<div class="df-grid df-grid-doc">

    <div class="df-stack">
        <!-- Preview -->
        <section class="df-card">
            <h3 class="df-card-title">Current version <span class="df-muted">· v<?= (int)$doc['current_version_no'] ?></span></h3>
            <?php if ($hasFile && $isImage): ?>
                <a href="<?= e($fileUrl . '&inline=1') ?>" target="_blank" rel="noopener" class="df-preview">
                    <img src="<?= e($fileUrl . '&inline=1') ?>" alt="Document preview">
                </a>
                <small class="df-hint">Tap the image to open it full size.</small>
            <?php elseif ($hasFile): ?>
                <div class="df-doc-card">
                    <span class="df-doc-icon <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                    <span class="df-doc-info">
                        <strong><?= e($doc['original_name'] ?: $doc['document_name']) ?></strong>
                        <small><?= e(strtoupper($ext)) ?> · <?= e(format_bytes($doc['file_size'] !== null ? (int)$doc['file_size'] : null)) ?></small>
                    </span>
                    <?php if ($ext === 'pdf'): ?>
                        <a class="df-btn df-btn-outline df-btn-sm" href="<?= e($fileUrl . '&inline=1') ?>" target="_blank" rel="noopener">Open</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="df-empty">
                    <div class="df-empty-icon"><i class="bi bi-file-earmark-x"></i></div>
                    <h3>No file attached</h3>
                    <p>This record has no file (it may be sample data).</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Version history -->
        <section class="df-card">
            <h3 class="df-card-title">Version history</h3>
            <ul class="df-timeline">
                <?php foreach ($versions as $v): ?>
                    <li>
                        <span class="df-timeline-text">
                            <strong>Version <?= (int)$v['version_no'] ?></strong>
                            <?= (int)$v['version_no'] === (int)$doc['current_version_no'] ? '<span class="df-you">Current</span>' : '' ?>
                            <?= (int)$v['is_final'] === 1 ? '<span class="df-badge df-badge-success">Final</span>' : '' ?>
                            <?= $v['change_note'] ? ' - ' . e($v['change_note']) : '' ?>
                        </span>
                        <span class="df-timeline-time">
                            <?= e($v['created_by_name']) ?> · <?= e(format_datetime($v['created_at'])) ?>
                            <?php if ($v['file_path']): ?>
                                · <a class="df-link" href="<?= e(url('files/download.php?v=' . (int)$v['version_id'])) ?>">Download</a>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <div class="df-stack">
        <section class="df-card">
            <h3 class="df-card-title">Details</h3>
            <dl class="df-details">
                <div><dt>Client</dt><dd><?= e($doc['client_name']) ?></dd></div>
                <div><dt>Document type</dt><dd><?= e($doc['type_name']) ?></dd></div>
                <div><dt>Financial year</dt><dd><?= (int)$doc['year'] ?></dd></div>
                <div><dt>Status</dt><dd><?= status_badge($doc['status']) ?></dd></div>
                <div><dt>Uploaded by</dt><dd><?= e($doc['uploader_name']) ?></dd></div>
                <div><dt>Uploaded</dt><dd><?= e(format_datetime($doc['created_at'])) ?></dd></div>
                <div><dt>Last updated</dt><dd><?= e(time_ago($doc['updated_at'])) ?></dd></div>
                <div><dt>Previous-year template</dt>
                    <dd><?php if ($doc['tpl_document_id']): ?>
                        <a class="df-link" href="<?= e(url('documents/view.php?id=' . (int)$doc['tpl_document_id'])) ?>"><?= e($doc['tpl_name']) ?> (<?= (int)$doc['tpl_year'] ?>)</a>
                    <?php else: ?><span class="df-muted">Not selected</span><?php endif; ?></dd></div>
            </dl>
        </section>

        <section class="df-card">
            <h3 class="df-card-title">Tasks</h3>
            <?php if ($tasks): ?>
                <div class="df-stack">
                    <?php foreach ($tasks as $t): ?>
                        <div class="df-task-card df-task-<?= e($t['priority']) ?>">
                            <strong><?= e($t['title']) ?></strong>
                            <span class="df-muted"><?= e($t['assignee']) ?></span>
                            <div class="df-task-meta"><?= status_badge($t['status']) ?><?= status_badge($t['priority']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="df-muted" style="margin:0">No tasks for this document.</p>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../includes/confirm-modal.php'; ?>
<?php if (DocumentService::canReview($doc, $user)): ?>
<div class="df-modal" id="returnModal" role="dialog" aria-modal="true">
    <div class="df-modal-dialog">
        <form method="post" action="<?= e(url('documents/return.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="document_id" value="<?= (int)$doc['document_id'] ?>">
            <div class="df-modal-icon df-modal-icon-warning"><i class="bi bi-arrow-return-left"></i></div>
            <h3>Return for changes</h3>
            <p class="df-muted">Let <?= e($doc['uploader_name']) ?> know what needs fixing.</p>
            <div class="df-field">
                <label class="df-label" for="returnNote">Note <span class="df-muted">(optional)</span></label>
                <textarea class="df-input" id="returnNote" name="note" rows="3" placeholder="e.g. Please recheck the Financial Year field"></textarea>
            </div>
            <div class="df-modal-actions">
                <button type="button" class="df-btn df-btn-ghost" data-modal-close>Cancel</button>
                <button type="submit" class="df-btn df-btn-primary">Return</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>