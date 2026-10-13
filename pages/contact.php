<?php
require_once '../config/db.php';
$pageTitle = 'Contact';
include '../includes/header.php';
$user = ['full_name' => '', 'email' => '']; $unack = 0;
if (is_logged_in()) {
    $u = $pdo->prepare('SELECT full_name, email FROM users WHERE user_id=?'); $u->execute([$_SESSION['user_id']]); $user = $u->fetch() ?: $user;
    $c = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE user_id=? AND status='Replied' AND customer_ack=0"); $c->execute([$_SESSION['user_id']]); $unack = (int)$c->fetchColumn();
}
?>
<h1 class="page-title">Contact Us</h1>
<div class="contact-details">
  <div class="box"><h3>Visit the Lab</h3><p><?= e(BIZ_ADDRESS) ?></p><p class="small muted">Drop-offs and repairs are accepted during opening hours.</p></div>
  <div class="box"><h3>Opening Hours</h3><p><?= e(BIZ_HOURS) ?></p><p class="small muted">Closed on weekends and holidays.</p></div>
  <div class="box"><h3>Call Us</h3><p><?= e(BIZ_PHONE) ?></p></div>
  <div class="box"><h3>Email Us</h3><p><?= e(BIZ_EMAIL) ?></p><p class="small muted">We reply within 1 business day.</p></div>
</div>

<h2>Send an Inquiry</h2>
<?php if (is_logged_in()): ?>
  <div class="inq-top"><a class="inq-box" href="<?= BASE_URL ?>pages/account/inquiries.php"><?= icon('mail', 18) ?> My Inquiries<?= $unack ? ' <span class="inq-count">' . $unack . '</span>' : '' ?></a></div>
<?php endif; ?>
<form class="form wide" method="post" action="<?= BASE_URL ?>actions/contact_action.php" data-validate novalidate>
  <div class="form-grid">
    <div><label for="name">Full Name</label><span class="hint">Enter your complete name.</span><input type="text" id="name" name="name" value="<?= e($user['full_name']) ?>" required></div>
    <div><label for="email">Email Address</label><span class="hint">We will send our reply to this address.</span><input type="email" id="email" name="email" value="<?= e($user['email']) ?>" required></div>
  </div>
  <label for="subject">Subject</label><span class="hint">A short title, for example "Question about 120 film".</span>
  <input type="text" id="subject" name="subject" required>
  <label for="message">Message</label><span class="hint">Tell us how we can help you.</span>
  <textarea id="message" name="message" rows="5" required></textarea>
  <div class="form-actions"><button class="btn" type="submit">Send Inquiry</button></div>
</form>
