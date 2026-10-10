<?php
/**
 * advocate/case_detail.php - view an open case and send a fee proposal.
 * The client's phone/email stay hidden until the client accepts this advocate.
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId   = (int) $_SESSION['profile_id'];
$caseId  = (int) ($_GET['id'] ?? 0);

$case = $db->fetchOne(
    "SELECT c.*, t.type_name, ct.name AS court_name, cl.first_name AS cl_first, cl.place AS cl_place, cl.login_id AS cl_login
     FROM cases c
     JOIN case_type t ON t.type_id = c.type_id
     JOIN client cl ON cl.client_id = c.client_id
     LEFT JOIN court ct ON ct.court_id = c.court_id
     WHERE c.case_id = ? AND c.status IN ('pending','proposed')", [$caseId]);
if (!$case) { flash('This case is not open for proposals.', 'danger'); redirect('/advocate/browse_cases.php'); }

$mine = $db->fetchOne('SELECT * FROM proposal WHERE case_id = ? AND advocate_id = ?', [$caseId, $advId]);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/advocate/case_detail.php?id=' . $caseId); }
    $fee = $_POST['fee'] ?? '';

    if ($mine && $mine['status'] !== 'withdrawn') {
        $error = 'You have already sent a proposal for this case.';
    } elseif (!is_numeric($fee) || (float) $fee <= 0 || (float) $fee > 10000000) {
        $error = 'Enter a fee greater than 0.';
    } else {
        try {
            $db->begin();
            if ($mine) {   // re-sending after a withdrawal
                $db->update('proposal', ['fee' => round((float) $fee, 2), 'status' => 'pending', 'proposed_at' => date('Y-m-d H:i:s'), 'responded_at' => null],
                            'proposal_id = ?', [(int) $mine['proposal_id']]);
            } else {
                $db->insert('proposal', ['case_id' => $caseId, 'advocate_id' => $advId, 'fee' => round((float) $fee, 2)]);
            }
            $db->update('cases', ['status' => 'proposed'], "case_id = ? AND status = 'pending'", [$caseId]);
            $db->insert('notification', [
                'login_id' => (int) $case['cl_login'], 'title' => 'New proposal',
                'message'  => mb_substr($_SESSION['display_name'] . ' sent a fee proposal for "' . $case['title'] . '".', 0, 500),
            ]);
            $db->commit();
            flash('Proposal sent to the client.');
            redirect('/advocate/my_proposals.php');
        } catch (PDOException $ex) {
            $db->rollback();
            error_log('Proposal failed: ' . $ex->getMessage());
            $error = 'Could not send the proposal. Please try again.';
        }
    }
}

$pageTitle = 'Case #' . $caseId; $activePage = 'browse';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<a href="browse_cases.php" class="small">&larr; Back to cases</a>
<h3 class="my-2"><?= e($case['title']) ?></h3>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel">
            <dl class="row mb-0">
                <dt class="col-sm-4">Case type</dt><dd class="col-sm-8"><?= e($case['type_name']) ?></dd>
                <dt class="col-sm-4">Client</dt><dd class="col-sm-8"><?= e($case['cl_first']) ?> (<?= e($case['cl_place'] ?? '-') ?>)</dd>
                <dt class="col-sm-4">Date of incident</dt><dd class="col-sm-8"><?= $case['case_date'] ? e(date('d M Y', strtotime($case['case_date']))) : '-' ?></dd>
                <dt class="col-sm-4">Police station</dt><dd class="col-sm-8"><?= e($case['police_station'] ?: '-') ?></dd>
                <dt class="col-sm-4">Court</dt><dd class="col-sm-8"><?= e($case['court_name'] ?: 'Not decided yet') ?></dd>
                <dt class="col-sm-4">Description</dt><dd class="col-sm-8"><?= nl2br(e($case['description'] ?? '')) ?></dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel">
            <h5>Your proposal</h5>
            <?php if ($mine && $mine['status'] !== 'withdrawn'): ?>
                <p class="mb-1">Fee: <strong>&#8377; <?= number_format((float) $mine['fee'], 2) ?></strong></p>
                <p class="text-secondary mb-0">Status: <?= e(ucfirst($mine['status'])) ?></p>
            <?php else: ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post"><?= csrf_field() ?>
                    <label class="form-label" for="fee">Your fee (&#8377;)</label>
                    <input class="form-control mb-3" type="number" step="0.01" min="1" id="fee" name="fee" required
                           value="<?= e($_POST['fee'] ?? '') ?>">
                    <button class="btn btn-emerald w-100">Send proposal</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
