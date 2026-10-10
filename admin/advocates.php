<?php
/** admin/advocates.php - approve / reject advocate registrations, enable / disable accounts */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);

$statuses = ['pending', 'approved', 'rejected'];
$filter = $_GET['status'] ?? 'pending';
if ($filter !== 'all' && !in_array($filter, $statuses, true)) { $filter = 'pending'; }
$self = '/admin/advocates.php?status=' . $filter;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    $id = (int) ($_POST['advocate_id'] ?? 0);
    $do = $_POST['do'] ?? '';
    $adv = $db->fetchOne('SELECT advocate_id, login_id, first_name FROM advocate WHERE advocate_id = ?', [$id]);

    if (!$adv) {
        flash('Advocate not found.', 'danger');
    } elseif ($do === 'approve' || $do === 'reject') {
        $new = $do === 'approve' ? 'approved' : 'rejected';
        $db->update('advocate', ['approval_status' => $new], 'advocate_id = ?', [$id]);
        $db->insert('notification', [
            'login_id' => (int) $adv['login_id'],
            'title'    => $do === 'approve' ? 'Account approved' : 'Registration not approved',
            'message'  => $do === 'approve'
                ? 'Your advocate account has been approved. You can now find cases and send proposals.'
                : 'Your advocate registration was not approved. Please contact the admin.',
        ]);
        flash('Advocate ' . $new . '.');
    } elseif ($do === 'toggle_active') {
        $db->update('login', ['is_active' => (int) !empty($_POST['make_active'])], 'login_id = ?', [(int) $adv['login_id']]);
        flash('Account updated.');
    }
    redirect($self);
}

$sql = 'SELECT a.*, l.username, l.is_active FROM advocate a JOIN login l ON l.login_id = a.login_id';
$params = [];
if ($filter !== 'all') { $sql .= ' WHERE a.approval_status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY a.advocate_id DESC';
$rows = $db->fetchAll($sql, $params);
$badge = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'];

$pageTitle = 'Advocate Approvals'; $activePage = 'advocates';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Advocates</h3>
<div class="mb-3 d-flex flex-wrap gap-2">
    <?php foreach (array_merge($statuses, ['all']) as $s): ?>
        <a href="advocates.php?status=<?= $s ?>" class="btn btn-sm <?= $filter === $s ? 'btn-dark' : 'btn-outline-dark' ?>"><?= e(ucfirst($s)) ?></a>
    <?php endforeach; ?>
</div>
<div class="panel">
<?php if (!$rows): ?><p class="text-secondary mb-0">No advocates in this list.</p>
<?php else: ?>
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Advocate</th><th>Bar Council no</th><th>Practice area</th><th>Contact</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $a): ?>
        <tr>
            <td><div class="fw-semibold"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></div>
                <div class="small text-secondary"><?= e($a['qualifications']) ?> &middot; <?= (int) $a['experience_years'] ?> yrs &middot; <?= e($a['place']) ?></div>
                <div class="small text-secondary">@<?= e($a['username']) ?></div></td>
            <td><?= e($a['bar_council_no']) ?></td>
            <td><?= e($a['specialization']) ?></td>
            <td><div class="small"><?= e($a['phone']) ?></div><div class="small"><?= e($a['email']) ?></div></td>
            <td><span class="badge bg-<?= $badge[$a['approval_status']] ?? 'secondary' ?>"><?= e(ucfirst($a['approval_status'])) ?></span>
                <?php if (!$a['is_active']): ?><span class="badge bg-dark">Disabled</span><?php endif; ?></td>
            <td class="text-end">
                <form method="post" class="d-inline"><?= csrf_field() ?>
                    <input type="hidden" name="advocate_id" value="<?= (int) $a['advocate_id'] ?>">
                    <?php if ($a['approval_status'] !== 'approved'): ?>
                        <button class="btn btn-sm btn-emerald" name="do" value="approve">Approve</button>
                    <?php endif; ?>
                    <?php if ($a['approval_status'] !== 'rejected'): ?>
                        <button class="btn btn-sm btn-outline-danger" name="do" value="reject"
                                onclick="return confirm('Reject this advocate?')">Reject</button>
                    <?php endif; ?>
                </form>
                <?php if ($a['approval_status'] === 'approved'): ?>
                <form method="post" class="d-inline"><?= csrf_field() ?>
                    <input type="hidden" name="advocate_id" value="<?= (int) $a['advocate_id'] ?>">
                    <input type="hidden" name="do" value="toggle_active">
                    <?php if ($a['is_active']): ?>
                        <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Disable this account?')">Disable</button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-success" name="make_active" value="1">Enable</button>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
