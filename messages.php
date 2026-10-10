<?php
/** admin/messages.php - messages sent from the public Contact page */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);
$self = '/admin/messages.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    $do = $_POST['do'] ?? '';
    $id = (int) ($_POST['message_id'] ?? 0);

    if ($do === 'read') {
        $db->update('contact_message', ['is_read' => 1], 'message_id = ?', [$id]);
    } elseif ($do === 'unread') {
        $db->update('contact_message', ['is_read' => 0], 'message_id = ?', [$id]);
    } elseif ($do === 'read_all') {
        $db->update('contact_message', ['is_read' => 1], 'is_read = 0', []);
        flash('All messages marked as read.');
    } elseif ($do === 'delete') {
        $db->delete('contact_message', 'message_id = ?', [$id]);
        flash('Message deleted.');
    }
    redirect($self);
}

$msgs   = $db->fetchAll('SELECT * FROM contact_message ORDER BY created_at DESC LIMIT 200');
$unread = $db->count('contact_message', 'is_read = 0');

$pageTitle = 'Messages'; $activePage = 'messages';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Contact messages <?php if ($unread): ?><span class="badge bg-success fs-6"><?= $unread ?> new</span><?php endif; ?></h3>
    <?php if ($unread): ?>
    <form method="post"><?= csrf_field() ?><button class="btn btn-sm btn-outline-dark" name="do" value="read_all">Mark all as read</button></form>
    <?php endif; ?>
</div>
<?php if (!$msgs): ?>
    <div class="panel"><p class="text-secondary mb-0">No messages yet.</p></div>
<?php else: foreach ($msgs as $m): ?>
    <div class="panel mb-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="<?= $m['is_read'] ? '' : 'fw-bold' ?>">
                    <?php if (!$m['is_read']): ?><span class="badge bg-success me-1">New</span><?php endif; ?><?= e($m['subject']) ?>
                </div>
                <div class="small text-secondary"><?= e($m['name']) ?> &middot; <?= e($m['email']) ?> &middot; <?= e(date('d M Y, h:i A', strtotime($m['created_at']))) ?></div>
            </div>
            <form method="post" class="text-nowrap"><?= csrf_field() ?>
                <input type="hidden" name="message_id" value="<?= (int) $m['message_id'] ?>">
                <a class="btn btn-sm btn-emerald" href="mailto:<?= e($m['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $m['subject'])) ?>">Reply by email</a>
                <?php if ($m['is_read']): ?>
                    <button class="btn btn-sm btn-outline-secondary" name="do" value="unread">Mark unread</button>
                <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" name="do" value="read">Mark read</button>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-danger" name="do" value="delete" onclick="return confirm('Delete this message?')">Delete</button>
            </form>
        </div>
        <p class="mt-2 mb-0"><?= nl2br(e($m['message'])) ?></p>
    </div>
<?php endforeach; endif; ?>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
