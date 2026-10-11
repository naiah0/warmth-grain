<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$eid    = (int)($_POST['event_id'] ?? 0);
$name   = trim($_POST['full_name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$guests = (int)($_POST['guests'] ?? 0);
$notes  = trim($_POST['notes'] ?? '');
$errors = [];

$s = $pdo->prepare("SELECT * FROM events WHERE event_id=? AND status='Published' AND event_date >= NOW()");
$s->execute([$eid]);
if (!$s->fetch()) $errors[] = 'This event is not open for sign-ups.';
if ($name === '') $errors[] = 'Full name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = 'Phone must look like 09XXXXXXXXX.';
if ($guests < 1 || $guests > 10) $errors[] = 'Guests must be between 1 and 10.';
if (!$errors) {
    $dup = $pdo->prepare('SELECT COUNT(*) FROM event_registrations WHERE event_id=? AND email=?');
    $dup->execute([$eid, $email]);
    if ($dup->fetchColumn()) $errors[] = 'This email is already registered for the event.';
}

if ($errors) set_flash('error', $errors);
else {
    $pdo->prepare('INSERT INTO event_registrations (event_id,user_id,full_name,email,phone,guests,notes) VALUES (?,?,?,?,?,?,?)')
        ->execute([$eid, $_SESSION['user_id'] ?? null, $name, $email, $phone, $guests, $notes ?: null]);
    set_flash('success', 'You are registered. See you there!');
}
header('Location: ' . BASE_URL . 'pages/event.php?id=' . $eid);
exit;