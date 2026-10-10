<?php
/**
 * client/payments.php - pay the fee of an accepted proposal, and see payment history.
 * NOTE: this is a demo payment (no real gateway). It records a "paid" payment and moves the case to "ongoing".
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);
$clientId = (int) $_SESSION['profile_id'];
$methods  = ['upi' => 'UPI', 'card' => 'Card', 'netbanking' => 'Net banking', 'cash' => 'Cash'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/payments.php'); }
    $pid    = (int) ($_POST['proposal_id'] ?? 0);
    $method = $_POST['method'] ?? '';

    // Must be an accepted proposal on MY case, and not paid already
    $p = $db->fetchOne(
        "SELECT p.proposal_id, p.fee, p.case_id, c.title, a.login_id AS adv_login
         FROM proposal p
         JOIN cases c ON c.case_id = p.case_id
         JOIN advocate a ON a.advocate_id = p.advocate_id
         WHERE p.proposal_id = ? AND c.client_id = ? AND p.status = 'accepted'
           AND NOT EXISTS (SELECT 1 FROM payment pay WHERE pay.proposal_id = p.proposal_id AND pay.status = 'paid')",
        [$pid, $clientId]
    );
    if (!$p || !isset($methods[$method])) {
        flash('This payment cannot be made.', 'danger');
        redirect('/client/payments.php');
    }

    try {
        $pdo->beginTransaction();
        $ref = 'TXN' . date('ymdHis') . random_int(100, 999);
        $pdo->prepare("INSERT INTO payment (proposal_id, client_id, amount, method, transaction_ref, status, paid_at)
                       VALUES (?,?,?,?,?,'paid',NOW())")
            ->execute([$p['proposal_id'], $clientId, $p['fee'], $method, $ref]);
        $pdo->prepare("UPDATE cases SET status='ongoing' WHERE case_id=? AND client_id=? AND status='assigned'")
            ->execute([$p['case_id'], $clientId]);
        $pdo->prepare('INSERT INTO notification (login_id, title, message) VALUES (?,?,?)')
            ->execute([$p['adv_login'], 'Payment received', mb_substr('Payment received for "' . $p['title'] . '". The case is now ongoing.', 0, 500)]);
        $pdo->commit();
        flash('Payment successful. Reference: ' . $ref);
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) { $pdo->rollBack(); }
        error_log('Payment failed: ' . $ex->getMessage());
        flash('Payment failed. Please try again.', 'danger');
    }
    redirect('/client/payments.php');
}

$due = $db->fetchAll(
    "SELECT p.proposal_id, p.fee, c.title, c.case_id, a.first_name, a.last_name
     FROM proposal p
     JOIN cases c ON c.case_id = p.case_id
     JOIN advocate a ON a.advocate_id = p.advocate_id
     WHERE c.client_id = ? AND p.status = 'accepted'
       AND NOT EXISTS (SELECT 1 FROM payment pay WHERE pay.proposal_id = p.proposal_id AND pay.status = 'paid')",
    [$clientId]
);
$history = $db->fetchAll(
    "SELECT pay.*, c.title FROM payment pay
     JOIN proposal p ON p.proposal_id = pay.proposal_id
     JOIN cases c ON c.case_id = p.case_id
     WHERE pay.client_id = ? ORDER BY pay.created_at DESC",
    [$clientId]
);
$badge = ['pending' => 'warning text-dark', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'secondary'];

$pageTitle = 'Payments'; $activePage = 'payments';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Payments</h3>

<div class="panel mb-3">
    <h5>Fees to pay</h5>
    <?php if (!$due): ?>
        <p class="text-secondary mb-0">Nothing to pay right now. Accept an advocate's proposal first.</p>
    <?php else: foreach ($due as $d): ?>
        <form method="post" class="border-bottom py-3 row g-2 align-items-end"><?= csrf_field() ?>
            <input type="hidden" name="proposal_id" value="<?= (int) $d['proposal_id'] ?>">
            <div class="col-md-5">
                <div class="fw-semibold"><?= e($d['title']) ?></div>
                <div class="text-secondary small">Advocate: <?= e($d['first_name'] . ' ' . $d['last_name']) ?></div>
            </div>
            <div class="col-md-2 fs-5 fw-bold" style="color:#0A5C36">&#8377; <?= number_format((float) $d['fee'], 2) ?></div>
            <div class="col-md-3">
                <select class="form-select" name="method" required>
                    <option value="">Payment method</option>
                    <?php foreach ($methods as $k => $label): ?><option value="<?= $k ?>"><?= e($label) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-emerald">Pay now</button></div>
        </form>
    <?php endforeach; endif; ?>
</div>

<div class="panel">
    <h5>Payment history</h5>
    <?php if (!$history): ?>
        <p class="text-secondary mb-0">No payments yet.</p>
    <?php else: ?>
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>Date</th><th>Case</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
            <tr>
                <td><?= e(date('d M Y', strtotime($h['paid_at'] ?: $h['created_at']))) ?></td>
                <td><?= e($h['title']) ?></td>
                <td>&#8377; <?= number_format((float) $h['amount'], 2) ?></td>
                <td><?= e($methods[$h['method']] ?? $h['method']) ?></td>
                <td><?= e($h['transaction_ref'] ?? '-') ?></td>
                <td><span class="badge bg-<?= $badge[$h['status']] ?? 'secondary' ?>"><?= e(ucfirst($h['status'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
