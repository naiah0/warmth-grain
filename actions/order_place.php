<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
require_once '../includes/payment.php';

$cart   = $_SESSION['cart'] ?? [];
$name   = trim($_POST['contact_name'] ?? '');
$phone  = trim($_POST['contact_phone'] ?? '');
$ful    = $_POST['fulfillment'] ?? '';
$addr   = trim($_POST['delivery_address'] ?? '');
$method = $_POST['method'] ?? '';
$refNo  = trim($_POST['reference_no'] ?? '');
$errors = payment_errors($method, $refNo);

if (!$cart) $errors[] = 'Your cart is empty.';
if ($name === '') $errors[] = 'Name is required.';
if (!preg_match('/^09\d{9}$/', $phone)) $errors[] = 'Phone must look like 09XXXXXXXXX.';
if (!in_array($ful, ['Pickup', 'Delivery'], true)) $errors[] = 'Choose pickup or delivery.';
if ($ful === 'Delivery' && $addr === '') $errors[] = 'Delivery address is required.';

if (!$errors) {
    try {
        $pdo->beginTransaction();
        $total = 0; $lines = [];
        foreach ($cart as $pid => $qty) {
            $s = $pdo->prepare('SELECT * FROM products WHERE product_id=? AND is_active=1 FOR UPDATE');
            $s->execute([(int)$pid]);
            $p = $s->fetch();
            if (!$p || $p['stock'] < $qty) throw new Exception('Not enough stock for ' . ($p['name'] ?? 'an item') . '.');
            $total += $p['price'] * $qty;
            $lines[] = [$p['product_id'], $qty, $p['price']];
        }
        $pdo->prepare('INSERT INTO orders (user_id,total_amount,fulfillment,contact_name,contact_phone,delivery_address,status) VALUES (?,?,?,?,?,?,?)')
            ->execute([$_SESSION['user_id'], $total, $ful, $name, $phone, $ful === 'Delivery' ? $addr : null, $method === 'Cash' ? 'Pending' : 'Paid']);
        $orderId = $pdo->lastInsertId();
        foreach ($lines as [$pid, $qty, $price]) {
            $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)')->execute([$orderId, $pid, $qty, $price]);
            $pdo->prepare('UPDATE products SET stock = stock - ? WHERE product_id=?')->execute([$qty, $pid]);
        }
        record_payment($pdo, $_SESSION['user_id'], 'order', $orderId, $method, $refNo, $total);
        $pdo->commit();
        unset($_SESSION['cart']);
        set_flash('success', "Order #$orderId placed. Total: " . peso($total));
        header('Location: ' . BASE_URL . 'pages/account/purchases.php?tab=orders');
        exit;
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors[] = $ex->getMessage();
    }
}

function payment_errors($method, $refNo) {
    $errors = [];
    // validation logic for payments...
    return $errors;
}

function record_payment($pdo, $userId, $type, $orderId, $method, $refNo, $total) {
    // payment recording logic...
}

set_flash('error', $errors);
header('Location: ' . BASE_URL . 'pages/checkout.php');
exit;