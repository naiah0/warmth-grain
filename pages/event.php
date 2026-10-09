<?php
require_once '../config/db.php';
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id=? AND status <> 'Cancelled'");
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$ev = $stmt->fetch();
$pageTitle = $ev['title'] ?? 'Event not found';
include '../includes/header.php';

if (!$ev): ?>
  <h1>Event not found</h1><p><a href="events.php">Back to events</a></p>
<?php else:
  $photos = $pdo->prepare('SELECT * FROM event_photos WHERE event_id=?');
  $photos->execute([$ev['event_id']]);
  $upcoming = strtotime($ev['event_date']) >= time();
  $user = ['full_name' => '', 'email' => '', 'phone' => ''];
  if (is_logged_in()) {
      $u = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE user_id=?');
      $u->execute([$_SESSION['user_id']]);
      $user = $u->fetch() ?: $user;
  }
?>
  <p><a href="events.php">&larr; All events</a></p>
  <img class="event-hero" src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
  <h1><?= e($ev['title']) ?></h1>
  <p><strong><?= e($ev['event_type']) ?></strong> &middot; <?= date('l, F d, Y g:i A', strtotime($ev['event_date'])) ?> &middot; <?= e($ev['location']) ?></p>
  <p><?= nl2br(e($ev['description'])) ?></p>

  <h2>Photos</h2>
  <div class="gallery">
    <?php foreach ($photos->fetchAll() as $ph): ?><img src="<?= img('events/' . $ph['file_name']) ?>" alt="<?= e($ev['title']) ?> photo" loading="lazy"><?php endforeach; ?>
  </div>

  <?php if ($upcoming): ?>
    <h2>Sign Up</h2>
    <form class="form" method="post" action="<?= BASE_URL ?>actions/event_register.php" data-validate novalidate>
      <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
      <label for="phone">Phone (09XXXXXXXXX)</label>
      <input type="text" id="phone" name="phone" value="<?= e($user['phone']) ?>" data-phone required>
      <label for="guests">Number of Guests (including you)</label>
      <input type="number" id="guests" name="guests" value="1" min="1" max="10" required>
      <label for="notes">Notes (optional)</label>
      <input type="text" id="notes" name="notes" maxlength="255" placeholder="e.g. I will bring cameras to swap">
      <button class="btn" type="submit">Register</button>
    </form>
  <?php else: ?>
    <p class="info-box">This event has ended. Check the upcoming events for the next one.</p>
  <?php endif; ?>
<?php endif;
include '../includes/footer.php'; ?>