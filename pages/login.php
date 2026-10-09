<?php
require_once '../includes/auth.php';
if (!empty($_GET['next'])) $_SESSION['next'] = $_GET['next'];
if (is_logged_in()) { header('Location: ' . BASE_URL . 'pages/account/dashboard.php'); exit; }
require_once '../config/db.php';
$pageTitle = 'Login';
include '../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-side">
    <h2 style="margin-top:0">Welcome back</h2>
    <p>Log in to book film services, send cameras for repair, track your orders and sign up for events.</p>
    <ul><li>Track bookings and repairs</li><li>See all your purchases</li><li>Faster checkout</li></ul>
  </div>
  <div class="auth-form">
    <h1>Login</h1>
    <form class="form" method="post" action="<?= BASE_URL ?>actions/login_action.php" data-validate novalidate>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required>
      <label for="password">Password</label>
      <div class="pw">
        <input type="password" id="password" name="password" required>
        <button type="button" class="pw-toggle" data-toggle-password="password">Show</button>
      </div>
      <button class="btn" type="submit">Log In</button>
      <p>No account yet? <a href="register.php">Register</a></p>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>