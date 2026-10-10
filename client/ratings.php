<?php
/** client/ratings.php - rate advocates from my closed cases (one rating per advocate, can be updated) */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);
$clientId = (int) $_SESSION['profile_id'];

$advocates = $db->fetchAll(
    "SELECT DISTINCT a.advocate_id, a.first_name, a.last_name, a.specialization,
            r.rate, r.review
     FROM cases c
     JOIN advocate a ON a.advocate_id = c.assigned_advocate_id
     LEFT JOIN ratings r ON r.advocate_id = a.advocate_id AND r.client_id = ?
     WHERE c.client_id = ? AND c.status = 'closed'",
    [$clientId, $clientId]
);
$allowed = array_map('intval', array_column($advocates, 'advocate_id'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/ratings.php'); }
    $aid  = (int) ($_POST['advocate_id'] ?? 0);
    $rate = (int) ($_POST['rate'] ?? 0);
    $rev  = trim($_POST['review'] ?? '');
    if (!in_array($aid, $allowed, true) || $rate < 1 || $rate > 5 || mb_strlen($rev) > 500) {
        flash('Please choose a rating from 1 to 5 (review up to 500 characters).', 'danger');
    } else {
        $pdo->prepare('INSERT INTO ratings (client_id, advocate_id, rate, review) VALUES (?,?,?,?)
                       ON DUPLICATE KEY UPDATE rate = VALUES(rate), review = VALUES(review), created_at = NOW()')
            ->execute([$clientId, $aid, $rate, $rev !== '' ? $rev : null]);
        flash('Thank you for your rating.');
    }
    redirect('/client/ratings.php');
}

$pageTitle = 'Ratings'; $activePage = 'ratings';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Rate your advocates</h3>
<?php if (!$advocates): ?>
    <div class="panel"><p class="text-secondary mb-0">You can rate an advocate once your case with them is closed.</p></div>
<?php else: foreach ($advocates as $a): ?>
    <div class="panel mb-3">
        <div class="fw-semibold fs-5"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></div>
        <div class="text-secondary small mb-2"><?= e($a['specialization'] ?? '') ?></div>
        <form method="post" class="row g-2 align-items-end"><?= csrf_field() ?>
            <input type="hidden" name="advocate_id" value="<?= (int) $a['advocate_id'] ?>">
            <div class="col-md-2">
                <label class="form-label">Rating</label>
                <select class="form-select" name="rate" required>
                    <option value="">Select</option>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>"<?= (int) $a['rate'] === $i ? ' selected' : '' ?>><?= $i ?> &#9733;</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-7">
                <label class="form-label">Review (optional)</label>
                <input class="form-control" name="review" maxlength="500" value="<?= e($a['review'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-grid"><button class="btn btn-emerald"><?= $a['rate'] ? 'Update rating' : 'Submit rating' ?></button></div>
        </form>
    </div>
<?php endforeach; endif; ?>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
