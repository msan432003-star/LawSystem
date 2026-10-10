<?php
/**
 * register_client.php - client sign up
 */
require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/includes/form_helpers.php';

if (is_logged_in()) {
    redirect('/index.php');
}

$v = new FormValidator($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash('Your session expired. Please try again.', 'danger');
        redirect('/register_client.php');
    }

    $v->field('first_name', 'First name')->required()->name()->maxLength(50);
    $v->field('last_name', 'Last name')->required()->name()->maxLength(50);
    $v->field('gender', 'Gender')->required()->in(['Male', 'Female', 'Other']);
    $v->field('dob', 'Date of birth')->required()->date()->dateNotInFuture();
    $v->field('phone', 'Phone')->required()->phone();
    $v->field('email', 'Email')->required()->email()->maxLength(100)->unique($db, 'client', 'email');
    $v->field('house_name', 'House name')->maxLength(50);
    $v->field('place', 'Place')->required()->maxLength(50);
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
                'user_type'     => 'client',
            ]);
            $db->insert('client', [
                'login_id'   => $loginId,
                'first_name' => $v->old('first_name'),
                'last_name'  => $v->old('last_name'),
                'gender'     => $v->old('gender'),
                'dob'        => $v->old('dob'),
                'phone'      => $v->old('phone'),
                'email'      => $v->old('email'),
                'house_name' => $v->old('house_name'),
                'place'      => $v->old('place'),
                'pincode'    => $v->old('pincode'),
            ]);
            $db->commit();
        } catch (Throwable $ex) {
            $db->rollback();
            error_log('Client registration failed: ' . $ex->getMessage());
            flash('Registration failed. Please try again.', 'danger');
            redirect('/register_client.php');
        }
        flash('Registration successful. You can now log in.');
        redirect('/login.php');
    }
}

$pageTitle  = 'Client Registration';
$activePage = '';
require_once __DIR__ . '/includes/header_public.php';
?>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <h2 class="text-center mb-1">Create a client account</h2>
                    <p class="text-center text-secondary mb-4">Register your cases and find advocates.</p>

                    <form method="post" action="register_client.php" novalidate>
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <?php
                            render_input($v, 'first_name', 'First name');
                            render_input($v, 'last_name', 'Last name');
                            render_select($v, 'gender', 'Gender', ['Male', 'Female', 'Other']);
                            render_input($v, 'dob', 'Date of birth', 'date', 'col-md-6', 'max="' . date('Y-m-d') . '"');
                            render_input($v, 'phone', 'Phone', 'tel');
                            render_input($v, 'email', 'Email', 'email');
                            render_input($v, 'house_name', 'House name');
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
                                <button class="btn btn-emerald" type="submit">Register</button>
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
