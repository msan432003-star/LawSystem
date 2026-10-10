<?php
/**
 * includes/header_dashboard.php - shared top bar + sidebar for admin, advocate and client pages.
 * Set before including:  $pageTitle = '...';  $activePage = 'dashboard';
 */
$role = $_SESSION['user_type'] ?? '';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Dashboard') ?> | Legal Advisor</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
</head>
<body class="dash">
<nav class="dash-topbar py-2 px-3 d-flex justify-content-between align-items-center">
    <a href="<?= BASE_URL ?>/<?= e($role) ?>/index.php" class="fs-5">
        <i class="fa-solid fa-scale-balanced me-2" style="color:#2ee6a6"></i>Legal Advisor
    </a>
    <div class="d-flex align-items-center gap-3">
        <span class="small d-none d-sm-inline">
            <i class="fa-solid fa-user me-1"></i><?= e($_SESSION['display_name'] ?? '') ?>
            <span class="text-uppercase opacity-75">(<?= e($role) ?>)</span>
        </span>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-light">Logout</a>
    </div>
</nav>
<div class="dash-wrap">
    <?php
    $sidebarFile = __DIR__ . '/sidebar_' . $role . '.php';
    if (is_file($sidebarFile)) { require $sidebarFile; }
    ?>
    <main class="dash-main">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
