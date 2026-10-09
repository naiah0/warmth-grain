<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (isset($_POST['remove'])) {
    unset($_SESSION['cart'][(int)$_POST['remove']]);
    set_flash('success', 'Item removed from your cart.');
} else {
    foreach (($_POST['qty'] ?? []) as $id => $q) {
        $id = (int)$id; $q = (int)$q;
        $stmt = $pdo->prepare('SELECT stock FROM products WHERE product_id=? AND is_active=1');
        $stmt->execute([$id]);
        $stock = (int)$stmt->fetchColumn();
        if ($q < 1 || $stock < 1) unset($_SESSION['cart'][$id]);
        else $_SESSION['cart'][$id] = min($q, $stock);
    }
    if (!isset($_POST['go'])) set_flash('success', 'Cart updated.');
}
$goCheckout = isset($_POST['go']) && !isset($_POST['remove']);
header('Location: ' . BASE_URL . 'pages/' . ($goCheckout ? 'checkout.php' : 'cart.php'));
exit;