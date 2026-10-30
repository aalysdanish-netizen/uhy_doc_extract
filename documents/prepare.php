<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';
require_once __DIR__ . '/../services/PreparationService.php';

$user   = require_permission('document.prepare');
$userId = (int)$user['user_id'];
$docId  = (int)($_GET['id'] ?? 0);

// ===================================================================
// NO ID: a picker of documents that still need preparation
// ===================================================================
if ($docId <= 0) {
    $q = trim($_GET['q'] ?? '');
    $sql = "SELECT d.document_id, d.document_name, d.year, d.status, d.updated_at,
                   c.client_name, t.name AS type_name, v.file_type, u.name AS uploader_name
            FROM documents d
            JOIN clients c ON c.client_id = d.client_id
            JOIN document_types t ON t.type_id = d.type_id
            JOIN users u ON u.user_id = d.uploaded_by
            LEFT JOIN document_versions v ON v.version_id = d.current_version_id
            WHERE d.status IN ('draft', 'returned') AND d.is_deleted = 0";
    $params = [];
    if ($q !== '') {
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $sql .= ' AND (d.document_name LIKE ? OR c.client_name LIKE ?)';
        array_push($params, $like, $like);
    }
    $sql .= ' ORDER BY d.updated_at DESC LIMIT 50';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    $pageTitle = 'Document Preparation';
    $activeNav = 'prepare';
    require __DIR__ . '/../includes/header.php';
    ?>
    <div class="df-page-header">
        <div>
            <h1>Document Preparation</h1>
            <p class="df-page-sub">Pick a document to continue, or capture new ones.</p>
        </div>
        <div class="df-page-actions">
            <?php if (can('document.capture')): ?>
                <a class="df-btn df-btn-primary" href="<?= e(url('documents/capture.php')) ?>"><i class="bi bi-camera"></i> Capture Document</a>
            <?php endif; ?>
        </div>
    </div>

    <form method="get" class="df-search" role="search">
        <i class="bi bi-search"></i>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search document or client" aria-label="Search">
        <button type="submit" class="df-btn df-btn-primary df-btn-sm">Search</button>
    </form>

    <?php if ($items): ?>
        <div class="df-card df-card-flush">
            <?php foreach ($items as $d): [$iconClass, $icon] = doc_icon($d['file_type']); ?>
                <a class="df-doc-row" style="margin:10px 14px" href="<?= e(url('documents/prepare.php?id=' . (int)$d['document_id'])) ?>">
                    <span class="df-doc-row-main">
                        <span class="df-doc-icon <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                        <span class="df-doc-info">
                            <strong><?= e($d['document_name']) ?> <span class="df-muted">(<?= (int)$d['year'] ?>)</span></strong>
                            <small><?= e($d['client_name']) ?> · <?= e($d['type_name']) ?> · <?= e(time_ago($d['updated_at'])) ?></small>
                        </span>
                    </span>
                    <span class="df-doc-row-status"><?= status_badge($d['status']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="df-card">
            <div class="df-empty">
                <div class="df-empty-icon"><i class="bi bi-emoji-smile"></i></div>
                <h3>Nothing waiting on you</h3>
                <p>There are no draft or returned documents to prepare right now.</p>
            </div>
        </div>
    <?php endif; ?>
    <?php require __DIR__ . '/../includes/footer.php'; ?>
    <?php
    exit;
}

// ===================================================================
// WORKSPACE FOR ONE DOCUMENT (no template, no compare)
// ===================================================================
$doc = DocumentService::find($docId);
if (!$doc) {
    flash_set('warning', 'That document could not be found.');
    redirect('documents/prepare.php');
}
if ((int)$doc['is_deleted'] === 1) {
    flash_set('warning', 'This document has been deleted and cannot be edited.');
    redirect('documents/library.php');
}

$editable = in_array($doc['status'], ['draft', 'returned'], true);

$curExt     = strtolower((string)$doc['file_type']);
$curIsImage = in_array($curExt, ['jpg', 'png'], true);
$curExtraction = PreparationService::extractionFor((int)$doc['current_version_id']);
$curFields  = [];
$curRawText = null;
if ($curExtraction) {
    $curRawText = PreparationService::rawText((int)$curExtraction['extraction_id']);
    foreach (PreparationService::extractionFields((int)$curExtraction['extraction_id']) as $fid => $row) {
        $curFields[] = ['field_id' => $fid, 'label' => $row['label'], 'value' => (int)$row['is_confirmed'] === 1 ? $row['confirmed_value'] : $row['extracted_value']];
    }
}

$bootstrap = [
    'documentId'          => $docId,
    'currentVersionId'    => (int)$doc['current_version_id'],
    'currentIsImage'      => $curIsImage,
    'currentExtractionId' => $curExtraction ? (int)$curExtraction['extraction_id'] : null,
    'currentRawText'      => $curRawText,
    'currentFields'       => $curFields,
    'currentDownloadUrl'  => url('files/download.php?v=' . (int)$doc['current_version_id']),
    'currentInlineUrl'    => $curIsImage ? url('files/download.php?v=' . (int)$doc['current_version_id'] . '&inline=1') : null,
    'editable'            => $editable,
];

$pageTitle    = $doc['document_name'];
$activeNav    = 'prepare';
$extraScripts = ['assets/js/prepare.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('documents/view.php?id=' . $docId)) ?>"><i class="bi bi-arrow-left"></i> Document</a>
        <h1><?= e($doc['document_name']) ?> <span class="df-muted">(<?= (int)$doc['year'] ?>)</span></h1>
        <p class="df-page-sub"><?= e($doc['client_name']) ?> · <?= e($doc['type_name']) ?> · <?= status_badge($doc['status']) ?></p>
    </div>
</div>

<?php if (!$editable): ?>
    <div class="df-alert df-alert-info">
        <i class="bi bi-info-circle"></i>
        This document is <strong><?= e(DocumentService::statuses()[$doc['status']] ?? $doc['status']) ?></strong> and can no longer be edited here.
    </div>
<?php endif; ?>

<div id="prepareRoot" class="df-workspace df-workspace-single" data-bootstrap='<?= e(json_encode($bootstrap)) ?>'>
    <section class="df-card df-panel" data-panel="current">
        <div class="df-panel-head">
            <h3 class="df-card-title"><i class="bi bi-file-earmark-text"></i> Document</h3>
        </div>

        <div class="df-panel-preview">
            <?php if ($curIsImage): ?>
                <a href="<?= e($bootstrap['currentInlineUrl']) ?>" target="_blank" rel="noopener" class="df-preview df-preview-sm">
                    <img src="<?= e($bootstrap['currentInlineUrl']) ?>" alt="Document">
                </a>
            <?php else: [$iconClass, $icon] = doc_icon($curExt); ?>
                <div class="df-doc-card">
                    <span class="df-doc-icon <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                    <span class="df-doc-info"><strong><?= e($doc['original_name'] ?: $doc['document_name']) ?></strong><small><?= e(strtoupper($curExt)) ?></small></span>
                    <a class="df-btn df-btn-outline df-btn-sm" href="<?= e($bootstrap['currentDownloadUrl']) ?>">Download</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="df-copybox" data-copybox="current"></div>
        <details class="df-fields-toggle">
            <summary>Break into labeled fields <span class="df-muted">(optional)</span></summary>
            <div class="df-panel-fields" data-fields="current"></div>
        </details>

        <div class="df-loading-box" data-loading="current" hidden>
            <div class="df-progress"><div class="df-progress-bar" data-loading-bar></div></div>
            <small class="df-hint"><span class="df-spinner"></span> <span data-loading-text>Processing…</span></small>
        </div>
    </section>
</div>

<?php if ($editable): ?>
<div class="df-workspace-actions">
    <a
        class="df-btn df-btn-primary"
        href="<?= e(url('documents/prepare.php')) ?>"
    >
        <i class="bi bi-check2-circle"></i> Done
    </a>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>