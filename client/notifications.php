<?php
/** client/notifications.php - list notifications, mark as read */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);
$loginId = (int) $_SESSION['login_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/notifications.php'); }
    if (isset($_POST['read_all'])) {
        $pdo->prepare('UPDATE notification SET is_read = 1 WHERE login_id = ?')->execute([$loginId]);
    } elseif (isset($_POST['read_id'])) {
        $pdo->prepare('UPDATE notification SET is_read = 1 WHERE notification_id = ? AND login_id = ?')
            ->execute([(int) $_POST['read_id'], $loginId]);
    }
    redirect('/client/notifications.php');
}

$notices = $db->fetchAll('SELECT * FROM notification WHERE login_id = ? ORDER BY created_at DESC LIMIT 100', [$loginId]);

$pageTitle = 'Notifications'; $activePage = 'notifications';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Notifications</h3>
    <?php if ($notices): ?>
    <form method="post"><?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-dark" name="read_all" value="1">Mark all as read</button>
    </form>
    <?php endif; ?>
</div>
<div class="panel">
<?php if (!$notices): ?>
    <p class="text-secondary mb-0">No notifications yet.</p>
<?php else: foreach ($notices as $n): ?>
    <div class="d-flex justify-content-between align-items-start border-bottom py-2">
        <div>
            <div class="<?= $n['is_read'] ? '' : 'fw-bold' ?>">
                <?php if (!$n['is_read']): ?><span class="badge bg-success me-1">New</span><?php endif; ?>
                <?= e($n['title']) ?>
            </div>
            <div class="text-secondary"><?= e($n['message']) ?></div>
            <div class="small text-muted"><?= e(date('d M Y, h:i A', strtotime($n['created_at']))) ?></div>
        </div>
        <?php if (!$n['is_read']): ?>
        <form method="post"><?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-secondary" name="read_id" value="<?= (int) $n['notification_id'] ?>">Mark read</button>
        </form>
        <?php endif; ?>
    </div>
<?php endforeach; endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>