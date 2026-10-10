<?php
/**
 * index.php - public home page
 */
$pageTitle  = 'Home';
$activePage = 'home';
require_once __DIR__ . '/includes/header_public.php';

// ---- Data for the search box and sections (all from the database) ----
$cities = $db->fetchAll(
    "SELECT DISTINCT place FROM advocate
     WHERE approval_status = 'approved' AND place IS NOT NULL AND place <> '' ORDER BY place"
);
$caseTypes = $db->fetchAll('SELECT type_name, description FROM case_type ORDER BY type_name');

$topAdvocates = $db->fetchAll(
    "SELECT a.advocate_id, a.first_name, a.last_name, a.specialization, a.place, a.experience_years,
            ROUND(AVG(r.rate), 1) AS avg_rating, COUNT(r.rating_id) AS total_reviews
     FROM advocate a
     LEFT JOIN ratings r ON r.advocate_id = a.advocate_id
     WHERE a.approval_status = 'approved'
     GROUP BY a.advocate_id
     ORDER BY avg_rating DESC, a.experience_years DESC
     LIMIT 3"
);

// Icon for each practice area (falls back to a gavel)
$icons = [
    'Criminal' => 'fa-handcuffs',
    'Civil'    => 'fa-scale-balanced',
    'Family'   => 'fa-people-roof',
    'Property' => 'fa-house-chimney',
    'Cyber'    => 'fa-shield-halved',
];
?>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="eyebrow mb-3">Trusted legal help, online</div>
                <h1 class="mb-3">Find the right advocate for your <em>case</em></h1>
                <p class="lead mb-4">
                    Register your case, receive proposals from approved advocates, and follow every hearing and document in one place.
                </p>

                <form class="hero-search" method="get" action="<?= BASE_URL ?>/find_advocate.php">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <select name="city" class="form-select" aria-label="City">
                                <option value="">All cities</option>
                                <?php foreach ($cities as $c): ?>
                                    <option value="<?= e($c['place']) ?>"><?= e($c['place']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <select name="area" class="form-select" aria-label="Practice area">
                                <option value="">All practice areas</option>
                                <?php foreach ($caseTypes as $t): ?>
                                    <option value="<?= e($t['type_name']) ?>"><?= e($t['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-emerald" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="fa-solid fa-scale-balanced hero-icon"></i>
            </div>
        </div>
    </div>
</section>

<!-- ================= ABOUT / WHY US ================= -->
<section class="section" style="margin-top:-60px">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="stat-box text-center">
                    <div class="num">Simple</div>
                    <div>From filing a case to the final hearing</div>
                </div>
                <blockquote class="mt-4 ps-3" style="border-left:4px solid #0A5C36">
                    Everyone should have affordable and simple access to legal services.
                </blockquote>
            </div>
            <div class="col-lg-7">
                <h2 class="section-title">One platform for clients and advocates</h2>
                <p class="text-secondary">
                    Legal Advisor removes the guesswork from finding a lawyer. Clients compare approved advocates,
                    agree on a fee, and keep track of their case. Advocates manage their clients, hearings and notes digitally.
                </p>
                <ul class="check-list">
                    <li>Only admin-approved advocates are listed</li>
                    <li>Transparent fee proposals before you commit</li>
                    <li>Hearing dates, notes and documents for every case</li>
                    <li>Built-in chat, ratings and complaint support</li>
                </ul>
                <a href="<?= BASE_URL ?>/about.php" class="btn btn-emerald mt-2">Learn more</a>
            </div>
        </div>
    </div>
</section>

<!-- ================= PRACTICE AREAS ================= -->
<section class="section section-fog">
    <div class="container text-center">
        <h2 class="section-title">Legal Practice Areas</h2>
        <p class="section-sub">Choose the category that matches your case and see advocates who handle it.</p>

        <div class="row g-4 justify-content-center">
            <?php if (empty($caseTypes)): ?>
                <p class="text-secondary">Practice areas will appear here once the admin adds case types.</p>
            <?php else: ?>
                <?php foreach ($caseTypes as $t): ?>
                    <div class="col-6 col-md-4 col-lg-2">
                        <a class="text-decoration-none text-reset"
                           href="<?= BASE_URL ?>/find_advocate.php?area=<?= urlencode($t['type_name']) ?>">
                            <div class="feature-card">
                                <span class="icon-circle mb-3">
                                    <i class="fa-solid <?= e($icons[$t['type_name']] ?? 'fa-gavel') ?>"></i>
                                </span>
                                <h6 class="mb-0"><?= e($t['type_name']) ?></h6>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section class="section">
    <div class="container text-center">
        <h2 class="section-title">How it works</h2>
        <p class="section-sub">Four steps from problem to representation.</p>

        <div class="row g-4">
            <?php
            $steps = [
                ['Register',  'Create a free client account.',                    'fa-user-plus'],
                ['File a case', 'Describe your case and choose its type.',        'fa-file-pen'],
                ['Get proposals', 'Approved advocates send their fee proposals.', 'fa-handshake'],
                ['Track progress', 'Follow hearings, notes and documents.',       'fa-chart-line'],
            ];
            foreach ($steps as $i => $s): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card">
                        <span class="step-number"><?= $i + 1 ?></span>
                        <div class="mb-2"><i class="fa-solid <?= $s[2] ?> fa-lg" style="color:#0A5C36"></i></div>
                        <h5><?= e($s[0]) ?></h5>
                        <p class="text-secondary mb-0"><?= e($s[1]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= TOP ADVOCATES ================= -->
<?php if (!empty($topAdvocates)): ?>
<section class="section section-fog">
    <div class="container">
        <h2 class="section-title text-center">Top Rated Advocates</h2>
        <p class="section-sub text-center">Approved advocates, ranked by client ratings.</p>
        <div class="row g-4">
            <?php foreach ($topAdvocates as $adv): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="advocate-card">
                        <h5 class="mb-1"><?= e($adv['first_name'] . ' ' . $adv['last_name']) ?></h5>
                        <div class="text-secondary"><?= e($adv['specialization'] ?: 'General practice') ?></div>
                        <div class="text-secondary small mb-2">
                            <?= e($adv['place']) ?> &middot; <?= (int) $adv['experience_years'] ?> yr experience
                        </div>
                        <div class="rating">
                            <?php if ($adv['total_reviews'] > 0): ?>
                                <i class="fa-solid fa-star"></i> <?= e((string) $adv['avg_rating']) ?>
                                <span class="text-secondary small">(<?= (int) $adv['total_reviews'] ?> reviews)</span>
                            <?php else: ?>
                                <span class="text-secondary small">No ratings yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>/find_advocate.php" class="btn btn-emerald">See all advocates</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= CALL TO ACTION ================= -->
<section class="cta-band">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div>
            <h3 class="mb-1">Are you an advocate?</h3>
            <div>Join the platform, get approved, and reach clients looking for your expertise.</div>
        </div>
        <a href="<?= BASE_URL ?>/register_advocate.php" class="btn btn-light btn-lg">Register as Advocate</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>