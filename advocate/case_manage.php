<?php
/**
 * advocate/case_manage.php - manage a case assigned to me:
 * client contact, hearings (add / update), notes, documents, and closing the case.
 */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId   = (int) $_SESSION['profile_id'];
$loginId = (int) $_SESSION['login_id'];
$caseId  = (int) ($_GET['id'] ?? 0);

// Ownership check: the case must be assigned to ME
$case = $db->fetchOne(
    "SELECT c.*, t.type_name, ct.name AS court_name, ct.place AS court_place,
            cl.first_name AS cl_first, cl.last_name AS cl_last, cl.phone AS cl_phone,
            cl.email AS cl_email, cl.login_id AS cl_login
     FROM cases c
     JOIN case_type t ON t.type_id = c.type_id
     JOIN client cl ON cl.client_id = c.client_id
     LEFT JOIN court ct ON ct.court_id = c.court_id
     WHERE c.case_id = ? AND c.assigned_advocate_id = ?", [$caseId, $advId]);
if (!$case) { flash('Case not found.', 'danger'); redirect('/advocate/my_cases.php'); }

$self = '/advocate/case_manage.php?id=' . $caseId;

// ---- Download a document ----
if (isset($_GET['download'])) {
    $f = $db->fetchOne('SELECT file_title, file_path FROM case_file WHERE file_id = ? AND case_id = ?', [(int) $_GET['download'], $caseId]);
    $full = $f ? BASE_PATH . '/' . $f['file_path'] : '';
    if (!$f || !is_file($full)) { flash('File not found.', 'danger'); redirect($self); }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_\- ]/', '', $f['file_title']) . '.' . pathinfo($full, PATHINFO_EXTENSION) . '"');
    header('Content-Length: ' . filesize($full));
    readfile($full);
    exit;
}

$isClosed = $case['status'] === 'closed';
$canWork  = $case['status'] === 'ongoing';   // hearings and notes unlock once the client has paid

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    $do = $_POST['do'] ?? '';

    if ($isClosed) {
        flash('This case is closed.', 'danger');

    } elseif (in_array($do, ['add_hearing', 'update_hearing', 'add_note'], true) && !$canWork) {
        flash('Hearings and notes can be added only after the client has paid and the case is ongoing.', 'danger');

    } elseif ($do === 'add_hearing') {
        $when    = str_replace('T', ' ', trim($_POST['hearing_date'] ?? ''));
        $purpose = trim($_POST['purpose'] ?? '');
        $ts      = strtotime($when);
        if ($ts === false || $ts < time()) {
            flash('Choose a hearing date and time in the future.', 'danger');
        } elseif ($purpose === '' || mb_strlen($purpose) > 200) {
            flash('Enter the purpose (up to 200 characters).', 'danger');
        } else {
            $db->insert('hearing', ['case_id' => $caseId, 'court_id' => $case['court_id'] ?: null,
                                    'hearing_date' => date('Y-m-d H:i:s', $ts), 'purpose' => $purpose]);
            $db->insert('notification', ['login_id' => (int) $case['cl_login'], 'title' => 'Hearing scheduled',
                'message' => mb_substr('A hearing for "' . $case['title'] . '" is set for ' . date('d M Y, h:i A', $ts) . '.', 0, 500)]);
            flash('Hearing added.');
        }

    } elseif ($do === 'update_hearing') {
        $hid    = (int) ($_POST['hearing_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $outcome = trim($_POST['outcome'] ?? '');
        if (in_array($status, ['scheduled', 'completed', 'adjourned', 'cancelled'], true) && mb_strlen($outcome) <= 1000) {
            $db->update('hearing', ['status' => $status, 'outcome' => $outcome !== '' ? $outcome : null],
                        'hearing_id = ? AND case_id = ?', [$hid, $caseId]);
            flash('Hearing updated.');
        }

    } elseif ($do === 'add_note') {
        $text = trim($_POST['description'] ?? '');
        if ($text === '' || mb_strlen($text) > 3000) {
            flash('Write a note (up to 3000 characters).', 'danger');
        } else {
            $db->insert('case_note', ['case_id' => $caseId, 'author_login_id' => $loginId, 'description' => $text]);
            flash('Note added.');
        }

    } elseif ($do === 'upload') {
        $title = trim($_POST['file_title'] ?? '');
        $file  = $_FILES['document'] ?? null;
        $err = '';
        if ($title === '' || mb_strlen($title) > 100)        { $err = 'Enter a title (up to 100 characters).'; }
        elseif (!$file || $file['error'] !== UPLOAD_ERR_OK)   { $err = 'Please choose a file.'; }
        elseif ($file['size'] > 5 * 1024 * 1024)              { $err = 'File must be 5 MB or smaller.'; }
        else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'], true)) { $err = 'Allowed: pdf, jpg, png, doc, docx.'; }
        }
        if ($err === '') {
            $dir = UPLOAD_DIR . 'case_' . $caseId . '/';
            if (!is_dir($dir)) { mkdir($dir, 0755, true); }
            $name = bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
                $db->insert('case_file', ['case_id' => $caseId, 'uploaded_by_login_id' => $loginId,
                    'file_title' => $title, 'file_path' => 'uploads/case_' . $caseId . '/' . $name]);
                flash('Document uploaded.');
            } else { flash('Upload failed. Check that the uploads folder is writable.', 'danger'); }
        } else { flash($err, 'danger'); }

    } elseif ($do === 'close_case') {
        if ($case['status'] !== 'ongoing') {
            flash('A case can be closed only after the client has paid and it is ongoing.', 'danger');
        } elseif ($db->count('hearing', "case_id = ? AND status = 'scheduled'", [$caseId]) > 0) {
            flash('Update every scheduled hearing (completed, adjourned or cancelled) before closing the case.', 'danger');
        } else {
            $db->update('cases', ['status' => 'closed'], "case_id = ? AND status = 'ongoing'", [$caseId]);
            $db->insert('notification', ['login_id' => (int) $case['cl_login'], 'title' => 'Case closed',
                'message' => mb_substr('Your case "' . $case['title'] . '" is closed. You can now rate your advocate.', 0, 500)]);
            flash('Case closed.');
        }
    }
    redirect($self);
}

$hearings = $db->fetchAll('SELECT * FROM hearing WHERE case_id = ? ORDER BY hearing_date DESC', [$caseId]);
$notes    = $db->fetchAll('SELECT * FROM case_note WHERE case_id = ? ORDER BY note_datetime DESC', [$caseId]);
$files    = $db->fetchAll('SELECT * FROM case_file WHERE case_id = ? ORDER BY uploaded_at DESC', [$caseId]);
$badge = ['assigned' => 'primary', 'ongoing' => 'success', 'closed' => 'secondary'];

$pageTitle = 'Case #' . $caseId; $activePage = 'my_cases';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<a href="my_cases.php" class="small">&larr; Back to my cases</a>
<div class="d-flex flex-wrap justify-content-between align-items-center my-2 gap-2">
    <h3 class="mb-0"><?= e($case['title']) ?></h3>
    <span class="badge fs-6 bg-<?= $badge[$case['status']] ?? 'secondary' ?>"><?= e(ucfirst($case['status'])) ?></span>
</div>
<?php if ($case['status'] === 'assigned'): ?>
    <div class="alert alert-info">Waiting for the client's payment. The case becomes <strong>ongoing</strong> once they pay.</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel mb-3">
            <h5>Case details</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Type</dt><dd class="col-sm-8"><?= e($case['type_name']) ?></dd>
                <dt class="col-sm-4">Date of incident</dt><dd class="col-sm-8"><?= $case['case_date'] ? e(date('d M Y', strtotime($case['case_date']))) : '-' ?></dd>
                <dt class="col-sm-4">Police station</dt><dd class="col-sm-8"><?= e($case['police_station'] ?: '-') ?></dd>
                <dt class="col-sm-4">Court</dt><dd class="col-sm-8"><?= $case['court_name'] ? e($case['court_name'] . ' - ' . $case['court_place']) : 'Not decided yet' ?></dd>
                <dt class="col-sm-4">Description</dt><dd class="col-sm-8"><?= nl2br(e($case['description'] ?? '')) ?></dd>
            </dl>
        </div>

        <div class="panel mb-3">
            <h5>Hearings</h5>
            <?php if ($case['status'] === 'assigned'): ?><p class="small text-secondary">Hearings can be scheduled once the client has paid.</p><?php endif; ?>
            <?php if ($canWork): ?>
            <form method="post" class="row g-2 mb-3"><?= csrf_field() ?>
                <input type="hidden" name="do" value="add_hearing">
                <div class="col-md-4"><input class="form-control" type="datetime-local" name="hearing_date" required></div>
                <div class="col-md-6"><input class="form-control" name="purpose" maxlength="200" placeholder="Purpose (e.g. First hearing)" required></div>
                <div class="col-md-2 d-grid"><button class="btn btn-emerald">Add</button></div>
            </form>
            <?php endif; ?>
            <?php if (!$hearings): ?><p class="text-secondary mb-0">No hearings yet.</p>
            <?php else: foreach ($hearings as $h): ?>
                <form method="post" class="border-top pt-2 mt-2 row g-2 align-items-center"><?= csrf_field() ?>
                    <input type="hidden" name="do" value="update_hearing">
                    <input type="hidden" name="hearing_id" value="<?= (int) $h['hearing_id'] ?>">
                    <div class="col-md-4"><strong><?= e(date('d M Y, h:i A', strtotime($h['hearing_date']))) ?></strong>
                        <div class="small text-secondary"><?= e($h['purpose'] ?? '') ?></div></div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" name="status" <?= $isClosed ? 'disabled' : '' ?>>
                            <?php foreach (['scheduled', 'completed', 'adjourned', 'cancelled'] as $s): ?>
                                <option value="<?= $s ?>"<?= $h['status'] === $s ? ' selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-3"><input class="form-control form-control-sm" name="outcome" maxlength="1000" placeholder="Outcome" value="<?= e($h['outcome'] ?? '') ?>" <?= $isClosed ? 'disabled' : '' ?>></div>
                    <div class="col-md-2 d-grid"><?php if (!$isClosed): ?><button class="btn btn-sm btn-outline-dark">Save</button><?php endif; ?></div>
                </form>
            <?php endforeach; endif; ?>
        </div>

        <div class="panel">
            <h5>Case notes</h5>
            <?php if ($case['status'] === 'assigned'): ?><p class="small text-secondary">Notes can be added once the client has paid.</p><?php endif; ?>
            <?php if ($canWork): ?>
            <form method="post" class="mb-3"><?= csrf_field() ?>
                <input type="hidden" name="do" value="add_note">
                <textarea class="form-control mb-2" name="description" rows="3" required placeholder="Add a note"></textarea>
                <button class="btn btn-sm btn-emerald">Add note</button>
            </form>
            <?php endif; ?>
            <?php if (!$notes): ?><p class="text-secondary mb-0">No notes yet.</p>
            <?php else: foreach ($notes as $n): ?>
                <div class="border-top pt-2 mt-2">
                    <div class="small text-secondary"><?= e(date('d M Y, h:i A', strtotime($n['note_datetime']))) ?></div>
                    <?= nl2br(e($n['description'])) ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel mb-3">
            <h5>Client</h5>
            <div class="fw-semibold"><?= e($case['cl_first'] . ' ' . $case['cl_last']) ?></div>
            <div><i class="fa-solid fa-phone me-2"></i><?= e($case['cl_phone']) ?></div>
            <div><i class="fa-solid fa-envelope me-2"></i><?= e($case['cl_email']) ?></div>
            <a class="btn btn-sm btn-outline-secondary mt-2" href="chat.php?with=<?= (int) $case['cl_login'] ?>">Chat with client</a>
        </div>

        <div class="panel mb-3">
            <h5>Documents</h5>
            <?php if (!$isClosed): ?>
            <form method="post" enctype="multipart/form-data" class="mb-3"><?= csrf_field() ?>
                <input type="hidden" name="do" value="upload">
                <input class="form-control form-control-sm mb-2" name="file_title" maxlength="100" placeholder="Title" required>
                <input class="form-control form-control-sm mb-2" type="file" name="document" required>
                <button class="btn btn-sm btn-emerald">Upload</button>
            </form>
            <?php endif; ?>
            <?php if (!$files): ?><p class="text-secondary mb-0">No documents.</p>
            <?php else: foreach ($files as $f): ?>
                <div class="d-flex justify-content-between align-items-center border-top py-1">
                    <span><i class="fa-solid fa-file me-1"></i><?= e($f['file_title']) ?></span>
                    <a class="small" href="case_manage.php?id=<?= $caseId ?>&download=<?= (int) $f['file_id'] ?>">Download</a>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <?php if ($case['status'] === 'ongoing'): ?>
        <div class="panel">
            <h5>Close case</h5>
            <p class="text-secondary small">Close the case once the matter is finished. The client will be asked to rate you.</p>
            <form method="post"><?= csrf_field() ?>
                <input type="hidden" name="do" value="close_case">
                <button class="btn btn-outline-danger w-100" onclick="return confirm('Close this case? This cannot be undone.')">Close case</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
