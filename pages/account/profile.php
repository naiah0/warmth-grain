<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? ''); $phone = trim($_POST['phone'] ?? '');
    $errors = [];
    if ($name === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = 'Mobile number must look like 09XXXXXXXXX.';
    if (!$errors) {
        $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email=? AND user_id<>?'); $dup->execute([$email, $_SESSION['user_id']]);
        if ($dup->fetchColumn()) $errors[] = 'That email is already used by another account.';
    }
    if ($errors) set_flash('error', $errors);
    else {
        $pdo->prepare('UPDATE users SET full_name=?, email=?, phone=? WHERE user_id=?')->execute([$name, $email, $phone, $_SESSION['user_id']]);
        $_SESSION['name'] = $name;
        set_flash('success', 'Profile updated.');
    }
    header('Location: profile.php'); exit;
}
$pageTitle = 'Edit Profile';
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?'); $stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch();
include '../../includes/header.php';
?>
<h1 class="page-title">Edit Profile</h1>
<p class="center">Your account number: <span class="acct-box"><?= e(account_no($u['user_id'])) ?></span></p>
<form class="form" method="post" data-validate novalidate style="margin:1.2rem auto 0">
  <label for="full_name">Full Name</label><input type="text" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required>
  <label for="email">Email Address</label><input type="email" id="email" name="email" value="<?= e($u['email']) ?>" required>
  <label for="phone">Mobile Number</label><span class="hint">Use the format 09XXXXXXXXX.</span>
  <input type="text" id="phone" name="phone" value="<?= e($u['phone']) ?>" data-phone required>
  <button class="btn" type="submit">Save Changes</button>
</form>
<?php include '../../includes/footer.php'; ?>