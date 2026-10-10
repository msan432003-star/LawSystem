<?php
/**
 * client/documents.php - upload and download documents for my cases.
 * Files are saved in uploads/case_<id>/ and served through this page (with an ownership check).
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['client']);
$clientId = (int) $_SESSION['profile_id'];
$loginId  = (int) $_SESSION['login_id'];

$myCases = $db->fetchAll('SELECT case_id, title FROM cases WHERE client_id = ? ORDER BY created_at DESC', [$clientId]);
$caseIds = array_map('intval', array_column($myCases, 'case_id'));

// ---- Download ----
if (isset($_GET['download'])) {
    $f = $db->fetchOne(
        'SELECT f.file_title, f.file_path FROM case_file f JOIN cases c ON c.case_id = f.case_id
         WHERE f.file_id = ? AND c.client_id = ?',
        [(int) $_GET['download'], $clientId]
    );
    $full = $f ? BASE_PATH . '/' . $f['file_path'] : '';
    if (!$f || !is_file($full)) { flash('File not found.', 'danger'); redirect('/client/documents.php'); }
    $ext = pathinfo($full, PATHINFO_EXTENSION);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_\- ]/', '', $f['file_title']) . '.' . $ext . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

// ---- Upload ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/documents.php'); }
    $caseId = (int) ($_POST['case_id'] ?? 0);
    $title  = trim($_POST['file_title'] ?? '');
    $file   = $_FILES['document'] ?? null;
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    $err = '';

    if (!in_array($caseId, $caseIds, true))                   { $err = 'Choose one of your cases.'; }
    elseif ($title === '' || mb_strlen($title) > 100)         { $err = 'Enter a title (up to 100 characters).'; }
    elseif (!$file || $file['error'] !== UPLOAD_ERR_OK)       { $err = 'Please choose a file to upload.'; }
    elseif ($file['size'] > 5 * 1024 * 1024)                  { $err = 'File must be 5 MB or smaller.'; }
    else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) { $err = 'Allowed types: ' . implode(', ', $allowed) . '.'; }
    }

    if ($err === '') {
        $dir = UPLOAD_DIR . 'case_' . $caseId . '/';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
            $db->insert('case_file', [
                'case_id' => $caseId, 'uploaded_by_login_id' => $loginId,
                'file_title' => $title, 'file_path' => 'uploads/case_' . $caseId . '/' . $name,
            ]);
            flash('Document uploaded.');
        } else {
            flash('Upload failed. Check that the uploads folder is writable.', 'danger');
        }
    } else {
        flash($err, 'danger');
    }
    redirect('/client/documents.php');
}

$files = $db->fetchAll(
    'SELECT f.file_id, f.file_title, f.uploaded_at, c.title AS case_title
     FROM case_file f JOIN cases c ON c.case_id = f.case_id
     WHERE c.client_id = ? ORDER BY f.uploaded_at DESC',
    [$clientId]
);

$pageTitle = 'Documents'; $activePage = 'documents';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Documents</h3>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="panel">
            <h5>Upload a document</h5>
            <?php if (!$myCases): ?>
                <p class="text-secondary mb-0">Add a case first.</p>
            <?php else: ?>
            <form method="post" enctype="multipart/form-data"><?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Case</label>
                    <select class="form-select" name="case_id" required>
                        <option value="">Select case</option>
                        <?php foreach ($myCases as $c): ?><option value="<?= (int) $c['case_id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="mb-3"><label class="form-label">Title</label>
                    <input class="form-control" name="file_title" maxlength="100" required></div>
                <div class="mb-3"><label class="form-label">File (PDF, image, Word, max 5 MB)</label>
                    <input class="form-control" type="file" name="document" required></div>
                <button class="btn btn-emerald">Upload</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel">
            <h5>My documents</h5>
            <?php if (!$files): ?><p class="text-secondary mb-0">No documents uploaded.</p>
            <?php else: ?>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Title</th><th>Case</th><th>Uploaded</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($files as $f): ?>
                    <tr>
                        <td><i class="fa-solid fa-file me-2"></i><?= e($f['file_title']) ?></td>
                        <td><?= e($f['case_title']) ?></td>
                        <td><?= e(date('d M Y', strtotime($f['uploaded_at']))) ?></td>
                        <td><a class="btn btn-sm btn-outline-secondary" href="documents.php?download=<?= (int) $f['file_id'] ?>">Download</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
