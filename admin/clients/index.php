<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../services/ClientService.php';

$admin = require_permission('admin.clients');

$q      = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
if (!in_array($status, ['all', 'active', 'inactive'], true)) {
    $status = 'all';
}

$perPage = 15;
$total   = ClientService::count($q, $status);
$pages   = max(1, (int)ceil($total / $perPage));
$page    = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
$clients = ClientService::search($q, $status, $perPage, ($page - 1) * $perPage);
$counts  = ClientService::statusCounts();

$link = function (array $extra) use ($q, $status): string {
    $params = array_merge(['q' => $q, 'status' => $status === 'all' ? '' : $status], $extra);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return url('admin/clients/index.php' . ($params ? '?' . http_build_query($params) : ''));
};

$pageTitle    = 'Clients';
$activeNav    = 'admin-clients';
$extraScripts = ['assets/js/confirm.js'];
require __DIR__ . '/../../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1>Clients</h1>
        <p class="df-page-sub">The companies whose documents you prepare.</p>
    </div>
    <div class="df-page-actions">
        <a class="df-btn df-btn-primary" href="<?= e(url('admin/clients/form.php')) ?>"><i class="bi bi-plus-lg"></i> Add Client</a>
    </div>
</div>

<form method="get" class="df-search" role="search">
    <?php if ($status !== 'all'): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <i class="bi bi-search"></i>
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, registration no. or contact" aria-label="Search clients">
    <?php if ($q !== ''): ?>
        <a class="df-search-clear" href="<?= e($link(['q' => ''])) ?>" aria-label="Clear search"><i class="bi bi-x-lg"></i></a>
    <?php endif; ?>
    <button type="submit" class="df-btn df-btn-primary df-btn-sm">Search</button>
</form>

<div class="df-tabs">
    <?php foreach (['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive'] as $key => $label): ?>
        <a class="df-tab<?= $status === $key ? ' is-active' : '' ?>" href="<?= e($link(['status' => $key === 'all' ? '' : $key, 'page' => ''])) ?>">
            <?= e($label) ?> <span class="df-tab-count"><?= (int)$counts[$key] ?></span>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($clients): ?>
    <div class="df-card df-card-flush">
        <div class="df-table-wrap">
            <table class="df-table df-table-cards">
                <thead><tr><th>Client</th><th>Registration No.</th><th>Contact Person</th><th>Documents</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($clients as $c): ?>
                    <tr>
                        <td class="df-td-title" data-label="Client">
                            <div class="df-user-cell">
                                <span class="df-doc-icon df-doc-icon-sm df-doc-docx"><i class="bi bi-building"></i></span>
                                <strong><?= e($c['client_name']) ?></strong>
                            </div>
                        </td>
                        <td data-label="Registration No."><?= e($c['registration_no'] ?? '-') ?></td>
                        <td data-label="Contact"><?= e($c['contact_person'] ?? '-') ?></td>
                        <td data-label="Documents">
                            <?php if ((int)$c['doc_count'] > 0): ?>
                                <a class="df-link" href="<?= e(url('documents/library.php?client=' . (int)$c['client_id'])) ?>"><?= (int)$c['doc_count'] ?></a>
                            <?php else: ?>0<?php endif; ?>
                        </td>
                        <td data-label="Status"><?= status_badge($c['status']) ?></td>
                        <td class="df-td-actions" data-label="Actions">
                            <div class="df-actions">
                                <a class="df-action" href="<?= e(url('admin/clients/form.php?id=' . (int)$c['client_id'])) ?>" title="Edit"><i class="bi bi-pencil"></i><span>Edit</span></a>
                                <?php if ($c['status'] === 'active'): ?>
                                    <button type="button" class="df-action df-action-danger" title="Deactivate" data-confirm data-tone="danger"
                                            data-title="Deactivate this client?"
                                            data-text="<?= e($c['client_name']) ?> will no longer appear when uploading documents. Existing documents are kept."
                                            data-button="Deactivate" data-action="<?= e(url('admin/clients/actions.php')) ?>"
                                            data-fields="<?= e(json_encode(['client_id' => (int)$c['client_id'], 'action' => 'deactivate'])) ?>">
                                        <i class="bi bi-slash-circle"></i><span>Deactivate</span>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="df-action df-action-success" title="Activate" data-confirm data-tone="success"
                                            data-title="Activate this client?"
                                            data-text="<?= e($c['client_name']) ?> will be available again when uploading documents."
                                            data-button="Activate" data-action="<?= e(url('admin/clients/actions.php')) ?>"
                                            data-fields="<?= e(json_encode(['client_id' => (int)$c['client_id'], 'action' => 'activate'])) ?>">
                                        <i class="bi bi-check-circle"></i><span>Activate</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pages > 1): ?>
        <div class="df-pager">
            <?php if ($page > 1): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page - 1])) ?>"><i class="bi bi-chevron-left"></i> Previous</a><?php endif; ?>
            <span class="df-muted">Page <?= $page ?> of <?= $pages ?> · <?= $total ?> clients</span>
            <?php if ($page < $pages): ?><a class="df-btn df-btn-outline df-btn-sm" href="<?= e($link(['page' => $page + 1])) ?>">Next <i class="bi bi-chevron-right"></i></a><?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="df-card">
        <div class="df-empty">
            <div class="df-empty-icon"><i class="bi bi-building"></i></div>
            <h3><?= ($q !== '' || $status !== 'all') ? 'No clients match your search' : 'No clients yet' ?></h3>
            <p><?= ($q !== '' || $status !== 'all') ? 'Try a different search or clear the filters.' : 'Add your first client so staff can upload documents for them.' ?></p>
            <?php if ($q !== '' || $status !== 'all'): ?>
                <a class="df-btn df-btn-outline df-btn-sm" href="<?= e(url('admin/clients/index.php')) ?>">Clear filters</a>
            <?php else: ?>
                <a class="df-btn df-btn-primary df-btn-sm" href="<?= e(url('admin/clients/form.php')) ?>">Add Client</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../../includes/confirm-modal.php'; ?>
<?php require __DIR__ . '/../../includes/footer.php'; ?>