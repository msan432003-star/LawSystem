<?php
/** admin/clients.php - list clients, enable / disable accounts */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/admin/clients.php'); }
    $loginId = (int) ($_POST['login_id'] ?? 0);
    // only touch login rows that really belong to a client
    $ok = $db->count('client', 'login_id = ?', [$loginId]);
    if ($ok) {
        $db->update('login', ['is_active' => (int) !empty($_POST['make_active'])], 'login_id = ?', [$loginId]);
        flash('Account updated.');
    }
    redirect('/admin/clients.php');
}

$rows = $db->fetchAll(
    'SELECT cl.client_id, cl.login_id, cl.first_name, cl.last_name, cl.phone, cl.email, cl.place,
            l.username, l.is_active,
            (SELECT COUNT(*) FROM cases c WHERE c.client_id = cl.client_id) AS case_count
     FROM client cl JOIN login l ON l.login_id = cl.login_id ORDER BY cl.client_id DESC');

$pageTitle = 'Clients'; $activePage = 'clients';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Clients</h3>
<div class="panel">
<?php if (!$rows): ?><p class="text-secondary mb-0">No clients yet.</p>
<?php else: ?>
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Name</th><th>Username</th><th>Contact</th><th>Place</th><th>Cases</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $c): ?>
        <tr>
            <td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
            <td>@<?= e($c['username']) ?></td>
            <td><div class="small"><?= e($c['phone']) ?></div><div class="small"><?= e($c['email']) ?></div></td>
            <td><?= e($c['place'] ?? '-') ?></td>
            <td><?= (int) $c['case_count'] ?></td>
            <td><span class="badge bg-<?= $c['is_active'] ? 'success' : 'secondary' ?>"><?= $c['is_active'] ? 'Active' : 'Disabled' ?></span></td>
            <td class="text-end">
                <form method="post" class="d-inline"><?= csrf_field() ?>
                    <input type="hidden" name="login_id" value="<?= (int) $c['login_id'] ?>">
                    <?php if ($c['is_active']): ?>
                        <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Disable this client?')">Disable</button>
                    <?php else: ?>
                        <button class="btn btn-sm btn-outline-success" name="make_active" value="1">Enable</button>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
