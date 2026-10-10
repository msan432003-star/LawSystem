<?php
/** admin/law_details.php - add, edit and delete the laws shown on the public Law Details page */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);
$self = '/admin/law_details.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    $do = $_POST['do'] ?? '';
    $id = (int) ($_POST['law_id'] ?? 0);

    if ($do === 'delete') {
        $db->delete('law_details', 'law_id = ?', [$id]);
        flash('Law deleted.');
        redirect($self);
    }

    $title   = trim($_POST['title'] ?? '');
    $ipc     = trim($_POST['ipc_section'] ?? '');
    $bns     = trim($_POST['bns_section'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $penalty = trim($_POST['penalty'] ?? '');

    if ($title === '' || mb_strlen($title) > 150) {
        flash('Enter the offence name (up to 150 characters).', 'danger');
    } elseif (mb_strlen($ipc) > 20 || mb_strlen($bns) > 20) {
        flash('Section numbers can be up to 20 characters.', 'danger');
    } elseif (mb_strlen($desc) > 5000 || mb_strlen($penalty) > 255) {
        flash('Description or penalty is too long.', 'danger');
    } else {
        $data = ['title' => $title, 'ipc_section' => $ipc !== '' ? $ipc : null, 'bns_section' => $bns !== '' ? $bns : null,
                 'description' => $desc !== '' ? $desc : null, 'penalty' => $penalty !== '' ? $penalty : null];
        if ($do === 'add') {
            $db->insert('law_details', $data);
            flash('Law added.');
            redirect($self);
        } elseif ($do === 'update') {
            $db->update('law_details', $data, 'law_id = ?', [$id]);
            flash('Law updated.');
            redirect($self);
        }
    }
    redirect($do === 'update' ? $self . '?edit=' . $id : $self);
}

$editId = (int) ($_GET['edit'] ?? 0);
$edit   = $editId ? $db->fetchOne('SELECT * FROM law_details WHERE law_id = ?', [$editId]) : null;
$laws   = $db->fetchAll('SELECT * FROM law_details ORDER BY title');

$pageTitle = 'Law Details'; $activePage = 'laws';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Law details</h3>
<div class="row g-3">
    <div class="col-lg-4"><div class="panel">
        <h5><?= $edit ? 'Edit law' : 'Add a law' ?></h5>
        <form method="post"><?= csrf_field() ?>
            <input type="hidden" name="do" value="<?= $edit ? 'update' : 'add' ?>">
            <input type="hidden" name="law_id" value="<?= (int) ($edit['law_id'] ?? 0) ?>">
            <label class="form-label">Offence name</label>
            <input class="form-control mb-2" name="title" maxlength="150" required value="<?= e($edit['title'] ?? '') ?>">
            <div class="row g-2 mb-2">
                <div class="col-6"><label class="form-label">IPC section</label>
                    <input class="form-control" name="ipc_section" maxlength="20" value="<?= e($edit['ipc_section'] ?? '') ?>"></div>
                <div class="col-6"><label class="form-label">BNS section</label>
                    <input class="form-control" name="bns_section" maxlength="20" value="<?= e($edit['bns_section'] ?? '') ?>"></div>
            </div>
            <label class="form-label">Description</label>
            <textarea class="form-control mb-2" name="description" rows="4"><?= e($edit['description'] ?? '') ?></textarea>
            <label class="form-label">Penalty</label>
            <input class="form-control mb-3" name="penalty" maxlength="255" value="<?= e($edit['penalty'] ?? '') ?>">
            <button class="btn btn-emerald"><?= $edit ? 'Save changes' : 'Add law' ?></button>
            <?php if ($edit): ?><a class="btn btn-outline-secondary" href="law_details.php">Cancel</a><?php endif; ?>
        </form>
    </div></div>
    <div class="col-lg-8"><div class="panel">
        <?php if (!$laws): ?><p class="text-secondary mb-0">No laws added yet.</p>
        <?php else: ?>
        <div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th>Offence</th><th>IPC</th><th>BNS</th><th>Penalty</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($laws as $l): ?>
                <tr>
                    <td><?= e($l['title']) ?></td>
                    <td><?= e($l['ipc_section'] ?: '-') ?></td>
                    <td><?= e($l['bns_section'] ?: '-') ?></td>
                    <td class="small"><?= e($l['penalty'] ?: '-') ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-secondary" href="law_details.php?edit=<?= (int) $l['law_id'] ?>">Edit</a>
                        <form method="post" class="d-inline"><?= csrf_field() ?>
                            <input type="hidden" name="do" value="delete">
                            <input type="hidden" name="law_id" value="<?= (int) $l['law_id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this law?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
