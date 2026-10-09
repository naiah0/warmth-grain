<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';
$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
include __DIR__ . '/_nav.php';

$one = fn($sql) => $pdo->query($sql)->fetchColumn();
$stats = [
  'Pending Bookings' => $one("SELECT COUNT(*) FROM service_bookings WHERE status='Pending'"),
  'Active Repairs'   => $one("SELECT COUNT(*) FROM repairs WHERE status <> 'Completed'"),
  'Pending Orders'   => $one("SELECT COUNT(*) FROM orders WHERE status='Pending'"),
  'Paid Revenue'     => peso($one("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid'")),
  'Customers'        => $one("SELECT COUNT(*) FROM users WHERE role='customer'"),
  'Low Stock Items'  => $one('SELECT COUNT(*) FROM products WHERE is_active=1 AND stock <= 5'),
  'Messages'         => $one('SELECT COUNT(*) FROM messages'),
  'Upcoming Events'  => $one("SELECT COUNT(*) FROM events WHERE status='Published' AND event_date >= NOW()"),
];
$bookings = $pdo->query('SELECT b.booking_id, u.full_name, s.name, b.total_price, b.status FROM service_bookings b JOIN users u ON u.user_id=b.user_id JOIN services s ON s.service_id=b.service_id ORDER BY b.created_at DESC LIMIT 5')->fetchAll();
$orders = $pdo->query('SELECT o.order_id, u.full_name, o.total_amount, o.status FROM orders o JOIN users u ON u.user_id=o.user_id ORDER BY o.created_at DESC LIMIT 5')->fetchAll();
$repairs = $pdo->query('SELECT r.repair_number, u.full_name, r.camera_brand, r.camera_model, r.status FROM repairs r JOIN users u ON u.user_id=r.user_id ORDER BY r.created_at DESC LIMIT 5')->fetchAll();
$low = $pdo->query('SELECT product_id, name, stock FROM products WHERE is_active=1 AND stock <= 5 ORDER BY stock, name LIMIT 8')->fetchAll();
?>
<link href="style.css" rel="stylesheet">
<h1>Admin Dashboard</h1>
<div class="grid">
  <?php foreach ($stats as $label => $v): ?><div class="card"><h3><?= e($label) ?></h3><p class="price" style="font-size:1.5rem"><?= e($v) ?></p></div><?php endforeach; ?>
</div>

<h2>Recent Bookings</h2>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Customer</th><th>Service</th><th>Total</th><th>Status</th></tr>
  <?php foreach ($bookings as $b): ?><tr><td><?= (int)$b['booking_id'] ?></td><td><?= e($b['full_name']) ?></td><td><?= e($b['name']) ?></td><td><?= peso($b['total_price']) ?></td><td><?= status_badge($b['status']) ?></td></tr><?php endforeach; if (!$bookings) echo '<tr><td colspan="5">No bookings yet.</td></tr>'; ?>
</table></div>

<h2>Recent Orders</h2>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr>
  <?php foreach ($orders as $o): ?><tr><td><?= (int)$o['order_id'] ?></td><td><?= e($o['full_name']) ?></td><td><?= peso($o['total_amount']) ?></td><td><?= status_badge($o['status']) ?></td></tr><?php endforeach; if (!$orders) echo '<tr><td colspan="4">No orders yet.</td></tr>'; ?>
</table></div>

<h2>Recent Repairs</h2>
<div class="table-wrap"><table>
  <tr><th>Repair No.</th><th>Customer</th><th>Camera</th><th>Status</th></tr>
  <?php foreach ($repairs as $r): ?><tr><td><?= e($r['repair_number']) ?></td><td><?= e($r['full_name']) ?></td><td><?= e($r['camera_brand'] . ' ' . $r['camera_model']) ?></td><td><?= status_badge($r['status']) ?></td></tr><?php endforeach; if (!$repairs) echo '<tr><td colspan="4">No repairs yet.</td></tr>'; ?>
</table></div>

<h2>Low Stock</h2>
<div class="table-wrap"><table>
  <tr><th>Product</th><th>Stock</th><th></th></tr>
  <?php foreach ($low as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= (int)$p['stock'] ?></td><td><a class="btn" href="product_form.php?id=<?= (int)$p['product_id'] ?>">Restock</a></td></tr><?php endforeach; if (!$low) echo '<tr><td colspan="3">All products are well stocked.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>