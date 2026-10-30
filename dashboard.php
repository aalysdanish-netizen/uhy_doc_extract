<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user   = require_login();
$userId = (int)$user['user_id'];
$isAdmin = $user['role'] === 'admin';

$firstName = explode(' ', trim($user['name']))[0];

$scalar = function (string $sql, array $p = []): int {
    $s = db()->prepare($sql);
    $s->execute($p);
    return (int)$s->fetchColumn();
};

// ---- 1. Work summary (my tasks grouped) ---------------------------------
$summary = ['todo' => 0, 'progress' => 0, 'review' => 0, 'done' => 0];
$stmt = db()->prepare('SELECT status, COUNT(*) AS c FROM tasks WHERE assigned_to = ? GROUP BY status');
$stmt->execute([$userId]);
foreach ($stmt->fetchAll() as $row) {
    switch ($row['status']) {
        case 'new': case 'uploaded':          $summary['todo']     += (int)$row['c']; break;
        case 'processing': case 'in_progress': $summary['progress'] += (int)$row['c']; break;
        case 'pending_review':                $summary['review']   += (int)$row['c']; break;
        case 'completed':                     $summary['done']     += (int)$row['c']; break;
    }
}
$totalTasks = array_sum($summary);
$percent    = $totalTasks > 0 ? (int)round($summary['done'] / $totalTasks * 100) : 0;

// ---- 2. Today's tasks (due today or overdue, not completed) ---------------
$stmt = db()->prepare(
    "SELECT t.task_id, t.title, t.status, t.priority, t.due_date, c.client_name, d.document_name
     FROM tasks t
     LEFT JOIN clients c   ON c.client_id = t.client_id
     LEFT JOIN documents d ON d.document_id = t.document_id
     WHERE t.assigned_to = ? AND t.status <> 'completed'
       AND t.due_date IS NOT NULL AND t.due_date <= CURDATE()
     ORDER BY t.due_date ASC, FIELD(t.priority, 'urgent', 'high', 'medium', 'low')
     LIMIT 6"
);
$stmt->execute([$userId]);
$todayTasks = $stmt->fetchAll();

// ---- 3. Recent documents ------------------------------------------------
$recentDocs = db()->query(
    'SELECT d.document_id, d.document_name, d.year, d.status, d.updated_at,
            c.client_name, v.file_type, u.name AS updated_by
     FROM documents d
     JOIN clients c ON c.client_id = d.client_id
     LEFT JOIN document_versions v ON v.version_id = d.current_version_id
     LEFT JOIN users u ON u.user_id = COALESCE(v.created_by, d.uploaded_by)
     WHERE d.is_deleted = 0
     ORDER BY d.updated_at DESC
     LIMIT 5'
)->fetchAll();

// ---- 4. Recent activity (admin: everyone, staff: only me; login noise hidden) ----
$noise = "'login','logout','login_failed','login_blocked','login_locked'";
if ($isAdmin) {
    $activity = db()->query(
        "SELECT a.description, a.action, a.created_at, a.user_id, u.name
         FROM audit_logs a LEFT JOIN users u ON u.user_id = a.user_id
         WHERE a.action NOT IN ($noise) ORDER BY a.log_id DESC LIMIT 6"
    )->fetchAll();
} else {
    $stmt = db()->prepare(
        "SELECT a.description, a.action, a.created_at, a.user_id, u.name
         FROM audit_logs a LEFT JOIN users u ON u.user_id = a.user_id
         WHERE a.user_id = ? AND a.action NOT IN ($noise) ORDER BY a.log_id DESC LIMIT 6"
    );
    $stmt->execute([$userId]);
    $activity = $stmt->fetchAll();
}

// ---- 5. Admin-only system overview -------------------------------------
$system = null;
if ($isAdmin) {
    $system = [
        'users'   => $scalar("SELECT COUNT(*) FROM users WHERE status = 'active'"),
        'clients' => $scalar("SELECT COUNT(*) FROM clients WHERE status = 'active'"),
        'docs'    => $scalar('SELECT COUNT(*) FROM documents WHERE is_deleted = 0'),
        'review'  => $scalar("SELECT COUNT(*) FROM documents WHERE status = 'in_review' AND is_deleted = 0"),
    ];
}

// ---- 6. Quick actions (only what this user is allowed to use) -------------
$quick = [
    ['perm' => 'document.capture', 'icon' => 'bi-camera',            'title' => 'Capture Document',       'sub' => 'Take or upload a photo', 'href' => 'documents/capture.php'],
    ['perm' => 'document.prepare', 'icon' => 'bi-file-earmark-text', 'title' => 'New Preparation',        'sub' => 'Compare with last year', 'href' => 'documents/prepare.php'],
    ['perm' => 'document.library', 'icon' => 'bi-folder2-open',      'title' => 'Document Library',       'sub' => 'Browse by client & year', 'href' => 'documents/library.php'],
    ['perm' => 'task.view',        'icon' => 'bi-check2-square',     'title' => 'My Tasks',               'sub' => 'See what needs doing',   'href' => 'tasks/index.php'],
];
if ($isAdmin) {
    $quick[] = ['perm' => 'admin.users', 'icon' => 'bi-person-plus', 'title' => 'Manage Users', 'sub' => 'Accounts and access', 'href' => 'admin/users/index.php'];
}

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="df-page-header">
    <div>
        <h1><?= e(greeting()) ?>, <?= e($firstName) ?> 👋</h1>
        <p class="df-page-sub">Here's what's happening with your work today.</p>
    </div>
    <div class="df-page-actions">
        <?php if (can('document.capture')): ?>
            <a class="df-btn df-btn-primary" href="<?= e(url('documents/capture.php')) ?>"><i class="bi bi-camera"></i> Capture Document</a>
        <?php endif; ?>
    </div>
</div>

<!-- Work summary -->
<div class="df-grid df-grid-stats">
    <a class="df-stat df-stat-blue" href="<?= e(url('tasks/index.php')) ?>">
        <span class="df-stat-icon"><i class="bi bi-list-task"></i></span>
        <div><div class="df-stat-value"><?= $summary['todo'] ?></div><div class="df-stat-label">To Do</div></div>
    </a>
    <a class="df-stat df-stat-amber" href="<?= e(url('tasks/index.php')) ?>">
        <span class="df-stat-icon"><i class="bi bi-hourglass-split"></i></span>
        <div><div class="df-stat-value"><?= $summary['progress'] ?></div><div class="df-stat-label">In Progress</div></div>
    </a>
    <a class="df-stat df-stat-purple" href="<?= e(url('tasks/index.php')) ?>">
        <span class="df-stat-icon"><i class="bi bi-eye"></i></span>
        <div><div class="df-stat-value"><?= $summary['review'] ?></div><div class="df-stat-label">Pending Review</div></div>
    </a>
    <a class="df-stat df-stat-green" href="<?= e(url('tasks/index.php')) ?>">
        <span class="df-stat-icon"><i class="bi bi-check2-circle"></i></span>
        <div><div class="df-stat-value"><?= $summary['done'] ?></div><div class="df-stat-label">Completed</div></div>
    </a>
</div>

<!-- Quick actions -->
<div class="df-quick">
    <?php foreach ($quick as $q): if (!can($q['perm'])) continue; ?>
        <a class="df-quick-card" href="<?= e(url($q['href'])) ?>">
            <span class="df-quick-icon"><i class="bi <?= e($q['icon']) ?>"></i></span>
            <span class="df-quick-text"><strong><?= e($q['title']) ?></strong><small><?= e($q['sub']) ?></small></span>
            <i class="bi bi-arrow-right df-quick-arrow"></i>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($system): ?>
    <h3 class="df-section-title">System overview</h3>
    <div class="df-grid df-grid-stats">
        <a class="df-stat df-stat-green" href="<?= e(url('admin/users/index.php')) ?>">
            <span class="df-stat-icon"><i class="bi bi-people"></i></span>
            <div><div class="df-stat-value"><?= $system['users'] ?></div><div class="df-stat-label">Active users</div></div>
        </a>
        <div class="df-stat df-stat-blue">
            <span class="df-stat-icon"><i class="bi bi-building"></i></span>
            <div><div class="df-stat-value"><?= $system['clients'] ?></div><div class="df-stat-label">Active clients</div></div>
        </div>
        <div class="df-stat df-stat-amber">
            <span class="df-stat-icon"><i class="bi bi-files"></i></span>
            <div><div class="df-stat-value"><?= $system['docs'] ?></div><div class="df-stat-label">Documents</div></div>
        </div>
        <div class="df-stat df-stat-purple">
            <span class="df-stat-icon"><i class="bi bi-eye"></i></span>
            <div><div class="df-stat-value"><?= $system['review'] ?></div><div class="df-stat-label">Awaiting review</div></div>
        </div>
    </div>
<?php endif; ?>

<div class="df-grid df-grid-main">

    <div class="df-stack">

        <!-- Today's tasks -->
        <section class="df-card df-card-flush">
            <div class="df-card-header">
                <h3 class="df-card-title">Today's Tasks</h3>
                <a class="df-link" href="<?= e(url('tasks/index.php')) ?>">View all</a>
            </div>
            <?php if ($todayTasks): ?>
                <div class="df-table-wrap">
                    <table class="df-table df-table-cards">
                        <thead><tr><th>Task</th><th>Client</th><th>Document</th><th>Due</th><th>Priority</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($todayTasks as $t): [$dueText, $dueClass] = due_label($t['due_date']); ?>
                            <tr>
                                <td class="df-td-title" data-label="Task">
                                    <a class="df-row-link" href="<?= e(url('tasks/view.php?id=' . (int)$t['task_id'])) ?>"><strong><?= e($t['title']) ?></strong></a>
                                </td>
                                <td data-label="Client"><?= e($t['client_name'] ?? '-') ?></td>
                                <td data-label="Document"><?= e($t['document_name'] ?? '-') ?></td>
                                <td data-label="Due"><span class="<?= e($dueClass) ?>"><?= e($dueText) ?></span></td>
                                <td data-label="Priority"><?= status_badge($t['priority']) ?></td>
                                <td data-label="Status"><?= status_badge($t['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="df-empty">
                    <div class="df-empty-icon"><i class="bi bi-emoji-smile"></i></div>
                    <h3>Nothing due today</h3>
                    <p>You have no tasks due today or overdue. Nice and clear.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent documents -->
        <section class="df-card df-card-flush">
            <div class="df-card-header">
                <h3 class="df-card-title">Recent Documents</h3>
                <a class="df-link" href="<?= e(url('documents/library.php')) ?>">Open library</a>
            </div>
            <?php if ($recentDocs): ?>
                <div class="df-table-wrap">
                    <table class="df-table df-table-cards">
                        <thead><tr><th>Document</th><th>Client</th><th>Updated By</th><th>Last Updated</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentDocs as $d): [$iconClass, $icon] = doc_icon($d['file_type']); ?>
                            <tr>
                                <td class="df-td-title" data-label="Document">
                                    <a class="df-row-link df-user-cell" href="<?= e(url('documents/view.php?id=' . (int)$d['document_id'])) ?>">
                                        <span class="df-doc-icon df-doc-icon-sm <?= e($iconClass) ?>"><i class="bi <?= e($icon) ?>"></i></span>
                                        <strong><?= e($d['document_name']) ?> <span class="df-muted">(<?= (int)$d['year'] ?>)</span></strong>
                                    </a>
                                </td>
                                <td data-label="Client"><?= e($d['client_name']) ?></td>
                                <td data-label="Updated By"><?= e($d['updated_by'] ?? '-') ?></td>
                                <td data-label="Last Updated"><?= e(time_ago($d['updated_at'])) ?></td>
                                <td data-label="Status"><?= status_badge($d['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="df-empty">
                    <div class="df-empty-icon"><i class="bi bi-file-earmark-plus"></i></div>
                    <h3>No documents yet</h3>
                    <p>Upload your first document to start.</p>
                    <?php if (can('document.capture')): ?>
                        <a class="df-btn df-btn-primary df-btn-sm" href="<?= e(url('documents/capture.php')) ?>">Upload Document</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="df-stack">

        <!-- Progress -->
        <section class="df-card">
            <h3 class="df-card-title">Your progress</h3>
            <?php if ($totalTasks > 0): ?>
                <div class="df-progress-head"><strong><?= $percent ?>%</strong><span class="df-muted"><?= $summary['done'] ?> of <?= $totalTasks ?> tasks completed</span></div>
                <div class="df-progress"><div class="df-progress-bar" style="width: <?= $percent ?>%"></div></div>
            <?php else: ?>
                <p class="df-muted" style="margin:0">No tasks assigned to you yet.</p>
            <?php endif; ?>
        </section>

        <!-- Recent activity -->
        <section class="df-card">
            <div class="df-card-header df-card-header-plain">
                <h3 class="df-card-title">Recent Activity</h3>
                <?php if ($isAdmin): ?><a class="df-link" href="<?= e(url('admin/activity/index.php')) ?>">See all</a><?php endif; ?>
            </div>
            <?php if ($activity): ?>
                <ul class="df-timeline">
                    <?php foreach ($activity as $a): ?>
                        <li>
                            <span class="df-timeline-text">
                                <strong><?= (int)$a['user_id'] === $userId ? 'You' : e($a['name'] ?? 'System') ?></strong>:
                                <?= e($a['description'] ?: ucfirst(str_replace('_', ' ', $a['action']))) ?>
                            </span>
                            <span class="df-timeline-time"><?= e(time_ago($a['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="df-empty">
                    <div class="df-empty-icon"><i class="bi bi-clock-history"></i></div>
                    <h3>No activity yet</h3>
                    <p>Your recent actions will show up here.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>