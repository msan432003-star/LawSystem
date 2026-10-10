<?php
/** includes/sidebar_client.php - client menu. Pages not built yet are marked "soon". */
$sbActive = $activePage ?? '';
$sbMenu = [
    // key            => [label,               file,                 icon,                      built?]
    'dashboard'     => ['Dashboard',        'client/index.php',    'fa-gauge',                true],
    'add_case'      => ['Add a Case',       'client/add_case.php', 'fa-plus-circle',          true],
    'my_cases'      => ['My Cases',         'client/my_cases.php', 'fa-folder-open',          true],
    'proposals'     => ['Proposals',        'client/proposals.php','fa-file-signature',       true],
    'payments'      => ['Payments',         'client/payments.php', 'fa-credit-card',          true],
    'documents'     => ['Documents',        'client/documents.php','fa-file-lines',           true],
    'chat'          => ['Chat',             'client/chat.php',     'fa-comments',             true],
    'ratings'       => ['Ratings',          'client/ratings.php',  'fa-star',                 true],
    'notifications' => ['Notifications',    'client/notifications.php', 'fa-bell',            true],
    'complaints'    => ['Complaints',       'client/complaints.php','fa-triangle-exclamation',true],
    'profile'       => ['My Profile',       'client/profile.php',  'fa-id-card',              true],
];
?>
<aside class="dash-sidebar">
    <ul class="nav flex-column">
        <?php foreach ($sbMenu as $sbKey => [$sbLabel, $sbFile, $sbIcon, $sbBuilt]): ?>
            <li class="nav-item">
                <?php if ($sbBuilt): ?>
                    <a class="nav-link <?= $sbActive === $sbKey ? 'active' : '' ?>" href="<?= BASE_URL ?>/<?= $sbFile ?>">
                        <i class="fa-solid <?= $sbIcon ?>"></i><?= e($sbLabel) ?>
                    </a>
                <?php else: ?>
                    <span class="nav-link text-muted" style="cursor:default">
                        <i class="fa-solid <?= $sbIcon ?>" style="color:#aaa"></i><?= e($sbLabel) ?>
                        <small class="ms-1">(soon)</small>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <li class="nav-item mt-2">
            <a class="nav-link" href="<?= BASE_URL ?>/find_advocate.php">
                <i class="fa-solid fa-magnifying-glass"></i>Find Advocate
            </a>
        </li>
    </ul>
</aside>