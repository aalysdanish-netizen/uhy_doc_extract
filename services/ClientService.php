<?php
/**
 * services/ClientService.php - client (company) records.
 */
class ClientService
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM clients WHERE client_id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Active clients for dropdown lists. */
    public static function activeList(): array
    {
        return db()->query("SELECT client_id, client_name FROM clients WHERE status = 'active' ORDER BY client_name")->fetchAll();
    }

    private static function where(string $q, string $status): array
    {
        $sql = ' WHERE 1=1';
        $params = [];
        if ($status === 'active' || $status === 'inactive') {
            $sql .= ' AND c.status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $sql .= ' AND (c.client_name LIKE ? OR c.registration_no LIKE ? OR c.contact_person LIKE ?)';
            array_push($params, $like, $like, $like);
        }
        return [$sql, $params];
    }

    public static function search(string $q, string $status, int $limit, int $offset): array
    {
        [$where, $params] = self::where($q, $status);
        $stmt = db()->prepare(
            'SELECT c.client_id, c.client_name, c.registration_no, c.contact_person, c.status,
                    (SELECT COUNT(*) FROM documents d WHERE d.client_id = c.client_id) AS doc_count
             FROM clients c' . $where . ' ORDER BY c.client_name ASC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(string $q, string $status): int
    {
        [$where, $params] = self::where($q, $status);
        $stmt = db()->prepare('SELECT COUNT(*) FROM clients c' . $where);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function statusCounts(): array
    {
        $out = ['all' => 0, 'active' => 0, 'inactive' => 0];
        foreach (db()->query('SELECT status, COUNT(*) AS c FROM clients GROUP BY status')->fetchAll() as $row) {
            $out[$row['status']] = (int)$row['c'];
            $out['all'] += (int)$row['c'];
        }
        return $out;
    }

    /** Cleans $form and returns errors keyed by field. */
    public static function validate(array &$form, ?int $existingId): array
    {
        $errors = [];

        $form['client_name']     = trim(preg_replace('/\s+/', ' ', (string)$form['client_name']));
        $form['registration_no'] = trim((string)$form['registration_no']);
        $form['contact_person']  = trim((string)$form['contact_person']);

        if ($form['client_name'] === '') {
            $errors['client_name'] = 'Please enter the client name.';
        } elseif (mb_strlen($form['client_name']) > 150) {
            $errors['client_name'] = 'The name is too long (maximum 150 characters).';
        } else {
            $stmt = db()->prepare('SELECT 1 FROM clients WHERE client_name = ? AND client_id <> ? LIMIT 1');
            $stmt->execute([$form['client_name'], $existingId ?? 0]);
            if ($stmt->fetch()) {
                $errors['client_name'] = 'A client with this name already exists.';
            }
        }
        if (mb_strlen($form['registration_no']) > 50) {
            $errors['registration_no'] = 'Maximum 50 characters.';
        }
        if (mb_strlen($form['contact_person']) > 120) {
            $errors['contact_person'] = 'Maximum 120 characters.';
        }
        if (!in_array($form['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Please choose a status.';
        }
        return $errors;
    }

    public static function create(array $form, int $actorId): int
    {
        db()->prepare('INSERT INTO clients (client_name, registration_no, contact_person, status, created_by) VALUES (?, ?, ?, ?, ?)')
            ->execute([
                $form['client_name'],
                $form['registration_no'] !== '' ? $form['registration_no'] : null,
                $form['contact_person'] !== '' ? $form['contact_person'] : null,
                $form['status'], $actorId,
            ]);
        $id = (int)db()->lastInsertId();
        AuditService::log($actorId, 'client_created', 'client', $id, 'Created client ' . $form['client_name']);
        return $id;
    }

    public static function update(int $id, array $form, array $existing, int $actorId): bool
    {
        $old = [];
        $new = [];
        foreach (['client_name', 'registration_no', 'contact_person', 'status'] as $f) {
            $before = (string)($existing[$f] ?? '');
            $after  = (string)($form[$f] ?? '');
            if ($before !== $after) {
                $old[] = $f . ': ' . ($before !== '' ? $before : '-');
                $new[] = $f . ': ' . ($after !== '' ? $after : '-');
            }
        }
        if (!$old) {
            return false;
        }
        db()->prepare('UPDATE clients SET client_name = ?, registration_no = ?, contact_person = ?, status = ? WHERE client_id = ?')
            ->execute([
                $form['client_name'],
                $form['registration_no'] !== '' ? $form['registration_no'] : null,
                $form['contact_person'] !== '' ? $form['contact_person'] : null,
                $form['status'], $id,
            ]);
        AuditService::log($actorId, 'client_updated', 'client', $id, 'Updated client ' . $form['client_name'], implode('; ', $old), implode('; ', $new));
        return true;
    }

    public static function setStatus(int $id, string $status, int $actorId): array
    {
        $client = self::find($id);
        if (!$client) {
            return ['ok' => false, 'message' => 'Client not found.'];
        }
        if ($client['status'] === $status) {
            return ['ok' => true, 'message' => 'No change: the client was already ' . $status . '.'];
        }
        db()->prepare('UPDATE clients SET status = ? WHERE client_id = ?')->execute([$status, $id]);
        AuditService::log($actorId, $status === 'active' ? 'client_activated' : 'client_deactivated', 'client', $id,
            ($status === 'active' ? 'Activated client ' : 'Deactivated client ') . $client['client_name'], $client['status'], $status);
        return ['ok' => true, 'message' => $client['client_name'] . ' is now ' . $status . '.'];
    }
}