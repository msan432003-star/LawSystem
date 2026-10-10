<?php
/** admin/subscribers.php - newsletter subscribers from the footer form */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);
$self = '/admin/subscribers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    if (($_POST['do'] ?? '') === 'delete') {
        $db->delete('newsletter_subscriber', 'subscriber_id = ?', [(int) ($_POST['subscriber_id'] ?? 0)]);
        flash('Subscriber removed.');
    }
    redirect($self);
}

$subs = $db->fetchAll('SELECT subscriber_id, email, subscribed_at FROM newsletter_subscriber ORDER BY subscribed_at DESC');

$pageTitle = 'Subscribers'; $activePage = 'subscribers';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Newsletter subscribers <span class="badge bg-secondary fs-6"><?= count($subs) ?></span></h3>
<div class="row g-3">
    <div class="col-lg-8"><div class="panel">
        <?php if (!$subs): ?><p class="text-secondary mb-0">No subscribers yet.</p>
        <?php else: ?>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Email</th><th>Subscribed</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($subs as $s): ?>
                <tr>
                    <td><?= e($s['email']) ?></td>
                    <td><?= e(date('d M Y, h:i A', strtotime($s['subscribed_at']))) ?></td>
                    <td class="text-end">
                        <form method="post" class="d-inline"><?= csrf_field() ?>
                            <input type="hidden" name="do" value="delete">
                            <input type="hidden" name="subscriber_id" value="<?= (int) $s['subscriber_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this subscriber?')">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div></div>
    <?php if ($subs): ?>
    <div class="col-lg-4"><div class="panel">
        <h5>All emails</h5>
        <p class="small text-secondary">Copy this list to send a newsletter from your own email account.</p>
        <textarea class="form-control" rows="6" readonly onclick="this.select()"><?= e(implode(', ', array_column($subs, 'email'))) ?></textarea>
    </div></div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
