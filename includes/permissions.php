<?php
/**
 * includes/permissions.php
 * ONE place that decides who may do what.
 *
 * Usage in a page:      $user = require_permission('document.export');
 * Usage in a template:  <?php if (can('document.approve')): ?> ... <?php endif; ?>
 *
 * To change what interns/staff can do, edit permission_map() below. Nothing else.
 */

function permission_map(): array
{
    // What every staff member (staff, contract, or intern) can do
    $staffCommon = [
        'dashboard.view',
        'document.capture',
        'document.prepare',
        'document.library',
        'document.submit',      // submit a document for review
        'task.view',
        'community.use',
        'chat.use',
        'history.view',         // their own activity history
    ];

    // Staff and Contract get the same extended access - both are doing real audit work,
    // Contract is just a different employment type, not a lower access tier.
    $seniorExtras = [
        'document.export',
        'document.approve',
        'task.create',
        'task.assign',
        'files.view',
    ];

    return [
        // '*' means "everything", including all admin pages
        'admin' => ['*'],

        'staff'    => array_merge($staffCommon, $seniorExtras),
        'contract' => array_merge($staffCommon, $seniorExtras),

        // Interns: same as staff, but cannot export, approve, assign tasks or browse Files
        'intern' => $staffCommon,
    ];
}

/** Which permission group does this user belong to? admin | staff | contract | intern */
function permission_profile(array $user): string
{
    if ($user['role'] === 'admin') {
        return 'admin';
    }
    return in_array($user['staff_type'], ['staff', 'contract', 'intern'], true) ? $user['staff_type'] : 'staff';
}

/** Can this user (default: the logged-in user) do this? */
function can(string $permission, ?array $user = null): bool
{
    $user ??= current_user();
    if ($user === null) {
        return false;
    }

    $allowed = permission_map()[permission_profile($user)] ?? [];
    return in_array('*', $allowed, true) || in_array($permission, $allowed, true);
}

/** Protect a whole page: login required AND the permission required. */
function require_permission(string $permission): array
{
    $user = require_login();

    if (!can($permission, $user)) {
        http_response_code(403);
        exit('403 - You do not have permission to open this page.');
    }

    return $user;
}