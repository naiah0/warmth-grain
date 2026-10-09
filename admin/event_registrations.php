<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('DELETE FROM event_registrations WHERE registration_id=?')->execute([(int)$_POST['delete_id']]);
    set_flash('success', 'Registration removed.');
    header('Location: event_registrations.php?id=' . $id);
    exit;
}
$ev = $pdo->prepare('SELECT * FROM events WHERE event_id=?'); $ev->execute([$id]); $ev = $ev->fetch();
$pageTitle = 'Event Sign-ups';
include '../includes/header.php';
include __DIR__ . '/_nav.php';
$rows = $pdo->prepare('SELECT * FROM event_registrations WHERE event_id=? ORDER BY created_at DESC'); $rows->execute([$id]); $rows = $rows->fetchAll();
?>
<h1>Sign-ups: <?= e($ev['title'] ?? 'Unknown event') ?></h1>
<p><a class="btn btn-outline" href="events.php">Back to events</a> &nbsp; Total guests: <strong><?= array_sum(array_column($rows, 'guests')) ?></strong></p>
<div class="table-wrap"><table>
  <tr><th>Name</th><th>Email</th><th>Phone</th><th>Guests</th><th>Notes</th><th>Registered</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['full_name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['phone']) ?></td><td><?= (int)$r['guests'] ?></td><td><?= e($r['notes']) ?></td><td><?= e($r['created_at']) ?></td>
      <td><form method="post"><input type="hidden" name="delete_id" value="<?= (int)$r['registration_id'] ?>"><button class="btn btn-danger" data-confirm="Remove this registration?">Remove</button></form></td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="7">No sign-ups yet.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>