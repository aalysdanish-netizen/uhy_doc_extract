<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';
require_once __DIR__ . '/../services/PreparationService.php';

$user   = require_permission('document.prepare');
$userId = (int)$user['user_id'];

$ids = array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? ''))));
$docs = [];
foreach (array_unique($ids) as $id) {
    $doc = DocumentService::find($id);
    // Only your own recent uploads (or Admin) may open a batch, to prevent guessing IDs.
    if ($doc && (int)$doc['is_deleted'] === 0 && ((int)$doc['uploaded_by'] === $userId || $user['role'] === 'admin')) {
        $docs[] = $doc;
    }
}

if (!$docs) {
    flash_set('warning', 'Those documents could not be found.');
    redirect('documents/capture.php');
}

$panels = [];
foreach ($docs as $doc) {
    $ext = strtolower((string)$doc['file_type']);
    $extraction = PreparationService::extractionFor((int)$doc['current_version_id']);
    $fields = [];
    $rawText = null;
    if ($extraction) {
        $rawText = PreparationService::rawText((int)$extraction['extraction_id']);
        foreach (PreparationService::extractionFields((int)$extraction['extraction_id']) as $fid => $row) {
            $fields[] = ['field_id' => $fid, 'label' => $row['label'], 'value' => (int)$row['is_confirmed'] === 1 ? $row['confirmed_value'] : $row['extracted_value']];
        }
    }
    $panels[] = [
        'documentId'    => (int)$doc['document_id'],
        'documentName'  => $doc['document_name'],
        'versionId'     => (int)$doc['current_version_id'],
        'isImage'       => in_array($ext, ['jpg', 'png'], true),
        'fileType'      => $ext,
        'originalName'  => $doc['original_name'],
        'downloadUrl'   => url('files/download.php?v=' . (int)$doc['current_version_id']),
        'inlineUrl'     => in_array($ext, ['jpg', 'png'], true) ? url('files/download.php?v=' . (int)$doc['current_version_id'] . '&inline=1') : null,
        'extractionId'  => $extraction ? (int)$extraction['extraction_id'] : null,
        'rawText'       => $rawText,
        'fields'        => $fields,
        'viewUrl'       => url('documents/view.php?id=' . (int)$doc['document_id']),
    ];
}

$pageTitle    = count($panels) > 1 ? count($panels) . ' Documents' : $panels[0]['documentName'];
$activeNav    = 'prepare';
$extraScripts = ['assets/js/batch.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1><?= count($panels) ?> document<?= count($panels) === 1 ? '' : 's' ?> uploaded</h1>
        <p class="df-page-sub">Extract each one, then copy the text into your Word or Excel file.</p>
    </div>
    <div class="df-page-actions">
        <?php if (count($panels) > 1): ?>
            <button type="button" class="df-btn df-btn-primary" id="btnExtractAll"><i class="bi bi-magic"></i> Extract All</button>
        <?php endif; ?>
        <a class="df-btn df-btn-outline" href="<?= e(url('documents/capture.php')) ?>"><i class="bi bi-plus-lg"></i> Upload More</a>
    </div>
</div>

<div id="batchRoot" class="df-batch-grid" data-panels='<?= e(json_encode($panels)) ?>'>
    <?php foreach ($panels as $p): ?>
        <section class="df-card df-batch-card" data-batch-card="<?= $p['documentId'] ?>">
            <div class="df-batch-card-head">
                <strong><?= e($p['documentName']) ?></strong>
                <a class="df-link" href="<?= e($p['viewUrl']) ?>">View document</a>
            </div>

            <div class="df-panel-preview">
                <?php if ($p['isImage']): ?>
                    <a href="<?= e($p['inlineUrl']) ?>" target="_blank" rel="noopener" class="df-preview df-preview-sm">
                        <img src="<?= e($p['inlineUrl']) ?>" alt="">
                    </a>
                <?php else: [$ic, $icon] = doc_icon($p['fileType']); ?>
                    <div class="df-doc-card">
                        <span class="df-doc-icon <?= e($ic) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                        <span class="df-doc-info"><strong><?= e($p['originalName']) ?></strong><small><?= e(strtoupper($p['fileType'])) ?></small></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="df-copybox" data-copybox></div>
            <div class="df-panel-fields" data-fields></div>

            <div class="df-loading-box" data-loading hidden>
                <div class="df-progress"><div class="df-progress-bar" data-loading-bar></div></div>
                <small class="df-hint"><span class="df-spinner"></span> <span data-loading-text>Processing…</span></small>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>