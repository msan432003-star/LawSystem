<?php
/**
 * config/database.example.php
 * Copy this file to config/database.php and fill in your own details.
 * (database.php is listed in .gitignore so your password is never uploaded.)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'legal_advisor');
define('DB_USER', 'root');
define('DB_PASS', '');        // WAMP default is empty; use a strong password on a real server
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Please try again later.');
}

return $pdo;