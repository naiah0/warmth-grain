<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
    set_flash('error', 'Incorrect email or password.');
    header('Location: ' . BASE_URL . 'pages/login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['name']    = $user['full_name'];
$_SESSION['role']    = $user['role'];

$next = safe_next();
header('Location: ' . ($next ?: BASE_URL . ($user['role'] === 'admin' ? 'admin/index.php' : 'pages/account/dashboard.php')));
exit;