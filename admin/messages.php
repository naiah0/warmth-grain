<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('DELETE FROM messages WHERE message_id=?')->execute([(int)$_POST['delete_id']]);
    set_flash('success', 'Message deleted.');
    header('Location: messages.php');
    exit;
}
$pageTitle = 'Messages';
include '../includes/header.php';
include __DIR__ . '/_nav.php';
$rows = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC')->fetchAll();
?>
<h1>Customer Messages</h1>
<?php foreach ($rows as $m): ?>
  <div class="card" style="margin-bottom:1rem">
    <h3><?= e($m['subject']) ?></h3>
    <p class="small muted"><?= e($m['name']) ?> &middot; <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a> &middot; <?= e($m['created_at']) ?></p>
    <p><?= nl2br(e($m['message'])) ?></p>
    <form method="post"><input type="hidden" name="delete_id" value="<?= (int)$m['message_id'] ?>"><button class="btn btn-danger" data-confirm="Delete this message?">Delete</button></form>
  </div>
<?php endforeach; if (!$rows) echo '<p>No messages yet.</p>'; ?>
<?php include '../includes/footer.php'; ?>