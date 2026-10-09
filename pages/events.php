<?php
require_once '../config/db.php';
$pageTitle = 'Events';
include '../includes/header.php';

$up = $pdo->query("SELECT * FROM events WHERE status='Published' AND event_date >= NOW() ORDER BY event_date")->fetchAll();
$past = $pdo->query("SELECT * FROM events WHERE status='Published' AND event_date < NOW() ORDER BY event_date DESC")->fetchAll();
?>
<h1>Community Events</h1>
<p>Gear swaps, trading meetups and workshops for the film community. Open an event for details and to sign up.</p>
<h2>Upcoming Events</h2>
<div class="grid">
  <?php foreach ($up as $ev): ?>
    <a class="card card-link" href="event.php?id=<?= (int)$ev['event_id'] ?>">
      <img src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
      <small class="muted"><?= e($ev['event_type']) ?></small>
      <h3><?= e($ev['title']) ?></h3>
      <p><?= date('M d, Y g:i A', strtotime($ev['event_date'])) ?><br><?= e($ev['location']) ?></p>
    </a>
  <?php endforeach; if (!$up) echo '<p>No upcoming events yet.</p>'; ?>
</div>
<h2>Past Events</h2>
<div class="grid past">
  <?php foreach ($past as $ev): ?>
    <a class="card card-link" href="event.php?id=<?= (int)$ev['event_id'] ?>">
      <img src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
      <small class="muted"><?= e($ev['event_type']) ?></small>
      <h3><?= e($ev['title']) ?></h3>
      <p><?= date('M d, Y', strtotime($ev['event_date'])) ?></p>
    </a>
  <?php endforeach; if (!$past) echo '<p>No past events.</p>'; ?>
</div>
<?php include '../includes/footer.php'; ?>