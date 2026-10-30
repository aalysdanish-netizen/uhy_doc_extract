<?php
/**
 * includes/sidebar.php
 * Included by header.php. Uses $user, $badges and $activeNav from there.
 */
$nav = nav_items();
?>
<aside class="df-sidebar" id="dfSidebar" aria-label="Main navigation">

    <div class="df-sidebar-head">
        <a class="df-brand" href="<?= e(url('dashboard.php')) ?>">
            <span class="df-brand-mark"><i class="bi bi-file-earmark-text"></i></span>
            <span>UHY WORKDOC EXTRACT</span>
        </a>
        <button type="button" class="df-sidebar-close" data-sidebar-close aria-label="Close menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <?php if (can('document.capture')): ?>
        <div class="df-sidebar-cta">
            <a class="df-btn df-btn-light df-btn-block" href="<?= e(url('documents/capture.php')) ?>">
                <i class="bi bi-camera"></i> Capture Document
            </a>
        </div>
    <?php endif; ?>

    <nav class="df-nav">
        <?php foreach ($nav['main'] as $item): ?>
            <?php if (!can($item['perm'])) continue; ?>
            <a class="df-nav-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
               href="<?= e(url($item['href'])) ?>">
                <i class="bi <?= e($item['icon']) ?>"></i>
                <span><?= e($item['label']) ?></span>
                <?php if (!empty($item['badge'])): $n = $badges[$item['badge']] ?? 0; ?>
                    <span class="df-nav-badge" data-badge="<?= e($item['badge']) ?>" <?= $n > 0 ? '' : 'hidden' ?>>
                        <?= e(badge_text($n)) ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <?php
        // Admin group: only render the heading if at least one item is allowed
        $adminItems = array_filter($nav['admin'], fn($i) => can($i['perm']));
        if ($adminItems):
        ?>
            <div class="df-nav-label">Admin</div>
            <?php foreach ($adminItems as $item): ?>
                <a class="df-nav-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
                   href="<?= e(url($item['href'])) ?>">
                    <i class="bi <?= e($item['icon']) ?>"></i>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>

    <div class="df-sidebar-foot">
        <a class="df-nav-link<?= $activeNav === 'profile' ? ' is-active' : '' ?>" href="<?= e(url('profile/index.php')) ?>">
            <i class="bi bi-person-circle"></i><span>My Profile</span>
        </a>
        <!-- Logout is a POST form with a CSRF token so other sites cannot log you out -->
        <form method="post" action="<?= e(url('auth/logout.php')) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="df-nav-link">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </button>
        </form>
    </div>
</aside>