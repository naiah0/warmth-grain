<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/mailer.php';

$name    = trim($_POST['full_name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$pass    = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';
$errors  = [];

if ($name === '') $errors[] = 'Full name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = 'Mobile number must look like 09XXXXXXXXX.';
if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($pass !== $confirm) $errors[] = 'Passwords do not match.';

if (!$errors) {
    $check = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) $errors[] = 'That email is already registered.';
}
if ($errors) {
    set_flash('error', $errors);
    header('Location: ' . BASE_URL . 'pages/register.php');
    exit;
}

$stmt = $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
$uid = (int)$pdo->lastInsertId();

// Confirmation email. A failed email never blocks the registration.
$sent = send_welcome_email($email, $name, account_no($uid));
set_flash('success', $sent
    ? 'Account created. A confirmation email was sent to ' . $email . '. You can log in now.'
    : 'Account created, but we could not send a confirmation email. Please check the mail configuration or contact us. You can log in now.');
header('Location: ' . BASE_URL . 'pages/login.php');
exit;