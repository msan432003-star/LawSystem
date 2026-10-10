<?php
/**
 * logout.php - ends the session and returns to the login page
 */
require_once __DIR__ . '/config/autoload.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

session_start();                     // new empty session, only to carry the message below
flash('You have been logged out.', 'info');
redirect('/login.php');
