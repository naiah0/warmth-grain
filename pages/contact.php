<?php
require_once '../config/db.php';
$pageTitle = 'Contact';
include '../includes/header.php';
?>
<h1>Contact Us</h1>
<p>Email: hello@warmthandgrain.example &middot; Phone: 0900 000 0000<br>Location: Warmth&amp;Grain Lab (add your address)</p>

<h2>Send an Inquiry</h2>
<form class="form" method="post" action="<?= BASE_URL ?>actions/contact_action.php">
  <label for="name">Name</label>
  <input type="text" id="name" name="name" required>
  <label for="email">Email</label>
  <input type="email" id="email" name="email" required>
  <label for="subject">Subject</label>
  <input type="text" id="subject" name="subject" required>
  <label for="message">Message</label>
  <textarea id="message" name="message" rows="5" required></textarea>
  <button class="btn" type="submit">Send</button>
</form>

<?php include '../includes/footer.php'; ?>