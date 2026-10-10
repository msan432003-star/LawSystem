<?php
/**
 * client/my_cases.php - list of the logged-in client's cases, with a status filter
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);

$clientId = (int) $_SESSION['profile_id'];

$statuses = ['pending','proposed','assigned','ongoing','closed','rejected'];
$filter   = $_GET['status'] ?? '';
if (!in_array($filter, $statuses, true)) {
    $filter = '';
}

$sql = "SELECT c.case_id, c.title, c.status, c.case_date, c.created_at, t.type_name,
               a.first_name AS adv_first, a.last_name AS adv_last,
               (SELECT COUNT(*) FROM proposal p WHERE p.case_id = c.case_id AND p.status = 'pending') AS open_proposals
        FROM cases c
        JOIN case_type t ON t.type_id = c.type_id
        LEFT JOIN advocate a ON a.advocate_id = c.assigned_advocate_id
        WHERE c.client_id = ?";
$params = [$clientId];
if ($filter !== '') {
    $sql .= ' AND c.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY c.created_at DESC';
$cases = $db->fetchAll($sql, $params);

$badge = ['pending'=>'warning text-dark','proposed'=>'info text-dark','assigned'=>'primary',
          'ongoing'=>'success','closed'=>'secondary','rejected'=>'danger'];

$pageTitle  = 'My Cases';
$activePage = 'my_cases';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h3 class="mb-0">My cases</h3>
    <a href="add_case.php" class="btn btn-emerald"><i class="fa-solid fa-plus me-1"></i>Add a case</a>
</div>

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="my_cases.php" class="btn btn-sm <?= $filter === '' ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
    <?php foreach ($statuses as $s): ?>
        <a href="my_cases.php?status=<?= $s ?>"
           class="btn btn-sm <?= $filter === $s ? 'btn-dark' : 'btn-outline-dark' ?>"><?= e(ucfirst($s)) ?></a>
    <?php endforeach; ?>
</div>

<div class="panel">
    <?php if (!$cases): ?>
        <p class="text-secondary mb-0">
            <?= $filter ? 'No cases with this status.' : 'You have not added any case yet.' ?>
        </p>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Title</th><th>Type</th><th>Status</th><th>Advocate</th><th>Proposals</th><th>Added</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($cases as $c): ?>
                <tr>
                    <td><?= (int) $c['case_id'] ?></td>
                    <td><?= e($c['title']) ?></td>
                    <td><?= e($c['type_name']) ?></td>
                    <td><span class="badge bg-<?= $badge[$c['status']] ?? 'secondary' ?>"><?= e(ucfirst($c['status'])) ?></span></td>
                    <td><?= $c['adv_first'] ? e($c['adv_first'] . ' ' . $c['adv_last']) : '<span class="text-secondary">Not assigned</span>' ?></td>
                    <td><?= (int) $c['open_proposals'] > 0 ? '<span class="badge bg-info text-dark">' . (int) $c['open_proposals'] . ' new</span>' : '-' ?></td>
                    <td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
                    <td><a class="btn btn-sm btn-outline-secondary" href="case_view.php?id=<?= (int) $c['case_id'] ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
