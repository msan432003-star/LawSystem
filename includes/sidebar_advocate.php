<?php
/** includes/sidebar_advocate.php - advocate menu (variables use the sb prefix so they never clash with page variables) */
$sbActive = $activePage ?? '';
$sbMenu = [
    'dashboard'     => ['Dashboard',      'advocate/index.php',         'fa-gauge'],
    'browse'        => ['Find Cases',     'advocate/browse_cases.php',  'fa-magnifying-glass'],
    'proposals'     => ['My Proposals',   'advocate/my_proposals.php',  'fa-file-signature'],
    'my_cases'      => ['My Cases',       'advocate/my_cases.php',      'fa-folder-open'],
    'chat'          => ['Chat',           'advocate/chat.php',          'fa-comments'],
    'reviews'       => ['My Reviews',     'advocate/reviews.php',       'fa-star'],
    'notifications' => ['Notifications',  'advocate/notifications.php', 'fa-bell'],
    'profile'       => ['My Profile',     'advocate/profile.php',       'fa-id-card'],
];
?>
<aside class="dash-sidebar">
    <ul class="nav flex-column">
        <?php foreach ($sbMenu as $sbKey => [$sbLabel, $sbFile, $sbIcon]): ?>
            <li class="nav-item">
                <a class="nav-link <?= $sbActive === $sbKey ? 'active' : '' ?>" href="<?= BASE_URL ?>/<?= $sbFile ?>">
                    <i class="fa-solid <?= $sbIcon ?>"></i><?= e($sbLabel) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>
