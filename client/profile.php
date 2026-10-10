<?php
/** client/profile.php - edit personal details and change password */
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../includes/form_helpers.php';
require_role(['client']);
$clientId = (int) $_SESSION['profile_id'];
$loginId  = (int) $_SESSION['login_id'];

$profile = $db->fetchOne('SELECT * FROM client WHERE client_id = ?', [$clientId]);
$v = new FormValidator($_POST);
$pwError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/client/profile.php'); }

    if (isset($_POST['save_profile'])) {
        $v->field('first_name', 'First name')->required()->name()->maxLength(50);
        $v->field('last_name', 'Last name')->required()->name()->maxLength(50);
        $v->field('gender', 'Gender')->required()->in(['Male', 'Female', 'Other']);
        $v->field('dob', 'Date of birth')->required()->date()->dateNotInFuture();
        $v->field('phone', 'Phone')->required()->phone();
        $v->field('house_name', 'House name')->maxLength(50);
        $v->field('place', 'Place')->required()->maxLength(50);
        $v->field('pincode', 'Pincode')->required()->pincode();
        if ($v->passes()) {
            $pdo->prepare('UPDATE client SET first_name=?, last_name=?, gender=?, dob=?, phone=?, house_name=?, place=?, pincode=? WHERE client_id=?')
                ->execute([$v->old('first_name'), $v->old('last_name'), $v->old('gender'), $v->old('dob'),
                           $v->old('phone'), $v->old('house_name'), $v->old('place'), $v->old('pincode'), $clientId]);
            $_SESSION['display_name'] = $v->old('first_name');
            flash('Profile updated.');
            redirect('/client/profile.php');
        }
    } elseif (isset($_POST['change_password'])) {
        $row = $db->fetchOne('SELECT password_hash FROM login WHERE login_id = ?', [$loginId]);
        $cur = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cnf = $_POST['confirm_password'] ?? '';
        if (!$row || !password_verify($cur, $row['password_hash'])) {
            $pwError = 'Current password is wrong.';
        } elseif (strlen($new) < 8 || !preg_match('/[A-Za-z]/', $new) || !preg_match('/\d/', $new)) {
            $pwError = 'New password needs at least 8 characters with a letter and a number.';
        } elseif ($new !== $cnf) {
            $pwError = 'New password and confirmation do not match.';
        } else {
            $pdo->prepare('UPDATE login SET password_hash = ? WHERE login_id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), $loginId]);
            flash('Password changed.');
            redirect('/client/profile.php');
        }
    }
}

// Show saved values unless the profile form was just submitted with errors
if (!isset($_POST['save_profile'])) {
    $_POST = array_merge($_POST, $profile ?: []);
    $v = new FormValidator($_POST);
}

$pageTitle = 'My Profile'; $activePage = 'profile';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">My profile</h3>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="panel">
            <h5 class="mb-3">Personal details</h5>
            <div class="text-secondary small mb-3">Username: <?= e($_SESSION['username']) ?> &middot; Email: <?= e($profile['email'] ?? '') ?></div>
            <form method="post" novalidate><?= csrf_field() ?>
                <div class="row g-3">
                    <?php
                    render_input($v, 'first_name', 'First name');
                    render_input($v, 'last_name', 'Last name');
                    render_select($v, 'gender', 'Gender', ['Male', 'Female', 'Other']);
                    render_input($v, 'dob', 'Date of birth', 'date', 'col-md-6', 'max="' . date('Y-m-d') . '"');
                    render_input($v, 'phone', 'Phone', 'tel');
                    render_input($v, 'house_name', 'House name');
                    render_input($v, 'place', 'Place / City');
                    render_input($v, 'pincode', 'Pincode');
                    ?>
                    <div class="col-12"><button class="btn btn-emerald" name="save_profile" value="1">Save changes</button></div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel">
            <h5 class="mb-3">Change password</h5>
            <?php if ($pwError): ?><div class="alert alert-danger"><?= e($pwError) ?></div><?php endif; ?>
            <form method="post"><?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Current password</label>
                    <input class="form-control" type="password" name="current_password" required></div>
                <div class="mb-3"><label class="form-label">New password</label>
                    <input class="form-control" type="password" name="new_password" required></div>
                <div class="mb-3"><label class="form-label">Confirm new password</label>
                    <input class="form-control" type="password" name="confirm_password" required></div>
                <button class="btn btn-emerald" name="change_password" value="1">Change password</button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
