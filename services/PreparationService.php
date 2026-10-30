<?php
/**
 * services/PreparationService.php
 * The preparation workspace: running OCR, saving corrections, adding/removing
 * fields, comparing current vs previous-year values, resolving changes,
 * and saving a version.
 */
class PreparationService
{
    // ------------------------------------------------------------------
    // EXTRACTIONS AND FIELDS (per document version)
    // ------------------------------------------------------------------

    public static function extractionFor(int $versionId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM document_extractions WHERE version_id = ? ORDER BY extraction_id DESC LIMIT 1');
        $stmt->execute([$versionId]);
        return $stmt->fetch() ?: null;
    }
        /** The raw OCR text for an extraction, or null (used for the "copy this text" box). */
    public static function rawText(int $extractionId): ?string
    {
        $stmt = db()->prepare('SELECT raw_text FROM document_extractions WHERE extraction_id = ?');
        $stmt->execute([$extractionId]);
        $text = $stmt->fetchColumn();
        return $text !== false && $text !== null ? (string)$text : null;
    }

    /** All rows of one extraction, keyed by their own field_id. */
    public static function extractionFields(int $extractionId): array
    {
        $stmt = db()->prepare('SELECT * FROM extraction_fields WHERE extraction_id = ? ORDER BY field_id');
        $stmt->execute([$extractionId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['field_id']] = $row;
        }
        return $out;
    }

    /**
     * Runs OCR on this version's file (images), or creates one blank editable
     * field for anything else (Word/Excel/PDF), so staff can fill it in by hand.
     * Replaces any earlier extraction for the SAME version.
     */
    public static function runOcr(int $versionId, int $userId): array
    {
        $version = DocumentService::findVersion($versionId);
        if (!$version) {
            return ['ok' => false, 'error' => 'Document version not found.'];
        }

        $ext = strtolower((string)$version['file_type']);
        $abs = UploadService::absolutePath($version['file_path']);

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM document_extractions WHERE version_id = ?')->execute([$versionId]);

            if (!$abs || !OcrService::supports($ext)) {
                $pdo->prepare("INSERT INTO document_extractions (version_id, source, status) VALUES (?, 'file', 'done')")
                    ->execute([$versionId]);
                $extractionId = (int)$pdo->lastInsertId();
                $rows = [['key' => 'field_1', 'label' => 'Notes', 'value' => '']];
            } else {
                $result = OcrService::extract($abs);   // may throw
                $pdo->prepare("INSERT INTO document_extractions (version_id, source, raw_text, engine, status) VALUES (?, 'ocr', ?, ?, 'done')")
                    ->execute([$versionId, $result['text'], $result['engine']]);
                $extractionId = (int)$pdo->lastInsertId();
                $rows = FieldExtractor::autoDetect($result['text']);
                if (!$rows) {
                    $rows = [['key' => 'field_1', 'label' => 'Notes', 'value' => '']];
                }
            }

            $insert = $pdo->prepare('INSERT INTO extraction_fields (extraction_id, field_key, label, extracted_value) VALUES (?, ?, ?, ?)');
            foreach ($rows as $row) {
                $insert->execute([$extractionId, $row['key'], $row['label'], $row['value']]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => $e->getMessage()];
        }

        AuditService::log($userId, 'ocr_run', 'document_version', $versionId, 'Extracted information from version ' . $version['version_no']);
        return ['ok' => true, 'extraction_id' => $extractionId];
    }

    /** Saves label + value edits. $rows: field_id => ['label' => string, 'value' => string]. */
    public static function saveFields(int $extractionId, array $rows, int $userId): void
    {
        $stmt = db()->prepare('UPDATE extraction_fields SET label = ?, confirmed_value = ?, is_confirmed = 1, confirmed_by = ? WHERE field_id = ? AND extraction_id = ?');
        foreach ($rows as $fieldId => $row) {
            $label = trim((string)($row['label'] ?? ''));
            $value = trim((string)($row['value'] ?? ''));
            if ($label === '') {
                $label = 'Field';
            }
            $stmt->execute([$label, $value, $userId, (int)$fieldId, $extractionId]);
        }
    }

    /** Adds one blank, already-confirmed field row that staff can fill in. */
    public static function addField(int $extractionId, string $label, int $userId): array
    {
        $label = $label !== '' ? $label : 'New field';
        $key   = FieldExtractor::slug($label, (int)(microtime(true) * 1000) % 100000);

        db()->prepare(
            'INSERT INTO extraction_fields (extraction_id, field_key, label, extracted_value, confirmed_value, is_confirmed, confirmed_by)
             VALUES (?, ?, ?, ?, ?, 1, ?)'
        )->execute([$extractionId, $key, $label, '', '', $userId]);

        return ['field_id' => (int)db()->lastInsertId(), 'label' => $label, 'value' => ''];
    }

    public static function removeField(int $fieldId, int $extractionId): bool
    {
        $stmt = db()->prepare('DELETE FROM extraction_fields WHERE field_id = ? AND extraction_id = ?');
        $stmt->execute([$fieldId, $extractionId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Normalised label => ['label' => original label, 'value' => confirmed/OCR value].
     * Used to line up "the same field" across two DIFFERENT documents when comparing,
     * since fields are now dynamic per document rather than a shared fixed schema.
     */
    public static function labeledValues(int $extractionId): array
    {
        $stmt = db()->prepare('SELECT label, extracted_value, confirmed_value, is_confirmed FROM extraction_fields WHERE extraction_id = ?');
        $stmt->execute([$extractionId]);
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $norm = trim(preg_replace('/\s+/', ' ', strtolower($r['label'])));
            $out[$norm] = [
                'label' => $r['label'],
                'value' => (int)$r['is_confirmed'] === 1 ? (string)$r['confirmed_value'] : (string)$r['extracted_value'],
            ];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // COMPARISON
    // ------------------------------------------------------------------

    /**
     * $currentLabeled / $previousLabeled: from labeledValues() above.
     * Rows where nothing changed are auto-accepted; only real differences need review.
     * Replaces any UNRESOLVED rows from an earlier comparison of this document.
     */
    public static function compare(int $documentId, array $currentLabeled, array $previousLabeled, int $userId): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM document_changes WHERE document_id = ? AND change_status = 'pending'")->execute([$documentId]);

        $insert = $pdo->prepare(
            'INSERT INTO document_changes (document_id, field_key, old_value, new_value, change_status, reviewed_at) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $allKeys = array_unique(array_merge(array_keys($currentLabeled), array_keys($previousLabeled)));

        foreach ($allKeys as $normLabel) {
            // field_key stores the DISPLAY LABEL now (max 50 chars) - fields are per-document,
            // not a shared predefined schema, so there is no separate "key" to keep them apart.
            $label = mb_substr($currentLabeled[$normLabel]['label'] ?? $previousLabeled[$normLabel]['label'] ?? $normLabel, 0, 50);
            $old   = trim((string)($previousLabeled[$normLabel]['value'] ?? ''));
            $new   = trim((string)($currentLabeled[$normLabel]['value'] ?? ''));

            if ($old === $new) {
                $insert->execute([$documentId, $label, $old, $new, 'accepted', date('Y-m-d H:i:s')]);
            } else {
                $insert->execute([$documentId, $label, $old, $new, 'pending', null]);
            }
        }

        AuditService::log($userId, 'comparison_run', 'document', $documentId, 'Compared current and previous-year information');
    }

    public static function changesFor(int $documentId): array
    {
        $stmt = db()->prepare('SELECT * FROM document_changes WHERE document_id = ? ORDER BY change_id ASC');
        $stmt->execute([$documentId]);
        return $stmt->fetchAll();
    }

    public static function resolveChange(int $changeId, int $documentId, string $action, ?string $newValue, int $userId): bool
    {
        $stmt = db()->prepare('SELECT * FROM document_changes WHERE change_id = ? AND document_id = ? LIMIT 1');
        $stmt->execute([$changeId, $documentId]);
        $change = $stmt->fetch();
        if (!$change) {
            return false;
        }

        switch ($action) {
            case 'accept': $status = 'accepted'; $value = $change['new_value']; break;
            case 'reject': $status = 'rejected'; $value = $change['old_value']; break;
            case 'edit':   $status = 'edited';   $value = (string)$newValue;    break;
            default: return false;
        }

        db()->prepare('UPDATE document_changes SET change_status = ?, new_value = ?, reviewed_by = ?, reviewed_at = NOW() WHERE change_id = ?')
            ->execute([$status, $value, $userId, $changeId]);
        return true;
    }

    public static function acceptAllPending(int $documentId, int $userId): int
    {
        $stmt = db()->prepare("UPDATE document_changes SET change_status = 'accepted', reviewed_by = ?, reviewed_at = NOW() WHERE document_id = ? AND change_status = 'pending'");
        $stmt->execute([$userId, $documentId]);
        return $stmt->rowCount();
    }

    public static function pendingCount(int $documentId): int
    {
        $stmt = db()->prepare("SELECT COUNT(*) FROM document_changes WHERE document_id = ? AND change_status = 'pending'");
        $stmt->execute([$documentId]);
        return (int)$stmt->fetchColumn();
    }

    /** field_key (= label) => final value, taking the most recently resolved change for each field. */
    public static function resolvedValues(int $documentId): array
    {
        $stmt = db()->prepare(
            "SELECT c1.field_key, c1.new_value FROM document_changes c1
             WHERE c1.document_id = ? AND c1.change_status <> 'pending'
               AND c1.change_id = (
                   SELECT MAX(c2.change_id) FROM document_changes c2
                   WHERE c2.document_id = c1.document_id AND c2.field_key = c1.field_key AND c2.change_status <> 'pending'
               )"
        );
        $stmt->execute([$documentId]);
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $out[$r['field_key']] = $r['new_value'];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // SAVE VERSION / SUBMIT FOR REVIEW
    // ------------------------------------------------------------------

     public static function saveVersion(int $documentId, int $userId): array
    {
        $doc = DocumentService::find($documentId);
        if (!$doc) {
            return ['ok' => false, 'error' => 'Document not found.'];
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $nextNo = ((int)$doc['current_version_no']) + 1;
            $pdo->prepare(
                'INSERT INTO document_versions (document_id, version_no, file_path, file_type, original_name, file_size, change_note, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$documentId, $nextNo, $doc['file_path'], $doc['file_type'], $doc['original_name'], $doc['file_size'], 'Reviewed', $userId]);
            $newVersionId = (int)$pdo->lastInsertId();

            $pdo->prepare('UPDATE documents SET current_version_id = ? WHERE document_id = ?')->execute([$newVersionId, $documentId]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Save version failed: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'Could not save the version. Please try again.'];
        }

        AuditService::log($userId, 'document_version_saved', 'document', $documentId, 'Saved version ' . $nextNo);
        return ['ok' => true, 'version_id' => $newVersionId];
    }
    public static function submitForReview(int $documentId, int $userId): array
    {
        $doc = DocumentService::find($documentId);
        if (!$doc) {
            return ['ok' => false, 'error' => 'Document not found.'];
        }
        if (!in_array($doc['status'], ['draft', 'returned'], true)) {
            return ['ok' => false, 'error' => 'This document has already been submitted.'];
        }

        $pdo = db();
        $pdo->prepare("UPDATE documents SET status = 'in_review' WHERE document_id = ?")->execute([$documentId]);
        $pdo->prepare("UPDATE tasks SET status = 'pending_review' WHERE document_id = ? AND status <> 'completed'")->execute([$documentId]);

        AuditService::log($userId, 'document_submitted', 'document', $documentId,
            'Submitted ' . $doc['document_name'] . ' for review', $doc['status'], 'in_review');

        $stmt = $pdo->prepare(
            "SELECT user_id FROM users WHERE status = 'active' AND (role = 'admin' OR staff_type IN ('staff','contract')) AND user_id <> ?"
        );
        $stmt->execute([$userId]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $reviewerId) {
            NotificationService::create((int)$reviewerId, 'document', 'Document submitted for review',
                $doc['document_name'] . ' - ' . $doc['client_name'], 'document', $documentId);
        }

        return ['ok' => true];
    }
}