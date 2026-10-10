<?php
/** advocate/chat.php - chat with clients of my cases */
require_once __DIR__ . '/../config/autoload.php';
require_role(['advocate']);
$advId   = (int) $_SESSION['profile_id'];
$loginId = (int) $_SESSION['login_id'];

$contacts = $db->fetchAll(
    "SELECT DISTINCT cl.client_id, cl.login_id, cl.first_name, cl.last_name,
            (SELECT COUNT(*) FROM chat ch WHERE ch.sender_login_id = cl.login_id AND ch.receiver_login_id = ? AND ch.is_read = 0) AS unread
     FROM cases c JOIN client cl ON cl.client_id = c.client_id
     WHERE c.assigned_advocate_id = ?", [$loginId, $advId]);
$allowed = array_map('intval', array_column($contacts, 'login_id'));
$with = (int) ($_GET['with'] ?? ($allowed[0] ?? 0));
if ($with && !in_array($with, $allowed, true)) { $with = 0; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) { flash('Your session expired. Please try again.', 'danger'); redirect('/advocate/chat.php'); }
    $to  = (int) ($_POST['to'] ?? 0);
    $msg = trim($_POST['message'] ?? '');
    if (in_array($to, $allowed, true) && $msg !== '' && mb_strlen($msg) <= 1000) {
        $db->insert('chat', ['sender_login_id' => $loginId, 'receiver_login_id' => $to, 'message' => $msg]);
    }
    redirect('/advocate/chat.php?with=' . $to);
}

$messages = [];
if ($with) {
    $db->update('chat', ['is_read' => 1], 'sender_login_id = ? AND receiver_login_id = ?', [$with, $loginId]);
    $messages = $db->fetchAll(
        'SELECT * FROM chat WHERE (sender_login_id = ? AND receiver_login_id = ?) OR (sender_login_id = ? AND receiver_login_id = ?) ORDER BY sent_at, chat_id',
        [$loginId, $with, $with, $loginId]);
}

$pageTitle = 'Chat'; $activePage = 'chat';
require_once __DIR__ . '/../includes/header_dashboard.php';
?>
<h3 class="mb-3">Chat</h3>
<?php if (!$contacts): ?>
    <div class="panel"><p class="text-secondary mb-0">You can chat with a client once they accept your proposal.</p></div>
<?php else: ?>
<div class="row g-3">
    <div class="col-md-4 col-lg-3"><div class="panel p-2">
        <?php foreach ($contacts as $c): ?>
            <a class="d-flex justify-content-between align-items-center p-2 rounded text-decoration-none text-dark <?= (int) $c['login_id'] === $with ? 'bg-light fw-semibold' : '' ?>"
               href="chat.php?with=<?= (int) $c['login_id'] ?>">
                <span><i class="fa-solid fa-user me-2"></i><?= e($c['first_name'] . ' ' . $c['last_name']) ?></span>
                <?php if ((int) $c['unread'] > 0): ?><span class="badge bg-success"><?= (int) $c['unread'] ?></span><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div></div>
    <div class="col-md-8 col-lg-9"><div class="panel">
        <?php if (!$with): ?><p class="text-secondary mb-0">Select a client to start chatting.</p>
        <?php else: ?>
            <div id="msgs" style="height:380px; overflow-y:auto" class="mb-3">
                <?php if (!$messages): ?><p class="text-secondary">No messages yet.</p><?php endif; ?>
                <?php foreach ($messages as $m): $mine = (int) $m['sender_login_id'] === $loginId; ?>
                    <div class="d-flex mb-2 <?= $mine ? 'justify-content-end' : '' ?>">
                        <div class="p-2 rounded" style="max-width:75%; background:<?= $mine ? '#0A5C36' : '#eef1f0' ?>; color:<?= $mine ? '#fff' : '#222' ?>">
                            <?= nl2br(e($m['message'])) ?>
                            <div class="small opacity-75"><?= e(date('d M, h:i A', strtotime($m['sent_at']))) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post" class="d-flex gap-2"><?= csrf_field() ?>
                <input type="hidden" name="to" value="<?= $with ?>">
                <input class="form-control" name="message" maxlength="1000" placeholder="Type a message" required autofocus>
                <button class="btn btn-emerald">Send</button>
            </form>
            <script>var b=document.getElementById('msgs'); if(b){b.scrollTop=b.scrollHeight;}</script>
        <?php endif; ?>
    </div></div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer_dashboard.php'; ?>
