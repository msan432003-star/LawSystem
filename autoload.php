<?php
/**
 * config/autoload.php
 * Include this ONE file at the top of every page:
 *     require_once __DIR__ . '/../config/autoload.php';   (adjust the path per folder)
 * It starts the session, connects to the database ($pdo) and loads helper functions.
 */

// ---- Session (started once, with safer settings) ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,       // JavaScript cannot read the session cookie
        'samesite' => 'Lax',
    ]);
    session_start();
    
}

// ---- Base paths ----
define('BASE_PATH', dirname(__DIR__));          // e.g. C:/wamp64/www/legal-advisor
define('BASE_URL', '/LawSystem');           // change if your project folder has another name
define('UPLOAD_DIR', BASE_PATH . '/uploads/');

// ---- Database connection: gives you $pdo ----
$pdo = require __DIR__ . '/database.php';

// ---- Classes ----
require_once BASE_PATH . '/classes/DataAccess.class.php';
require_once BASE_PATH . '/classes/FormValidator.class.php';
require_once BASE_PATH . '/classes/FileUpload.class.php';

// ---- Ready-to-use query helper: gives you $db ----
$db = new DataAccess($pdo);

// ---- Helper functions ----

/** Escape output to prevent XSS. Use: echo e($row['title']); */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect and stop the script. Use: redirect('/login.php'); */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/** Is someone logged in? */
function is_logged_in(): bool
{
    return isset($_SESSION['login_id'], $_SESSION['user_type']);
}

/** Block a page unless the user has one of the allowed roles. Use: require_role(['admin']); */
function require_role(array $roles): void
{
    if (!is_logged_in() || !in_array($_SESSION['user_type'], $roles, true)) {
        redirect('/login.php');
    }
}

/** One-time messages (e.g. "Case added"). Set with flash('Saved'); show with get_flash(). */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ---- CSRF protection: put csrf_field() in every POST form, call csrf_verify() when handling it ----
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token']);
}
