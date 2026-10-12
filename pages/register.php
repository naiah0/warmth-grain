<?php
require_once '../includes/auth.php';
if (is_logged_in()) { header('Location: ' . BASE_URL . (is_admin() ? 'admin/index.php' : 'index.php')); exit; }
require_once '../config/db.php';
$pageTitle = 'Register';
include '../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-side"><h2 class="plain" style="margin-top:0">Join Warmth&amp;Grain</h2><p>One account for film processing, repairs, the store and community events.</p>
    <ul><li>Book drop-offs online</li><li>Follow your repair status</li><li>Keep your order history</li></ul></div>
  <div class="auth-form">
    <h1 class="page-title">Create an Account</h1>
    <form class="form" method="post" action="<?= BASE_URL ?>actions/register_action.php" data-validate novalidate>
      <label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" required>
      <label for="email">Email Address</label><input type="email" id="email" name="email" required>
      <label for="phone">Mobile Number</label><span class="hint">Use the format 09XXXXXXXXX.</span>
      <input type="text" id="phone" name="phone" data-phone required>
      <label for="password">Password</label><span class="hint">Use at least 8 characters.</span>
      <div class="pw"><input type="password" id="password" name="password" minlength="8" required><button type="button" class="pw-toggle" data-toggle-password="password">Show</button></div>
      <label for="confirm">Confirm Password</label><input type="password" id="confirm" name="confirm" data-match="password" required>
      <button class="btn" type="submit">Register</button>
      <p style="margin-top:.8rem">Already have an account? <a href="login.php">Log in</a></p>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>