<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

foreach (($_POST['qty'] ?? []) as $id => $q) {
    $id = (int)$id; $q = (int)$q;
    $s = $pdo->prepare('SELECT stock FROM products WHERE product_id=? AND is_active=1'); $s->execute([$id]);
    $stock = (int)$s->fetchColumn();
    if ($q < 1 || $stock < 1) unset($_SESSION['cart'][$id]); else $_SESSION['cart'][$id] = min($q, $stock);
}
if (isset($_POST['remove'])) {
    unset($_SESSION['cart'][(int)$_POST['remove']]);
    set_flash('success', 'Item removed from your cart.');
    header('Location: ' . BASE_URL . 'pages/cart.php'); exit;
}
if (isset($_POST['go'])) {
    require_login();
    $cart = $_SESSION['cart'] ?? [];
    $name = trim($_POST['contact_name'] ?? ''); $phone = trim($_POST['contact_phone'] ?? '');
    $ful = $_POST['fulfillment'] ?? ''; $addr = trim($_POST['delivery_address'] ?? '');
    $errors = [];
    if (!$cart) $errors[] = 'Your cart is empty.';
    if ($name === '') $errors[] = 'Name is required.';
    if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = 'Phone must look like 09XXXXXXXXX.';
    if (!in_array($ful, ['Pickup', 'Delivery'], true)) $errors[] = 'Choose pickup or delivery.';
    if ($ful === 'Delivery' && $addr === '') $errors[] = 'Delivery address is required.';
    $lines = []; $sub = 0; $items = [];
    foreach ($cart as $pid => $qty) {
        $s = $pdo->prepare('SELECT * FROM products WHERE product_id=? AND is_active=1'); $s->execute([(int)$pid]);
        $p = $s->fetch();
        if (!$p || $p['stock'] < $qty) { $errors[] = 'Not enough stock for ' . ($p['name'] ?? 'an item') . '.'; continue; }
        $sub += $p['price'] * $qty; $items[(int)$pid] = (int)$qty;
        $lines[] = ['label' => $p['name'] . ' × ' . $qty, 'amount' => $p['price'] * $qty];
    }
    if ($errors) { set_flash('error', $errors); header('Location: ' . BASE_URL . 'pages/cart.php'); exit; }
    $_SESSION['pending'] = ['type' => 'order', 'back' => BASE_URL . 'pages/cart.php', 'subtotal' => $sub, 'lines' => $lines,
        'data' => ['items' => $items, 'fulfillment' => $ful, 'contact_name' => $name, 'contact_phone' => $phone, 'delivery_address' => $ful === 'Delivery' ? $addr : null]];
    header('Location: ' . BASE_URL . 'pages/payment.php'); exit;
}
header('Location: ' . BASE_URL . 'pages/cart.php');
exit;