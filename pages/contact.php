<?php
require_once '../config/db.php';
$pageTitle = 'Contact';
include '../includes/header.php';
$user = ['full_name' => '', 'email' => ''];
if (is_logged_in()) { $u = $pdo->prepare('SELECT full_name, email FROM users WHERE user_id=?'); $u->execute([$_SESSION['user_id']]); $user = $u->fetch() ?: $user; }
?>
<h1 class="page-title">Contact Us</h1>
<div class="contact-details">
  <div class="box"><h3>Visit the lab</h3><p><?= e(BIZ_ADDRESS) ?></p><p class="small muted">Drop-offs and repairs are accepted during opening hours.</p></div>
  <div class="box"><h3>Opening hours</h3><p><?= e(BIZ_HOURS) ?></p><p class="small muted">Closed on weekends and holidays.</p></div>
  <div class="box"><h3>Call us</h3><p><?= e(BIZ_PHONE) ?></p></div>
  <div class="box"><h3>Email us</h3><p><?= e(BIZ_EMAIL) ?></p><p class="small muted">We reply within 1 business day.</p></div>
</div>

<h2>Send an Inquiry</h2>
<form class="form wide" method="post" action="<?= BASE_URL ?>actions/contact_action.php" data-validate novalidate>
  <div class="form-grid">
    <div><label for="name">Name</label><span class="hint">Your full name.</span><input type="text" id="name" name="name" value="<?= e($user['full_name']) ?>" required></div>
    <div><label for="email">Email</label><span class="hint">Where we should send our reply.</span><input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required></div>
  </div>
  <label for="subject">Subject</label><span class="hint">A short title, for example "Question about 120 film".</span>
  <input type="text" id="subject" name="subject" required>
  <label for="message">Message</label><span class="hint">Tell us how we can help.</span>
  <textarea id="message" name="message" rows="5" required></textarea>
  <button class="btn" type="submit">Send Inquiry</button>
  <?php if (is_logged_in()): ?><a class="btn btn-outline" href="<?= BASE_URL ?>pages/account/inquiries.php">View my inquiries and replies</a><?php endif; ?>
</form>
<?php include '../includes/footer.php'; ?>