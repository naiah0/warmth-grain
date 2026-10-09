<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $pdo->prepare('DELETE FROM events WHERE event_id=?')->execute([(int)$_POST['delete_id']]);
        set_flash('success', 'Event deleted.');
    } else {
        $id = (int)($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? ''); $desc = trim($_POST['description'] ?? '');
        $type = $_POST['event_type'] ?? ''; $date = str_replace('T', ' ', $_POST['event_date'] ?? '');
        $loc = trim($_POST['location'] ?? ''); $status = $_POST['status'] ?? 'Published';
        $image = trim($_POST['current_image'] ?? '') ?: 'placeholder.jpg';
        $errors = [];
        if ($title === '' || $loc === '') $errors[] = 'Title and location are required.';
        if (!in_array($type, ['Gear Swap', 'Trading Meetup', 'Other'], true)) $errors[] = 'Invalid event type.';
        if (!in_array($status, ['Published', 'Cancelled'], true)) $errors[] = 'Invalid status.';
        if (!strtotime($date)) $errors[] = 'Enter a valid date and time.';
        try { if ($up = save_upload('photo', 'events')) $image = $up; } catch (Exception $ex) { $errors[] = $ex->getMessage(); }
        if ($errors) set_flash('error', $errors);
        elseif ($id) {
            $pdo->prepare('UPDATE events SET title=?,description=?,event_type=?,event_date=?,location=?,status=?,image=? WHERE event_id=?')
                ->execute([$title, $desc, $type, $date, $loc, $status, $image, $id]);
            set_flash('success', 'Event updated.');
        } else {
            $pdo->prepare('INSERT INTO events (title,description,event_type,event_date,location,status,image) VALUES (?,?,?,?,?,?,?)')
                ->execute([$title, $desc, $type, $date, $loc, $status, $image]);
            set_flash('success', 'Event added.');
        }
    }
    header('Location: events.php');
    exit;
}

$pageTitle = 'Manage Events';
include '../includes/header.php';
include __DIR__ . '/_nav.php';
$editId = (int)($_GET['edit'] ?? 0);
$ev = ['event_id' => 0, 'title' => '', 'description' => '', 'event_type' => 'Other', 'event_date' => '', 'location' => '', 'status' => 'Published', 'image' => ''];
if ($editId) { $s = $pdo->prepare('SELECT * FROM events WHERE event_id=?'); $s->execute([$editId]); $ev = $s->fetch() ?: $ev; }
$rows = $pdo->query('SELECT e.*, (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id=e.event_id) AS signups FROM events e ORDER BY e.event_date DESC')->fetchAll();
?>
<h1>Events</h1>
<h2><?= $ev['event_id'] ? 'Edit Event' : 'Add Event' ?></h2>
<form class="form" method="post" enctype="multipart/form-data">
  <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
  <input type="hidden" name="current_image" value="<?= e($ev['image']) ?>">
  <label for="title">Title</label>
  <input type="text" id="title" name="title" value="<?= e($ev['title']) ?>" required>
  <div class="form-row">
    <div><label for="event_type">Type</label>
      <select id="event_type" name="event_type"><?php foreach (['Gear Swap', 'Trading Meetup', 'Other'] as $t): ?><option <?= $t === $ev['event_type'] ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div><label for="status">Status</label>
      <select id="status" name="status"><?php foreach (['Published', 'Cancelled'] as $s): ?><option <?= $s === $ev['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
  </div>
  <label for="event_date">Date and Time</label>
  <input type="datetime-local" id="event_date" name="event_date" value="<?= $ev['event_date'] ? date('Y-m-d\TH:i', strtotime($ev['event_date'])) : '' ?>" required>
  <label for="location">Location</label>
  <input type="text" id="location" name="location" value="<?= e($ev['location']) ?>" required>
  <label for="description">Description</label>
  <textarea id="description" name="description" rows="3"><?= e($ev['description']) ?></textarea>
  <label for="photo">Cover Photo (JPG, PNG or WEBP, max 2 MB)</label>
  <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
  <button class="btn" type="submit">Save</button>
  <?php if ($ev['event_id']): ?><a class="btn btn-outline" href="events.php">Cancel</a><?php endif; ?>
</form>

<h2>All Events</h2>
<div class="table-wrap"><table>
  <tr><th>Title</th><th>Type</th><th>Date</th><th>Location</th><th>Status</th><th>Sign-ups</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['title']) ?></td><td><?= e($r['event_type']) ?></td><td><?= e($r['event_date']) ?></td>
      <td><?= e($r['location']) ?></td><td><?= status_badge($r['status']) ?></td>
      <td><a href="event_registrations.php?id=<?= (int)$r['event_id'] ?>"><?= (int)$r['signups'] ?> view</a></td>
      <td>
        <a class="btn" href="events.php?edit=<?= (int)$r['event_id'] ?>">Edit</a>
        <form method="post" style="display:inline"><input type="hidden" name="delete_id" value="<?= (int)$r['event_id'] ?>">
          <button class="btn btn-danger" data-confirm="Delete this event and its sign-ups?">Delete</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
</table></div>
<?php include '../includes/footer.php'; ?>