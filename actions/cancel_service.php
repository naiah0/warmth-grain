<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$type = $_POST['type'] ?? ''; $id = (int)($_POST['id'] ?? 0);
$map = ['booking' => ['service_bookings', 'booking_id', 'bookings'], 'repair' => ['repairs', 'repair_id', 'repairs'], 'order' => ['orders', 'order_id', 'orders']];
if (!isset($map[$type])) { header('Location: ' . BASE_URL . 'pages/account/purchases.php'); exit; }
[$table, $key, $tab] = $map[$type];

$r = get_record($pdo, $type, $id, $_SESSION['user_id']);
if (!$r || !can_cancel($type, $r['status'], $r['pay_status'])) {
    set_flash('error', 'This can no longer be cancelled. It has already been reviewed or processed, and no refunds apply.');
} else {
    $pdo->beginTransaction();
    $pdo->prepare("UPDATE $table SET status='Cancelled' WHERE $key=?")->execute([$id]);
    $pdo->prepare("UPDATE payments SET status='Cancelled' WHERE payment_type=? AND reference_id=?")->execute([$type, $id]);
    if ($type === 'order') {
        $it = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id=?'); $it->execute([$id]);
        foreach ($it->fetchAll() as $row) $pdo->prepare('UPDATE products SET stock = stock + ? WHERE product_id=?')->execute([$row['quantity'], $row['product_id']]);
    }
    $pdo->commit();
    set_flash('success', 'Cancelled.');
}
header('Location: ' . BASE_URL . 'pages/account/purchases.php?tab=' . $tab);
exit;