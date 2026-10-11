<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $pass = $_POST['password'] ?? ''; $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($pass !== '' && strlen($pass) < 8) $errors[] = 'New password must be at least 8 characters.';
    $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email=? AND user_id<>?'); $dup->execute([$email, $_SESSION['user_id']]);
    if ($dup->fetchColumn()) $errors[] = 'That email is already used.';
    if ($errors) set_flash('error', $errors);
    else {
        $pdo->prepare('UPDATE users SET full_name=?, email=?, phone=? WHERE user_id=?')->execute([$name, $email, $phone, $_SESSION['user_id']]);
        if ($pass !== '') $pdo->prepare('UPDATE users SET password_hash=? WHERE user_id=?')->execute([password_hash($pass, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        $_SESSION['name'] = $name; set_flash('success', 'Profile updated.');
    }
    header('Location: profile.php'); exit;
}
$pageTitle = 'Admin Profile';
$s = $pdo->prepare('SELECT * FROM users WHERE user_id=?'); $s->execute([$_SESSION['user_id']]); $u = $s->fetch();
include '../includes/header.php';
?>
<h1 class="page-title">Admin Profile</h1>
<form class="form" method="post" style="margin:0 auto">
  <label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required>
  <label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($u['email']) ?>" required>
  <label for="phone">Phone</label><input type="text" id="phone" name="phone" value="<?= e($u['phone']) ?>">
  <label for="password">New password</label><span class="hint">Leave blank to keep your current password.</span><input type="password" id="password" name="password" minlength="8">
  <button class="btn" type="submit">Save Changes</button>
</form>
<?php include '../includes/footer.php'; ?>