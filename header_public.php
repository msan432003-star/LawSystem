<?php
/**
 * includes/header_public.php
 * Header for public pages (home, find advocate, law details, login ...).
 *
 * Before including it, set (optional):
 *     $pageTitle  = 'Find an Advocate';
 *     $activePage = 'find';      // home | about | find | laws | news | contact
 */
require_once __DIR__ . '/../config/autoload.php';

$pageTitle  = $pageTitle  ?? 'Home';
$activePage = $activePage ?? '';

/** Adds the "active" class to the current menu link. */
function nav_active(string $page, string $current): string
{
    return $page === $current ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Legal Advisor</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body>

<!-- Top bar -->
<div class="topbar py-2 d-none d-md-block">
    <div class="container d-flex justify-content-between">
        <span><i class="fa-solid fa-envelope me-2"></i>contact@legaladvisor.example</span>
        <span>
            <i class="fa-solid fa-phone me-2"></i>+91 00000 00000
        </span>
    </div>
</div>

<!-- Main navigation -->
<nav class="navbar navbar-expand-lg site-nav sticky-top">
    <div class="container">
        <a class="navbar-brand brand-text" href="<?= BASE_URL ?>/index.php">
            <i class="fa-solid fa-scale-balanced me-2"></i>Legal Advisor
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainMenu" aria-controls="mainMenu" aria-label="Toggle menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link<?= nav_active('home', $activePage) ?>" href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('about', $activePage) ?>" href="<?= BASE_URL ?>/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('find', $activePage) ?>" href="<?= BASE_URL ?>/find_advocate.php">Find Advocate</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('laws', $activePage) ?>" href="<?= BASE_URL ?>/law_details.php">Law Details</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('news', $activePage) ?>" href="<?= BASE_URL ?>/news.php">News</a></li>
                <li class="nav-item"><a class="nav-link<?= nav_active('contact', $activePage) ?>" href="<?= BASE_URL ?>/contact.php">Contact</a></li>

                <?php if (is_logged_in()): ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-emerald btn-sm" href="<?= BASE_URL ?>/<?= e($_SESSION['user_type']) ?>/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-light-soft btn-sm" href="<?= BASE_URL ?>/logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-light-soft btn-sm" href="<?= BASE_URL ?>/login.php">Login</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-emerald btn-sm" href="<?= BASE_URL ?>/register_client.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flash = get_flash()): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> mb-0"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>
