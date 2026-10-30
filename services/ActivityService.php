<?php
/**
 * services/ActivityService.php
 * Reads from audit_logs for the History (personal) and System Activity (admin) pages.
 */
class ActivityService
{
    /** Buckets used by both the personal and system-wide filters. */
    public static function categories(): array
    {
        return [
            'all'      => ['label' => 'All',       'actions' => null],
            'documents'=> ['label' => 'Documents',  'actions' => ['document_uploaded', 'document_version_saved', 'document_submitted', 'document_approved', 'document_returned', 'document_deleted', 'document_restored', 'ocr_run', 'comparison_run']],
            'tasks'    => ['label' => 'Tasks',      'actions' => ['task_status_changed']],
            'users'    => ['label' => 'Users',      'actions' => ['user_created', 'user_updated', 'user_activated', 'user_deactivated', 'password_reset', 'password_changed']],
            'clients'  => ['label' => 'Clients',    'actions' => ['client_created', 'client_updated', 'client_activated', 'client_deactivated']],
            'security' => ['label' => 'Security',   'actions' => ['login', 'logout', 'login_failed', 'login_blocked', 'login_locked']],
        ];
    }

    /** Human-friendly label + icon for an action, when a description isn't enough context. */
    public static function actionMeta(string $action): array
    {
        $map = [
            'login' => ['Signed in', 'bi-box-arrow-in-right'],
            'logout' => ['Signed out', 'bi-box-arrow-right'],
            'login_failed' => ['Failed sign-in attempt', 'bi-exclamation-triangle'],
            'login_blocked' => ['Sign-in blocked (inactive account)', 'bi-slash-circle'],
            'login_locked' => ['Sign-in locked (too many attempts)', 'bi-lock'],
            'password_changed' => ['Changed password', 'bi-key'],
            'password_reset' => ['Password reset', 'bi-key'],
            'user_created' => ['Created a user', 'bi-person-plus'],
            'user_updated' => ['Updated a user', 'bi-person-gear'],
            'user_activated' => ['Activated a user', 'bi-person-check'],
            'user_deactivated' => ['Deactivated a user', 'bi-person-x'],
            'client_created' => ['Added a client', 'bi-building-add'],
            'client_updated' => ['Updated a client', 'bi-building'],
            'client_activated' => ['Activated a client', 'bi-building-check'],
            'client_deactivated' => ['Deactivated a client', 'bi-building-slash'],
            'document_type_created' => ['Created a document type', 'bi-tag'],
            'document_type_updated' => ['Updated a document type', 'bi-tag'],
            'document_uploaded' => ['Uploaded a document', 'bi-cloud-arrow-up'],
            'ocr_run' => ['Extracted document information', 'bi-magic'],
            'comparison_run' => ['Compared document information', 'bi-arrow-left-right'],
            'document_version_saved' => ['Saved a document version', 'bi-save'],
            'document_submitted' => ['Submitted a document for review', 'bi-send-check'],
            'document_approved' => ['Approved a document', 'bi-check2-circle'],
            'document_returned' => ['Returned a document for changes', 'bi-arrow-return-left'],
            'document_deleted' => ['Deleted a document', 'bi-trash'],
            'document_restored' => ['Restored a document', 'bi-arrow-counterclockwise'],
            'file_downloaded' => ['Downloaded a file', 'bi-download'],
            'task_status_changed' => ['Updated a task', 'bi-check2-square'],
        ];
        return $map[$action] ?? [ucfirst(str_replace('_', ' ', $action)), 'bi-activity'];
    }

    private static function where(?int $userId, string $category, string $q, string $from, string $to): array
    {
        $sql = ' WHERE 1=1';
        $p = [];
        if ($userId !== null) {
            $sql .= ' AND a.user_id = ?';
            $p[] = $userId;
        }
        $cat = self::categories()[$category] ?? null;
        if ($cat && $cat['actions'] !== null) {
            $placeholders = implode(',', array_fill(0, count($cat['actions']), '?'));
            $sql .= " AND a.action IN ($placeholders)";
            array_push($p, ...$cat['actions']);
        }
        if ($q !== '') {
            $sql .= ' AND (a.description LIKE ? OR u.name LIKE ?)';
            $like = '%' . addcslashes($q, '%_\\') . '%';
            array_push($p, $like, $like);
        }
        if ($from !== '') { $sql .= ' AND a.created_at >= ?'; $p[] = $from . ' 00:00:00'; }
        if ($to !== '')   { $sql .= ' AND a.created_at <= ?'; $p[] = $to . ' 23:59:59'; }
        return [$sql, $p];
    }

    public static function search(?int $userId, string $category, string $q, string $from, string $to, int $limit, int $offset): array
    {
        [$where, $p] = self::where($userId, $category, $q, $from, $to);
        $stmt = db()->prepare(
            'SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.user_id = a.user_id'
            . $where . ' ORDER BY a.log_id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset
        );
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public static function count(?int $userId, string $category, string $q, string $from, string $to): int
    {
        [$where, $p] = self::where($userId, $category, $q, $from, $to);
        $stmt = db()->prepare('SELECT COUNT(*) FROM audit_logs a LEFT JOIN users u ON u.user_id = a.user_id' . $where);
        $stmt->execute($p);
        return (int)$stmt->fetchColumn();
    }

    /** Staff list for the System Activity "user" filter dropdown. */
    public static function staffWithActivity(): array
    {
        return db()->query(
            'SELECT DISTINCT u.user_id, u.name FROM users u JOIN audit_logs a ON a.user_id = u.user_id ORDER BY u.name'
        )->fetchAll();
    }
}