<?php
/**
 * client/index.php - client dashboard
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);

$clientId = (int) $_SESSION['profile_id'];
$loginId  = (int) $_SESSION['login_id'];

$openCases = (int) ($db->fetchOne(
    "SELECT COUNT(*) AS n FROM cases WHERE client_id = ? AND status NOT IN ('closed','rejected')",
    [$clientId]
)['n'] ?? 0);

$pendingProposals = (int) ($db->fetchOne(
    "SELECT COUNT(*) AS n FROM proposal p JOIN cases c ON c.case_id = p.case_id
     WHERE c.client_id = ? AND p.status = 'pending'",
    [$clientId]
)['n'] ?? 0);

$upcomingHearings = (int) ($db->fetchOne(
    "SELECT COUNT(*) AS n FROM hearing h JOIN cases c ON c.case_id = h.case_id
     WHERE c.client_id = ? AND h.status = 'scheduled' AND h.hearing_date >= NOW()",
    [$clientId]
)['n'] ?? 0);

$unread = (int) ($db->fetchOne(
    'SELECT COUNT(*) AS n FROM notification WHERE login_id = ? AND is_read = 0',
    [$loginId]
)['n'] ?? 0);

$nextHearing = $db->fetchOne(
    "SELECT h.hearing_date, h.purpose, c.title, c.case_id
     FROM hearing h JOIN cases c ON c.case_id = h.case_id
     WHERE c.client_id = ? AND h.status = 'scheduled' AND h.hearing_date >= NOW()
     ORDER BY h.hearing_date LIMIT 1",
    [$clientId]
);

$recentCases = $db->fetchAll(
    "SELECT c.case_id, c.title, c.status, c.created_at, t.type_name
     FROM cases c JOIN case_type t ON t.type_id = c.type_id
     WHERE c.client_id = ? ORDER BY c.created_at DESC LIMIT 5",
    [$clientId]
);

$badge = ['pending'=>'warning text-dark','proposed'=>'info text-dark','assigned'=>'primary',
          'ongoing'=>'success','closed'=>'secondary','rejected'=>'danger'];

$pageTitle  = 'Client Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<h3 class="mb-1">Welcome, <?= e($_SESSION['display_name']) ?></h3>
<p class="text-secondary mb-4">Here is a summary of your cases.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card">
        <div class="num"><?= $openCases ?></div><div class="lbl">Open cases</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card">
        <div class="num"><?= $pendingProposals ?></div><div class="lbl">Pending proposals</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card">
        <div class="num"><?= $upcomingHearings ?></div><div class="lbl">Upcoming hearings</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card">
        <div class="num"><?= $unread ?></div><div class="lbl">Unread notifications</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Recent cases</h5>
                <a href="add_case.php" class="btn btn-sm btn-emerald"><i class="fa-solid fa-plus me-1"></i>Add a case</a>
            </div>
            <?php if (!$recentCases): ?>
                <p class="text-secondary mb-0">You have not added any case yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Added</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentCases as $c): ?>
                        <tr>
                            <td><?= e($c['title']) ?></td>
                            <td><?= e($c['type_name']) ?></td>
                            <td><span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
                            <td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
                            <td><a class="btn btn-sm btn-outline-secondary" href="case_view.php?id=<?= (int) $c['case_id'] ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <div class="mt-3"><a href="my_cases.php">View all cases &rarr;</a></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel">
            <h5 class="mb-3">Next hearing</h5>
            <?php if ($nextHearing): ?>
                <div class="fw-semibold"><?= e(date('d M Y, h:i A', strtotime($nextHearing['hearing_date']))) ?></div>
                <div><?= e($nextHearing['title']) ?></div>
                <div class="text-secondary small"><?= e($nextHearing['purpose'] ?? '') ?></div>
                <a class="small" href="case_view.php?id=<?= (int) $nextHearing['case_id'] ?>">Open case</a>
            <?php else: ?>
                <p class="text-secondary mb-0">No hearings scheduled.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
