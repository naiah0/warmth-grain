<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = 'Phone must look like 09XXXXXXXXX.';
    if ($errors) set_flash('error', $errors);
    else {
        $pdo->prepare('UPDATE users SET full_name=?, phone=? WHERE user_id=?')->execute([$name, $phone, $_SESSION['user_id']]);
        $_SESSION['name'] = $name;
        set_flash('success', 'Profile updated.');
    }
    header('Location: profile.php');
    exit;
}

$pageTitle = 'Edit Profile';
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id=?');
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch();
include '../../includes/header.php';
?>
<h1>Edit Profile</h1>
<form class="form" method="post">
  <label for="full_name">Full Name</label>
  <input type="text" id="full_name" name="full_name" value="<?= e($u['full_name']) ?>" required>
  <label>Email (cannot be changed)</label>
  <input type="email" value="<?= e($u['email']) ?>" disabled>
  <label for="phone">Phone</label>
  <input type="text" id="phone" name="phone" value="<?= e($u['phone']) ?>" pattern="09[0-9]{9}" required>
  <button class="btn" type="submit">Save</button>
</form>
<?php include '../../includes/footer.php'; ?>