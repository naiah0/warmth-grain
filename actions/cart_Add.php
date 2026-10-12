<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
$id = (int)($_POST['product_id'] ?? 0); $qty = (int)($_POST['qty'] ?? 1);
$back = $_POST['back'] ?? '';
if (strpos($back, BASE_URL) !== 0 || strpos($back, '//') !== false || preg_match('/[\r\n]/', $back)) $back = BASE_URL . 'pages/shop.php';

$stmt = $pdo->prepare('SELECT name, stock FROM products WHERE product_id=? AND is_active=1');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p || $p['stock'] < 1 || $qty < 1) { $ok = false; $msg = 'That product is not available.'; }
else {
    $_SESSION['cart'][$id] = min(($_SESSION['cart'][$id] ?? 0) + $qty, (int)$p['stock']);
    $ok = true; $msg = $p['name'] . ' was added to your cart.';
}
if ($ajax) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => $ok, 'message' => $msg, 'count' => array_sum($_SESSION['cart'] ?? [])]);
    exit;
}
set_flash($ok ? 'success' : 'error', $msg);
header('Location: ' . $back);
exit;