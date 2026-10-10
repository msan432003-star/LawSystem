<?php
/** advocate/index.php - advocate dashboard */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId   = (int) $_SESSION['profile_id'];
$loginId = (int) $_SESSION['login_id'];

$activeCases  = $db->count('cases', "assigned_advocate_id = ? AND status IN ('assigned','ongoing')", [$advId]);
$myPending    = $db->count('proposal', "advocate_id = ? AND status = 'pending'", [$advId]);
$availableNow = (int) $db->fetchValue(
    "SELECT COUNT(*) FROM cases c WHERE c.status IN ('pending','proposed')
     AND NOT EXISTS (SELECT 1 FROM proposal p WHERE p.case_id = c.case_id AND p.advocate_id = ?)", [$advId]);
$unread       = $db->count('notification', 'login_id = ? AND is_read = 0', [$loginId]);
$avgRate      = $db->fetchValue('SELECT ROUND(AVG(rate),1) FROM ratings WHERE advocate_id = ?', [$advId]);

$hearings = $db->fetchAll(
    "SELECT h.hearing_date, h.purpose, c.case_id, c.title
     FROM hearing h JOIN cases c ON c.case_id = h.case_id
     WHERE c.assigned_advocate_id = ? AND h.status = 'scheduled' AND h.hearing_date >= NOW()
     ORDER BY h.hearing_date LIMIT 5", [$advId]);

$newCases = $db->fetchAll(
    "SELECT c.case_id, c.title, c.created_at, t.type_name
     FROM cases c JOIN case_type t ON t.type_id = c.type_id
     WHERE c.status IN ('pending','proposed')
       AND NOT EXISTS (SELECT 1 FROM proposal p WHERE p.case_id = c.case_id AND p.advocate_id = ?)
     ORDER BY c.created_at DESC LIMIT 5", [$advId]);

$pageTitle = 'Advocate Dashboard'; $activePage = 'dashboard';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-1">Welcome, <?= e($_SESSION['display_name']) ?></h3>
<p class="text-secondary mb-4">Your practice at a glance.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $activeCases ?></div><div class="lbl">Active cases</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $myPending ?></div><div class="lbl">Proposals awaiting reply</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $availableNow ?></div><div class="lbl">New cases to review</div></div></div>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="num"><?= $avgRate !== null ? e((string) $avgRate) : '-' ?></div><div class="lbl">Average rating (<?= $unread ?> unread alerts)</div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0">Newest cases open for proposals</h5>
                <a href="browse_cases.php">See all &rarr;</a>
            </div>
            <?php if (!$newCases): ?><p class="text-secondary mb-0">No new cases right now.</p>
            <?php else: foreach ($newCases as $c): ?>
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div><div class="fw-semibold"><?= e($c['title']) ?></div>
                        <div class="small text-secondary"><?= e($c['type_name']) ?> &middot; <?= e(date('d M Y', strtotime($c['created_at']))) ?></div></div>
                    <a class="btn btn-sm btn-outline-secondary" href="case_detail.php?id=<?= (int) $c['case_id'] ?>">Review</a>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <h5 class="mb-3">Upcoming hearings</h5>
            <?php if (!$hearings): ?><p class="text-secondary mb-0">No hearings scheduled.</p>
            <?php else: foreach ($hearings as $h): ?>
                <div class="border-bottom py-2">
                    <div class="fw-semibold"><?= e(date('d M Y, h:i A', strtotime($h['hearing_date']))) ?></div>
                    <a href="case_manage.php?id=<?= (int) $h['case_id'] ?>"><?= e($h['title']) ?></a>
                    <div class="small text-secondary"><?= e($h['purpose'] ?? '') ?></div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
