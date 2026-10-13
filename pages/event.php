<?php
require_once '../config/db.php';
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id=? AND status <> 'Cancelled'");
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$ev = $stmt->fetch();
$pageTitle = $ev['title'] ?? 'Event not found';
include '../includes/header.php';

if (!$ev): ?>
  <?= back_button(BASE_URL . 'pages/events.php') ?><h1 class="page-title">Event not found</h1>
<?php else:
  $photos = $pdo->prepare("SELECT * FROM event_photos WHERE event_id=? AND file_name<>? AND file_name<>'placeholder' ORDER BY photo_id LIMIT 2"); $photos->execute([$ev['event_id'], $ev['image']]);
  $upcoming = strtotime($ev['event_date']) >= time();
  $user = ['full_name' => '', 'email' => '', 'phone' => ''];
  if (is_logged_in()) { $u = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE user_id=?'); $u->execute([$_SESSION['user_id']]); $user = $u->fetch() ?: $user; }
?>
  <?= back_button(BASE_URL . 'pages/events.php') ?>
  <h1 class="page-title"><?= e($ev['title']) ?></h1>
  <div class="<?= $upcoming ? 'two-col' : '' ?>">
    <div>
      <div class="ev-photo-box">
        <img class="main" src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
        <div class="gallery"><?php foreach ($photos->fetchAll() as $ph): ?><img src="<?= img('events/' . $ph['file_name']) ?>" alt="<?= e($ev['title']) ?> photo" loading="lazy"><?php endforeach; ?></div>
      </div>
      <div class="box ev-desc-box">
        <h3><?= e($ev['event_type']) ?></h3>
        <p><strong>Date and Time:</strong> <?= date('l, F d, Y \a\t g:i A', strtotime($ev['event_date'])) ?></p>
        <p><strong>Location:</strong> <?= e($ev['location']) ?></p>
        <?php if ($upcoming): ?><p style="margin-top:.7rem"><?= nl2br(e($ev['description'])) ?></p>
        <?php else: ?><div class="event-ended">This event has ended. Thank you to everyone who joined us!</div><?php endif; ?>
      </div>
    </div>
    <?php if ($upcoming): ?>
    <div class="side">
      <?php if (!is_logged_in()): ?>
        <div class="side-box"><h3>Save Your Spot!</h3><p>An account is required to register for events.</p><p style="margin-top:.8rem"><a class="btn" href="<?= BASE_URL ?>pages/login.php?next=<?= urlencode(BASE_URL . 'pages/event.php?id=' . (int)$ev['event_id']) ?>">Log In / Register</a></p></div>
      <?php else: ?>
      <form class="form" method="post" action="<?= BASE_URL ?>actions/event_register.php" data-validate novalidate>
        <h3>Save Your Spot!</h3>
        <p class="small">Join the community. It only takes a minute.</p>
        <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
        <label for="full_name">Full Name</label><span class="hint">As it should appear on the guest list.</span>
        <input type="text" id="full_name" name="full_name" value="<?= e($user['full_name']) ?>" required>
        <label for="email">Email Address</label><span class="hint">We will send event updates to this address.</span>
        <input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required>
        <label for="phone">Mobile Number</label><span class="hint">Use the format 09XXXXXXXXX.</span>
        <input type="text" id="phone" name="phone" value="<?= e($user['phone']) ?>" data-phone required>
        <label for="guests">Number of Guests</label><span class="hint">Include yourself. The maximum is 10.</span>
        <input type="number" id="guests" name="guests" value="1" min="1" max="10" required>
        <label for="notes">Additional Notes (Optional)</label><span class="hint">For example, the items you plan to bring for swapping.</span>
        <input type="text" id="notes" name="notes" maxlength="255">
        <div class="reg-actions"><button class="btn" type="submit">Register</button></div>
      </form>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
<?php endif;
include '../includes/footer.php'; ?>