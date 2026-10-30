<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/UploadService.php';

$user = require_permission('files.view');

$q = trim($_GET['q'] ?? '');

$sql = "SELECT v.version_id, v.file_path, v.file_type, v.original_name, v.file_size, v.created_at,
               d.document_id, d.document_name, d.year, u.name AS uploader_name, c.client_name
        FROM document_versions v
        JOIN documents d ON d.document_id = v.document_id AND d.is_deleted = 0
        JOIN clients c ON c.client_id = d.client_id
        JOIN users u ON u.user_id = v.created_by
        WHERE v.version_id = d.current_version_id AND v.file_path IS NOT NULL";
$p = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $sql .= ' AND (v.original_name LIKE ? OR d.document_name LIKE ? OR c.client_name LIKE ?)';
    array_push($p, $like, $like, $like);
}
$sql .= ' ORDER BY v.created_at DESC LIMIT 200';

$stmt = db()->prepare($sql);
$stmt->execute($p);
$files = $stmt->fetchAll();

$pageTitle = 'Files';
$activeNav = 'files';
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>Files</h1>
        <p class="df-page-sub">Every document file currently on record, in one flat list.</p>
    </div>
</div>

<form method="get" class="df-search" role="search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search file, document or client name" aria-label="Search files">
    <button type="submit" class="df-btn df-btn-primary df-btn-sm">Search</button>
</form>

<?php if ($files): ?>
    <div class="df-card df-card-flush">
        <div class="df-table-wrap">
            <table class="df-table df-table-cards">
                <thead><tr><th>File</th><th>Client</th><th>Document</th><th>Uploaded By</th><th>Size</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($files as $f): [$iconClass, $icon] = doc_icon($f['file_type']); ?>
                    <tr>
                        <td class="df-td-title" data-label="File">
                            <div class="df-user-cell">
                                <span class="df-doc-icon df-doc-icon-sm <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                                <strong><?= e($f['original_name'] ?: $f['document_name']) ?></strong>
                            </div>
                        </td>
                        <td data-label="Client"><?= e($f['client_name']) ?></td>
                        <td data-label="Document"><a class="df-link" href="<?= e(url('documents/view.php?id=' . (int)$f['document_id'])) ?>"><?= e($f['document_name']) ?> (<?= (int)$f['year'] ?>)</a></td>
                        <td data-label="Uploaded By"><?= e($f['uploader_name']) ?></td>
                        <td data-label="Size"><?= e(format_bytes($f['file_size'] !== null ? (int)$f['file_size'] : null)) ?></td>
                        <td data-label="Date"><?= e(time_ago($f['created_at'])) ?></td>
                        <td class="df-td-actions" data-label="Actions">
                            <a class="df-action" href="<?= e(url('files/download.php?v=' . (int)$f['version_id'])) ?>" title="Download"><i class="bi bi-download"></i><span>Download</span></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-files"></i></div>
            <h3><?= $q !== '' ? 'No files match your search' : 'No files yet' ?></h3>
            <p><?= $q !== '' ? 'Try a different search term.' : 'Uploaded documents will appear here.' ?></p>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>