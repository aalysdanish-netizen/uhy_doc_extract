<?php
/**
 * includes/header.php
 * The top of EVERY logged-in page: <head>, sidebar, top bar with the bell.
 *
 * A page must set these BEFORE including this file:
 *   $pageTitle  = 'Dashboard';     // shown in the browser tab and top bar
 *   $activeNav  = 'dashboard';     // which sidebar item is highlighted (see nav_items())
 * Optional:
 *   $extraScripts = ['assets/js/documents.js'];   // page-specific JS, loaded after app.js
 */

$user = current_user();
if ($user === null) {
    redirect('auth/login.php');        // safety net - pages should already call require_login()
}

$pageTitle = $pageTitle ?? 'Dashboard';
$activeNav = $activeNav ?? '';
$userId    = (int)$user['user_id'];

$badges       = get_badge_counts($userId);
$recentNotifs = NotificationService::recent($userId, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="df-base" content="<?= e(BASE_URL) ?>">
    <title><?= e($pageTitle) ?> · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body class="df-app-body">
<div class="df-shell">

    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="df-sidebar-overlay" data-sidebar-close></div>

    <div class="df-main">

        <!-- ============ TOP BAR ============ -->
        <header class="df-topbar">
            <button type="button" class="df-icon-btn df-menu-btn" data-sidebar-open aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>

            <a class="df-topbar-brand" href="<?= e(url('dashboard.php')) ?>">
    UHY WORKDOC EXTRACT
</a>
            <h2 class="df-topbar-title"><?= e($pageTitle) ?></h2>

            <div class="df-topbar-actions">

                <!-- Notification bell -->
                <div class="df-dropdown" data-dropdown>
                    <button type="button" class="df-icon-btn" data-dropdown-toggle
                            aria-label="Notifications" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <span class="df-icon-badge" data-badge="notifications" <?= $badges['notifications'] > 0 ? '' : 'hidden' ?>>
                            <?= e(badge_text($badges['notifications'])) ?>
                        </span>
                    </button>

                    <div class="df-dropdown-panel">
                        <div class="df-dropdown-head">
                            <strong>Notifications</strong>
                            <button type="button" class="df-link-btn" data-mark-all-read>Mark all as read</button>
                        </div>

                        <div class="df-notif-list">
                            <?php if ($recentNotifs): ?>
                                <?php foreach ($recentNotifs as $n): ?>
                                    <?= render_notification_item($n) ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="df-dropdown-empty">
                                    <i class="bi bi-bell-slash"></i>
                                    <span>You're all caught up.</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <a class="df-dropdown-foot" href="<?= e(url('notifications/index.php')) ?>">
                            View all notifications
                        </a>
                    </div>
                </div>

                <!-- Who am I (desktop only) -->
                <a class="df-topbar-user" href="<?= e(url('profile/index.php')) ?>">
                    <?= render_avatar($user) ?>
                    <span class="df-topbar-user-text">
                        <strong><?= e($user['name']) ?></strong>
                        <small><?= e(role_label($user)) ?></small>
                    </span>
                </a>
            </div>
        </header>

        <!-- ============ PAGE CONTENT ============ -->
        <main class="df-content">
            <?php foreach (flash_get() as $flash): ?>
                <div class="df-alert df-alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>