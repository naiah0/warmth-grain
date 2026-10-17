<?php
date_default_timezone_set('Asia/Manila');
$__local = (bool)preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $_SERVER['HTTP_HOST'] ?? '');
ini_set('display_errors', $__local ? '1' : '0');   // never show PHP errors to visitors on the live site
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    $__https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $__https, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// Reject form posts that come from another website
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $__from = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
    $__host = explode(':', $_SERVER['HTTP_HOST'] ?? '')[0];
    $__fh = $__from !== '' ? parse_url($__from, PHP_URL_HOST) : $__host;
    $__devOk = in_array($__fh, ['localhost', '127.0.0.1'], true) && in_array($__host, ['localhost', '127.0.0.1'], true);
    if ($__fh !== $__host && !$__devOk) { http_response_code(403); exit('Blocked: invalid request origin.'); }
}

// BASE_URL is detected automatically: /warmth-grain/ on XAMPP and / on a live host
$__root = rtrim(str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$__proj = rtrim(str_replace('\\', '/', (string)realpath(__DIR__ . '/..')), '/');
$__base = ($__root !== '' && strpos($__proj, $__root) === 0) ? substr($__proj, strlen($__root)) : '/warmth-grain';
define('BASE_URL', rtrim($__base, '/') . '/');

const ADMIN_PASSCODE = '1234'; // 4-digit second step for admin login
const SERVICE_CHARGE_RATE = 0.10;
const VAT_RATE = 0.12;
const PAY_METHODS = ['GoTyme', 'GCash', 'Maya', 'Cash'];
const TIME_SLOTS = ['10:00 AM - 12:00 PM', '12:00 PM - 3:00 PM', '3:00 PM - 5:00 PM'];
const REPAIR_DAILY_LIMIT = 5;
const BOOKING_FLOW = ['Booked', 'Received', 'Developing', 'Scanning', 'Printing', 'Ready for Pickup', 'Completed'];
const REPAIR_FLOW  = ['Booked', 'Received', 'Under Inspection', 'Repairing', 'Ready for Pickup', 'Completed'];
const ORDER_FLOW   = ['Processing', 'Ready', 'Completed'];
const BIZ_EMAIL = 'danaiahniccolabajao@gmail.com';
const BIZ_PHONE = '0919 005 0355';
const BIZ_HOURS = 'Mon-Fri: 10:00 AM to 5:00 PM';
const BIZ_ADDRESS = 'Unit 12, Example Building, Example Street, Quezon City, Metro Manila';

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function peso($n) { return '₱' . number_format((float)$n, 2); }
function pay_label($m) { return $m === 'Cash' ? 'Pay at the Lab' : $m; }
function slug($s) { return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-'); }
function account_no($id) { return 'WG-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT); }
function ref_code($type, $id) { return 'WG-' . ['booking' => 'B', 'repair' => 'R', 'order' => 'O'][$type] . '-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT); }
function in_admin_area() { return strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false; }

// ----- icons -----
function icon($name, $size = 20) {
    static $p = [
        'home' => '<path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/>',
        'film' => '<rect x="3" y="4" width="18" height="16"/><path d="M7 4v16M17 4v16M3 9h4M3 15h4M17 9h4M17 15h4"/>',
        'store' => '<path d="M3 9l1-5h16l1 5"/><path d="M4 9v11h16V9"/><path d="M9 20v-6h6v6"/>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.7.4-1 .9-1 1.7M12 17v.5"/>',
        'bag' => '<path d="M6 7h12l1 14H5z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
        'login' => '<path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4"/><path d="M10 17l5-5-5-5M15 12H3"/>',
        'logout' => '<path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'cart' => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
        'refresh' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>',
        'back' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14"/><path d="M3 7l9 6 9-6"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    ];
    return '<svg class="ico" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($p[$name] ?? '') . '</svg>';
}

// ----- images (.jpg / .jpeg / .png / .webp, with or without extension) -----
function find_img($rel) {
    $root = dirname(__DIR__) . '/assets/img/';
    if (!$rel) return null;
    if (preg_match('/\.\w{3,4}$/', $rel)) return file_exists($root . $rel) ? $rel : null;
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $x) if (file_exists("$root$rel.$x")) return "$rel.$x";
    return null;
}
function img(...$paths) {
    foreach ($paths as $p) if ($f = find_img($p)) return e(BASE_URL . 'assets/img/' . $f);
    return e(BASE_URL . 'assets/img/placeholder.svg');
}
function banner($title, $desc, $imgs, $action = '') {
    $f = null;
    foreach ((array)$imgs as $p) if ($f = find_img($p)) break;
    $style = $f ? "background-image:url('" . e(BASE_URL . 'assets/img/' . $f) . "')" : '';
    return '<section class="banner" style="' . $style . '"><div><h1>' . e($title) . '</h1><p>' . e($desc) . '</p></div>'
         . ($action ? '<div class="banner-action">' . $action . '</div>' : '') . '</section>';
}
// One back-button design for the whole site; only the destination changes
function back_button($href, $label = 'Back') {
    return '<a class="btn btn-back" href="' . e($href) . '">' . icon('back', 16) . ' Back</a>';
}

// ----- sessions, flash, access -----
function is_logged_in() { return isset($_SESSION['user_id']); }
function is_admin() { return ($_SESSION['role'] ?? '') === 'admin'; }
function set_flash($type, $messages) { $_SESSION['flash'] = ['type' => $type, 'messages' => (array)$messages]; }
function show_flash() {
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    echo '<div class="alert alert-' . e($f['type']) . ' flash"><ul>';
    foreach ($f['messages'] as $m) echo '<li>' . e($m) . '</li>';
    echo '</ul></div>';
}
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['next'] = $_SERVER['REQUEST_URI'];
        set_flash('error', 'Please log in or register first. An account is needed for purchases, bookings and event sign-ups.');
        header('Location: ' . BASE_URL . 'pages/login.php');
        exit;
    }
}
function require_admin() { if (!is_admin()) { header('Location: ' . BASE_URL . 'index.php'); exit; } }
function safe_next() {
    $n = $_SESSION['next'] ?? ''; unset($_SESSION['next']);
    return (strpos($n, BASE_URL) === 0 && strpos($n, '//') === false && !preg_match('/[\r\n]/', $n)) ? $n : '';
}

// ----- money -----
function calc_totals($subtotal) {
    $sub = round((float)$subtotal, 2);
    $sc  = round($sub * SERVICE_CHARGE_RATE, 2);
    $vat = round(($sub + $sc) * VAT_RATE, 2);
    return ['subtotal' => $sub, 'service_charge' => $sc, 'vat' => $vat, 'total' => round($sub + $sc + $vat, 2)];
}
function breakdown_html($t) {
    return '<div class="summary">'
      . '<div><span>Subtotal</span><span>' . peso($t['subtotal']) . '</span></div>'
      . '<div><span>Service charge (10%)</span><span>' . peso($t['service_charge']) . '</span></div>'
      . '<div><span>VAT (12%)</span><span>' . peso($t['vat']) . '</span></div>'
      . '<div class="total"><span>Total</span><span>' . peso($t['total']) . '</span></div></div>';
}

// ----- status helpers -----
function status_badge($s) { return '<span class="badge-status s-' . e(slug($s)) . '">' . e($s) . '</span>'; }
function next_status($type, $row) {
    $flow = ['booking' => BOOKING_FLOW, 'repair' => REPAIR_FLOW, 'order' => ORDER_FLOW][$type];
    $i = array_search($row['status'], $flow, true);
    if ($i === false) return null;
    for ($j = $i + 1; $j < count($flow); $j++) {
        $s = $flow[$j];
        if ($type === 'booking') {
            if ($s === 'Scanning' && empty($row['scanning_id'])) continue;
            if ($s === 'Printing' && empty($row['printing_id'])) continue;
        }
        return $s;
    }
    return null;
}
// Customers can only cancel before the shop has reviewed or processed anything (no refunds after)
function can_cancel($type, $status, $payStatus) {
    $first = ['booking' => 'Booked', 'repair' => 'Booked', 'order' => 'Processing'][$type] ?? '';
    return $status === $first && in_array($payStatus, ['For Verification', 'Pending'], true);
}
function get_record(PDO $pdo, $type, $id, $uid = null) {
    $map = ['booking' => ['service_bookings', 'booking_id', 'booking_code'], 'repair' => ['repairs', 'repair_id', 'repair_code'], 'order' => ['orders', 'order_id', 'order_code']];
    if (!isset($map[$type])) return null;
    [$t, $k, $c] = $map[$type];
    $sql = "SELECT r.*, r.$c AS code, p.method, p.status AS pay_status, p.proof_file FROM $t r LEFT JOIN payments p ON p.payment_type=? AND p.reference_id=r.$k WHERE r.$k=?";
    $params = [$type, $id];
    if ($uid) { $sql .= ' AND r.user_id=?'; $params[] = $uid; }
    $s = $pdo->prepare($sql); $s->execute($params);
    return $s->fetch() ?: null;
}
function repair_names_sql($alias = 'r') {
    return "(SELECT GROUP_CONCAT(rs.name ORDER BY rs.repair_service_id SEPARATOR ' + ') FROM repair_items ri JOIN repair_services rs ON rs.repair_service_id=ri.repair_service_id WHERE ri.repair_id=$alias.repair_id)";
}

// ----- calendars: Monday to Friday only, minus closed dates (and full repair days) -----
function unavailable_dates(PDO $pdo, $forRepair = false, $days = 130) {
    $out = [];
    for ($i = 1; $i <= $days; $i++) {
        $d = date('Y-m-d', strtotime("+$i day"));
        if (in_array(date('w', strtotime($d)), ['0', '6'], true)) $out[] = $d;
    }
    $blocked = $pdo->query('SELECT blocked_date FROM repair_blocked_dates')->fetchAll(PDO::FETCH_COLUMN);
    $full = [];
    if ($forRepair) $full = $pdo->query("SELECT repair_date FROM repairs WHERE status<>'Cancelled' AND repair_date>=CURDATE() GROUP BY repair_date HAVING COUNT(*)>=" . REPAIR_DAILY_LIMIT)->fetchAll(PDO::FETCH_COLUMN);
    return array_values(array_unique(array_merge($out, $blocked, $full)));
}
function date_ok(PDO $pdo, $date, $forRepair = false) {
    return $date > date('Y-m-d') && $date <= date('Y-m-d', strtotime('+130 day')) && !in_array($date, unavailable_dates($pdo, $forRepair), true);
}

// ----- uploads -----
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
function save_proof($field) {
    if (empty($_FILES[$field]['name'])) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new Exception('Proof of payment upload failed.');
    if ($f['size'] > 3 * 1024 * 1024) throw new Exception('Proof of payment must be 3 MB or smaller.');
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime'] ?? ''] ?? null;
    if (!$ext) throw new Exception('Proof of payment must be a JPG, PNG or WEBP screenshot.');
    $dir = dirname(__DIR__) . '/uploads/proofs/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $name = 'proof_' . bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($f['tmp_name'], $dir . $name);
    return $name;
}

// ----- shared product card (Store + "You may also like") -----
function product_card($p) {
    $out = $p['stock'] < 1;
    $url = e(BASE_URL . 'pages/product.php?id=' . (int)$p['product_id']);
    return '<div class="pcard product' . ($out ? ' out' : '') . '" data-href="' . $url . '">'
      . '<div class="card-photo"><img src="' . img('products/' . $p['image'], 'categories/' . slug($p['cat'])) . '" alt="' . e($p['name']) . '" loading="lazy">'
      . ($out ? '<span class="out-label">Out of Stock</span>' : '') . '</div>'
      . '<h3><a href="' . $url . '">' . e($p['name']) . '</a></h3>'
      . '<div class="pcard-row"><span class="price">' . peso($p['price']) . '</span>'
      . '<form method="post" action="' . e(BASE_URL) . 'actions/cart_add.php" data-ajax-cart><input type="hidden" name="product_id" value="' . (int)$p['product_id'] . '"><input type="hidden" name="qty" value="1"><input type="hidden" name="back" value="' . e($_SERVER['REQUEST_URI']) . '">'
      . '<button class="btn btn-icon" type="submit" title="Add to cart" aria-label="Add to cart"' . ($out ? ' disabled' : '') . '>' . icon('cart', 18) . '</button></form></div></div>';
}