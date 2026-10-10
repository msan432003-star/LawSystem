<?php
/**
 * subscribe.php - handles the newsletter form in the footer, then returns to the page it came from
 */
require_once __DIR__ . '/config/autoload.php';

// Go back to the page the visitor was on (only if it is on this same site)
function back_to_previous(): void
{
    $ref     = $_SERVER['HTTP_REFERER'] ?? '';
    $host    = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
    $refHost = parse_url($ref, PHP_URL_HOST);
    if ($ref !== '' && $refHost === $host) {
        header('Location: ' . $ref);
        exit;
    }
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

if (!csrf_verify()) {
    flash('Your session expired. Please try again.', 'danger');
    back_to_previous();
}

$v = new FormValidator($_POST);
$v->field('email', 'Email')->required()->email()->maxLength(100);

if ($v->fails()) {
    flash($v->error('email'), 'danger');
} elseif ($db->exists('newsletter_subscriber', 'email = ?', [$v->old('email')])) {
    flash('You are already subscribed.', 'info');
} else {
    $db->insert('newsletter_subscriber', ['email' => $v->old('email')]);
    flash('Thanks for subscribing to our newsletter!');
}

back_to_previous();
