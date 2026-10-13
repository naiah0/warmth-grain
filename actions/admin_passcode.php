<?php
require_once '../includes/auth.php';

$loginUrl = BASE_URL . 'pages/login.php';
$pending  = $_SESSION['admin_pending'] ?? null;

// No verified email/password step yet -> back to login
if (!$pending || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . $loginUrl); exit; }

// Cancel button
if (isset($_POST['cancel'])) {
    unset($_SESSION['admin_pending']);
    header('Location: ' . $loginUrl); exit;
}

$code = $_POST['passcode'] ?? '';
if (preg_match('/^\d{4}$/', $code) && hash_equals(ADMIN_PASSCODE, $code)) {
    session_regenerate_id(true);
    unset($_SESSION['admin_pending'], $_SESSION['pc_error'], $_SESSION['next']);
    $_SESSION['user_id'] = $pending['user_id'];
    $_SESSION['name']    = $pending['name'];
    $_SESSION['role']    = 'admin';
    header('Location: ' . BASE_URL . 'admin/index.php'); exit;
}

// Wrong passcode: 3 tries, then the admin must log in again
$_SESSION['admin_pending']['attempts']++;
if ($_SESSION['admin_pending']['attempts'] >= 3) {
    unset($_SESSION['admin_pending']);
    set_flash('error', 'Too many incorrect passcode attempts. Please log in again.');
} else {
    $_SESSION['pc_error'] = 'Incorrect passcode. Please try again.';
}
header('Location: ' . $loginUrl);
exit;