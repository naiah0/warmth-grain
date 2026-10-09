<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

$id  = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);

$stmt = $pdo->prepare('SELECT stock FROM products WHERE product_id=? AND is_active=1');
$stmt->execute([$id]);
$stock = $stmt->fetchColumn();

if ($stock === false || $qty < 1) {
    set_flash('error', 'Invalid product or quantity.');
} else {
    $current = $_SESSION['cart'][$id] ?? 0;
    $_SESSION['cart'][$id] = min($current + $qty, (int)$stock);
    set_flash('success', 'Added to cart.');
}
header('Location: ' . BASE_URL . 'pages/cart.php');
exit;