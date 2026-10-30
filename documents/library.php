<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/ClientService.php';
require_once __DIR__ . '/../services/DocumentTypeService.php';
require_once __DIR__ . '/../services/DocumentService.php';

$user = require_permission('document.library');
$deletedView = $user['role'] === 'admin' && ($_GET['deleted'] ?? '') === '1';

// ---- Clean the filters from the URL ----
$statuses = DocumentService::statuses();
$date = fn(string $v): string => preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';

$f = [
    'q'      => trim((string)($_GET['q'] ?? '')),
    'client' => (int)($_GET['client'] ?? 0),
    'type'   => (int)($_GET['type'] ?? 0),
    'year'   => (int)($_GET['year'] ?? 0),
    'status' => isset($statuses[$_GET['status'] ?? '']) ? $_GET['status'] : '',
    'staff'  => (int)($_GET['staff'] ?? 0),
    'from'   => $date((string)($_GET['from'] ?? '')),
    'to'     => $date((string)($_GET['to'] ?? '')),
];
$activeFilters = count(array_filter($f, fn($v) => $v !== '' && $v !== 0));

// ---- Query ----
const LIBRARY_LIMIT = 200;
$rows      = DocumentService::library($f, LIBRARY_LIMIT, $deletedView);
$truncated = count($rows) > LIBRARY_LIMIT;
$rows      = array_slice($rows, 0, LIBRARY_LIMIT);
$deletedCount = $user['role'] === 'admin' ? DocumentService::countDeleted() : 0;

// Group: client -> year -> documents
$groups = [];
foreach ($rows as $r) {
    $cid = (int)$r['client_id'];
    $groups[$cid]['name']  = $r['client_name'];
    $groups[$cid]['count'] = ($groups[$cid]['count'] ?? 0) + 1;
    $groups[$cid]['years'][(int)$r['year']][] = $r;
}
$openAll = $activeFilters > 0 || count($groups) <= 3;

// ---- Filter dropdown data ----
$allClients = db()->query('SELECT client_id, client_name FROM clients ORDER BY client_name')->fetchAll();
$allTypes   = db()->query('SELECT type_id, name FROM document_types ORDER BY name')->fetchAll();
$years      = DocumentService::filterYears();
$staffList  = DocumentService::filterStaff();
$sel = fn($a, $b): string => (string)$a === (string)$b ? 'selected' : '';

$pageTitle    = $deletedView ? 'Deleted Documents' : 'Document Library';
$activeNav    = 'library';
$extraScripts = ['assets/js/library.js', 'assets/js/confirm.js'];
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <?php if ($deletedView): ?>
            <a class="df-back" href="<?= e(url('documents/library.php')) ?>">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <h1>Deleted Documents</h1>
            <p class="df-page-sub">Documents removed from the library. Restore anything deleted by mistake.</p>
        <?php else: ?>
            <?php if ($f['client'] > 0): ?>
                <a class="df-back" href="<?= e(url('documents/library.php')) ?>">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            <?php endif; ?>
            <h1>Document Library</h1>
            <p class="df-page-sub">Browse documents by client and year. Pick a previous-year document to start next year's.</p>
        <?php endif; ?>
    </div>
    <div class="df-page-actions">
        <?php if (!$deletedView && can('document.capture')): ?>
            <a class="df-btn df-btn-primary" href="<?= e(url('documents/capture.php')) ?>"><i class="bi bi-camera"></i> Capture Document</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($deletedView): ?>
    <input type="hidden" id="dfDeletedView" value="1">
<?php endif; ?>

<!-- Filters (collapsed on phones) -->
<details class="df-filters" data-filters <?= $activeFilters ? 'open' : '' ?>>
    <summary>
        <i class="bi bi-funnel"></i> Search &amp; filters
        <?php if ($activeFilters): ?><span class="df-tab-count"><?= $activeFilters ?></span><?php endif; ?>
        <i class="bi bi-chevron-down df-filters-chevron"></i>
    </summary>
        <form method="get" class="df-filters-form">
        <?php if ($deletedView): ?><input type="hidden" name="deleted" value="1"><?php endif; ?>
        <div class="df-filter-grid">
            <div class="df-field df-filter-wide">
                <label class="df-label" for="q">Search</label>
                <input class="df-input" type="search" id="q" name="q" value="<?= e($f['q']) ?>" placeholder="Document or client name">
            </div>
            <div class="df-field">
                <label class="df-label" for="client">Client</label>
                <select class="df-input" id="client" name="client">
                    <option value="">All clients</option>
                    <?php foreach ($allClients as $c): ?>
                        <option value="<?= (int)$c['client_id'] ?>" <?= $sel($f['client'], $c['client_id']) ?>><?= e($c['client_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="df-field">
                <label class="df-label" for="type">Document type</label>
                <select class="df-input" id="type" name="type">
                    <option value="">All types</option>
                    <?php foreach ($allTypes as $t): ?>
                        <option value="<?= (int)$t['type_id'] ?>" <?= $sel($f['type'], $t['type_id']) ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="df-field">
                <label class="df-label" for="year">Year</label>
                <select class="df-input" id="year" name="year">
                    <option value="">All years</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $sel($f['year'], $y) ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="df-field">
                <label class="df-label" for="status">Status</label>
                <select class="df-input" id="status" name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($statuses as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $sel($f['status'], $key) ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="df-field">
                <label class="df-label" for="staff">Uploaded by</label>
                <select class="df-input" id="staff" name="staff">
                    <option value="">Anyone</option>
                    <?php foreach ($staffList as $s): ?>
                        <option value="<?= (int)$s['user_id'] ?>" <?= $sel($f['staff'], $s['user_id']) ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="df-field">
                <label class="df-label" for="from">Uploaded from</label>
                <input class="df-input" type="date" id="from" name="from" value="<?= e($f['from']) ?>">
            </div>
            <div class="df-field">
                <label class="df-label" for="to">Uploaded to</label>
                <input class="df-input" type="date" id="to" name="to" value="<?= e($f['to']) ?>">
            </div>
        </div>
        <div class="df-form-actions df-filter-actions">
            <?php if ($activeFilters): ?>
                <a class="df-btn df-btn-ghost" href="<?= e(url('documents/library.php' . ($deletedView ? '?deleted=1' : ''))) ?>">Clear</a>
            <?php endif; ?>
            <button type="submit" class="df-btn df-btn-primary"><i class="bi bi-search"></i> Apply</button>
        </div>
    </form>
</details>

<?php if ($groups): ?>
    <p class="df-muted df-result-count">
        <?= count($rows) ?> document<?= count($rows) === 1 ? '' : 's' ?> · <?= count($groups) ?> client<?= count($groups) === 1 ? '' : 's' ?>
    </p>

    <?php foreach ($groups as $cid => $g): ?>
        <details class="df-group" <?= $openAll ? 'open' : '' ?>>
            <summary class="df-group-head">
                <span class="df-group-icon"><i class="bi bi-building"></i></span>
                <span class="df-group-title"><strong><?= e($g['name']) ?></strong><small><?= (int)$g['count'] ?> document<?= $g['count'] === 1 ? '' : 's' ?></small></span>
                <i class="bi bi-chevron-down df-group-chevron"></i>
            </summary>

            <div class="df-group-body">
                <?php foreach ($g['years'] as $year => $docs): ?>
                    <div class="df-year"><span class="df-year-label"><i class="bi bi-calendar3"></i> <?= (int)$year ?></span><span class="df-year-line"></span></div>

                    <?php foreach ($docs as $d): [$iconClass, $icon] = doc_icon($d['file_type']); $hasFile = !empty($d['file_path']); ?>
                        <div class="df-doc-row">
                            <?php if ($deletedView): ?>
                                <a class="df-doc-row-main" href="<?= e(url('documents/view.php?id=' . (int)$d['document_id'])) ?>">
                                    <span class="df-doc-icon <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                                    <span class="df-doc-info">
                                        <strong><?= e($d['document_name']) ?></strong>
                                        <small>
                                            <?= e($d['type_name']) ?> · v<?= (int)$d['version_no'] ?> · <?= e($d['uploader_name']) ?>
                                            <br>Deleted by <?= e($d['deleted_by_name'] ?? '-') ?>
                                            · <?= !empty($d['deleted_at']) ? e(time_ago($d['deleted_at'])) : 'Unknown date' ?>
                                        </small>
                                    </span>
                                </a>
                                <span class="df-doc-row-status"><?= status_badge($d['status']) ?></span>
                                <div class="df-actions">
                                    <a class="df-action" href="<?= e(url('documents/view.php?id=' . (int)$d['document_id'])) ?>" title="View"><i class="bi bi-eye"></i><span>View</span></a>
                                    <?php if ($hasFile): ?>
                                        <a class="df-action" href="<?= e(url('files/download.php?v=' . (int)$d['version_id'])) ?>" title="Download"><i class="bi bi-download"></i><span>Download</span></a>
                                    <?php endif; ?>
                                    <button type="button" class="df-action df-action-success" title="Restore" data-confirm data-tone="success"
                                            data-title="Restore this document?" data-text="<?= e($d['document_name']) ?> will reappear in the library." data-button="Restore"
                                            data-action="<?= e(url('documents/restore.php')) ?>"
                                            data-fields="<?= e(json_encode(['document_id' => (int)$d['document_id']])) ?>">
                                        <i class="bi bi-arrow-counterclockwise"></i><span>Restore</span>
                                    </button>
                                </div>
                            <?php else: ?>
                                <a class="df-doc-row-main" href="<?= e(url('documents/view.php?id=' . (int)$d['document_id'])) ?>">
                                    <span class="df-doc-icon <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                                    <span class="df-doc-info">
                                        <strong><?= e($d['document_name']) ?></strong>
                                        <small><?= e($d['type_name']) ?> · v<?= (int)$d['version_no'] ?> · <?= e($d['uploader_name']) ?> · <?= e(time_ago($d['updated_at'])) ?></small>
                                    </span>
                                </a>
                                <span class="df-doc-row-status"><?= status_badge($d['status']) ?></span>
                                <div class="df-actions">
                                    <a class="df-action" href="<?= e(url('documents/view.php?id=' . (int)$d['document_id'])) ?>" title="View"><i class="bi bi-eye"></i><span>View</span></a>
                                    <?php if ($hasFile): ?>
                                        <a class="df-action" href="<?= e(url('files/download.php?v=' . (int)$d['version_id'])) ?>" title="Download"><i class="bi bi-download"></i><span>Download</span></a>

                                    <?php endif; ?>
                                    <?php if (DocumentService::canDelete($d, $user)): ?>
                                        <button type="button" class="df-action df-action-danger" title="Delete" data-confirm data-tone="danger"
                                                data-title="Delete this document?" data-text="<?= e($d['document_name']) ?> will be removed from the library. An Admin can restore it later if needed."
                                                data-button="Delete" data-action="<?= e(url('documents/delete.php')) ?>"
                                                data-fields="<?= e(json_encode(['document_id' => (int)$d['document_id']])) ?>">
                                            <i class="bi bi-trash"></i><span>Delete</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </details>
    <?php endforeach; ?>

    <?php if ($truncated): ?>
        <div class="df-alert df-alert-info"><i class="bi bi-info-circle"></i> Showing the first <?= LIBRARY_LIMIT ?> documents. Use the filters to narrow the list.</div>
    <?php endif; ?>

<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-folder2-open"></i></div>
            <h3><?= $activeFilters ? 'No documents match your filters' : 'No documents yet' ?></h3>
            <p><?= $activeFilters ? 'Try removing a filter or searching for something else.' : 'Upload your first document to start.' ?></p>
            <?php if ($activeFilters): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('documents/library.php')) ?>">Clear filters</a>
            <?php elseif (can('document.capture')): ?>
                <a class="df-btn df-btn-primary df-btn-sm" href="<?= e(url('documents/capture.php')) ?>">Upload Document</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php if (!$deletedView && $user['role'] === 'admin'): ?>
    <p class="df-deleted-link">
        <a href="<?= e(url('documents/library.php?deleted=1')) ?>">
            <i class="bi bi-trash3"></i> View deleted documents<?= $deletedCount > 0 ? ' (' . $deletedCount . ')' : '' ?>
        </a>
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/confirm-modal.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>