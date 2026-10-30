<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../services/TaskService.php';

$user   = require_permission('task.view');
$userId = (int)$user['user_id'];

$task = TaskService::find((int)($_GET['id'] ?? 0));
if (!$task) {
    flash_set('warning', 'That task could not be found.');
    redirect('tasks/index.php');
}
$isMine = (int)$task['assigned_to'] === $userId;
if (!$isMine && $user['role'] !== 'admin') {
    flash_set('danger', 'That task is not assigned to you.');
    redirect('tasks/index.php');
}
if ($isMine) {
    TaskService::markSeen((int)$task['task_id'], $userId);
}

if (is_post() && csrf_verify() && $isMine) {
    $result = TaskService::updateStatus((int)$task['task_id'], $_POST['status'] ?? '', $userId);
    flash_set($result['ok'] ? 'success' : 'danger', $result['ok'] ? 'Task updated.' : $result['error']);
    redirect('tasks/view.php?id=' . (int)$task['task_id']);
}

[$dueText, $dueClass] = due_label($task['due_date']);

$pageTitle = $task['title'];
$activeNav = 'tasks';
require __DIR__ . '/../includes/header.php';
?>

<div class="df-page-header">
    <div>
        <a class="df-back" href="<?= e(url('tasks/index.php')) ?>"><i class="bi bi-arrow-left"></i> My Tasks</a>
        <h1><?= e($task['title']) ?></h1>
        <p class="df-page-sub"><?= e($task['client_name'] ?? '-') ?></p>
    </div>
</div>

<div class="df-grid df-grid-doc">
    <div class="df-stack">
        <section class="df-card">
            <h3 class="df-card-title">Details</h3>
            <dl class="df-details">
                <div><dt>Assigned to</dt><dd><?= e($task['assignee_name']) ?></dd></div>
                <div><dt>Created by</dt><dd><?= e($task['created_by_name']) ?></dd></div>
                <div><dt>Priority</dt><dd><?= status_badge($task['priority']) ?></dd></div>
                <div><dt>Status</dt><dd><?= status_badge($task['status']) ?></dd></div>
                <div><dt>Due</dt><dd><span class="<?= e($dueClass) ?>"><?= e($dueText) ?></span></dd></div>
                <?php if ($task['document_id']): ?>
                    <div><dt>Document</dt><dd><a class="df-link" href="<?= e(url('documents/view.php?id=' . (int)$task['document_id'])) ?>"><?= e($task['document_name']) ?> (<?= (int)$task['document_year'] ?>)</a></dd></div>
                    <div><dt>Document status</dt><dd><?= status_badge($task['document_status']) ?></dd></div>
                <?php endif; ?>
            </dl>
        </section>
    </div>

    <div class="df-stack">
        <?php if ($isMine && !in_array($task['status'], ['pending_review', 'completed'], true)): ?>
        <section class="df-card">
            <h3 class="df-card-title">Update status</h3>
            <form method="post" class="df-stack">
                <?= csrf_field() ?>
                <?php if ($task['status'] !== 'in_progress'): ?>
                    <button type="submit" name="status" value="in_progress" class="df-btn df-btn-outline df-btn-block"><i class="bi bi-play-circle"></i> Mark as In Progress</button>
                <?php endif; ?>
                <button type="submit" name="status" value="completed" class="df-btn df-btn-primary df-btn-block"><i class="bi bi-check2-circle"></i> Mark as Completed</button>
            </form>
        </section>
        <?php elseif ($task['status'] === 'pending_review'): ?>
        <div class="df-alert df-alert-info"><i class="bi bi-info-circle"></i> This document is with a reviewer. The task will complete automatically once approved.</div>
        <?php endif; ?>

        <?php if ($task['document_id']): ?>
        <section class="df-card">
            <a class="df-btn df-btn-primary df-btn-block" href="<?= e(url('documents/prepare.php?id=' . (int)$task['document_id'])) ?>">
                <i class="bi bi-arrow-left-right"></i> Open Document Preparation
            </a>
        </section>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>