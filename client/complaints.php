<?php
/** client/complaints.php - send a complaint to the admin and read replies */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);
$clientId = (int) $_SESSION['profile_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/complaints.php'); }
    $text = trim($_POST['description'] ?? '');
    if (mb_strlen($text) < 10 || mb_strlen($text) > 2000) {
        $error = 'Please write between 10 and 2000 characters.';
    } else {
        $db->insert('complaints', ['client_id' => $clientId, 'description' => $text]);
        flash('Complaint sent. The admin will reply here.');
        redirect('/client/complaints.php');
    }
}

$list = $db->fetchAll('SELECT * FROM complaints WHERE client_id = ? ORDER BY complaint_date DESC', [$clientId]);
$badge = ['open' => 'warning text-dark', 'replied' => 'success', 'closed' => 'secondary'];

$pageTitle = 'Complaints'; $activePage = 'complaints';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Complaints</h3>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="panel">
            <h5>New complaint</h5>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post"><?= csrf_field() ?>
                <textarea class="form-control mb-3" name="description" rows="5" required><?= e($_POST['description'] ?? '') ?></textarea>
                <button class="btn btn-emerald">Send complaint</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel">
            <h5>My complaints</h5>
            <?php if (!$list): ?><p class="text-secondary mb-0">No complaints yet.</p>
            <?php else: foreach ($list as $c): ?>
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="small text-secondary"><?= e(date('d M Y, h:i A', strtotime($c['complaint_date']))) ?></span>
                        <span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>"><?= e(ucfirst($c['status'])) ?></span>
                    </div>
                    <div class="mt-1"><?= nl2br(e($c['description'])) ?></div>
                    <?php if ($c['reply']): ?>
                        <div class="mt-2 p-2 rounded" style="background:#e8f3ed">
                            <strong>Admin reply:</strong> <?= nl2br(e($c['reply'])) ?>
                            <div class="small text-secondary"><?= $c['reply_date'] ? e(date('d M Y', strtotime($c['reply_date']))) : '' ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
