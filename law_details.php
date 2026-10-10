<?php
/**
 * law_details.php - public law reference: search by title, IPC section or BNS section
 */
$pageTitle  = 'Law Details';
$activePage = 'laws';
require_once __DIR__ . '/includes/header_public.php';

$q = trim($_GET['q'] ?? '');

$laws = $db->fetchAll(
    "SELECT title, ipc_section, bns_section, description, penalty
     FROM law_details
     WHERE (? = '' OR title LIKE ? OR ipc_section = ? OR bns_section = ?)
     ORDER BY title
     LIMIT 100",
    [$q, '%' . $q . '%', $q, $q]
);
?>

<section class="section-dark py-5">
    <div class="container text-center">
        <h1 class="mb-2">Law Details</h1>
        <p class="section-sub mb-4">Search by offence name, IPC section or BNS section.</p>

        <form class="hero-search mx-auto" style="max-width:640px" method="get" action="law_details.php">
            <div class="row g-2">
                <div class="col-9">
                    <input type="text" name="q" class="form-control" value="<?= e($q) ?>"
                           placeholder="e.g. theft, 302, 103" aria-label="Search laws">
                </div>
                <div class="col-3 d-grid">
                    <button class="btn btn-emerald" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="section">
    <div class="container">
        <p class="text-secondary text-center mb-4">
            <?= count($laws) ?> result(s)
            <?php if ($q !== ''): ?>&middot; <a href="law_details.php">Show all</a><?php endif; ?>
        </p>

        <?php if (empty($laws)): ?>
            <p class="text-center text-secondary">No laws found. Try another keyword or section number.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle bg-white border">
                    <thead class="table-dark">
                        <tr>
                            <th>Offence</th>
                            <th>IPC</th>
                            <th>BNS</th>
                            <th>Description</th>
                            <th>Penalty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laws as $l): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($l['title']) ?></td>
                                <td><?= e($l['ipc_section'] ?: '-') ?></td>
                                <td><?= e($l['bns_section'] ?: '-') ?></td>
                                <td><?= e($l['description']) ?></td>
                                <td><?= e($l['penalty']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p class="text-secondary small text-center mt-4 mb-0">
            For general information only. This is not legal advice; please consult an advocate for your case.
        </p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
