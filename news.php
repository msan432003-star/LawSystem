<?php
/**
 * news.php - public list of news items added by the admin
 */
$pageTitle  = 'News';
$activePage = 'news';
require_once __DIR__ . '/includes/header_public.php';

$items = $db->fetchAll(
    'SELECT news_id, title, content, published_date FROM news ORDER BY published_date DESC, news_id DESC LIMIT 20'
);
?>

<section class="section-dark py-5">
    <div class="container text-center">
        <h1 class="mb-2">Legal News</h1>
        <p class="section-sub mb-0">Updates from the platform and the legal world.</p>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:860px">
        <?php if (empty($items)): ?>
            <p class="text-center text-secondary">No news has been published yet. Please check back soon.</p>
        <?php else: ?>
            <?php foreach ($items as $n): ?>
                <article class="feature-card text-start mb-4">
                    <div class="text-secondary small mb-1">
                        <i class="fa-regular fa-calendar me-1"></i><?= e(date('d M Y', strtotime($n['published_date']))) ?>
                    </div>
                    <h4 class="serif"><?= e($n['title']) ?></h4>
                    <p class="mb-0 text-secondary"><?= nl2br(e($n['content'])) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer_public.php'; ?>
