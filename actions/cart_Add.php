<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$id = (int)($_POST['product_id'] ?? 0); $qty = (int)($_POST['qty'] ?? 1);
$back = $_POST['back'] ?? '';
if (strpos($back, BASE_URL) !== 0 || strpos($back, '//') !== false || preg_match('/[\r\n]/', $back)) $back = BASE_URL . 'pages/shop.php';

$stmt = $pdo->prepare('SELECT name, stock FROM products WHERE product_id=? AND is_active=1');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p || $p['stock'] < 1 || $qty < 1) set_flash('error', 'That product is not available.');
else {
    $_SESSION['cart'][$id] = min(($_SESSION['cart'][$id] ?? 0) + $qty, (int)$p['stock']);
    set_flash('success', $p['name'] . ' was added to your cart.');
}
header('Location: ' . $back);
exit;