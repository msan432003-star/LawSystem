<?php
/** advocate/profile.php - edit details and change password */
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../includes/form_helpers.php';
require_role(['advocate']);
$advId   = (int) $_SESSION['profile_id'];
$loginId = (int) $_SESSION['login_id'];

$profile = $db->fetchOne('SELECT * FROM advocate WHERE advocate_id = ?', [$advId]);
$specs   = array_column($db->fetchAll('SELECT type_name FROM case_type ORDER BY type_name'), 'type_name');
$v = new FormValidator($_POST);
$pwError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/advocate/profile.php'); }

    if (isset($_POST['save_profile'])) {
        $v->field('first_name', 'First name')->required()->name()->maxLength(50);
        $v->field('last_name', 'Last name')->required()->name()->maxLength(50);
        $v->field('gender', 'Gender')->required()->in(['Male', 'Female', 'Other']);
        $v->field('phone', 'Phone')->required()->phone();
        $v->field('qualifications', 'Qualifications')->required()->maxLength(100);
        $v->field('specialization', 'Practice area')->required()->in($specs);
        $v->field('experience_years', 'Experience')->required()->numeric()->min(0);
        if ($v->old('experience_years') !== '' && (int) $v->old('experience_years') > 60) {
            $v->addError('experience_years', 'Experience must be 60 years or less');
        }
        $v->field('house_name', 'House name')->maxLength(50);
        $v->field('place', 'Place')->required()->maxLength(50);
        $v->field('pincode', 'Pincode')->required()->pincode();
        if ($v->passes()) {
            $db->update('advocate', [
                'first_name' => $v->old('first_name'), 'last_name' => $v->old('last_name'), 'gender' => $v->old('gender'),
                'phone' => $v->old('phone'), 'qualifications' => $v->old('qualifications'),
                'specialization' => $v->old('specialization'), 'experience_years' => (int) $v->old('experience_years'),
                'house_name' => $v->old('house_name'), 'place' => $v->old('place'), 'pincode' => $v->old('pincode'),
            ], 'advocate_id = ?', [$advId]);
            $_SESSION['display_name'] = $v->old('first_name');
            flash('Profile updated.');
            redirect('/advocate/profile.php');
        }
    } elseif (isset($_POST['change_password'])) {
        $row = $db->fetchOne('SELECT password_hash FROM login WHERE login_id = ?', [$loginId]);
        $cur = $_POST['current_password'] ?? ''; $new = $_POST['new_password'] ?? ''; $cnf = $_POST['confirm_password'] ?? '';
        if (!$row || !password_verify($cur, $row['password_hash'])) { $pwError = 'Current password is wrong.'; }
        elseif (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) { $pwError = 'New password needs at least 8 characters with a letter and a number.'; }
        elseif ($new !== $cnf) { $pwError = 'New password and confirmation do not match.'; }
        else {
            $db->update('login', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], 'login_id = ?', [$loginId]);
            flash('Password changed.');
            redirect('/advocate/profile.php');
        }
    }
}

if (!isset($_POST['save_profile'])) {   // show saved values
    $_POST = array_merge($_POST, $profile ?: []);
    $v = new FormValidator($_POST);
}

$pageTitle = 'My Profile'; $activePage = 'profile';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">My profile</h3>
<div class="row g-3">
    <div class="col-lg-8"><div class="panel">
        <h5 class="mb-1">Professional details</h5>
        <div class="text-secondary small mb-3">Username: <?= e($_SESSION['username']) ?> &middot; Email: <?= e($profile['email'] ?? '') ?> &middot; Bar Council no: <?= e($profile['bar_council_no'] ?? '') ?></div>
        <form method="post" novalidate><?= csrf_field() ?>
            <div class="row g-3">
                <?php
                render_input($v, 'first_name', 'First name');
                render_input($v, 'last_name', 'Last name');
                render_select($v, 'gender', 'Gender', ['Male', 'Female', 'Other']);
                render_input($v, 'phone', 'Phone', 'tel');
                render_input($v, 'qualifications', 'Qualifications');
                render_select($v, 'specialization', 'Practice area', $specs);
                render_input($v, 'experience_years', 'Years of experience', 'number', 'col-md-6', 'min="0" max="60"');
                render_input($v, 'house_name', 'House / Office name');
                render_input($v, 'place', 'Place / City');
                render_input($v, 'pincode', 'Pincode');
                ?>
                <div class="col-12"><button class="btn btn-emerald" name="save_profile" value="1">Save changes</button></div>
            </div>
        </form>
    </div></div>
    <div class="col-lg-4"><div class="panel">
        <h5 class="mb-3">Change password</h5>
        <?php if ($pwError): ?><div class="alert alert-danger"><?= e($pwError) ?></div><?php endif; ?>
        <form method="post"><?= csrf_field() ?>
            <div class="mb-3"><label class="form-label">Current password</label><input class="form-control" type="password" name="current_password" required></div>
            <div class="mb-3"><label class="form-label">New password</label><input class="form-control" type="password" name="new_password" required></div>
            <div class="mb-3"><label class="form-label">Confirm new password</label><input class="form-control" type="password" name="confirm_password" required></div>
            <button class="btn btn-emerald" name="change_password" value="1">Change password</button>
        </form>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
