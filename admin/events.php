<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) { $pdo->prepare('DELETE FROM events WHERE event_id=?')->execute([(int)$_POST['delete_id']]); set_flash('success', 'Event deleted.'); }
    else {
        $id = (int)($_POST['event_id'] ?? 0); $title = trim($_POST['title'] ?? ''); $desc = trim($_POST['description'] ?? '');
        $type = $_POST['event_type'] ?? ''; $date = str_replace('T', ' ', $_POST['event_date'] ?? ''); $loc = trim($_POST['location'] ?? '');
        $status = $_POST['status'] ?? 'Published'; $image = trim($_POST['current_image'] ?? '') ?: 'placeholder'; $errors = [];
        if ($title === '' || $loc === '') $errors[] = 'Title and location are required.';
        if (!in_array($type, ['Gear Swap', 'Trading Meetup', 'Other'], true)) $errors[] = 'Invalid event type.';
        if (!in_array($status, ['Published', 'Cancelled'], true)) $errors[] = 'Invalid status.';
        if (!strtotime($date)) $errors[] = 'Enter a valid date and time.';
        try { if ($up = save_upload('photo', 'events')) $image = $up; } catch (Exception $ex) { $errors[] = $ex->getMessage(); }
        if ($errors) set_flash('error', $errors);
        elseif ($id) { $pdo->prepare('UPDATE events SET title=?,description=?,event_type=?,event_date=?,location=?,status=?,image=? WHERE event_id=?')->execute([$title, $desc, $type, $date, $loc, $status, $image, $id]); set_flash('success', 'Event updated.'); }
        else { $pdo->prepare('INSERT INTO events (title,description,event_type,event_date,location,status,image) VALUES (?,?,?,?,?,?,?)')->execute([$title, $desc, $type, $date, $loc, $status, $image]); set_flash('success', 'Event added.'); }
    }
    header('Location: events.php'); exit;
}
$pageTitle = 'Manage Events';
include '../includes/header.php';
$editId = (int)($_GET['edit'] ?? 0);
$ev = ['event_id' => 0, 'title' => '', 'description' => '', 'event_type' => 'Other', 'event_date' => '', 'location' => '', 'status' => 'Published', 'image' => ''];
if ($editId) { $s = $pdo->prepare('SELECT * FROM events WHERE event_id=?'); $s->execute([$editId]); $ev = $s->fetch() ?: $ev; }
$rows = $pdo->query('SELECT e.*, (SELECT COALESCE(SUM(guests),0) FROM event_registrations r WHERE r.event_id=e.event_id) AS guests FROM events e ORDER BY e.event_date DESC')->fetchAll();
?>
<h1 class="page-title">Events</h1>
<form class="form-h" method="post" enctype="multipart/form-data">
  <div class="form-head"><h2 class="plain"><?= $ev['event_id'] ? 'Edit Event' : 'Add Event' ?></h2>
    <div class="actions"><button class="btn" type="submit">Save</button><?php if ($ev['event_id']): ?><a class="btn btn-outline" href="events.php">Cancel</a><?php endif; ?></div></div>
  <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>"><input type="hidden" name="current_image" value="<?= e($ev['image']) ?>">
  <div class="fields">
    <div class="span2"><label for="title">Title</label><input type="text" id="title" name="title" value="<?= e($ev['title']) ?>" required></div>
    <div><label for="event_type">Type</label><select id="event_type" name="event_type"><?php foreach (['Gear Swap', 'Trading Meetup', 'Other'] as $t): ?><option <?= $t === $ev['event_type'] ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div><label for="status">Status</label><select id="status" name="status"><?php foreach (['Published', 'Cancelled'] as $s): ?><option <?= $s === $ev['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div><label for="event_date">Date and time</label><input type="datetime-local" id="event_date" name="event_date" value="<?= $ev['event_date'] ? date('Y-m-d\TH:i', strtotime($ev['event_date'])) : '' ?>" required></div>
    <div class="span2"><label for="location">Location</label><input type="text" id="location" name="location" value="<?= e($ev['location']) ?>" required></div>
    <div><label for="photo">Cover photo (4:3, max 2 MB)</label><input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp"></div>
    <div class="span4"><label for="description">Description</label><textarea id="description" name="description" rows="2"><?= e($ev['description']) ?></textarea></div>
  </div>
</form>

<h2>All Events</h2>
<div class="table-wrap"><table class="zebra">
  <tr><th>Title</th><th>Type</th><th>Date</th><th>Location</th><th>Status</th><th>Guests</th><th>Action</th></tr>
  <?php foreach ($rows as $r): ?>
    <tr><td><strong><?= e($r['title']) ?></strong></td><td><?= e($r['event_type']) ?></td><td><?= e(date('M d, Y g:i A', strtotime($r['event_date']))) ?></td><td><?= e($r['location']) ?></td><td><?= status_badge($r['status']) ?></td>
      <td><a href="event_registrations.php?id=<?= (int)$r['event_id'] ?>"><?= (int)$r['guests'] ?> view</a></td>
      <td><div class="actions"><a class="btn btn-sm" href="events.php?edit=<?= (int)$r['event_id'] ?>">Edit</a>
        <form method="post"><input type="hidden" name="delete_id" value="<?= (int)$r['event_id'] ?>"><button class="btn btn-danger btn-sm" data-confirm="Delete this event and its sign-ups?">Delete</button></form></div></td></tr>
  <?php endforeach; ?>
</table></div>
<?php include '../includes/footer.php'; ?>