<?php
/**
 * find_advocate.php
 * Public page: search approved advocates by city and practice area.
 */
$pageTitle  = 'Find an Advocate';
$activePage = 'find';
require_once __DIR__ . '/includes/header_public.php';

// ---- Values for the two dropdowns (taken from approved advocates only) ----
$cities = $db->fetchAll(
    "SELECT DISTINCT place FROM advocate
     WHERE approval_status = 'approved' AND place IS NOT NULL AND place <> ''
     ORDER BY place"
);
$areas = $db->fetchAll(
    "SELECT DISTINCT specialization FROM advocate
     WHERE approval_status = 'approved' AND specialization IS NOT NULL AND specialization <> ''
     ORDER BY specialization"
);

// ---- Read the search filters (GET, so the result URL can be shared) ----
$city = trim($_GET['city'] ?? '');
$area = trim($_GET['area'] ?? '');

// ---- Search: an empty dropdown means "any" ----
$sql = "SELECT a.advocate_id, a.first_name, a.last_name, a.qualifications,
               a.specialization, a.experience_years, a.place,
               ROUND(AVG(r.rate), 1) AS avg_rating,
               COUNT(r.rating_id)    AS total_reviews
        FROM advocate a
        LEFT JOIN ratings r ON r.advocate_id = a.advocate_id
        WHERE a.approval_status = 'approved'
          AND (? = '' OR a.place = ?)
          AND (? = '' OR a.specialization = ?)
        GROUP BY a.advocate_id
        ORDER BY avg_rating DESC, a.experience_years DESC";

$advocates = $db->fetchAll($sql, [$city, $city, $area, $area]);
?>

<section class="section-dark py-5">
    <div class="container text-center">
        <h1 class="mb-2">Find an Advocate</h1>
        <p class="section-sub mb-4">Search approved advocates by city and practice area.</p>

        <form class="hero-search mx-auto" style="max-width:760px" method="get" action="find_advocate.php">
            <div class="row g-2">
                <div class="col-md-5">
                    <select name="city" class="form-select" aria-label="City">
                        <option value="">All cities</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?= e($c['place']) ?>" <?= $city === $c['place'] ? 'selected' : '' ?>>
                                <?= e($c['place']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <select name="area" class="form-select" aria-label="Practice area">
                        <option value="">All practice areas</option>
                        <?php foreach ($areas as $a): ?>
                            <option value="<?= e($a['specialization']) ?>" <?= $area === $a['specialization'] ? 'selected' : '' ?>>
                                <?= e($a['specialization']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-emerald" type="submit">Search</button>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="text-secondary text-center mb-4">
            <?= count($advocates) ?> advocate(s) found
            <?php if ($city !== '' || $area !== ''): ?>
                &middot; <a href="find_advocate.php">Reset filters</a>
            <?php endif; ?>
        </p>

        <?php if (empty($advocates)): ?>
            <p class="text-center text-secondary">No advocates match your search. Try a different city or practice area.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($advocates as $adv): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="advocate-card">
                            <h5 class="mb-1"><?= e($adv['first_name'] . ' ' . $adv['last_name']) ?></h5>
                            <div class="text-secondary"><?= e($adv['specialization'] ?: 'General practice') ?></div>
                            <div class="text-secondary small"><?= e($adv['qualifications']) ?></div>
                            <div class="text-secondary small mb-2">
                                <i class="fa-solid fa-location-dot me-1"></i><?= e($adv['place']) ?> &middot;
                                <?= (int) $adv['experience_years'] ?> yr experience
                            </div>
                            <div class="rating">
                                <?php if ($adv['total_reviews'] > 0): ?>
                                    <i class="fa-solid fa-star"></i> <?= e((string) $adv['avg_rating']) ?>
                                    <span class="text-secondary small">(<?= (int) $adv['total_reviews'] ?> reviews)</span>
                                <?php else: ?>
                                    <span class="text-secondary small">No ratings yet</span>
                                <?php endif; ?>
                            </div>
                            <!-- Later: link to advocate_profile.php?id=<?= (int) $adv['advocate_id'] ?> -->
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>