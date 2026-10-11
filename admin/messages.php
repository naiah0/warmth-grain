<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['delete'])) { $pdo->prepare('DELETE FROM messages WHERE message_id=?')->execute([$id]); set_flash('success', 'Message deleted.'); }
    else {
        $reply = trim($_POST['reply'] ?? '');
        if ($reply === '') set_flash('error', 'Write a reply first.');
        else { $pdo->prepare("UPDATE messages SET admin_reply=?, status='Replied', replied_at=NOW() WHERE message_id=?")->execute([$reply, $id]); set_flash('success', 'Reply saved. Logged-in customers see it under My Inquiries.'); }
    }
    header('Location: messages.php?show=' . urlencode($_GET['show'] ?? '')); exit;
}
$pageTitle = 'Messages';
include '../includes/header.php';
$show = $_GET['show'] ?? '';
$rows = $pdo->query('SELECT * FROM messages ' . (in_array($show, ['New', 'Replied'], true) ? "WHERE status='$show' " : '') . 'ORDER BY FIELD(status,"New","Replied"), created_at DESC')->fetchAll();
?>
<h1 class="page-title">Customer Messages</h1>
<div class="tabs" style="margin-bottom:1rem">
  <a href="messages.php" class="<?= !$show ? 'active' : '' ?>" style="border-bottom:2px solid var(--text)">All</a>
  <a href="messages.php?show=New" class="<?= $show === 'New' ? 'active' : '' ?>" style="border-bottom:2px solid var(--text)">New</a>
  <a href="messages.php?show=Replied" class="<?= $show === 'Replied' ? 'active' : '' ?>" style="border-bottom:2px solid var(--text)">Replied</a>
</div>
<?php foreach ($rows as $m): ?>
  <div class="msg-card">
    <div class="msg-meta"><strong><?= e($m['subject']) ?></strong><span><?= status_badge($m['status']) ?> &nbsp; <small><?= e($m['created_at']) ?></small></span></div>
    <p class="small"><?= e($m['name']) ?> &middot; <a href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['subject']) ?>"><?= e($m['email']) ?></a><?= $m['user_id'] ? ' &middot; has an account' : ' &middot; guest' ?></p>
    <div class="msg-body"><?= nl2br(e($m['message'])) ?></div>
    <form method="post">
      <input type="hidden" name="id" value="<?= (int)$m['message_id'] ?>">
      <label for="r<?= (int)$m['message_id'] ?>" class="small"><strong><?= $m['admin_reply'] ? 'Your reply (edit to update)' : 'Reply' ?></strong></label>
      <textarea id="r<?= (int)$m['message_id'] ?>" name="reply" rows="2" style="width:100%;padding:.6rem;border:1.5px solid var(--text);background:var(--bg);font:inherit"><?= e($m['admin_reply']) ?></textarea>
      <div class="actions" style="margin-top:.6rem"><button class="btn btn-sm" type="submit">Save reply</button>
        <a class="btn btn-outline btn-sm" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['subject']) ?>">Also email</a>
        <button class="btn btn-danger btn-sm" name="delete" value="1" data-confirm="Delete this message?">Delete</button></div>
    </form>
  </div>
<?php endforeach; if (!$rows) echo '<div class="box center">No messages.</div>'; ?>
<?php include '../includes/footer.php'; ?>