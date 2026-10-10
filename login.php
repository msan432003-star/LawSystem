<?php
/**
 * login.php - one login page for admin, advocate and client
 */
require_once __DIR__ . '/config/autoload.php';

if (is_logged_in()) {
    redirect('/' . $_SESSION['user_type'] . '/index.php');
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = $db->fetchOne(
            'SELECT login_id, username, password_hash, user_type, is_active FROM login WHERE username = ?',
            [$username]
        );

        // One generic message for wrong username OR wrong password (does not reveal which)
        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid username or password.';
        } else {
            $profileId   = null;
            $displayName = $user['username'];

            if ($user['user_type'] === 'advocate') {
                $row = $db->fetchOne(
                    'SELECT advocate_id, first_name, approval_status FROM advocate WHERE login_id = ?',
                    [$user['login_id']]
                );
                if (!$row) {
                    $error = 'Advocate profile not found.';
                } elseif ($row['approval_status'] === 'pending') {
                    $error = 'Your registration is waiting for admin approval.';
                } elseif ($row['approval_status'] === 'rejected') {
                    $error = 'Your registration was not approved. Please contact the admin.';
                } else {
                    $profileId   = (int) $row['advocate_id'];
                    $displayName = $row['first_name'];
                }
            } elseif ($user['user_type'] === 'client') {
                $row = $db->fetchOne('SELECT client_id, first_name FROM client WHERE login_id = ?', [$user['login_id']]);
                if (!$row) {
                    $error = 'Client profile not found.';
                } else {
                    $profileId   = (int) $row['client_id'];
                    $displayName = $row['first_name'];
                }
            }

            if ($error === '') {
                session_regenerate_id(true);          // protects against session fixation
                $_SESSION['login_id']     = (int) $user['login_id'];
                $_SESSION['user_type']    = $user['user_type'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['profile_id']   = $profileId;     // advocate_id or client_id (null for admin)
                $_SESSION['display_name'] = $displayName;

                $target = in_array($user['user_type'], ['admin', 'advocate', 'client'], true)
                    ? '/' . $user['user_type'] . '/index.php'
                    : '/index.php';
                redirect($target);
            }
        }
    }
}

$pageTitle  = 'Login';
$activePage = '';
require_once __DIR__ . '/includes/header_public.php';
?>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="form-card">
                    <h2 class="text-center mb-1">Welcome back</h2>
                    <p class="text-center text-secondary mb-4">Login to your Legal Advisor account</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="login.php" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control" type="text" id="username" name="username"
                                   value="<?= e($username) ?>" autofocus required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-emerald" type="submit">Login</button>
                        </div>
                    </form>

                    <hr class="my-4">
                    <p class="text-center mb-1">New client? <a href="register_client.php">Create an account</a></p>
                    <p class="text-center mb-0">Are you an advocate? <a href="register_advocate.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
