<?php
/** admin/case_types.php - add / rename case types (also the advocates' practice areas) */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/admin/case_types.php'); }
    $do   = $_POST['do'] ?? '';
    $name = trim($_POST['type_name'] ?? '');

    if ($name === '' || mb_strlen($name) > 50) {
        flash('Enter a name (up to 50 characters).', 'danger');
    } elseif ($do === 'add') {
        if ($db->count('case_type', 'type_name = ?', [$name])) {
            flash('That case type already exists.', 'danger');
        } else {
            $db->insert('case_type', ['type_name' => $name]);
            flash('Case type added.');
        }
    } elseif ($do === 'rename') {
        $id  = (int) ($_POST['type_id'] ?? 0);
        $old = $db->fetchOne('SELECT type_name FROM case_type WHERE type_id = ?', [$id]);
        if (!$old) {
            flash('Case type not found.', 'danger');
        } elseif ($db->count('case_type', 'type_name = ? AND type_id <> ?', [$name, $id])) {
            flash('That case type already exists.', 'danger');
        } else {
            try {
                $db->begin();
                $db->update('case_type', ['type_name' => $name], 'type_id = ?', [$id]);
                // advocates store the type NAME as their specialization, keep them in sync
                $db->update('advocate', ['specialization' => $name], 'specialization = ?', [$old['type_name']]);
                $db->commit();
                flash('Case type renamed.');
            } catch (Throwable $ex) {
                $db->rollback();
                error_log('Rename case type failed: ' . $ex->getMessage());
                flash('Could not rename. Please try again.', 'danger');
            }
        }
    }
    redirect('/admin/case_types.php');
}

$types = $db->fetchAll(
    'SELECT t.type_id, t.type_name,
            (SELECT COUNT(*) FROM cases c WHERE c.type_id = t.type_id) AS case_count
     FROM case_type t ORDER BY t.type_name');

$pageTitle = 'Case Types'; $activePage = 'types';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Case types</h3>
<div class="row g-3">
    <div class="col-lg-4"><div class="panel">
        <h5>Add a case type</h5>
        <form method="post"><?= csrf_field() ?>
            <input type="hidden" name="do" value="add">
            <input class="form-control mb-2" name="type_name" maxlength="50" placeholder="e.g. Criminal, Family, Property" required>
            <button class="btn btn-emerald w-100">Add</button>
        </form>
        <p class="small text-secondary mt-3 mb-0">These are the practice areas advocates choose when they register.</p>
    </div></div>
    <div class="col-lg-8"><div class="panel">
        <?php if (!$types): ?><p class="text-secondary mb-0">No case types yet. Add one so advocates can register.</p>
        <?php else: foreach ($types as $t): ?>
            <form method="post" class="row g-2 align-items-center border-bottom py-2"><?= csrf_field() ?>
                <input type="hidden" name="do" value="rename">
                <input type="hidden" name="type_id" value="<?= (int) $t['type_id'] ?>">
                <div class="col-md-6"><input class="form-control form-control-sm" name="type_name" maxlength="50" value="<?= e($t['type_name']) ?>" required></div>
                <div class="col-md-3 small text-secondary"><?= (int) $t['case_count'] ?> case(s)</div>
                <div class="col-md-3 d-grid"><button class="btn btn-sm btn-outline-dark">Rename</button></div>
            </form>
        <?php endforeach; endif; ?>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
