<?php
/**
 * includes/footer_public.php
 * Footer for public pages. Closes the <body> and <html> opened in header_public.php.
 */
?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5><i class="fa-solid fa-scale-balanced me-2" style="color:#34D399"></i>Legal Advisor</h5>
                <p>A platform that connects clients with approved advocates, and helps manage cases, hearings and documents in one place.</p>
            </div>

            <div class="col-6 col-lg-2">
                <h5>Explore</h5>
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/find_advocate.php">Find Advocate</a></li>
                    <li><a href="<?= BASE_URL ?>/law_details.php">Law Details</a></li>
                    <li><a href="<?= BASE_URL ?>/news.php">News</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h5>Join</h5>
                <ul>
                    <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
                    <li><a href="<?= BASE_URL ?>/register_client.php">Client Sign Up</a></li>
                    <li><a href="<?= BASE_URL ?>/register_advocate.php">Advocate Sign Up</a></li>
                </ul>
            </div>

            <div class="col-lg-4">
                <h5>Newsletter</h5>
                <p>Get legal news and updates.</p>
                <form method="post" action="<?= BASE_URL ?>/subscribe.php" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="email" name="email" class="form-control" placeholder="Your email" required>
                    <button class="btn btn-emerald" type="submit">Join</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom text-center">
            &copy; <?= date('Y') ?> Legal Advisor. Academic project.
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>