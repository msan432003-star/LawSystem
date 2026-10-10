<?php
/** admin/complaints.php - read client complaints, reply, close */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);

$statuses = ['open', 'replied', 'closed'];
$filter = $_GET['status'] ?? 'open';
if ($filter !== 'all' && !in_array($filter, $statuses, true)) { $filter = 'open'; }
$self = '/admin/complaints.php?status=' . $filter;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    $id = (int) ($_POST['complaint_id'] ?? 0);
    $do = $_POST['do'] ?? '';
    $c = $db->fetchOne(
        'SELECT c.complaint_id, c.status, cl.login_id FROM complaints c JOIN client cl ON cl.client_id = c.client_id WHERE c.complaint_id = ?', [$id]);

    if (!$c) {
        flash('Complaint not found.', 'danger');
    } elseif ($do === 'reply') {
        $reply = trim($_POST['reply'] ?? '');
        if ($reply === '' || mb_strlen($reply) > 2000) {
            flash('Write a reply (up to 2000 characters).', 'danger');
        } else {
            $db->update('complaints', ['reply' => $reply, 'reply_date' => date('Y-m-d H:i:s'), 'status' => 'replied'],
                        'complaint_id = ?', [$id]);
            $db->insert('notification', ['login_id' => (int) $c['login_id'], 'title' => 'Complaint answered',
                'message' => 'The admin has replied to your complaint.']);
            flash('Reply sent.');
        }
    } elseif ($do === 'close') {
        $db->update('complaints', ['status' => 'closed'], 'complaint_id = ?', [$id]);
        flash('Complaint closed.');
    }
    redirect($self);
}

$sql = 'SELECT c.*, cl.first_name, cl.last_name, cl.phone, cl.email
        FROM complaints c JOIN client cl ON cl.client_id = c.client_id';
$params = [];
if ($filter !== 'all') { $sql .= ' WHERE c.status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY c.complaint_date DESC';
$rows = $db->fetchAll($sql, $params);
$badge = ['open' => 'warning text-dark', 'replied' => 'success', 'closed' => 'secondary'];

$pageTitle = 'Complaints'; $activePage = 'complaints';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Complaints</h3>
<div class="mb-3 d-flex flex-wrap gap-2">
    <?php foreach (array_merge($statuses, ['all']) as $s): ?>
        <a href="complaints.php?status=<?= $s ?>" class="btn btn-sm <?= $filter === $s ? 'btn-dark' : 'btn-outline-dark' ?>"><?= e(ucfirst($s)) ?></a>
    <?php endforeach; ?>
</div>
<?php if (!$rows): ?>
    <div class="panel"><p class="text-secondary mb-0">No complaints in this list.</p></div>
<?php else: foreach ($rows as $c): ?>
    <div class="panel mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="fw-semibold"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></div>
                <div class="small text-secondary"><?= e($c['phone']) ?> &middot; <?= e($c['email']) ?> &middot; <?= e(date('d M Y, h:i A', strtotime($c['complaint_date']))) ?></div>
            </div>
            <span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>"><?= e(ucfirst($c['status'])) ?></span>
        </div>
        <p class="mt-2 mb-2"><?= nl2br(e($c['description'])) ?></p>

        <?php if ($c['reply']): ?>
            <div class="border-start border-3 border-success ps-3 mb-2">
                <div class="small text-secondary">Your reply &middot; <?= $c['reply_date'] ? e(date('d M Y, h:i A', strtotime($c['reply_date']))) : '' ?></div>
                <?= nl2br(e($c['reply'])) ?>
            </div>
        <?php endif; ?>

        <?php if ($c['status'] !== 'closed'): ?>
        <form method="post" class="row g-2"><?= csrf_field() ?>
            <input type="hidden" name="complaint_id" value="<?= (int) $c['complaint_id'] ?>">
            <div class="col-md-9"><input class="form-control" name="reply" maxlength="2000" placeholder="<?= $c['reply'] ? 'Send another reply' : 'Write a reply' ?>"></div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-emerald flex-fill" name="do" value="reply">Reply</button>
                <button class="btn btn-outline-secondary" name="do" value="close" onclick="return confirm('Close this complaint?')">Close</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
<?php endforeach; endif; ?>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
