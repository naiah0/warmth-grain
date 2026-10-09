<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$errors = [];

if ($name === '' || $subject === '' || $message === '') $errors[] = 'All fields are required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

if ($errors) {
    set_flash('error', $errors);
} else {
    $pdo->prepare('INSERT INTO messages (name,email,subject,message) VALUES (?,?,?,?)')
        ->execute([$name, $email, $subject, $message]);
    set_flash('success', 'Message sent. Thank you!');
}
header('Location: ' . BASE_URL . 'pages/contact.php');
exit;