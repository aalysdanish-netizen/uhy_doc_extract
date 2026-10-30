<?php
/**
 * services/DocumentTypeService.php
 * Document types (categories) and the fields OCR looks for in each type.
 */
class DocumentTypeService
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM document_types WHERE type_id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function activeList(): array
    {
        return db()->query('SELECT type_id, name FROM document_types WHERE is_active = 1 ORDER BY name')->fetchAll();
    }

    /** All types with counts, for the admin list. */
    public static function all(): array
    {
        return db()->query(
            'SELECT t.type_id, t.name, t.is_active,
                    (SELECT COUNT(*) FROM document_type_fields f WHERE f.type_id = t.type_id) AS field_count,
                    (SELECT COUNT(*) FROM documents d WHERE d.type_id = t.type_id) AS doc_count
             FROM document_types t ORDER BY t.name'
        )->fetchAll();
    }

    /** Fields of one type, keyed by field id. */
    public static function fields(int $typeId): array
    {
        $stmt = db()->prepare('SELECT * FROM document_type_fields WHERE type_id = ? ORDER BY sort_order, type_field_id');
        $stmt->execute([$typeId]);
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $out[(int)$row['type_field_id']] = $row;
        }
        return $out;
    }

    public static function nameError(string $name, ?int $exceptId): ?string
    {
        if ($name === '') {
            return 'Please enter a name.';
        }
        if (mb_strlen($name) > 100) {
            return 'The name is too long (maximum 100 characters).';
        }
        $stmt = db()->prepare('SELECT 1 FROM document_types WHERE name = ? AND type_id <> ? LIMIT 1');
        $stmt->execute([$name, $exceptId ?? 0]);
        return $stmt->fetch() ? 'A document type with this name already exists.' : null;
    }

    public static function create(string $name, int $actorId): int
    {
        db()->prepare('INSERT INTO document_types (name) VALUES (?)')->execute([$name]);
        $id = (int)db()->lastInsertId();
        AuditService::log($actorId, 'document_type_created', 'document_type', $id, 'Created document type ' . $name);
        return $id;
    }

    /** "Address | Registered Address||" -> "Address|Registered Address" */
    private static function cleanPattern(string $pattern): string
    {
        $parts = array_filter(array_map('trim', explode('|', $pattern)), fn($p) => $p !== '');
        return mb_substr(implode('|', $parts), 0, 255);
    }

    /** "Financial Year" -> "financial_year" (unique within the type) */
    private static function makeKey(string $label, array $taken): string
    {
        $key = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($label)), '_');
        $key = substr($key !== '' ? $key : 'field', 0, 40);
        $base = $key;
        $i = 2;
        while (in_array($key, $taken, true)) {
            $key = $base . '_' . $i++;
        }
        return $key;
    }

    /** Saves the type and its fields from the edit form. Returns errors (empty = saved). */
    public static function save(int $typeId, array $post, int $actorId): array
    {
        $errors = [];

        $name = trim(preg_replace('/\s+/', ' ', (string)($post['name'] ?? '')));
        if ($err = self::nameError($name, $typeId)) {
            $errors['name'] = $err;
        }
        $active = !empty($post['is_active']) ? 1 : 0;

        $labels   = (array)($post['field_label'] ?? []);
        $patterns = (array)($post['field_pattern'] ?? []);
        $orders   = (array)($post['field_order'] ?? []);
        $removes  = (array)($post['field_delete'] ?? []);

        $existing = self::fields($typeId);
        $updates  = [];
        $toDelete = [];

        foreach ($existing as $id => $field) {
            if (!empty($removes[$id])) {
                $toDelete[] = $id;
                continue;
            }
            $label = trim((string)($labels[$id] ?? ''));
            if ($label === '' || mb_strlen($label) > 100) {
                $errors['fields'] = 'Every field needs a label (maximum 100 characters).';
                continue;
            }
            $updates[] = [$id, $label, self::cleanPattern((string)($patterns[$id] ?? '')), (int)($orders[$id] ?? 0)];
        }

        $newLabel = trim((string)($post['new_label'] ?? ''));
        if (mb_strlen($newLabel) > 100) {
            $errors['fields'] = 'Every field needs a label (maximum 100 characters).';
        }

        if ($errors) {
            return $errors;
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE document_types SET name = ?, is_active = ? WHERE type_id = ?')->execute([$name, $active, $typeId]);

            $upd = $pdo->prepare('UPDATE document_type_fields SET label = ?, ocr_pattern = ?, sort_order = ? WHERE type_field_id = ? AND type_id = ?');
            foreach ($updates as [$id, $label, $pattern, $order]) {
                $upd->execute([$label, $pattern !== '' ? $pattern : null, $order, $id, $typeId]);
            }

            $del = $pdo->prepare('DELETE FROM document_type_fields WHERE type_field_id = ? AND type_id = ?');
            foreach ($toDelete as $id) {
                $del->execute([$id, $typeId]);
            }

            if ($newLabel !== '') {
                $taken = array_column($existing, 'field_key');
                $max   = $existing ? max(array_column($existing, 'sort_order')) : 0;
                $pdo->prepare('INSERT INTO document_type_fields (type_id, field_key, label, ocr_pattern, sort_order) VALUES (?, ?, ?, ?, ?)')
                    ->execute([
                        $typeId, self::makeKey($newLabel, $taken), $newLabel,
                        self::cleanPattern((string)($post['new_pattern'] ?? '')) ?: null, $max + 1,
                    ]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Document type save failed: ' . $e->getMessage());
            return ['general' => 'Could not save. Please try again.'];
        }

        AuditService::log($actorId, 'document_type_updated', 'document_type', $typeId, 'Updated document type ' . $name);
        return [];
    }
}