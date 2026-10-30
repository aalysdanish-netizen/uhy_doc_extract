<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/ClientService.php';
require_once __DIR__ . '/../services/DocumentTypeService.php';
require_once __DIR__ . '/../services/DocumentService.php';
require_once __DIR__ . '/../services/UploadService.php';

$user   = require_permission('document.capture');
$userId = (int)$user['user_id'];

$clients  = ClientService::activeList();
$types    = DocumentTypeService::activeList();
$thisYear = (int)date('Y');

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
$errors = [];

$form = [
    'client_id'     => (int)($_GET['client'] ?? 0),
    'type_id'       => (int)($_GET['type'] ?? 0),
    'year'          => $thisYear,
    'document_name' => '',
];

const MAX_FILES = 10;

if (is_post()) {
    $form['client_id']     = (int)($_POST['client_id'] ?? 0);
    $form['type_id']       = (int)($_POST['type_id'] ?? 0);
    $form['year']          = (int)($_POST['year'] ?? 0);
    $form['document_name'] = trim(preg_replace('/\s+/', ' ', (string)($_POST['document_name'] ?? '')));

    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors['files'] = 'The files are larger than the server allows. Please choose fewer or smaller files.';
    } elseif (!csrf_verify()) {
        $errors['general'] = 'Your session expired. Please refresh the page and try again.';
    } else {
        $clientName = null;
        foreach ($clients as $c) { if ((int)$c['client_id'] === $form['client_id']) $clientName = $c['client_name']; }
        if ($clientName === null) $errors['client_id'] = 'Please select a client.';

        $typeName = null;
        foreach ($types as $t) { if ((int)$t['type_id'] === $form['type_id']) $typeName = $t['name']; }
        if ($typeName === null) $errors['type_id'] = 'Please select a document type.';

        if ($form['year'] < 2000 || $form['year'] > $thisYear + 1) {
            $errors['year'] = 'Please choose a valid year.';
        }
        if ($form['document_name'] === '' && $typeName !== null) {
            $form['document_name'] = $typeName;
        }
        if ($form['document_name'] === '' || mb_strlen($form['document_name']) > 160) {
            $errors['document_name'] = 'Enter a document name (maximum 160 characters).';
        }

        $fileCount = empty($_FILES['files']['name']) ? 0 : count(array_filter($_FILES['files']['name']));
        if ($fileCount === 0) {
            $errors['files'] = 'Please add at least one photo or file.';
        } elseif ($fileCount > MAX_FILES) {
            $errors['files'] = 'You can upload up to ' . MAX_FILES . ' files at once.';
        }

        if (!$errors) {
            // Validate every file BEFORE storing any of them, so a bad file doesn't leave a partial batch.
            $validated = [];
            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $_FILES['files']['name'][$i], 'type' => $_FILES['files']['type'][$i],
                    'tmp_name' => $_FILES['files']['tmp_name'][$i], 'error' => $_FILES['files']['error'][$i], 'size' => $_FILES['files']['size'][$i],
                ];
                [$fileError, $ext] = UploadService::validate($file);
                if ($fileError) {
                    $errors['files'] = 'File ' . ($i + 1) . ' (' . e($file['name']) . '): ' . $fileError;
                    break;
                }
                $validated[] = ['file' => $file, 'ext' => $ext];
            }

            if (!$errors) {
                $createdIds = [];
                $stored = [];
                try {
                    foreach ($validated as $idx => $v) {
                        $s = UploadService::store($v['file'], $v['ext']);
                        $stored[] = $s;
                        $name = $fileCount > 1 ? $form['document_name'] . ' (' . ($idx + 1) . ')' : $form['document_name'];
                        $docId = DocumentService::create([
                            'client_id'           => $form['client_id'],
                            'client_name'         => $clientName,
                            'type_id'             => $form['type_id'],
                            'document_name'       => $name,
                            'year'                => $form['year'],
                            'template_version_id' => 0,
                        ], $s, $v['ext'], $userId);
                        $createdIds[] = $docId;
                    }

                    $count = count($createdIds);
                    flash_set('success', $count > 1 ? $count . ' documents uploaded.' : 'Document uploaded.');
                    $next = url('documents/batch.php?ids=' . implode(',', $createdIds));

                    if ($isAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['ok' => true, 'redirect' => $next]);
                        exit;
                    }
                    header('Location: ' . $next);
                    exit;
                } catch (Throwable $e) {
                    foreach ($stored as $s) { UploadService::delete($s['path']); }
                    error_log('Batch upload failed: ' . $e->getMessage());
                    $errors['general'] = 'The documents could not be saved. Please try again.';
                }
            }
        }
    }

    if ($isAjax) {
        http_response_code(422);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }
}

$sel = fn($a, $b): string => (string)$a === (string)$b ? 'selected' : '';

$pageTitle    = 'Capture Document';
$activeNav    = 'prepare';
$extraScripts = ['assets/js/capture.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>Capture Document</h1>
        <p class="df-page-sub">Photograph or upload as many pages as you need, then extract and copy the text.</p>
    </div>
</div>

<?php if (!$clients): ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-building"></i></div>
            <h3>No clients available yet</h3>
            <p>A client is needed before a document can be uploaded.
               <?= $user['role'] === 'admin' ? 'Add the first one now.' : 'Please ask an Admin to add clients.' ?></p>
            <?php if ($user['role'] === 'admin'): ?>
                <a class="df-btn df-btn-primary df-btn-sm" href="<?= e(url('admin/clients/form.php')) ?>">Add Client</a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>

<form id="captureForm" class="df-capture" method="post" novalidate
      data-max-bytes="<?= (int)UploadService::MAX_BYTES ?>" data-max-files="<?= MAX_FILES ?>">
    <?= csrf_field() ?>

    <div id="formAlert" class="df-alert df-alert-danger" hidden></div>
    <?php if (!empty($errors['general'])): ?><div class="df-alert df-alert-danger"><?= e($errors['general']) ?></div><?php endif; ?>

    <!-- STEP 1: Photos / files -->
    <section class="df-card df-capture-card">
        <div class="df-step-label"><span class="df-step-num">1</span> Photos / Files</div>

        <div class="df-dropzone" id="dropzone">
            <div class="df-dz-empty" id="dzEmpty">
                <div class="df-dz-icon"><i class="bi bi-camera"></i></div>
                <h3>Capture Document</h3>
                <p>Take photos or choose files. Add as many as you need, up to <?= MAX_FILES ?>.</p>
                <div class="df-dz-buttons">
                    <button type="button" class="df-btn df-btn-primary df-cam-btn" id="btnCamera"><i class="bi bi-camera"></i> Take Photo</button>
                    <button type="button" class="df-btn df-btn-outline" id="btnGallery">
                        <span class="df-only-touch">Choose From Gallery</span><span class="df-only-fine">Choose Files</span>
                    </button>
                </div>
                <small class="df-hint">JPG, PNG, PDF, Word or Excel · up to <?= (int)(UploadService::MAX_BYTES / 1048576) ?> MB each</small>
            </div>
        </div>

        <div class="df-file-queue" id="fileQueue" hidden>
            <div class="df-file-queue-head">
                <strong><span id="queueCount">0</span> file(s) added</strong>
                <button type="button" class="df-btn df-btn-outline df-btn-sm" id="btnAddMore"><i class="bi bi-plus-lg"></i> Add More</button>
            </div>
            <div class="df-file-grid" id="fileGrid"></div>
        </div>

        <input type="file" id="inCamera"  accept="image/*" capture="environment" hidden>
        <input type="file" id="inGallery" accept="image/*,application/pdf,.pdf,.docx,.xlsx" multiple hidden>
        <small class="df-error" data-error-for="files" <?= isset($errors['files']) ? '' : 'hidden' ?>><?= e($errors['files'] ?? '') ?></small>
    </section>

    <!-- STEP 2: Details (applied to every file in the batch) -->
    <section class="df-card df-capture-card">
        <div class="df-step-label"><span class="df-step-num">2</span> Document details</div>
        <p class="df-muted" style="margin-top:-10px">These details apply to every file you added above.</p>

        <div class="df-form-grid">
            <div class="df-field">
                <label class="df-label" for="client_id">Client</label>
                <select class="df-input" id="client_id" name="client_id">
                    <option value="">Select a client…</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int)$c['client_id'] ?>" <?= $sel($form['client_id'], $c['client_id']) ?>><?= e($c['client_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="df-error" data-error-for="client_id" <?= isset($errors['client_id']) ? '' : 'hidden' ?>><?= e($errors['client_id'] ?? '') ?></small>
            </div>

            <div class="df-field">
                <label class="df-label" for="type_id">Document type</label>
                <select class="df-input" id="type_id" name="type_id">
                    <option value="">Select a type…</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= (int)$t['type_id'] ?>" <?= $sel($form['type_id'], $t['type_id']) ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="df-error" data-error-for="type_id" <?= isset($errors['type_id']) ? '' : 'hidden' ?>><?= e($errors['type_id'] ?? '') ?></small>
            </div>

            <div class="df-field">
                <label class="df-label" for="year">Financial year</label>
                <select class="df-input" id="year" name="year">
                    <?php for ($y = $thisYear + 1; $y >= $thisYear - 10; $y--): ?>
                        <option value="<?= $y ?>" <?= $sel($form['year'], $y) ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <small class="df-error" data-error-for="year" <?= isset($errors['year']) ? '' : 'hidden' ?>><?= e($errors['year'] ?? '') ?></small>
            </div>

            <div class="df-field">
                <label class="df-label" for="document_name">Document name</label>
                <input class="df-input" type="text" id="document_name" name="document_name" maxlength="160"
                       value="<?= e($form['document_name']) ?>" placeholder="Filled in from the type">
                <small class="df-hint" id="nameHint">With more than one file, " (1)", " (2)"… is added automatically.</small>
                <small class="df-error" data-error-for="document_name" <?= isset($errors['document_name']) ? '' : 'hidden' ?>><?= e($errors['document_name'] ?? '') ?></small>
            </div>
        </div>

        <div class="df-upload-status" id="uploadStatus" hidden>
            <div class="df-progress"><div class="df-progress-bar" id="uploadBar" style="width:0%"></div></div>
            <small class="df-hint"><span class="df-spinner"></span> <span id="uploadText">Uploading…</span></small>
        </div>

        <div class="df-form-actions">
            <a class="df-btn df-btn-ghost" href="<?= e(url('dashboard.php')) ?>">Cancel</a>
            <button type="submit" class="df-btn df-btn-primary" id="btnUpload"><i class="bi bi-cloud-arrow-up"></i> Upload &amp; Extract</button>
        </div>
    </section>
</form>

<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>