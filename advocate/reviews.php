<?php
/** advocate/reviews.php - ratings clients gave me */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId = (int) $_SESSION['profile_id'];

$avg   = $db->fetchValue('SELECT ROUND(AVG(rate),1) FROM ratings WHERE advocate_id = ?', [$advId]);
$total = $db->count('ratings', 'advocate_id = ?', [$advId]);
$list  = $db->fetchAll(
    'SELECT r.rate, r.review, r.created_at, cl.first_name FROM ratings r JOIN client cl ON cl.client_id = r.client_id
     WHERE r.advocate_id = ? ORDER BY r.created_at DESC', [$advId]);

$pageTitle = 'My Reviews'; $activePage = 'reviews';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">My reviews</h3>
<div class="row g-3">
    <div class="col-lg-3"><div class="stat-card">
        <div class="num"><?= $avg !== null ? e((string) $avg) : '-' ?></div>
        <div class="lbl">Average rating from <?= $total ?> review<?= $total === 1 ? '' : 's' ?></div></div></div>
    <div class="col-lg-9"><div class="panel">
        <?php if (!$list): ?><p class="text-secondary mb-0">No reviews yet. Clients can rate you after a case is closed.</p>
        <?php else: foreach ($list as $r): ?>
            <div class="border-bottom pb-2 mb-2">
                <div><span style="color:#e0a800"><?= str_repeat('&#9733;', (int) $r['rate']) . str_repeat('&#9734;', 5 - (int) $r['rate']) ?></span>
                    <span class="text-secondary small ms-2"><?= e($r['first_name']) ?> &middot; <?= e(date('d M Y', strtotime($r['created_at']))) ?></span></div>
                <?php if ($r['review']): ?><div><?= e($r['review']) ?></div><?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
