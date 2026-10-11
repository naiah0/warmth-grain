<?php
require_once '../config/db.php';
$pageTitle = 'Events'; $hasBanner = true;
include '../includes/header.php';
$up = $pdo->query("SELECT * FROM events WHERE status='Published' AND event_date >= NOW() ORDER BY event_date")->fetchAll();
$past = $pdo->query("SELECT * FROM events WHERE status='Published' AND event_date < NOW() ORDER BY event_date DESC")->fetchAll();
echo banner('Community Events', 'Gear swaps, trading meetups and workshops for the film community.', ['banners/events', 'events/event-1-1']);
$card = function ($ev) { ob_start(); ?>
  <a class="card card-wide card-link" href="event.php?id=<?= (int)$ev['event_id'] ?>">
    <div class="card-photo"><img src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>"></div>
    <div class="card-body"><small><?= e($ev['event_type']) ?></small><h3><?= e($ev['title']) ?></h3>
      <p class="small"><?= date('F d, Y', strtotime($ev['event_date'])) ?> at <?= date('g:i A', strtotime($ev['event_date'])) ?></p><p class="small"><?= e($ev['location']) ?></p></div>
  </a>
<?php return ob_get_clean(); };
?>
<h2>Upcoming Events</h2>
<div class="grid-wide"><?php foreach ($up as $ev) echo $card($ev); if (!$up) echo '<p class="center">No upcoming events yet.</p>'; ?></div>
<h2>Past Events</h2>
<div class="grid-wide past"><?php foreach ($past as $ev) echo $card($ev); if (!$past) echo '<p class="center">No past events.</p>'; ?></div>
<?php include '../includes/footer.php'; ?>