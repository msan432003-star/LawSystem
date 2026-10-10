<?php
/** admin/index.php - admin dashboard */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);

$pendingAdv  = $db->count('advocate', "approval_status = 'pending'");
$totalAdv    = $db->count('advocate');
$totalClient = $db->count('client');
$totalCases  = $db->count('cases');
$openCases   = $db->count('cases', "status IN ('assigned','ongoing')");

$pending = $db->fetchAll(
    "SELECT advocate_id, first_name, last_name, specialization, bar_council_no, place
     FROM advocate WHERE approval_status = 'pending' ORDER BY advocate_id DESC LIMIT 5");

$pageTitle = 'Admin Dashboard'; $activePage = 'dashboard';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-1">Welcome, <?= e($_SESSION['display_name']) ?></h3>
<p class="text-secondary mb-4">Platform overview.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $pendingAdv ?></div><div class="lbl">Advocates awaiting approval</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $totalAdv ?></div><div class="lbl">Total advocates</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $totalClient ?></div><div class="lbl">Clients</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $totalCases ?></div><div class="lbl">Cases (<?= $openCases ?> active)</div></div></div>
</div>

<div class="panel">
    <div class="d-flex justify-content-between mb-3">
        <h5 class="mb-0">Pending advocate registrations</h5>
        <a href="advocates.php">See all &rarr;</a>
    </div>
    <?php if (!$pending): ?><p class="text-secondary mb-0">No registrations waiting.</p>
    <?php else: foreach ($pending as $a): ?>
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div><div class="fw-semibold"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></div>
                <div class="small text-secondary"><?= e($a['specialization']) ?> &middot; Bar no: <?= e($a['bar_council_no']) ?> &middot; <?= e($a['place']) ?></div></div>
            <a class="btn btn-sm btn-outline-secondary" href="advocates.php?status=pending">Review</a>
        </div>
    <?php endforeach; endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
