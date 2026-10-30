<?php
/**
 * services/AuditService.php
 * Writes to the audit trail. Rows are only ever added, never edited or deleted.
 *
 * Usage:
 *   AuditService::log($userId, 'document_uploaded', 'document', $docId, 'Uploaded Company Profile');
 */
class AuditService
{
    public static function log(
        ?int $userId,
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        ?string $oldValue = null,
        ?string $newValue = null
    ): void {
        try {
            $stmt = db()->prepare(
                'INSERT INTO audit_logs
                 (user_id, action, entity_type, entity_id, description, old_value, new_value, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$userId, $action, $entityType, $entityId, $description, $oldValue, $newValue, client_ip()]);
        } catch (PDOException $e) {
            // A logging problem must never crash the page the user is using.
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}