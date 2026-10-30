<?php
/**
 * config/database.php
 * Database connection using PDO (safe, supports prepared statements).
 * XAMPP defaults: user "root" with an empty password.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'uhy_documentflow');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * Returns ONE shared database connection.
 * Usage anywhere:  $stmt = db()->prepare('SELECT ...');
 */
function db(): PDO
{
    static $pdo = null;   // "static" keeps the connection between calls

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // rows as arrays
                    PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+08:00'", // Malaysia time
                ]
            );
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit(APP_DEBUG
                ? 'Database connection failed: ' . htmlspecialchars($e->getMessage())
                : 'The system is temporarily unavailable.');
        }
    }

    return $pdo;
}