<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) { $pdo->prepare('DELETE FROM events WHERE event_id=?')->execute([(int)$_POST['delete_id']]); set_flash('success', 'Event deleted.'); header('Location: events.php'); exit; }
    $id = (int)($_POST['event_id'] ?? 0); $title = trim($_POST['title'] ?? ''); $desc = trim($_POST['description'] ?? '');
    $type = $_POST['event_type'] ?? ''; $date = str_replace('T', ' ', $_POST['event_date'] ?? ''); $loc = trim($_POST['location'] ?? ''); $status = $_POST['status'] ?? 'Published';
    $errors = [];
    if ($title === '' || $loc === '') $errors[] = 'Title and location are required.';
    if (!in_array($type, ['Gear Swap', 'Trading Meetup', 'Other'], true)) $errors[] = 'Invalid event type.';
    if (!in_array($status, ['Published', 'Cancelled'], true)) $errors[] = 'Invalid status.';
    if (!strtotime($date)) $errors[] = 'Enter a valid date and time.';

    $cover = 'placeholder'; $existing = [];
    if ($id) {
        $q = $pdo->prepare('SELECT image FROM events WHERE event_id=?'); $q->execute([$id]);
        $cover = $q->fetchColumn() ?: $cover;
        $q = $pdo->prepare("SELECT photo_id, file_name FROM event_photos WHERE event_id=? AND file_name<>? AND file_name<>'placeholder' ORDER BY photo_id LIMIT 2"); $q->execute([$id, $cover]); $existing = $q->fetchAll();
    }
    $names = [];
    for ($i = 0; $i < 3; $i++) {
        try { $up = save_upload('photo' . ($i + 1), 'events'); } catch (Exception $ex) { $errors[] = 'Photo ' . ($i + 1) . ': ' . $ex->getMessage(); $up = null; }
        $names[$i] = $up ?: ($i === 0 ? $cover : ($existing[$i - 1]['file_name'] ?? ''));
    }
    if ($errors) { set_flash('error', $errors); header('Location: events.php' . ($id ? '?edit=' . $id : '')); exit; }

    if ($id) {
        $pdo->prepare('UPDATE events SET title=?,description=?,event_type=?,event_date=?,location=?,status=?,image=? WHERE event_id=?')->execute([$title, $desc, $type, $date, $loc, $status, $names[0], $id]);
        set_flash('success', 'Event updated.');
    } else {
        $pdo->prepare('INSERT INTO events (title,description,event_type,event_date,location,status,image) VALUES (?,?,?,?,?,?,?)')->execute([$title, $desc, $type, $date, $loc, $status, $names[0]]);
        $id = (int)$pdo->lastInsertId();
        set_flash('success', 'Event added.');
    }
    for ($i = 1; $i < 3; $i++) {
        if ($names[$i] === '') continue;
        if (isset($existing[$i - 1])) $pdo->prepare('UPDATE event_photos SET file_name=? WHERE photo_id=?')->execute([$names[$i], $existing[$i - 1]['photo_id']]);
        else $pdo->prepare('INSERT INTO event_photos (event_id, file_name) VALUES (?,?)')->execute([$id, $names[$i]]);
    }
    header('Location: events.php'); exit;
}
$pageTitle = 'Manage Events';
include '../includes/header.php';
$editId = (int)($_GET['edit'] ?? 0);
$ev = ['event_id' => 0, 'title' => '', 'description' => '', 'event_type' => 'Other', 'event_date' => '', 'location' => '', 'status' => 'Published', 'image' => ''];
$slots = [];
if ($editId) {
    $s = $pdo->prepare('SELECT * FROM events WHERE event_id=?'); $s->execute([$editId]); $ev = $s->fetch() ?: $ev;
    $q = $pdo->prepare("SELECT file_name FROM event_photos WHERE event_id=? AND file_name<>? AND file_name<>'placeholder' ORDER BY photo_id LIMIT 2"); $q->execute([$editId, $ev['image']]); $slots = array_merge([$ev['image']], $q->fetchAll(PDO::FETCH_COLUMN));
}
$editing = (bool)$ev['event_id'];
$rows = $pdo->query('SELECT e.*, (SELECT COALESCE(SUM(guests),0) FROM event_registrations r WHERE r.event_id=e.event_id) AS guests FROM events e ORDER BY e.event_date DESC')->fetchAll();
$slotLabels = ['Cover Photo (4:3)', 'Photo 2 (4:3)', 'Photo 3 (4:3)'];
?>
<h1 class="page-title">Events</h1>
<form class="form-h <?= $editing ? 'is-edit' : 'is-add' ?>" method="post" enctype="multipart/form-data">
  <div class="form-head">
    <div style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap">
      <span class="mode-badge <?= $editing ? 'edit' : 'add' ?>"><?= $editing ? 'Editing Event' : 'Adding New Event' ?></span>
    </div>
    <div class="actions"><button class="btn" type="submit"><?= $editing ? 'Save Changes' : 'Add Event' ?></button><?php if ($editing): ?><a class="btn btn-danger" href="events.php">Cancel</a><?php endif; ?></div>
  </div>
  <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
  <div class="fields">
    <div class="span2"><label for="title">Title</label><input type="text" id="title" name="title" value="<?= e($ev['title']) ?>" required></div>
    <div><label for="event_type">Type</label><select id="event_type" name="event_type"><?php foreach (['Gear Swap', 'Trading Meetup', 'Other'] as $t): ?><option <?= $t === $ev['event_type'] ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
    <div><label for="status">Status</label><select id="status" name="status"><?php foreach (['Published', 'Cancelled'] as $s): ?><option <?= $s === $ev['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
    <div><label for="event_date">Date and Time</label><input type="datetime-local" id="event_date" name="event_date" value="<?= $ev['event_date'] ? date('Y-m-d\TH:i', strtotime($ev['event_date'])) : '' ?>" required></div>
    <div class="span3" style="grid-column:span 3"><label for="location">Location</label><input type="text" id="location" name="location" value="<?= e($ev['location']) ?>" required></div>
    <div class="span4"><label for="description">Description</label><textarea id="description" name="description" rows="2"><?= e($ev['description']) ?></textarea></div>
    <div class="span4"><label>Event Photos (JPG, PNG or WEBP, maximum 2 MB each<?= $editing ? '. Leave a slot empty to keep its current photo' : '' ?>)</label>
      <div class="photo-slots">
        <?php for ($i = 0; $i < 3; $i++): $cur = $slots[$i] ?? ($i === 0 ? $ev['image'] : ''); ?>
          <div class="photo-slot">
            <?php if ($editing && $cur): ?><img class="preview" src="<?= img('events/' . $cur) ?>" alt=""><?php endif; ?>
            <label for="photo<?= $i + 1 ?>"><?= e($slotLabels[$i]) ?></label>
            <input type="file" id="photo<?= $i + 1 ?>" name="photo<?= $i + 1 ?>" accept="image/jpeg,image/png,image/webp">
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</form>

<h2>All Events</h2>
<div class="table-wrap"><table class="zebra">
  <tr><th>Title</th><th>Type</th><th>Date</th><th>Location</th><th>Status</th><th>Guests</th><th>Action</th></tr>
  <?php foreach ($rows as $r): ?>
    <tr><td><strong><?= e($r['title']) ?></strong></td><td><?= e($r['event_type']) ?></td><td><?= e(date('M d, Y g:i A', strtotime($r['event_date']))) ?></td><td><?= e($r['location']) ?></td><td><?= status_badge($r['status']) ?></td>
      <td><a class="btn btn-sm" href="event_registrations.php?id=<?= (int)$r['event_id'] ?>"><?= (int)$r['guests'] ?> guests</a></td>
      <td><div class="actions"><a class="btn btn-sm" href="events.php?edit=<?= (int)$r['event_id'] ?>">Edit</a>
        <form method="post"><input type="hidden" name="delete_id" value="<?= (int)$r['event_id'] ?>"><button class="btn btn-danger btn-sm" data-confirm="Delete this event and its sign-ups?">Delete</button></form></div></td></tr>
  <?php endforeach; ?>
</table></div>
<?php include '../includes/footer.php'; ?>