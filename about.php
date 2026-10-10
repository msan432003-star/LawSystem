<?php
/**
 * about.php - static public page
 */
$pageTitle  = 'About';
$activePage = 'about';
require_once __DIR__ . '/includes/header_public.php';
?>

<section class="section-dark py-5">
    <div class="container text-center">
        <h1 class="mb-2">About Legal Advisor</h1>
        <p class="section-sub mb-0">Making it simpler to find an advocate and follow a case from start to finish.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title">Our purpose</h2>
                <p class="text-secondary">
                    Finding a trustworthy advocate and keeping track of a case is confusing for most people.
                    Legal Advisor brings clients, advocates and administrators onto one platform, so that
                    every step, from filing a case to the final hearing, is clear and recorded.
                </p>
                <ul class="check-list">
                    <li>Clients compare approved advocates and their fee proposals</li>
                    <li>Advocates manage cases, hearings, notes and documents</li>
                    <li>Admins verify advocates and keep the platform trustworthy</li>
                    <li>Law information (IPC and BNS sections) is available to everyone</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="stat-box">
                    <h4 class="serif mb-3">Why only approved advocates?</h4>
                    <p class="mb-0">
                        Every advocate registers with a Bar Council enrolment number and is reviewed by the admin
                        before appearing in search results.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-fog">
    <div class="container text-center">
        <h2 class="section-title">Three roles, one platform</h2>
        <p class="section-sub">Each user sees only the tools they need.</p>
        <div class="row g-4">
            <?php
            $roles = [
                ['Client', 'fa-user', 'Register a case, receive proposals, accept an advocate, track hearings, chat, rate and complain.'],
                ['Advocate', 'fa-user-tie', 'View client cases, send fee proposals, manage assigned cases, add hearings, notes and documents.'],
                ['Admin', 'fa-user-shield', 'Approve advocates, manage case types, laws, courts and news, and reply to complaints.'],
            ];
            foreach ($roles as $r): ?>
                <div class="col-md-4">
                    <div class="feature-card">
                        <span class="icon-circle mb-3"><i class="fa-solid <?= $r[1] ?>"></i></span>
                        <h5><?= e($r[0]) ?></h5>
                        <p class="text-secondary mb-0"><?= e($r[2]) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
