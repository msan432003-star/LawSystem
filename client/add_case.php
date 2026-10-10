<?php
/**
 * client/add_case.php - client registers a new case (status starts as "pending")
 */
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../includes/form_helpers.php';
require_role(['client']);

$clientId = (int) $_SESSION['profile_id'];

// Dropdown data: id => name
$caseTypes = [];
foreach ($db->fetchAll('SELECT type_id, type_name FROM case_type ORDER BY type_name') as $r) {
    $caseTypes[(int) $r['type_id']] = $r['type_name'];
}
$courts = [];
foreach ($db->fetchAll('SELECT court_id, name, place FROM court ORDER BY name') as $r) {
    $courts[(int) $r['court_id']] = $r['name'] . ($r['place'] ? ' - ' . $r['place'] : '');
}

$v = new FormValidator($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash('Your session expired. Please try again.', 'danger');
        redirect('/client/add_case.php');
    }

    $v->field('title', 'Case title')->required()->maxLength(150);
    $v->field('type_id', 'Case type')->required();
    $v->field('description', 'Description')->required()->minLength(20)->maxLength(3000);
    $v->field('case_date', 'Date of incident')->required()->date()->dateNotInFuture();
    $v->field('police_station', 'Police station')->maxLength(100);
    $v->field('pincode', 'Pincode')->pincode();
    $v->field('phone', 'Phone')->required()->phone();

    $typeId  = (int) $v->old('type_id');
    $courtId = (int) ($_POST['court_id'] ?? 0);

    if ($v->old('type_id') !== '' && !isset($caseTypes[$typeId])) {
        $v->addError('type_id', 'Please choose a valid case type');
    }
    if ($courtId !== 0 && !isset($courts[$courtId])) {
        $v->addError('court_id', 'Please choose a valid court');
    }

    if ($v->passes()) {
        try {
            $newId = $db->insert('cases', [
                'client_id'      => $clientId,
                'type_id'        => $typeId,
                'court_id'       => $courtId ?: null,
                'title'          => $v->old('title'),
                'description'    => $v->old('description'),
                'case_date'      => $v->old('case_date'),
                'police_station' => $v->old('police_station') ?: null,
                'pincode'        => $v->old('pincode') ?: null,
                'phone'          => $v->old('phone'),
                'status'         => 'pending',
            ]);
        } catch (Throwable $ex) {
            error_log('Add case failed: ' . $ex->getMessage());
            flash('Could not save the case. Please try again.', 'danger');
            redirect('/client/add_case.php');
        }
        flash('Your case has been registered. Advocates can now send you proposals.');
        redirect('/client/case_view.php?id=' . (int) $newId);
    }
}

// Pre-fill phone from the client's profile on first load
$profile = $db->fetchOne('SELECT phone, pincode FROM client WHERE client_id = ?', [$clientId]);

$pageTitle  = 'Add a Case';
$activePage = 'add_case';
require_once __DIR__ . '/../includes/header_dashboard.php';

$courtErr = $v->error('court_id');
$typeErr  = $v->error('type_id');
?>

<h3 class="mb-1">Add a case</h3>
<p class="text-secondary mb-4">Describe your legal matter. Approved advocates will review it and send you proposals.</p>

<div class="panel">
    <form method="post" action="add_case.php" novalidate>
        <?= csrf_field() ?>
        <div class="row g-3">
            <?php
            render_input($v, 'title', 'Case title', 'text', 'col-md-8');
            ?>
            <div class="col-md-4">
                <label class="form-label" for="type_id">Case type</label>
                <select class="form-select<?= $typeErr ? ' is-invalid' : '' ?>" id="type_id" name="type_id">
                    <option value="">Select type</option>
                    <?php foreach ($caseTypes as $id => $name): ?>
                        <option value="<?= (int) $id ?>"<?= (int) $v->old('type_id') === $id ? ' selected' : '' ?>>
                            <?= e($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($typeErr): ?><div class="field-error"><?= e($typeErr) ?></div><?php endif; ?>
            </div>
            <?php
            render_textarea($v, 'description', 'Describe what happened', 6);
            render_input($v, 'case_date', 'Date of incident', 'date', 'col-md-4', 'max="' . date('Y-m-d') . '"');
            render_input($v, 'police_station', 'Police station (if any)', 'text', 'col-md-4');
            ?>
            <div class="col-md-4">
                <label class="form-label" for="court_id">Court (if known)</label>
                <select class="form-select<?= $courtErr ? ' is-invalid' : '' ?>" id="court_id" name="court_id">
                    <option value="">Not decided yet</option>
                    <?php foreach ($courts as $id => $name): ?>
                        <option value="<?= (int) $id ?>"<?= (int) ($_POST['court_id'] ?? 0) === $id ? ' selected' : '' ?>>
                            <?= e($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($courtErr): ?><div class="field-error"><?= e($courtErr) ?></div><?php endif; ?>
            </div>
            <?php
            // Show profile phone/pincode as the starting values (only before the form is submitted)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $profile) {
                $_POST['phone']   = $profile['phone'];
                $_POST['pincode'] = $profile['pincode'];
                $v = new FormValidator($_POST);
            }
            render_input($v, 'phone', 'Contact phone', 'tel', 'col-md-6');
            render_input($v, 'pincode', 'Pincode', 'text', 'col-md-6');
            ?>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-emerald" type="submit">Submit case</button>
                <a class="btn btn-outline-secondary" href="my_cases.php">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
