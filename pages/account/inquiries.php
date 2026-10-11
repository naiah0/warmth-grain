<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Inquiries';
include '../../includes/header.php';
$s = $pdo->prepare('SELECT * FROM messages WHERE user_id=? ORDER BY created_at DESC'); $s->execute([$_SESSION['user_id']]);
$rows = $s->fetchAll();
?>
<?= back_button(BASE_URL . 'pages/contact.php') ?>
<h1 class="page-title">My Inquiries</h1>
<?php foreach ($rows as $m): ?>
  <div class="msg-card">
    <div class="msg-meta"><strong><?= e($m['subject']) ?></strong><span><?= status_badge($m['status']) ?> &nbsp; <small><?= e($m['created_at']) ?></small></span></div>
    <div class="msg-body"><?= nl2br(e($m['message'])) ?></div>
    <?php if ($m['admin_reply']): ?><p><strong>Warmth&amp;Grain replied (<?= e($m['replied_at']) ?>):</strong></p><div class="msg-body"><?= nl2br(e($m['admin_reply'])) ?></div><?php else: ?><p class="small muted">No reply yet. We answer within 1 business day.</p><?php endif; ?>
  </div>
<?php endforeach; if (!$rows) echo '<div class="box center">You have not sent any inquiries yet.</div>'; ?>
<?php include '../../includes/footer.php'; ?>