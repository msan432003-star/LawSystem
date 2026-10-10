<?php
/** advocate/browse_cases.php - cases still open for proposals */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId = (int) $_SESSION['profile_id'];

$types  = $db->fetchAll('SELECT type_id, type_name FROM case_type ORDER BY type_name');
$typeId = (int) ($_GET['type'] ?? 0);

$sql = "SELECT c.case_id, c.title, c.created_at, c.case_date, t.type_name, cl.place,
               p.status AS my_status, p.fee AS my_fee
        FROM cases c
        JOIN case_type t ON t.type_id = c.type_id
        JOIN client cl ON cl.client_id = c.client_id
        LEFT JOIN proposal p ON p.case_id = c.case_id AND p.advocate_id = ?
        WHERE c.status IN ('pending','proposed')";
$params = [$advId];
if ($typeId > 0) { $sql .= ' AND c.type_id = ?'; $params[] = $typeId; }
$sql .= ' ORDER BY c.created_at DESC';
$cases = $db->fetchAll($sql, $params);

$badge = ['pending' => 'warning text-dark', 'accepted' => 'success', 'rejected' => 'danger', 'withdrawn' => 'secondary'];

$pageTitle = 'Find Cases'; $activePage = 'browse';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Cases open for proposals</h3>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="browse_cases.php" class="btn btn-sm <?= $typeId === 0 ? 'btn-dark' : 'btn-outline-dark' ?>">All types</a>
    <?php foreach ($types as $t): ?>
        <a href="browse_cases.php?type=<?= (int) $t['type_id'] ?>"
           class="btn btn-sm <?= $typeId === (int) $t['type_id'] ? 'btn-dark' : 'btn-outline-dark' ?>"><?= e($t['type_name']) ?></a>
    <?php endforeach; ?>
</div>
<div class="panel">
<?php if (!$cases): ?><p class="text-secondary mb-0">No open cases match.</p>
<?php else: ?>
<div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Title</th><th>Type</th><th>Client place</th><th>Posted</th><th>My proposal</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($cases as $c): ?>
        <tr>
            <td><?= e($c['title']) ?></td>
            <td><?= e($c['type_name']) ?></td>
            <td><?= e($c['place'] ?? '-') ?></td>
            <td><?= e(date('d M Y', strtotime($c['created_at']))) ?></td>
            <td><?php if ($c['my_status']): ?>
                <span class="badge bg-<?= $badge[$c['my_status']] ?? 'secondary' ?>"><?= e(ucfirst($c['my_status'])) ?></span>
                &#8377; <?= number_format((float) $c['my_fee'], 2) ?>
                <?php else: ?>-<?php endif; ?></td>
            <td><a class="btn btn-sm btn-outline-secondary" href="case_detail.php?id=<?= (int) $c['case_id'] ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div>
<?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
