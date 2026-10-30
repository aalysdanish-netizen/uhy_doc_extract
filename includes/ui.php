<?php
/**
 * includes/ui.php
 * Display helpers and the sidebar menu definition.
 */

/** "2 minutes ago", "3 hours ago", or a date when it is older than a week. */
function time_ago(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   { $n = (int)floor($diff / 60);    return $n . ' minute' . ($n > 1 ? 's' : '') . ' ago'; }
    if ($diff < 86400)  { $n = (int)floor($diff / 3600);  return $n . ' hour'   . ($n > 1 ? 's' : '') . ' ago'; }
    if ($diff < 604800) { $n = (int)floor($diff / 86400); return $n . ' day'    . ($n > 1 ? 's' : '') . ' ago'; }
    return date('d M Y', $time);
}

function greeting(): string
{
    $hour = (int)date('G');
    if ($hour < 12) return 'Good morning';
    if ($hour < 18) return 'Good afternoon';
    return 'Good evening';
}

/** "Muhammad Alysdanish" -> "MA" */
function user_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(mb_substr(end($parts), 0, 1));
    }
    return $initials !== '' ? $initials : '?';
}

/** "Admin", "Staff", "Contract Staff" or "Intern" */
function role_label(array $user): string
{
    if ($user['role'] === 'admin') {
        return 'Admin';
    }
    $labels = ['staff' => 'Staff', 'contract' => 'Contract Staff', 'intern' => 'Intern'];
    return $labels[$user['staff_type']] ?? 'Staff';
}
/** Coloured status pill for tasks, documents, users and priorities. */
function status_badge(string $status): string
{
    $map = [
        // task statuses
        'new'            => ['New', 'info'],
        'uploaded'       => ['Uploaded', 'purple'],
        'processing'     => ['Processing', 'warning'],
        'in_progress'    => ['In Progress', 'warning'],
        'pending_review' => ['Pending Review', 'purple'],
        'completed'      => ['Completed', 'success'],
        // document statuses
        'draft'          => ['Draft', 'neutral'],
        'in_review'      => ['In Review', 'purple'],
        'returned'       => ['Returned', 'danger'],
        'approved'       => ['Approved', 'success'],
        'final'          => ['Final', 'success'],
        // users
        'active'         => ['Active', 'success'],
        'inactive'       => ['Inactive', 'neutral'],
        // priorities
        'low'            => ['Low', 'neutral'],
        'medium'         => ['Medium', 'info'],
        'high'           => ['High', 'warning'],
        'urgent'         => ['Urgent', 'danger'],
    ];

    [$label, $color] = $map[$status] ?? [ucfirst(str_replace('_', ' ', $status)), 'neutral'];
    return '<span class="df-badge df-badge-' . e($color) . '">' . e($label) . '</span>';
}

/**
 * The sidebar menu. Each item is only shown if the user has the permission.
 * 'badge' links an item to a key from get_badge_counts().
 * Pages that are not built yet will show "not found" until their phase.
 */
function nav_items(): array
{
    return [
        'main' => [
            ['key' => 'dashboard', 'label' => 'Dashboard',            'icon' => 'bi-house-door',        'href' => 'dashboard.php',            'perm' => 'dashboard.view'],
            ['key' => 'prepare',   'label' => 'Document Preparation', 'icon' => 'bi-file-earmark-text', 'href' => 'documents/prepare.php',    'perm' => 'document.prepare'],
            ['key' => 'library',   'label' => 'Document Library',     'icon' => 'bi-folder2-open',      'href' => 'documents/library.php',    'perm' => 'document.library'],
            ['key' => 'tasks',     'label' => 'My Tasks',             'icon' => 'bi-check2-square',     'href' => 'tasks/index.php',          'perm' => 'task.view',      'badge' => 'tasks'],
            ['key' => 'community', 'label' => 'Community',            'icon' => 'bi-people',            'href' => 'chat/community.php',       'perm' => 'community.use',  'badge' => 'community'],
            ['key' => 'chat',      'label' => 'Personal Chat',        'icon' => 'bi-chat-dots',         'href' => 'chat/personal.php',        'perm' => 'chat.use',       'badge' => 'chat'],
            ['key' => 'files',     'label' => 'Files',                'icon' => 'bi-files',             'href' => 'files/index.php',          'perm' => 'files.view'],
            ['key' => 'history',   'label' => 'History',              'icon' => 'bi-clock-history',     'href' => 'history/index.php',        'perm' => 'history.view'],
        ],
        'admin' => [
            ['key' => 'admin-users',     'label' => 'Users',           'icon' => 'bi-person-gear',    'href' => 'admin/users/index.php',          'perm' => 'admin.users'],
            ['key' => 'admin-clients',   'label' => 'Clients',         'icon' => 'bi-building',       'href' => 'admin/clients/index.php',        'perm' => 'admin.clients'],
            ['key' => 'admin-types',     'label' => 'Document Types',  'icon' => 'bi-tags',           'href' => 'admin/document-types/index.php', 'perm' => 'admin.templates'],
            ['key' => 'admin-templates', 'label' => 'Templates',       'icon' => 'bi-file-earmark-ruled', 'href' => 'admin/templates/index.php',  'perm' => 'admin.templates'],
            ['key' => 'admin-activity',  'label' => 'System Activity', 'icon' => 'bi-activity',       'href' => 'admin/activity/index.php',       'perm' => 'admin.activity'],
        ],
    ];
}

/** One notification row (used by the bell panel AND the All Notifications page). */
function render_notification_item(array $n): string
{
    $unread = (int)$n['is_read'] === 0;
    $href   = url('notifications/open.php?id=' . (int)$n['notification_id']);
    $type   = $n['type'];

    $html  = '<a class="df-notif df-notif-' . e($type) . ($unread ? ' is-unread' : '') . '" href="' . e($href) . '">';
    $html .= '<span class="df-notif-icon"><i class="bi ' . e(NotificationService::icon($type)) . '"></i></span>';
    $html .= '<span class="df-notif-body">';
    $html .= '<span class="df-notif-title">' . e($n['title']) . '</span>';
    if (!empty($n['message'])) {
        $html .= '<span class="df-notif-msg">' . e($n['message']) . '</span>';
    }
    $html .= '<span class="df-notif-time">' . e(time_ago($n['created_at'])) . '</span>';
    $html .= '</span>';
    $html .= '<span class="df-notif-dot" aria-hidden="true"></span>';
    $html .= '</a>';

    return $html;
}
/** Due date -> [text, css class] for task lists. */
function due_label(?string $date): array
{
    if (!$date) {
        return ['No due date', ''];
    }
    $today = strtotime(date('Y-m-d'));
    $due   = strtotime($date);
    $diff  = (int)round(($due - $today) / 86400);

    if ($diff === 0) return ['Due today', 'df-due-today'];
    if ($diff < 0)   return ['Overdue by ' . abs($diff) . ' day' . (abs($diff) > 1 ? 's' : ''), 'df-due-overdue'];
    if ($diff === 1) return ['Tomorrow', ''];
    return [date('d M Y', $due), ''];
}

/** File type -> [css class, icon] for document cards. */
function doc_icon(?string $type): array
{
    switch (strtolower((string)$type)) {
        case 'docx': case 'doc':                 return ['df-doc-docx',  'bi-file-earmark-word'];
        case 'xlsx': case 'xls': case 'csv':     return ['df-doc-xlsx',  'bi-file-earmark-excel'];
        case 'pdf':                              return ['df-doc-pdf',   'bi-file-earmark-pdf'];
        case 'jpg': case 'jpeg': case 'png':     return ['df-doc-image', 'bi-file-earmark-image'];
    }
    return ['df-doc-image', 'bi-file-earmark'];
}

/** "18 Mar 2026, 3:45 PM" or a dash when empty. */
function format_datetime(?string $datetime): string
{
    return $datetime ? date('d M Y, g:i A', strtotime($datetime)) : '-';
}
/** 1536000 -> "1.5 MB" */
function format_bytes(?int $bytes): string
{
    if (!$bytes) {
        return '-';
    }
    if ($bytes < 1048576) {
        return max(1, (int)round($bytes / 1024)) . ' KB';
    }
    return number_format($bytes / 1048576, 1) . ' MB';
}
/** Small "Name / @username" block used everywhere a person appears in chat. */
function render_identity(string $name, string $username): string
{
    return '<span class="df-identity"><strong>' . e($name) . '</strong><small>@' . e($username) . '</small></span>';
}
/**
 * Renders the avatar circle used everywhere: a photo if the user has one,
 * initials otherwise. $user needs 'name' and optionally 'avatar_path' and 'user_id'.
 * $size: '' (default ~40px), 'sm', or 'lg' - matches the existing df-avatar-sm/lg classes.
 */
function render_avatar(array $user, string $size = ''): string
{
    $class = 'df-avatar' . ($size ? ' df-avatar-' . $size : '');
    if (!empty($user['avatar_path']) && !empty($user['user_id'])) {
        $src = url('files/avatar.php?u=' . (int)$user['user_id']);
        return '<span class="' . $class . ' df-avatar-photo"><img src="' . e($src) . '" alt=""></span>';
    }
    return '<span class="' . $class . '">' . e(user_initials($user['name'] ?? '')) . '</span>';
}