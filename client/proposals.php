<?php
/**
 * client/proposals.php - fee proposals from advocates on my cases; accept or reject.
 * Accepting: proposal -> accepted, case -> assigned to that advocate, other pending proposals -> rejected.
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);
$clientId = (int) $_SESSION['profile_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/proposals.php'); }
    $pid    = (int) ($_POST['proposal_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    // Ownership check: proposal must belong to one of MY cases and still be pending
    $p = $db->fetchOne(
        "SELECT p.proposal_id, p.case_id, p.advocate_id, a.login_id AS adv_login, c.title
         FROM proposal p
         JOIN cases c ON c.case_id = p.case_id
         JOIN advocate a ON a.advocate_id = p.advocate_id
         WHERE p.proposal_id = ? AND c.client_id = ? AND p.status = 'pending' AND c.status IN ('pending','proposed')",
        [$pid, $clientId]
    );
    if (!$p || !in_array($action, ['accept', 'reject'], true)) {
        flash('This proposal can no longer be changed.', 'danger');
        redirect('/client/proposals.php');
    }

    try {
        $pdo->beginTransaction();
        if ($action === 'accept') {
            $pdo->prepare("UPDATE proposal SET status='accepted', responded_at=NOW() WHERE proposal_id=?")->execute([$pid]);
            $pdo->prepare("UPDATE proposal SET status='rejected', responded_at=NOW() WHERE case_id=? AND proposal_id<>? AND status='pending'")
                ->execute([$p['case_id'], $pid]);
            $pdo->prepare("UPDATE cases SET assigned_advocate_id=?, status='assigned' WHERE case_id=? AND client_id=?")
                ->execute([$p['advocate_id'], $p['case_id'], $clientId]);
            $msg = 'Your proposal for "' . $p['title'] . '" was accepted.';
            $ttl = 'Proposal accepted';
        } else {
            $pdo->prepare("UPDATE proposal SET status='rejected', responded_at=NOW() WHERE proposal_id=?")->execute([$pid]);
            $left = $pdo->prepare("SELECT COUNT(*) FROM proposal WHERE case_id=? AND status='pending'");
            $left->execute([$p['case_id']]);
            if ((int) $left->fetchColumn() === 0) {
                $pdo->prepare("UPDATE cases SET status='pending' WHERE case_id=? AND status='proposed'")->execute([$p['case_id']]);
            }
            $msg = 'Your proposal for "' . $p['title'] . '" was declined.';
            $ttl = 'Proposal declined';
        }
        $pdo->prepare('INSERT INTO notification (login_id, title, message) VALUES (?,?,?)')
            ->execute([$p['adv_login'], $ttl, mb_substr($msg, 0, 500)]);
        $pdo->commit();
        flash($action === 'accept' ? 'Advocate assigned. You can now pay the fee from the Payments page.' : 'Proposal declined.');
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log('Proposal action failed: ' . $ex->getMessage());
        flash('Something went wrong. Please try again.', 'danger');
    }
    redirect('/client/proposals.php');
}

$rows = $db->fetchAll(
    "SELECT p.*, c.title, c.status AS case_status, a.first_name, a.last_name, a.specialization,
            a.experience_years, a.qualifications,
            (SELECT ROUND(AVG(rate),1) FROM ratings r WHERE r.advocate_id = a.advocate_id) AS avg_rate
     FROM proposal p
     JOIN cases c ON c.case_id = p.case_id
     JOIN advocate a ON a.advocate_id = p.advocate_id
     WHERE c.client_id = ?
     ORDER BY (p.status='pending') DESC, p.proposed_at DESC",
    [$clientId]
);
$badge = ['pending' => 'warning text-dark', 'accepted' => 'success', 'rejected' => 'danger', 'withdrawn' => 'secondary'];

$pageTitle = 'Proposals'; $activePage = 'proposals';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Proposals from advocates</h3>
<?php if (!$rows): ?>
    <div class="panel"><p class="text-secondary mb-0">No proposals yet. Advocates will send fee proposals for your pending cases.</p></div>
<?php else: foreach ($rows as $r): ?>
    <div class="panel mb-3">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <div>
                <div class="fw-semibold fs-5"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></div>
                <div class="text-secondary small">
                    <?= e($r['specialization'] ?? '') ?> &middot; <?= (int) $r['experience_years'] ?> yrs &middot; <?= e($r['qualifications'] ?? '') ?>
                    <?= $r['avg_rate'] ? ' &middot; &#9733; ' . e((string) $r['avg_rate']) : '' ?>
                </div>
                <div class="mt-1">Case: <a href="case_view.php?id=<?= (int) $r['case_id'] ?>"><?= e($r['title']) ?></a></div>
            </div>
            <div class="text-end">
                <div class="fs-4 fw-bold" style="color:#0A5C36">&#8377; <?= number_format((float) $r['fee'], 2) ?></div>
                <span class="badge bg-<?= $badge[$r['status']] ?? 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span>
                <div class="small text-muted"><?= e(date('d M Y', strtotime($r['proposed_at']))) ?></div>
            </div>
        </div>
        <?php if ($r['status'] === 'pending' && in_array($r['case_status'], ['pending', 'proposed'], true)): ?>
            <form method="post" class="mt-3 d-flex gap-2"><?= csrf_field() ?>
                <input type="hidden" name="proposal_id" value="<?= (int) $r['proposal_id'] ?>">
                <button class="btn btn-emerald btn-sm" name="action" value="accept"
                        onclick="return confirm('Accept this proposal and assign the advocate?')">Accept</button>
                <button class="btn btn-outline-danger btn-sm" name="action" value="reject"
                        onclick="return confirm('Decline this proposal?')">Decline</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; endif; ?>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
