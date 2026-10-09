<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('BASE_URL', '/warmth-grain/');
const PAY_METHODS = ['GCash', 'Maya', 'Card', 'Cash'];

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function peso($n) { return '₱' . number_format((float)$n, 2); }
function slug($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'); }

// Returns an image URL; falls back to $fallback, then the placeholder
function img($path, $fallback = '') {
    $root = dirname(__DIR__) . '/assets/img/';
    foreach ([$path, $fallback] as $p) {
        if ($p && file_exists($root . $p)) return e(BASE_URL . 'assets/img/' . $p);
    }
    return e(BASE_URL . 'assets/img/placeholder.svg');
}

function is_logged_in() { return isset($_SESSION['user_id']); }
function is_admin() { return ($_SESSION['role'] ?? '') === 'admin'; }

function set_flash($type, $messages) { $_SESSION['flash'] = ['type' => $type, 'messages' => (array)$messages]; }
function show_flash() {
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    echo '<div class="alert alert-' . e($f['type']) . '"><ul>';
    foreach ($f['messages'] as $m) echo '<li>' . e($m) . '</li>';
    echo '</ul></div>';
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['next'] = $_SERVER['REQUEST_URI'];
        set_flash('error', 'Please log in first.');
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit;
    }
}
function require_admin() {
    if (!is_admin()) { header('Location: ' . BASE_URL . 'index.php'); exit; }
}
// Only allow redirecting back to pages inside this site
function safe_next() {
    $n = $_SESSION['next'] ?? ''; unset($_SESSION['next']);
    return (strpos($n, BASE_URL) === 0 && strpos($n, '//') === false && !preg_match('/[\r\n]/', $n)) ? $n : '';
}

function payment_errors($method, $ref) {
    if (!in_array($method, PAY_METHODS, true)) return ['Choose a payment method.'];
    if (in_array($method, ['GCash', 'Maya'], true) && !preg_match('/^[A-Za-z0-9]{6,20}$/', $ref))
        return ['Enter a valid payment reference number (6-20 letters or numbers).'];
    return [];
}
function record_payment(PDO $pdo, $uid, $type, $refId, $method, $refNo, $amount) {
    $status = $method === 'Cash' ? 'Pending' : 'Paid';
    $pdo->prepare('INSERT INTO payments (user_id,payment_type,reference_id,method,amount,status,paid_at,reference_no) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([$uid, $type, $refId, $method, $amount, $status, $status === 'Paid' ? date('Y-m-d H:i:s') : null, $refNo ?: null]);
    return $status;
}

function save_upload($field, $folder) {
    if (empty($_FILES[$field]['name'])) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new Exception('Image upload failed.');
    if ($f['size'] > 2 * 1024 * 1024) throw new Exception('Image must be 2 MB or smaller.');
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext) throw new Exception('Only JPG, PNG or WEBP images are allowed.');
    $dir = dirname(__DIR__) . "/assets/img/$folder/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $name = 'img_' . bin2hex(random_bytes(6)) . '.' . $ext;
    move_uploaded_file($f['tmp_name'], $dir . $name);
    return $name;
}

function status_badge($s) { return '<span class="badge-status s-' . e(slug($s)) . '">' . e($s) . '</span>'; }