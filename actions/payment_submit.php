<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$pend = $_SESSION['pending'] ?? null;
if (!$pend) { header('Location: ' . BASE_URL . 'index.php'); exit; }
$method = $_POST['method'] ?? '';
$errors = []; $proof = null;
if (!in_array($method, PAY_METHODS, true)) $errors[] = 'Choose a payment method.';
if ($method && $method !== 'Cash') {
    try { $proof = save_proof('proof'); if (!$proof) $errors[] = 'Attach your proof of payment before clicking Paid.'; }
    catch (Exception $ex) { $errors[] = $ex->getMessage(); }
}
if ($errors) { set_flash('error', $errors); header('Location: ' . BASE_URL . 'pages/payment.php'); exit; }

$t = calc_totals($pend['subtotal']); $d = $pend['data']; $uid = $_SESSION['user_id']; $type = $pend['type'];
try {
    $pdo->beginTransaction();
    if ($type === 'booking') {
        if (!date_ok($pdo, $d['dropoff_date'], false)) throw new Exception('That drop-off date is no longer available. Please pick another.');
        $pdo->prepare('INSERT INTO service_bookings (user_id,package_id,developing_id,scanning_id,printing_id,output_option,film_format,roll_count,exposures,dropoff_date,time_slot,notes,subtotal,service_charge,vat,total_price) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$uid, $d['package_id'], $d['developing_id'], $d['scanning_id'], $d['printing_id'], $d['output_option'], $d['film_format'], $d['roll_count'], $d['exposures'], $d['dropoff_date'], $d['time_slot'], $d['notes'], $t['subtotal'], $t['service_charge'], $t['vat'], $t['total']]);
        $id = $pdo->lastInsertId();
        $pdo->prepare('UPDATE service_bookings SET booking_code=? WHERE booking_id=?')->execute([ref_code('booking', $id), $id]);
    } elseif ($type === 'repair') {
        if (!date_ok($pdo, $d['repair_date'], true)) throw new Exception('Sorry, that repair date was just taken. Please pick another date.');
        $pdo->prepare('INSERT INTO repairs (user_id,repair_service_id,camera_brand,camera_model,serial_number,concerns,repair_date,subtotal,service_charge,vat,total_price) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$uid, $d['services'][0], $d['camera_brand'], $d['camera_model'], $d['serial_number'], $d['concerns'], $d['repair_date'], $t['subtotal'], $t['service_charge'], $t['vat'], $t['total']]);
        $id = $pdo->lastInsertId();
        $pdo->prepare('UPDATE repairs SET repair_code=? WHERE repair_id=?')->execute([ref_code('repair', $id), $id]);
        foreach ($d['services'] as $sid) $pdo->prepare('INSERT INTO repair_items (repair_id, repair_service_id) VALUES (?,?)')->execute([$id, $sid]);
    } else {
        $lines = [];
        foreach ($d['items'] as $pid => $qty) {
            $s = $pdo->prepare('SELECT * FROM products WHERE product_id=? AND is_active=1 FOR UPDATE'); $s->execute([$pid]);
            $p = $s->fetch();
            if (!$p || $p['stock'] < $qty) throw new Exception('Not enough stock for ' . ($p['name'] ?? 'an item') . '.');
            $lines[] = [$pid, $qty, $p['price']];
        }
        $pdo->prepare('INSERT INTO orders (user_id,fulfillment,contact_name,contact_phone,delivery_address,subtotal,service_charge,vat,total_amount) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$uid, $d['fulfillment'], $d['contact_name'], $d['contact_phone'], $d['delivery_address'], $t['subtotal'], $t['service_charge'], $t['vat'], $t['total']]);
        $id = $pdo->lastInsertId();
        $pdo->prepare('UPDATE orders SET order_code=? WHERE order_id=?')->execute([ref_code('order', $id), $id]);
        foreach ($lines as [$pid, $qty, $price]) {
            $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,unit_price) VALUES (?,?,?,?)')->execute([$id, $pid, $qty, $price]);
            $pdo->prepare('UPDATE products SET stock = stock - ? WHERE product_id=?')->execute([$qty, $pid]);
        }
    }
    $pdo->prepare('INSERT INTO payments (user_id,payment_type,reference_id,method,amount,status,proof_file) VALUES (?,?,?,?,?,?,?)')
        ->execute([$uid, $type, $id, $method, $t['total'], $method === 'Cash' ? 'Pending' : 'For Verification', $proof]);
    $pdo->commit();
} catch (Exception $ex) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', $ex->getMessage());
    header('Location: ' . $pend['back']); exit;
}
unset($_SESSION['pending']);
if ($type === 'order') unset($_SESSION['cart']);
header('Location: ' . BASE_URL . 'pages/thank-you.php?type=' . $type . '&id=' . $id . '&new=1');
exit;