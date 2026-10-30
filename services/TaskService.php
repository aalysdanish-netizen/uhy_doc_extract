<?php
/**
 * services/TaskService.php
 */
class TaskService
{
    public static function statuses(): array
    {
        return [
            'new' => 'New', 'uploaded' => 'Uploaded', 'processing' => 'Processing',
            'in_progress' => 'In Progress', 'pending_review' => 'Pending Review', 'completed' => 'Completed',
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT t.*, c.client_name, d.document_name, d.year AS document_year, d.status AS document_status,
                    a.name AS assignee_name, cb.name AS created_by_name
             FROM tasks t
             LEFT JOIN clients c ON c.client_id = t.client_id
             LEFT JOIN documents d ON d.document_id = t.document_id
             JOIN users a ON a.user_id = t.assigned_to
             JOIN users cb ON cb.user_id = t.created_by
             WHERE t.task_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Marks it seen (clears the "new" glow / badge contribution) the first time the assignee opens it. */
    public static function markSeen(int $taskId, int $userId): void
    {
        db()->prepare('UPDATE tasks SET seen_at = NOW() WHERE task_id = ? AND assigned_to = ? AND seen_at IS NULL')
            ->execute([$taskId, $userId]);
    }

    /**
     * $scope: 'mine' or 'all' (all = Admin only, enforced by the caller).
     * $status: '' (all open), 'completed', or a specific status key.
     */
    public static function search(int $viewerId, string $scope, string $status, string $q, int $limit, int $offset): array
    {
        $sql = 'SELECT t.*, c.client_name, d.document_name, d.year AS document_year, a.name AS assignee_name
                FROM tasks t
                LEFT JOIN clients c ON c.client_id = t.client_id
                LEFT JOIN documents d ON d.document_id = t.document_id
                JOIN users a ON a.user_id = t.assigned_to
                WHERE 1=1';
        $p = [];

        if ($scope === 'mine') {
            $sql .= ' AND t.assigned_to = ?';
            $p[] = $viewerId;
        }
        if ($status === 'completed') {
            $sql .= " AND t.status = 'completed'";
        } elseif ($status !== '') {
            $sql .= ' AND t.status = ?';
            $p[] = $status;
        } else {
            $sql .= " AND t.status <> 'completed'";
        }
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $sql .= ' AND (t.title LIKE ? OR c.client_name LIKE ?)';
            array_push($p, $like, $like);
        }

        $sql .= ' ORDER BY FIELD(t.priority, "urgent","high","medium","low"),
                  (t.due_date IS NULL), t.due_date ASC, t.created_at DESC
                  LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;

        $stmt = db()->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public static function count(int $viewerId, string $scope, string $status, string $q): int
    {
        $sql = 'SELECT COUNT(*) FROM tasks t JOIN clients c ON c.client_id = t.client_id WHERE 1=1';
        // clients join above may exclude tasks with null client_id, so use LEFT JOIN consistently:
        $sql = 'SELECT COUNT(*) FROM tasks t LEFT JOIN clients c ON c.client_id = t.client_id WHERE 1=1';
        $p = [];
        if ($scope === 'mine') { $sql .= ' AND t.assigned_to = ?'; $p[] = $viewerId; }
        if ($status === 'completed') { $sql .= " AND t.status = 'completed'"; }
        elseif ($status !== '') { $sql .= ' AND t.status = ?'; $p[] = $status; }
        else { $sql .= " AND t.status <> 'completed'"; }
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $sql .= ' AND (t.title LIKE ? OR c.client_name LIKE ?)';
            array_push($p, $like, $like);
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($p);
        return (int)$stmt->fetchColumn();
    }

    /** Staff can move their own task forward manually only between these open states. */
    public static function updateStatus(int $taskId, string $status, int $userId): array
    {
        $task = self::find($taskId);
        if (!$task) {
            return ['ok' => false, 'error' => 'Task not found.'];
        }
        if ((int)$task['assigned_to'] !== $userId) {
            return ['ok' => false, 'error' => 'This task is not assigned to you.'];
        }
        if (!in_array($status, ['in_progress', 'completed'], true)) {
            return ['ok' => false, 'error' => 'That status change is not allowed here.'];
        }
        if (in_array($task['status'], ['pending_review', 'completed'], true)) {
            return ['ok' => false, 'error' => 'This task cannot be changed manually right now.'];
        }

        $completedSql = $status === 'completed' ? ', completed_at = NOW()' : '';
        db()->prepare("UPDATE tasks SET status = ?$completedSql WHERE task_id = ?")->execute([$status, $taskId]);
        AuditService::log($userId, 'task_status_changed', 'task', $taskId, 'Task "' . $task['title'] . '" set to ' . $status, $task['status'], $status);

        return ['ok' => true];
    }
}