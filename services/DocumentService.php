<?php
/**
 * services/DocumentService.php
 * Documents, versions, the library query and the upload workflow.
 */
class DocumentService
{
    public static function statuses(): array
    {
        return ['draft' => 'Draft', 'in_review' => 'In Review', 'returned' => 'Returned', 'approved' => 'Approved', 'final' => 'Final'];
    }

    // ------------------------------------------------------------------
    // READING
    // ------------------------------------------------------------------

    /** One document with its client, type, uploader, current file and template info. */
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT d.*, c.client_name, t.name AS type_name, u.name AS uploader_name, du.name AS deleted_by_name,
                    v.version_no AS current_version_no, v.file_type, v.file_path, v.original_name, v.file_size,
                    pd.document_id AS tpl_document_id, pd.document_name AS tpl_name, pd.year AS tpl_year
             FROM documents d
             JOIN clients c        ON c.client_id = d.client_id
             JOIN document_types t ON t.type_id   = d.type_id
             JOIN users u          ON u.user_id   = d.uploaded_by
             LEFT JOIN users du             ON du.user_id = d.deleted_by
             LEFT JOIN document_versions v  ON v.version_id  = d.current_version_id
             LEFT JOIN document_versions pv ON pv.version_id = d.prepared_from_version_id
             LEFT JOIN documents pd         ON pd.document_id = pv.document_id
             WHERE d.document_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Can this user delete this document? Admin: always. Uploader: only while still draft/returned. */
    public static function canDelete(array $doc, array $user): bool
    {
        if ((int)$doc['is_deleted'] === 1) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        return (int)$doc['uploaded_by'] === (int)$user['user_id'] && in_array($doc['status'], ['draft', 'returned'], true);
    }

    public static function softDelete(int $documentId, int $userId): array
    {
        $doc = self::find($documentId);
        if (!$doc) {
            return ['ok' => false, 'error' => 'Document not found.'];
        }
        if ((int)$doc['is_deleted'] === 1) {
            return ['ok' => false, 'error' => 'This document has already been deleted.'];
        }

        db()->prepare('UPDATE documents SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE document_id = ?')
            ->execute([$userId, $documentId]);

        AuditService::log($userId, 'document_deleted', 'document', $documentId,
            'Deleted ' . $doc['document_name'] . ' (' . $doc['year'] . ') for ' . $doc['client_name'], $doc['status'], 'deleted');

        return ['ok' => true, 'name' => $doc['document_name']];
    }

    public static function restore(int $documentId, int $userId): array
    {
        $doc = self::find($documentId);
        if (!$doc) {
            return ['ok' => false, 'error' => 'Document not found.'];
        }
        if ((int)$doc['is_deleted'] === 0) {
            return ['ok' => false, 'error' => 'This document is not deleted.'];
        }

        db()->prepare('UPDATE documents SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE document_id = ?')
            ->execute([$documentId]);

        AuditService::log($userId, 'document_restored', 'document', $documentId,
            'Restored ' . $doc['document_name'] . ' (' . $doc['year'] . ') for ' . $doc['client_name'], 'deleted', $doc['status']);

        return ['ok' => true, 'name' => $doc['document_name']];
    }

    public static function countDeleted(): int
    {
        return (int)db()->query('SELECT COUNT(*) FROM documents WHERE is_deleted = 1')->fetchColumn();
    }

    public static function versions(int $documentId): array
    {
        $stmt = db()->prepare(
            'SELECT v.*, u.name AS created_by_name
             FROM document_versions v JOIN users u ON u.user_id = v.created_by
             WHERE v.document_id = ? ORDER BY v.version_no DESC'
        );
        $stmt->execute([$documentId]);
        return $stmt->fetchAll();
    }

    /** A version plus the details of its document (used by download + template checks). */
    public static function findVersion(int $versionId): ?array
    {
        $stmt = db()->prepare(
            'SELECT v.*, d.client_id, d.type_id, d.year, d.document_name, d.is_deleted
             FROM document_versions v JOIN documents d ON d.document_id = v.document_id
             WHERE v.version_id = ? LIMIT 1'
        );
        $stmt->execute([$versionId]);
        return $stmt->fetch() ?: null;
    }

    public static function tasksFor(int $documentId): array
    {
        $stmt = db()->prepare(
            'SELECT t.task_id, t.title, t.status, t.priority, t.due_date, u.name AS assignee
             FROM tasks t JOIN users u ON u.user_id = t.assigned_to
             WHERE t.document_id = ? ORDER BY t.created_at DESC'
        );
        $stmt->execute([$documentId]);
        return $stmt->fetchAll();
    }
    
        /** Admin or Permanent Staff, not the uploader themselves, reviewing an in_review document. */
    public static function canReview(array $doc, array $user): bool
    {
        if ((int)$doc['uploaded_by'] === (int)$user['user_id']) {
            return false;
        }
        return $doc['status'] === 'in_review' && ($user['role'] === 'admin' || in_array($user['staff_type'], ['staff', 'contract'], true));
    }

    public static function approve(int $documentId, int $userId): array
    {
        $doc = self::find($documentId);
        if (!$doc || $doc['status'] !== 'in_review') {
            return ['ok' => false, 'error' => 'This document is not awaiting review.'];
        }

        db()->prepare("UPDATE documents SET status = 'approved' WHERE document_id = ?")->execute([$documentId]);
        db()->prepare("UPDATE tasks SET status = 'completed', completed_at = NOW() WHERE document_id = ? AND status <> 'completed'")->execute([$documentId]);

        AuditService::log($userId, 'document_approved', 'document', $documentId, 'Approved ' . $doc['document_name'], 'in_review', 'approved');

        NotificationService::create((int)$doc['uploaded_by'], 'document', 'Document approved',
            $doc['document_name'] . ' - ' . $doc['client_name'], 'document', $documentId);

        return ['ok' => true];
    }

    public static function returnForChanges(int $documentId, string $note, int $userId): array
    {
        $doc = self::find($documentId);
        if (!$doc || $doc['status'] !== 'in_review') {
            return ['ok' => false, 'error' => 'This document is not awaiting review.'];
        }

        db()->prepare("UPDATE documents SET status = 'returned' WHERE document_id = ?")->execute([$documentId]);
        db()->prepare("UPDATE tasks SET status = 'in_progress', seen_at = NULL WHERE document_id = ? AND status <> 'completed'")->execute([$documentId]);

        AuditService::log($userId, 'document_returned', 'document', $documentId,
            'Returned ' . $doc['document_name'] . ($note !== '' ? ' - ' . $note : ''), 'in_review', 'returned');

        NotificationService::create((int)$doc['uploaded_by'], 'document', 'Document returned for changes',
            $doc['document_name'] . ' - ' . $doc['client_name'] . ($note !== '' ? ': ' . $note : ''), 'document', $documentId);

        return ['ok' => true];
    }

    // ------------------------------------------------------------------
    // LIBRARY
    // ------------------------------------------------------------------

    /**
     * $f = ['q','client','type','year','status','staff','from','to'] (already cleaned).
     * Fetches one extra row so the page can tell if results were cut off.
     */
    public static function library(array $f, int $limit, bool $deletedOnly = false): array
    {
$sql = 'SELECT d.document_id, d.document_name, d.year, d.status, d.updated_at, d.created_at,
               d.client_id, d.type_id, d.uploaded_by, d.is_deleted, d.deleted_at, du.name AS deleted_by_name,
                       c.client_name, t.name AS type_name,
                       v.version_id, v.version_no, v.file_type, v.file_path, u.name AS uploader_name
                FROM documents d
                JOIN clients c        ON c.client_id = d.client_id
                JOIN document_types t ON t.type_id   = d.type_id
                JOIN users u          ON u.user_id   = d.uploaded_by
                LEFT JOIN users du             ON du.user_id = d.deleted_by
                LEFT JOIN document_versions v ON v.version_id = d.current_version_id
                WHERE d.is_deleted = ' . ($deletedOnly ? '1' : '0');
        $p = [];

        if ($f['client'] > 0) { $sql .= ' AND d.client_id = ?';   $p[] = $f['client']; }
        if ($f['type'] > 0)   { $sql .= ' AND d.type_id = ?';     $p[] = $f['type']; }
        if ($f['year'] > 0)   { $sql .= ' AND d.year = ?';        $p[] = $f['year']; }
        if ($f['staff'] > 0)  { $sql .= ' AND d.uploaded_by = ?'; $p[] = $f['staff']; }
        if ($f['status'] !== '') { $sql .= ' AND d.status = ?';   $p[] = $f['status']; }
        if ($f['from'] !== '')   { $sql .= ' AND d.created_at >= ?'; $p[] = $f['from'] . ' 00:00:00'; }
        if ($f['to'] !== '')     { $sql .= ' AND d.created_at <= ?'; $p[] = $f['to'] . ' 23:59:59'; }
        if ($f['q'] !== '') {
            $like = '%' . addcslashes($f['q'], '%_\\') . '%';
            $sql .= ' AND (d.document_name LIKE ? OR c.client_name LIKE ?)';
            array_push($p, $like, $like);
        }

        $sql .= ' ORDER BY c.client_name ASC, d.year DESC, d.document_name ASC LIMIT ' . ((int)$limit + 1);
        $stmt = db()->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public static function filterYears(): array
    {
        return array_map('intval', db()->query('SELECT DISTINCT year FROM documents ORDER BY year DESC')->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Staff who have uploaded at least one document. */
    public static function filterStaff(): array
    {
        return db()->query(
            'SELECT DISTINCT u.user_id, u.name FROM users u JOIN documents d ON d.uploaded_by = u.user_id ORDER BY u.name'
        )->fetchAll();
    }

    // ------------------------------------------------------------------
    // TEMPLATES (previous-year documents)
    // ------------------------------------------------------------------

    /** Earlier documents of the same client and type, offered as templates. */
    public static function templateOptions(int $clientId, int $typeId): array
    {
        $stmt = db()->prepare(
            'SELECT d.document_name, d.year, d.status, v.version_id, v.version_no, v.file_type
             FROM documents d
             JOIN document_versions v ON v.version_id = d.current_version_id
             WHERE d.client_id = ? AND d.type_id = ? AND d.is_deleted = 0 AND v.file_path IS NOT NULL
             ORDER BY d.year DESC, d.document_id DESC LIMIT 50'
        );
        $stmt->execute([$clientId, $typeId]);

        $statuses = self::statuses();
        $items = [];
        foreach ($stmt->fetchAll() as $r) {
            $items[] = [
                'version_id' => (int)$r['version_id'],
                'label'      => $r['document_name'] . ' · ' . $r['year'] . ' · ' . ($statuses[$r['status']] ?? $r['status'])
                                . ' · v' . $r['version_no'] . ' (' . strtoupper((string)$r['file_type']) . ')',
            ];
        }
        return $items;
    }

    /** Is this version a valid template for this client + type? */
    public static function isValidTemplate(int $versionId, int $clientId, int $typeId): bool
    {
        $v = self::findVersion($versionId);
        return $v && $v['file_path'] && (int)$v['client_id'] === $clientId && (int)$v['type_id'] === $typeId;
    }

    // ------------------------------------------------------------------
    // CREATING (the upload workflow)
    // ------------------------------------------------------------------

    /**
     * Creates the document, Version 1 and an "Uploaded" task in ONE transaction:
     * either everything is saved or nothing is.
     * $d = client_id, client_name, type_id, document_name, year, template_version_id
     * $file = result of UploadService::store()
     */
    public static function create(array $d, array $file, string $ext, int $userId): int
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $pdo->prepare(
                "INSERT INTO documents (client_id, type_id, document_name, year, uploaded_by, prepared_from_version_id, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'draft')"
            )->execute([$d['client_id'], $d['type_id'], $d['document_name'], $d['year'], $userId, $d['template_version_id'] ?: null]);
            $docId = (int)$pdo->lastInsertId();

            $pdo->prepare(
                'INSERT INTO document_versions (document_id, version_no, file_path, file_type, original_name, file_size, change_note, created_by)
                 VALUES (?, 1, ?, ?, ?, ?, ?, ?)'
            )->execute([$docId, $file['path'], $ext, $file['original'], $file['size'], 'Uploaded', $userId]);
            $versionId = (int)$pdo->lastInsertId();

            $pdo->prepare('UPDATE documents SET current_version_id = ? WHERE document_id = ?')->execute([$versionId, $docId]);

            // seen_at = NOW(): the uploader created it, so it must not add to the "My Tasks" badge
            $pdo->prepare(
                "INSERT INTO tasks (document_id, client_id, title, assigned_to, created_by, status, priority, seen_at)
                 VALUES (?, ?, ?, ?, ?, 'uploaded', 'medium', NOW())"
            )->execute([$docId, $d['client_id'], 'Prepare ' . $d['document_name'], $userId, $userId]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        AuditService::log($userId, 'document_uploaded', 'document', $docId,
            'Uploaded ' . $d['document_name'] . ' (' . $d['year'] . ') for ' . $d['client_name']);

        return $docId;
    }
}