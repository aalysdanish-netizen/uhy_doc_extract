<?php
/**
 * services/UserService.php
 * All database work for user management lives here, so the pages stay short.
 */
class UserService
{
    public static function departments(): array
    {
        return ['Audit', 'Tax', 'Accounting', 'Advisory', 'IT', 'Administration', 'Other'];
    }

    // ------------------------------------------------------------------
    // READING
    // ------------------------------------------------------------------

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT user_id, name, username, email, role, staff_type, department, status,
                    must_change_password, last_login_at, created_at
             FROM users WHERE user_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Builds the WHERE part for the search + status filter. */
    private static function where(string $q, string $status): array
    {
        $sql    = ' WHERE 1=1';
        $params = [];

        if ($status === 'active' || $status === 'inactive') {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';   // escape LIKE wildcards typed by the user
            $sql .= ' AND (name LIKE ? OR username LIKE ? OR email LIKE ? OR department LIKE ?)';
            array_push($params, $like, $like, $like, $like);
        }
        return [$sql, $params];
    }

    public static function search(string $q, string $status, int $limit, int $offset): array
    {
        [$where, $params] = self::where($q, $status);
        $stmt = db()->prepare(
            'SELECT user_id, name, username, email, role, staff_type, department, status, last_login_at
             FROM users' . $where . ' ORDER BY name ASC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(string $q, string $status): int
    {
        [$where, $params] = self::where($q, $status);
        $stmt = db()->prepare('SELECT COUNT(*) FROM users' . $where);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** ['all' => 10, 'active' => 8, 'inactive' => 2] for the filter tabs */
    public static function statusCounts(): array
    {
        $out = ['all' => 0, 'active' => 0, 'inactive' => 0];
        foreach (db()->query('SELECT status, COUNT(*) AS c FROM users GROUP BY status')->fetchAll() as $row) {
            $out[$row['status']] = (int)$row['c'];
            $out['all'] += (int)$row['c'];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // ADMIN PROTECTION
    // ------------------------------------------------------------------

    /** True when NO other active Admin exists besides this user. */
    public static function isLastActiveAdmin(int $userId): bool
    {
        $stmt = db()->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active' AND user_id <> ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn() === 0;
    }

    // ------------------------------------------------------------------
    // VALIDATION (shared by create and edit)
    // ------------------------------------------------------------------

    /**
     * Cleans $form (passed by reference) and returns an array of errors keyed by field.
     * $existing = null when creating, or the user row when editing.
     */
    public static function validate(array &$form, ?array $existing, int $actorId): array
    {
        $errors = [];

        // Name
        $form['name'] = trim(preg_replace('/\s+/', ' ', (string)$form['name']));
        if ($form['name'] === '') {
            $errors['name'] = 'Please enter the full name.';
        } elseif (mb_strlen($form['name']) > 120) {
            $errors['name'] = 'The name is too long (maximum 120 characters).';
        }

        // Username (only chosen when creating)
        if ($existing === null) {
            $username = normalize_username((string)$form['username']);
            if ($username === '') {
                $errors['username'] = 'Use 2-50 letters, numbers, dots, dashes or underscores.';
            } else {
                $form['username'] = $username;
                $stmt = db()->prepare('SELECT 1 FROM users WHERE username = ? LIMIT 1');
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $errors['username'] = 'That username is already taken.';
                }
            }
        } else {
            $form['username'] = $existing['username'];
        }

        // Role and staff type
        if (!in_array($form['role'], ['admin', 'staff'], true)) {
            $errors['role'] = 'Please choose a role.';
        }
        if ($form['role'] === 'staff') {
            if (!in_array($form['staff_type'], ['staff', 'contract', 'intern'], true)) {
                $errors['staff_type'] = 'Please choose Staff, Contract Staff or Intern.';
            }
        } else {
            $form['staff_type'] = null;      // Admins have no staff type
        }

        // Department
        if (!in_array($form['department'], self::departments(), true)) {
            $errors['department'] = 'Please choose a department.';
        }

        // Status
        if (!in_array($form['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Please choose a status.';
        }

        // Protection rules (editing only)
        if ($existing !== null) {
            $id = (int)$existing['user_id'];

            if ($id === $actorId) {
                if (isset($form['role']) && $form['role'] !== $existing['role']) {
                    $errors['role'] = 'You cannot change your own role.';
                }
                if ($form['status'] === 'inactive') {
                    $errors['status'] = 'You cannot deactivate your own account.';
                }
            }

            $wasActiveAdmin = $existing['role'] === 'admin' && $existing['status'] === 'active';
            if ($wasActiveAdmin && self::isLastActiveAdmin($id)) {
                if ($form['status'] !== 'active') {
                    $errors['status'] = 'The last active Admin account cannot be deactivated.';
                }
                if ($form['role'] !== 'admin') {
                    $errors['role'] = 'The last active Admin cannot be changed to Staff.';
                }
            }
        }

        return $errors;
    }

    // ------------------------------------------------------------------
    // WRITING
    // ------------------------------------------------------------------

    public static function create(array $form, int $actorId): int
    {
        $email = build_email($form['username']);

        $stmt = db()->prepare(
            'INSERT INTO users (name, username, email, password, role, staff_type, department, status, must_change_password)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        $stmt->execute([
            $form['name'], $form['username'], $email,
            password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT),     // hashed, never stored as plain text
            $form['role'], $form['staff_type'], $form['department'], $form['status'],
        ]);
        $newId = (int)db()->lastInsertId();

        AuditService::log($actorId, 'user_created', 'user', $newId, "Created user {$email}");
        NotificationService::create(
            $newId, 'system', 'Welcome to ' . APP_NAME,
            'Your account is ready. Please change your password after your first sign in.'
        );

        return $newId;
    }

    /** Returns true if something changed. */
    public static function update(int $id, array $form, array $existing, int $actorId): bool
    {
        $old = [];
        $new = [];
        foreach (['name', 'role', 'staff_type', 'department', 'status'] as $field) {
            if (($existing[$field] ?? null) !== ($form[$field] ?? null)) {
                $old[] = $field . ': ' . ($existing[$field] ?? '-');
                $new[] = $field . ': ' . ($form[$field] ?? '-');
            }
        }
        if (!$old) {
            return false;
        }

        db()->prepare('UPDATE users SET name = ?, role = ?, staff_type = ?, department = ?, status = ? WHERE user_id = ?')
            ->execute([$form['name'], $form['role'], $form['staff_type'], $form['department'], $form['status'], $id]);

        AuditService::log($actorId, 'user_updated', 'user', $id, 'Updated user ' . $existing['email'],
            implode('; ', $old), implode('; ', $new));
        return true;
    }

    /** Activate or deactivate. Returns ['ok' => bool, 'message' => string]. */
    public static function setStatus(int $id, string $status, int $actorId): array
    {
        $target = self::find($id);
        if (!$target) {
            return ['ok' => false, 'message' => 'User not found.'];
        }
        if ($target['status'] === $status) {
            return ['ok' => true, 'message' => 'No change: the account was already ' . $status . '.'];
        }

        if ($status === 'inactive') {
            if ($id === $actorId) {
                return ['ok' => false, 'message' => 'You cannot deactivate your own account.'];
            }
            if ($target['role'] === 'admin' && self::isLastActiveAdmin($id)) {
                return ['ok' => false, 'message' => 'The last active Admin account cannot be deactivated.'];
            }
        }

        db()->prepare('UPDATE users SET status = ? WHERE user_id = ?')->execute([$status, $id]);
        AuditService::log($actorId, $status === 'active' ? 'user_activated' : 'user_deactivated', 'user', $id,
            ($status === 'active' ? 'Activated user ' : 'Deactivated user ') . $target['email'],
            $target['status'], $status);

        return ['ok' => true, 'message' => $target['name'] . ' is now ' . $status . '.'];
    }

    public static function resetPassword(int $id, int $actorId): array
    {
        $target = self::find($id);
        if (!$target) {
            return ['ok' => false, 'message' => 'User not found.'];
        }

        db()->prepare('UPDATE users SET password = ?, must_change_password = 1 WHERE user_id = ?')
            ->execute([password_hash(DEFAULT_PASSWORD, PASSWORD_DEFAULT), $id]);
        // Also lift any login lockout so they can sign in straight away
        db()->prepare('DELETE FROM login_attempts WHERE email = ?')->execute([$target['email']]);

        AuditService::log($actorId, 'password_reset', 'user', $id, 'Reset password for ' . $target['email']);

        return ['ok' => true, 'message' => 'Password reset for ' . $target['name'] . '. They must set a new one at next sign in.'];
    }
        /**
     * Uploads a profile photo for a user if one was submitted. Silently does nothing
     * if no file was chosen (avatar is always optional in the Admin form).
     * Returns true if a photo was actually saved.
     */
    public static function maybeUploadAvatar(int $userId, ?array $file, ?string $previousPath = null): bool
    {
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return false;
        }
        require_once __DIR__ . '/AvatarService.php';
        require_once __DIR__ . '/UploadService.php';

        [$error, $src] = AvatarService::validate($file);
        if ($error) {
            return false;   // admin form doesn't block on this; photo is optional
        }
        $relative = AvatarService::storeForUser($userId, $src, $previousPath);
        db()->prepare('UPDATE users SET avatar_path = ? WHERE user_id = ?')->execute([$relative, $userId]);
        return true;
    }
}