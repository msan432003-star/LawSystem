<?php
/** admin/news.php - add, edit and delete the news shown on the public news page */
require_once __DIR__ . '/../config/autoload.php';
require_role(['admin']);
$loginId = (int) $_SESSION['login_id'];
$self = '/admin/news.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect($self); }
    $do = $_POST['do'] ?? '';
    $id = (int) ($_POST['news_id'] ?? 0);

    if ($do === 'delete') {
        $db->delete('news', 'news_id = ?', [$id]);
        flash('News deleted.');
        redirect($self);
    }

    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $date    = trim($_POST['published_date'] ?? '');
    $d = DateTime::createFromFormat('Y-m-d', $date);
    $dateOk = $d && $d->format('Y-m-d') === $date;

    if ($title === '' || mb_strlen($title) > 200) {
        flash('Enter a title (up to 200 characters).', 'danger');
    } elseif ($content === '' || mb_strlen($content) > 5000) {
        flash('Enter the news text (up to 5000 characters).', 'danger');
    } elseif (!$dateOk) {
        flash('Choose a valid date.', 'danger');
    } elseif ($do === 'add') {
        $db->insert('news', ['title' => $title, 'content' => $content, 'published_date' => $date, 'created_by' => $loginId]);
        flash('News published.');
        redirect($self);
    } elseif ($do === 'update') {
        $db->update('news', ['title' => $title, 'content' => $content, 'published_date' => $date], 'news_id = ?', [$id]);
        flash('News updated.');
        redirect($self);
    }
    redirect($do === 'update' ? $self . '?edit=' . $id : $self);
}

$editId = (int) ($_GET['edit'] ?? 0);
$edit   = $editId ? $db->fetchOne('SELECT * FROM news WHERE news_id = ?', [$editId]) : null;
$items  = $db->fetchAll('SELECT news_id, title, content, published_date FROM news ORDER BY published_date DESC, news_id DESC');

$pageTitle = 'News'; $activePage = 'news';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">News</h3>
<div class="row g-3">
    <div class="col-lg-5"><div class="panel">
        <h5><?= $edit ? 'Edit news' : 'Add news' ?></h5>
        <form method="post"><?= csrf_field() ?>
            <input type="hidden" name="do" value="<?= $edit ? 'update' : 'add' ?>">
            <input type="hidden" name="news_id" value="<?= (int) ($edit['news_id'] ?? 0) ?>">
            <label class="form-label">Title</label>
            <input class="form-control mb-2" name="title" maxlength="200" required value="<?= e($edit['title'] ?? '') ?>">
            <label class="form-label">Date</label>
            <input class="form-control mb-2" type="date" name="published_date" required value="<?= e($edit['published_date'] ?? date('Y-m-d')) ?>">
            <label class="form-label">Content</label>
            <textarea class="form-control mb-3" name="content" rows="6" maxlength="5000" required><?= e($edit['content'] ?? '') ?></textarea>
            <button class="btn btn-emerald"><?= $edit ? 'Save changes' : 'Publish' ?></button>
            <?php if ($edit): ?><a class="btn btn-outline-secondary" href="news.php">Cancel</a><?php endif; ?>
        </form>
    </div></div>
    <div class="col-lg-7"><div class="panel">
        <h5>Published news</h5>
        <?php if (!$items): ?><p class="text-secondary mb-0">Nothing published yet.</p>
        <?php else: foreach ($items as $n): ?>
            <div class="border-top pt-2 mt-2 d-flex justify-content-between gap-3">
                <div>
                    <div class="fw-semibold"><?= e($n['title']) ?></div>
                    <div class="small text-secondary"><?= e(date('d M Y', strtotime($n['published_date']))) ?></div>
                    <div class="small"><?= e(mb_strimwidth($n['content'], 0, 140, '...')) ?></div>
                </div>
                <div class="text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="news.php?edit=<?= (int) $n['news_id'] ?>">Edit</a>
                    <form method="post" class="d-inline"><?= csrf_field() ?>
                        <input type="hidden" name="do" value="delete">
                        <input type="hidden" name="news_id" value="<?= (int) $n['news_id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this news item?')">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
