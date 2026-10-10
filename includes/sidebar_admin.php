<?php
/** includes/sidebar_admin.php - admin menu (sb prefix so it never clashes with page variables) */
$sbActive = $activePage ?? '';
$sbMenu = [
    'dashboard'   => ['Dashboard',          'admin/index.php',       'fa-gauge'],
    'advocates'   => ['Advocate Approvals', 'admin/advocates.php',   'fa-user-check'],
    'clients'     => ['Clients',            'admin/clients.php',     'fa-users'],
    'cases'       => ['All Cases',          'admin/cases.php',       'fa-folder-open'],
    'complaints'  => ['Complaints',         'admin/complaints.php',  'fa-triangle-exclamation'],
    'messages'    => ['Contact Messages',   'admin/messages.php',    'fa-envelope'],
    'news'        => ['News',               'admin/news.php',        'fa-newspaper'],
    'laws'        => ['Law Details',        'admin/law_details.php', 'fa-scale-balanced'],
    'subscribers' => ['Subscribers',        'admin/subscribers.php', 'fa-paper-plane'],
    'types'       => ['Case Types',         'admin/case_types.php',  'fa-list'],
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
