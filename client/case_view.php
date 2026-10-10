<?php
/**
 * client/case_view.php - one case in full (read-only for now)
 * Ownership check: a client can only open their own cases.
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);

$clientId = (int) $_SESSION['profile_id'];
$caseId   = (int) ($_GET['id'] ?? 0);

$case = $db->fetchOne(
    "SELECT c.*, t.type_name, ct.name AS court_name, ct.place AS court_place,
            a.first_name AS adv_first, a.last_name AS adv_last, a.phone AS adv_phone,
            a.email AS adv_email, a.specialization
     FROM cases c
     JOIN case_type t ON t.type_id = c.type_id
     LEFT JOIN court ct ON ct.court_id = c.court_id
     LEFT JOIN advocate a ON a.advocate_id = c.assigned_advocate_id
     WHERE c.case_id = ? AND c.client_id = ?",
    [$caseId, $clientId]
);

if (!$case) {
    flash('Case not found.', 'danger');
    redirect('/client/my_cases.php');
}

// ---- Download one of this case's documents (case ownership was checked above) ----
if (isset($_GET['download'])) {
    $f = $db->fetchOne('SELECT file_title, file_path FROM case_file WHERE file_id = ? AND case_id = ?', [(int) $_GET['download'], $caseId]);
    $full = $f ? BASE_PATH . '/' . $f['file_path'] : '';
    if (!$f || !is_file($full)) {
        flash('File not found.', 'danger');
        redirect('/client/case_view.php?id=' . $caseId);
    }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_\- ]/', '', $f['file_title']) . '.' . pathinfo($full, PATHINFO_EXTENSION) . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

$hearings = $db->fetchAll(
    'SELECT hearing_date, purpose, status, outcome FROM hearing WHERE case_id = ? ORDER BY hearing_date DESC',
    [$caseId]
);
$notes = $db->fetchAll(
    'SELECT note_datetime, description FROM case_note WHERE case_id = ? ORDER BY note_datetime DESC',
    [$caseId]
);
$files = $db->fetchAll(
    'SELECT file_id, file_title, file_path, uploaded_at FROM case_file WHERE case_id = ? ORDER BY uploaded_at DESC',
    [$caseId]
);

$badge = ['pending'=>'warning text-dark','proposed'=>'info text-dark','assigned'=>'primary',
          'ongoing'=>'success','closed'=>'secondary','rejected'=>'danger'];

$pageTitle  = 'Case #' . $caseId;
$activePage = 'my_cases';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>

<a href="my_cases.php" class="small">&larr; Back to my cases</a>
<div class="d-flex flex-wrap justify-content-between align-items-center my-2 gap-2">
    <h3 class="mb-0"><?= e($case['title']) ?></h3>
    <span class="badge fs-6 bg-<?= $badge[$case['status']] ?? 'secondary' ?>"><?= e(ucfirst($case['status'])) ?></span>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-8">
        <div class="panel mb-3">
            <h5>Case details</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Case type</dt><dd class="col-sm-8"><?= e($case['type_name']) ?></dd>
                <dt class="col-sm-4">Date of incident</dt>
                <dd class="col-sm-8"><?= $case['case_date'] ? e(date('d M Y', strtotime($case['case_date']))) : '-' ?></dd>
                <dt class="col-sm-4">Police station</dt><dd class="col-sm-8"><?= e($case['police_station'] ?: '-') ?></dd>
                <dt class="col-sm-4">Court</dt>
                <dd class="col-sm-8"><?= $case['court_name'] ? e($case['court_name'] . ' - ' . $case['court_place']) : 'Not decided yet' ?></dd>
                <dt class="col-sm-4">Contact phone</dt><dd class="col-sm-8"><?= e($case['phone'] ?: '-') ?></dd>
                <dt class="col-sm-4">Registered on</dt>
                <dd class="col-sm-8"><?= e(date('d M Y, h:i A', strtotime($case['created_at']))) ?></dd>
                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8"><?= nl2br(e($case['description'] ?? '')) ?></dd>
            </dl>
        </div>

        <div class="panel mb-3">
            <h5>Hearings</h5>
            <?php if (!$hearings): ?>
                <p class="text-secondary mb-0">No hearings yet.</p>
            <?php else: ?>
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Purpose</th><th>Status</th><th>Outcome</th></tr></thead>
                    <tbody>
                    <?php foreach ($hearings as $h): ?>
                        <tr>
                            <td><?= e(date('d M Y, h:i A', strtotime($h['hearing_date']))) ?></td>
                            <td><?= e($h['purpose'] ?? '') ?></td>
                            <td><?= e(ucfirst($h['status'])) ?></td>
                            <td><?= e($h['outcome'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h5>Case notes</h5>
            <?php if (!$notes): ?>
                <p class="text-secondary mb-0">No notes yet.</p>
            <?php else: foreach ($notes as $n): ?>
                <div class="border-bottom pb-2 mb-2">
                    <div class="small text-secondary"><?= e(date('d M Y, h:i A', strtotime($n['note_datetime']))) ?></div>
                    <?= nl2br(e($n['description'])) ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel mb-3">
            <h5>Assigned advocate</h5>
            <?php if ($case['adv_first']): ?>
                <div class="fw-semibold"><?= e($case['adv_first'] . ' ' . $case['adv_last']) ?></div>
                <div class="text-secondary small mb-2"><?= e($case['specialization'] ?? '') ?></div>
                <div><i class="fa-solid fa-phone me-2"></i><?= e($case['adv_phone']) ?></div>
                <div><i class="fa-solid fa-envelope me-2"></i><?= e($case['adv_email']) ?></div>
            <?php else: ?>
                <p class="text-secondary mb-0">No advocate assigned yet. Proposals from advocates will appear here.</p>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h5>Documents</h5>
            <?php if (!$files): ?>
                <p class="text-secondary mb-0">No documents uploaded.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                <?php foreach ($files as $f): ?>
                    <li class="mb-1 d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-file me-2"></i><?= e($f['file_title']) ?>
                        <span class="text-secondary small">(<?= e(date('d M Y', strtotime($f['uploaded_at']))) ?>)</span></span>
                        <a class="small" href="case_view.php?id=<?= $caseId ?>&download=<?= (int) $f['file_id'] ?>">Download</a></li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
