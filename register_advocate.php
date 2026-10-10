<?php
/**
 * register_advocate.php - advocate sign up (account stays "pending" until the admin approves)
 */
require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/includes/form_helpers.php';

if (is_logged_in()) {
    redirect('/index.php');
}

// Specialization choices come from the case types the admin manages
$caseTypeNames = array_column($db->fetchAll('SELECT type_name FROM case_type ORDER BY type_name'), 'type_name');

$v = new FormValidator($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash('Your session expired. Please try again.', 'danger');
        redirect('/register_advocate.php');
    }

    $v->field('first_name', 'First name')->required()->name()->maxLength(50);
    $v->field('last_name', 'Last name')->required()->name()->maxLength(50);
    $v->field('gender', 'Gender')->required()->in(['Male', 'Female', 'Other']);
    $v->field('phone', 'Phone')->required()->phone();
    $v->field('email', 'Email')->required()->email()->maxLength(100)->unique($db, 'advocate', 'email');
    $v->field('qualifications', 'Qualifications')->required()->maxLength(100);
    $v->field('specialization', 'Practice area')->required()->in($caseTypeNames);
    $v->field('experience_years', 'Experience')->required()->numeric()->min(0);
    if ($v->old('experience_years') !== '' && (int) $v->old('experience_years') > 60) {
        $v->addError('experience_years', 'Experience must be 60 years or less');
    }
    $v->field('bar_council_no', 'Bar Council number')->required()->maxLength(30)
      ->unique($db, 'advocate', 'bar_council_no');
    $v->field('house_name', 'House name')->maxLength(50);
    $v->field('place', 'Place / City')->required()->maxLength(50);
    $v->field('pincode', 'Pincode')->required()->pincode();
    $v->field('username', 'Username')->required()->username()->unique($db, 'login', 'username');
    $v->field('password', 'Password')->required()->password();
    $v->field('confirm_password', 'Confirm password')->required()->matches('password', 'the password');

    if ($v->passes()) {
        try {
            $db->begin();
            $loginId = $db->insert('login', [
                'username'      => $v->old('username'),
                'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'user_type'     => 'advocate',
            ]);
            $db->insert('advocate', [
                'login_id'         => $loginId,
                'first_name'       => $v->old('first_name'),
                'last_name'        => $v->old('last_name'),
                'gender'           => $v->old('gender'),
                'phone'            => $v->old('phone'),
                'email'            => $v->old('email'),
                'qualifications'   => $v->old('qualifications'),
                'specialization'   => $v->old('specialization'),
                'experience_years' => (int) $v->old('experience_years'),
                'bar_council_no'   => $v->old('bar_council_no'),
                'house_name'       => $v->old('house_name'),
                'place'            => $v->old('place'),
                'pincode'          => $v->old('pincode'),
                'approval_status'  => 'pending',
            ]);
            $db->commit();
        } catch (Throwable $ex) {
            $db->rollback();
            error_log('Advocate registration failed: ' . $ex->getMessage());
            flash('Registration failed. Please try again.', 'danger');
            redirect('/register_advocate.php');
        }
        flash('Registration submitted. You can log in once the admin approves your account.', 'info');
        redirect('/login.php');
    }
}

$pageTitle  = 'Advocate Registration';
$activePage = '';
require_once __DIR__ . '/includes/header_public.php';
?>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <h2 class="text-center mb-1">Register as an advocate</h2>
                    <p class="text-center text-secondary mb-4">
                        Your account will be reviewed and approved by the admin before you can log in.
                    </p>

                    <?php if (empty($caseTypeNames)): ?>
                        <div class="alert alert-warning">
                            No practice areas exist yet. The admin must add case types before advocates can register.
                        </div>
                    <?php endif; ?>

                    <form method="post" action="register_advocate.php" novalidate>
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <?php
                            render_input($v, 'first_name', 'First name');
                            render_input($v, 'last_name', 'Last name');
                            render_select($v, 'gender', 'Gender', ['Male', 'Female', 'Other']);
                            render_input($v, 'phone', 'Phone', 'tel');
                            render_input($v, 'email', 'Email', 'email');
                            render_input($v, 'qualifications', 'Qualifications (e.g. LLB, LLM)');
                            render_select($v, 'specialization', 'Practice area', $caseTypeNames);
                            render_input($v, 'experience_years', 'Years of experience', 'number', 'col-md-6', 'min="0" max="60"');
                            render_input($v, 'bar_council_no', 'Bar Council enrolment number');
                            render_input($v, 'house_name', 'House / Office name');
                            render_input($v, 'place', 'Place / City');
                            render_input($v, 'pincode', 'Pincode');
                            ?>
                            <div class="col-12"><hr class="my-1"></div>
                            <?php
                            render_input($v, 'username', 'Username');
                            render_input($v, 'password', 'Password', 'password');
                            render_input($v, 'confirm_password', 'Confirm password', 'password');
                            ?>
                            <div class="col-12 form-text">
                                Password: at least 8 characters with a letter and a number.
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-emerald" type="submit">Submit for approval</button>
                            </div>
                        </div>
                    </form>
                    <p class="text-center mt-3 mb-0">Already registered? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
