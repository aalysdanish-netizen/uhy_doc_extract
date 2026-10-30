<?php
/**
 * includes/auth.php
 * Everything about logging in, the current user, and page protection.
 */

// ---------------------------------------------------------------------
// LOGIN PROTECTION
// ---------------------------------------------------------------------

/** Has this email failed to log in too many times recently? */
function is_locked_out(string $email): bool
{
    $minutes = (int)LOCKOUT_MINUTES;
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE email = ? AND success = 0
           AND attempted_at > (NOW() - INTERVAL {$minutes} MINUTE)"
    );
    $stmt->execute([$email]);
    return (int)$stmt->fetchColumn() >= MAX_LOGIN_ATTEMPTS;
}

function record_login_attempt(string $email, bool $success): void
{
    $stmt = db()->prepare('INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, ?)');
    $stmt->execute([$email, client_ip(), $success ? 1 : 0]);
}

// ---------------------------------------------------------------------
// LOGIN / LOGOUT
// ---------------------------------------------------------------------

/**
 * Try to log in.
 * Returns ['ok' => true] or ['ok' => false, 'error' => 'message for the user'].
 */
function attempt_login(string $usernameInput, string $password): array
{
    $username = normalize_username($usernameInput);

    // The same vague message is used for "no such user" and "wrong password"
    // so attackers cannot discover which accounts exist.
    $invalid = ['ok' => false, 'error' => 'Invalid email or password.'];

    if ($username === '' || $password === '') {
        return $invalid;
    }

    $email = build_email($username);

    if (is_locked_out($email)) {
        AuditService::log(null, 'login_locked', 'user', null, "Login blocked (too many attempts): {$email}");
        return ['ok' => false, 'error' => 'Too many failed attempts. Please wait ' . LOCKOUT_MINUTES . ' minutes and try again.'];
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always run password_verify, even if the user does not exist,
    // so response time does not reveal whether the account exists.
    $hash  = $user['password'] ?? password_hash('not-a-real-password', PASSWORD_DEFAULT);
    $valid = password_verify($password, $hash);

    if (!$user || !$valid) {
        record_login_attempt($email, false);
        AuditService::log($user['user_id'] ?? null, 'login_failed', 'user', $user['user_id'] ?? null, "Failed login: {$email}");
        return $invalid;
    }

    // Correct password, but the account is switched off
    if ($user['status'] !== 'active') {
        AuditService::log((int)$user['user_id'], 'login_blocked', 'user', (int)$user['user_id'], 'Inactive account tried to log in');
        return ['ok' => false, 'error' => 'This account is inactive. Please contact your administrator.'];
    }

    // ---- Success ----
    db()->prepare('DELETE FROM login_attempts WHERE email = ? AND success = 0')->execute([$email]);
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE user_id = ?')->execute([$user['user_id']]);

    session_regenerate_id(true);          // new session ID prevents session fixation
    $_SESSION['user_id'] = (int)$user['user_id'];

    AuditService::log((int)$user['user_id'], 'login', 'user', (int)$user['user_id'], 'User logged in');

    return ['ok' => true];
}

function logout_user(): void
{
    if (!empty($_SESSION['user_id'])) {
        AuditService::log((int)$_SESSION['user_id'], 'logout', 'user', (int)$_SESSION['user_id'], 'User logged out');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ---------------------------------------------------------------------
// CURRENT USER
// ---------------------------------------------------------------------

/**
 * Returns the logged-in user as an array, or null.
 * It reads the database on every request, so if an Admin deactivates
 * someone, that person is kicked out immediately.
 */
function current_user(): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }
    $loaded = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT user_id, name, username, email, role, staff_type, department, status, must_change_password, avatar_path
         FROM users WHERE user_id = ? LIMIT 1'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();

    if (!$row || $row['status'] !== 'active') {
        unset($_SESSION['user_id']);       // account removed or deactivated
        return null;
    }

    $user = $row;
    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

// ---------------------------------------------------------------------
// PAGE PROTECTION
// ---------------------------------------------------------------------

/**
 * Put at the top of every private page.
 * $allowPasswordChange = true is only used by the change-password page itself.
 */
function require_login(bool $allowPasswordChange = false): array
{
    $user = current_user();

    if ($user === null) {
        redirect('auth/login.php');
    }

    if ((int)$user['must_change_password'] === 1 && !$allowPasswordChange) {
        redirect('auth/change-password.php');
    }

    return $user;
}

/** Restrict a page to certain roles, e.g. require_role('admin'); */
function require_role(string ...$roles): array
{
    $user = require_login();

    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('403 - You do not have permission to open this page.');
    }

    return $user;
}

// ---------------------------------------------------------------------
// CHANGE PASSWORD
// ---------------------------------------------------------------------

/** Returns an error message, or null if the new password is acceptable. */
function validate_new_password(string $current, string $new, string $confirm): ?string
{
    if ($current === '' || $new === '' || $confirm === '') {
        return 'Please fill in all fields.';
    }
    if (strlen($new) < 8) {
        return 'The new password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
        return 'The new password must contain at least one letter and one number.';
    }
    if ($new !== $confirm) {
        return 'The new password and confirmation do not match.';
    }
    if ($new === $current) {
        return 'The new password must be different from your current password.';
    }
    if ($new === DEFAULT_PASSWORD) {
        return 'You cannot use the default password. Please choose your own.';
    }
    return null;
}

/** Verifies the current password, saves the new one and clears must_change_password. */
function change_user_password(int $userId, string $current, string $new): bool
{
    $stmt = db()->prepare('SELECT password FROM users WHERE user_id = ?');
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        return false;
    }

    db()->prepare('UPDATE users SET password = ?, must_change_password = 0 WHERE user_id = ?')
        ->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);

    AuditService::log($userId, 'password_changed', 'user', $userId, 'User changed their password');
    return true;
}