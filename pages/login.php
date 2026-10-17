<?php
require_once '../includes/auth.php';
if (!empty($_GET['next'])) $_SESSION['next'] = $_GET['next'];
if (is_logged_in()) { header('Location: ' . BASE_URL . (is_admin() ? 'admin/index.php' : 'index.php')); exit; }
require_once '../config/db.php';
$pcPending = !empty($_SESSION['admin_pending']);
$pcError   = $_SESSION['pc_error'] ?? ''; unset($_SESSION['pc_error']);
$pageTitle = 'Login';
include '../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-side"><h2 class="plain" style="margin-top:0">Welcome Back</h2><p>Log in to book film services, send cameras for repair, track your orders and register for events.</p>
    <ul><li>Track bookings and repairs</li><li>View all your purchases</li><li>Enjoy faster checkout</li></ul></div>
  <div class="auth-form">
  <?php if ($pcPending): ?>
    <h1 class="page-title">Admin Verification</h1>
    <form class="form" method="post" action="<?= BASE_URL ?>actions/admin_passcode.php" autocomplete="off">
      <p style="margin:0 0 .6rem">Enter the 4-digit admin passcode to continue.</p>
      <?php if ($pcError): ?><div class="alert alert-error"><ul><li><?= e($pcError) ?></li></ul></div><?php endif; ?>
      <label for="passcode">Passcode</label>
      <input type="password" id="passcode" name="passcode" class="passcode-input" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" minlength="4" placeholder="&bull;&bull;&bull;&bull;" required autofocus oninput="this.value=this.value.replace(/\D/g,'')">
      <div class="actions" style="margin-top:.8rem">
        <button class="btn" type="submit">Verify</button>
        <button class="btn btn-danger" type="submit" name="cancel" value="1" formnovalidate>Cancel</button>
      </div>
    </form>
  <?php else: ?>
    <h1 class="page-title">Login</h1>
    <form class="form" method="post" action="<?= BASE_URL ?>actions/login_action.php" data-validate novalidate>
      <label for="email">Email Address</label><input type="email" id="email" name="email" required>
      <label for="password">Password</label>
      <div class="pw"><input type="password" id="password" name="password" required><button type="button" class="pw-toggle" data-toggle-password="password">Show</button></div>
      <div class="actions"><button class="btn" type="submit">Log In</button></div>
      <p style="margin-top:.8rem">No account yet? <a href="register.php">Register</a></p>
    </form>
  <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>