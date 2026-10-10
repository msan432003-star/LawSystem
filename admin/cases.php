<?php
/** admin/cases.php - read-only overview of every case */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);

$statuses = ['pending', 'proposed', 'assigned', 'ongoing', 'closed'];
$filter = $_GET['status'] ?? '';
if (!in_array($filter, $statuses, true)) { $filter = ''; }

$sql = "SELECT c.case_id, c.title, c.status, c.created_at, t.type_name,
               cl.first_name AS cl_first, cl.last_name AS cl_last,
               a.first_name AS adv_first, a.last_name AS adv_last
        FROM cases c
        JOIN case_type t ON t.type_id = c.type_id
        JOIN client cl ON cl.client_id = c.client_id
        LEFT JOIN advocate a ON a.advocate_id = c.assigned_advocate_id";
$params = [];
if ($filter !== '') { $sql .= ' WHERE c.status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY c.created_at DESC';
$cases = $db->fetchAll($sql, $params);
$badge = ['pending' => 'warning text-dark', 'proposed' => 'info', 'assigned' => 'primary', 'ongoing' => 'success', 'closed' => 'secondary'];

$pageTitle = 'All Cases'; $activePage = 'cases';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">All cases</h3>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="cases.php" class="btn btn-sm <?= $filter === '' ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
    <?php foreach ($statuses as $s): ?>
        <a href="cases.php?status=<?= $s ?>" class="btn btn-sm <?= $filter === $s ? 'btn-dark' : 'btn-outline-dark' ?>"><?= e(ucfirst($s)) ?></a>
    <?php endforeach; ?>
</div>
<div class="panel">
<?php if (!$cases): ?><p class="text-secondary mb-0">No cases here.</p>
<?php else: ?>
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>#</th><th>Title</th><th>Type</th><th>Client</th><th>Advocate</th><th>Status</th><th>Posted</th></tr></thead>
    <tbody>
    <?php foreach ($cases as $c): ?>
        <tr>
            <td><?= (int) $c['case_id'] ?></td>
            <td><?= e($c['title']) ?></td>
            <td><?= e($c['type_name']) ?></td>
            <td><?= e($c['cl_first'] . ' ' . $c['cl_last']) ?></td>
            <td><?= $c['adv_first'] ? e($c['adv_first'] . ' ' . $c['adv_last']) : '-' ?></td>
            <td><span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
            <td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
