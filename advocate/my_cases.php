<?php
/** advocate/my_cases.php - cases assigned to me */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId = (int) $_SESSION['profile_id'];

$statuses = ['assigned', 'ongoing', 'closed'];
$filter = $_GET['status'] ?? '';
if (!in_array($filter, $statuses, true)) { $filter = ''; }

$sql = "SELECT c.case_id, c.title, c.status, c.created_at, t.type_name, cl.first_name, cl.last_name,
               (SELECT MIN(h.hearing_date) FROM hearing h WHERE h.case_id = c.case_id AND h.status = 'scheduled' AND h.hearing_date >= NOW()) AS next_hearing
        FROM cases c JOIN case_type t ON t.type_id = c.type_id JOIN client cl ON cl.client_id = c.client_id
        WHERE c.assigned_advocate_id = ?";
$params = [$advId];
if ($filter !== '') { $sql .= ' AND c.status = ?'; $params[] = $filter; }
$sql .= ' ORDER BY c.created_at DESC';
$cases = $db->fetchAll($sql, $params);
$badge = ['assigned' => 'primary', 'ongoing' => 'success', 'closed' => 'secondary'];

$pageTitle = 'My Cases'; $activePage = 'my_cases';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">My cases</h3>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="my_cases.php" class="btn btn-sm <?= $filter === '' ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
    <?php foreach ($statuses as $s): ?>
        <a href="my_cases.php?status=<?= $s ?>" class="btn btn-sm <?= $filter === $s ? 'btn-dark' : 'btn-outline-dark' ?>"><?= e(ucfirst($s)) ?></a>
    <?php endforeach; ?>
</div>
<div class="panel">
<?php if (!$cases): ?><p class="text-secondary mb-0">No cases here yet.</p>
<?php else: ?>
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Case</th><th>Client</th><th>Type</th><th>Status</th><th>Next hearing</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($cases as $c): ?>
        <tr>
            <td><?= e($c['title']) ?></td>
            <td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
            <td><?= e($c['type_name']) ?></td>
            <td><span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
            <td><?= $c['next_hearing'] ? e(date('d M Y, h:i A', strtotime($c['next_hearing']))) : '-' ?></td>
            <td><a class="btn btn-sm btn-outline-secondary" href="case_manage.php?id=<?= (int) $c['case_id'] ?>">Manage</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
