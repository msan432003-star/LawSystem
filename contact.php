<?php
/**
 * contact.php - contact details and a message form (saved in contact_message)
 */
require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/includes/form_helpers.php';

$v = new FormValidator($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash('Your session expired. Please try again.', 'danger');
        redirect('/contact.php');
    }

    $v->field('name', 'Name')->required()->name()->maxLength(100);
    $v->field('email', 'Email')->required()->email()->maxLength(100);
    $v->field('subject', 'Subject')->required()->maxLength(150);
    $v->field('message', 'Message')->required()->minLength(10)->maxLength(2000);

    if ($v->passes()) {
        $db->insert('contact_message', [
            'name'    => $v->old('name'),
            'email'   => $v->old('email'),
            'subject' => $v->old('subject'),
            'message' => $v->old('message'),
        ]);
        flash('Thank you! Your message has been sent.');
        redirect('/contact.php');
    }
}

$pageTitle  = 'Contact';
$activePage = 'contact';
require_once __DIR__ . '/includes/header_public.php';
?>

<section class="section-dark py-5">
    <div class="container text-center">
        <h1 class="mb-2">Contact Us</h1>
        <p class="section-sub mb-0">Questions, feedback or support? Send us a message.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="feature-card text-start mb-3 h-auto" style="height:auto;min-height:0">
                    <h6><i class="fa-solid fa-envelope me-2" style="color:#0A5C36"></i>Email</h6>
                    <div class="text-secondary">contact@legaladvisor.example</div>
                </div>
                <div class="feature-card text-start mb-3 h-auto" style="height:auto;min-height:0">
                    <h6><i class="fa-solid fa-phone me-2" style="color:#0A5C36"></i>Phone</h6>
                    <div class="text-secondary">+91 00000 00000</div>
                </div>
                <div class="feature-card text-start h-auto" style="height:auto;min-height:0">
                    <h6><i class="fa-solid fa-location-dot me-2" style="color:#0A5C36"></i>Address</h6>
                    <div class="text-secondary">Your college / city, Kerala, India</div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="form-card">
                    <h3 class="mb-3">Send a message</h3>
                    <form method="post" action="contact.php" novalidate>
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <?php
                            render_input($v, 'name', 'Your name');
                            render_input($v, 'email', 'Email', 'email');
                            render_input($v, 'subject', 'Subject', 'text', 'col-12');
                            render_textarea($v, 'message', 'Message', 5);
                            ?>
                            <div class="col-12 d-grid">
                                <button class="btn btn-emerald" type="submit">Send message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
