<?php
/**
 * includes/functions.php
 * Small helper functions used everywhere.
 */

/** Escape text before printing into HTML (prevents XSS). Use it on ALL user data. */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Build a URL inside the project. url('auth/login.php') -> /uhy-documentflow/auth/login.php */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Send the browser to another page and stop running this script. */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/** Was this page opened by submitting a form (POST)? */
function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/** The visitor's IP address (used for security logs). */
function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Turn what the user typed into a clean username.
 * Accepts "alysdanish" or "alysdanish@uhy.docs.hub.com".
 * Returns '' if it is not valid.
 */
function normalize_username(string $input): string
{
    $input = strtolower(trim($input));

    if (str_contains($input, '@')) {
        [$name, $domain] = explode('@', $input, 2);
        if ($domain !== EMAIL_DOMAIN) {
            return '';                       // wrong domain
        }
        $input = $name;
    }

    return preg_match('/^[a-z0-9._-]{2,50}$/', $input) ? $input : '';
}

/** alysdanish -> alysdanish@uhy.docs.hub.com */
function build_email(string $username): string
{
    return $username . '@' . EMAIL_DOMAIN;
}

// ---- Flash messages: show a message once after a redirect --------------
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}