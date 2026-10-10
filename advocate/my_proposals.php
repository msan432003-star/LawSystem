<?php
/** advocate/my_proposals.php - proposals I sent; withdraw pending ones */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId = (int) $_SESSION['profile_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/advocate/my_proposals.php'); }
    $pid = (int) ($_POST['proposal_id'] ?? 0);
    $p = $db->fetchOne("SELECT proposal_id, case_id FROM proposal WHERE proposal_id = ? AND advocate_id = ? AND status = 'pending'", [$pid, $advId]);
    if ($p) {
        $db->update('proposal', ['status' => 'withdrawn', 'responded_at' => date('Y-m-d H:i:s')], 'proposal_id = ?', [$pid]);
        if ($db->count('proposal', "case_id = ? AND status = 'pending'", [$p['case_id']]) === 0) {
            $db->update('cases', ['status' => 'pending'], "case_id = ? AND status = 'proposed'", [$p['case_id']]);
        }
        flash('Proposal withdrawn.');
    } else {
        flash('This proposal can no longer be withdrawn.', 'danger');
    }
    redirect('/advocate/my_proposals.php');
}

$rows = $db->fetchAll(
    "SELECT p.*, c.title, c.status AS case_status, t.type_name
     FROM proposal p JOIN cases c ON c.case_id = p.case_id JOIN case_type t ON t.type_id = c.type_id
     WHERE p.advocate_id = ? ORDER BY p.proposed_at DESC", [$advId]);
$badge = ['pending' => 'warning text-dark', 'accepted' => 'success', 'rejected' => 'danger', 'withdrawn' => 'secondary'];

$pageTitle = 'My Proposals'; $activePage = 'proposals';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">My proposals</h3>
<div class="panel">
<?php if (!$rows): ?><p class="text-secondary mb-0">You have not sent any proposals yet.</p>
<?php else: ?>
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Case</th><th>Type</th><th>Fee</th><th>Sent</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['type_name']) ?></td>
            <td>&#8377; <?= number_format((float) $r['fee'], 2) ?></td>
            <td><?= e(date('d M Y', strtotime($r['proposed_at']))) ?></td>
            <td><span class="badge bg-<?= $badge[$r['status']] ?? 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
            <td class="text-end">
                <?php if ($r['status'] === 'pending'): ?>
                    <form method="post" class="d-inline"><?= csrf_field() ?>
                        <input type="hidden" name="proposal_id" value="<?= (int) $r['proposal_id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Withdraw this proposal?')">Withdraw</button>
                    </form>
                <?php elseif ($r['status'] === 'accepted'): ?>
                    <a class="btn btn-sm btn-outline-secondary" href="case_manage.php?id=<?= (int) $r['case_id'] ?>">Open case</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
