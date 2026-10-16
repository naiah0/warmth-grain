<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';
$pageTitle = 'Admin Dashboard';
include '../includes/header.php';

$one = fn($sql) => $pdo->query($sql)->fetchColumn();
$rev = (float)$one("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid'");
$revMonth = (float)$one("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Paid' AND paid_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')");
$toVerify = (int)$one("SELECT COUNT(*) FROM payments WHERE status IN ('For Verification','Pending')");
$stats = [
  ['Total Revenue (Paid)', peso($rev), false], ['Revenue This Month', peso($revMonth), false], ['Payments to Verify', $toVerify, $toVerify > 0],
  ['Active Film Jobs', $one("SELECT COUNT(*) FROM service_bookings WHERE status NOT IN ('Completed','Cancelled')"), false],
  ['Active Repairs', $one("SELECT COUNT(*) FROM repairs WHERE status NOT IN ('Completed','Cancelled')"), false],
  ['Open Product Orders', $one("SELECT COUNT(*) FROM orders WHERE status NOT IN ('Completed','Cancelled')"), false],
  ['Customers', $one("SELECT COUNT(*) FROM users WHERE role='customer'"), false],
  ['Low Stock Items', $one('SELECT COUNT(*) FROM products WHERE is_active=1 AND stock <= 5'), false],
  ['New Messages', $one("SELECT COUNT(*) FROM messages WHERE status='New'"), false],
];
$byType = $pdo->query("SELECT payment_type t, COALESCE(SUM(amount),0) s FROM payments WHERE status='Paid' GROUP BY payment_type")->fetchAll(PDO::FETCH_KEY_PAIR);
$names = ['booking' => 'Film processing', 'repair' => 'Repairs', 'order' => 'Store'];
$daily = $pdo->query("SELECT DATE(paid_at) d, SUM(amount) s FROM payments WHERE status='Paid' AND paid_at >= CURDATE() - INTERVAL 6 DAY GROUP BY DATE(paid_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
$days = []; $maxDay = 1;
for ($i = 6; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $days[$d] = (float)($daily[$d] ?? 0); $maxDay = max($maxDay, $days[$d]); }
$top = $pdo->query("SELECT p.name, SUM(oi.quantity) q FROM order_items oi JOIN products p ON p.product_id=oi.product_id JOIN orders o ON o.order_id=oi.order_id WHERE o.status<>'Cancelled' GROUP BY p.product_id ORDER BY q DESC LIMIT 5")->fetchAll();
$upcoming = $pdo->query("SELECT 'Film' kind, booking_code code, dropoff_date dt FROM service_bookings WHERE status NOT IN ('Completed','Cancelled') AND dropoff_date >= CURDATE()
  UNION ALL SELECT 'Repair', repair_code, repair_date FROM repairs WHERE status NOT IN ('Completed','Cancelled') AND repair_date >= CURDATE() ORDER BY dt LIMIT 6")->fetchAll();
$pay = $pdo->query("SELECT p.*, u.full_name FROM payments p JOIN users u ON u.user_id=p.user_id WHERE p.status IN ('For Verification','Pending') ORDER BY p.created_at DESC LIMIT 6")->fetchAll();
$low = $pdo->query('SELECT product_id, name, stock FROM products WHERE is_active=1 AND stock <= 5 ORDER BY stock, name LIMIT 6')->fetchAll();
$evs = $pdo->query("SELECT e.title, e.event_date, (SELECT COALESCE(SUM(guests),0) FROM event_registrations r WHERE r.event_id=e.event_id) g FROM events e WHERE e.status='Published' AND e.event_date >= NOW() ORDER BY e.event_date LIMIT 4")->fetchAll();
?>
<h1 class="page-title">Dashboard</h1>
<div class="dash-top">
  <div class="stats3"><?php foreach ($stats as [$l, $v, $hot]): ?><div class="stat <?= $hot ? 'hot' : '' ?>"><small><?= e($l) ?></small><strong><?= e($v) ?></strong></div><?php endforeach; ?></div>
  <div class="quick">
    <h3>Quick Actions</h3>
    <a class="btn" href="scan.php">Scan QR Code</a>
    <a class="btn btn-outline" href="payments.php">Verify Payments</a>
    <a class="btn btn-outline" href="product_form.php">Add Product</a>
    <a class="btn btn-outline" href="events.php">Add Event</a>
  </div>
</div>

<div class="dash-grid">
  <div class="panel"><h3>Revenue, Last 7 Days</h3>
    <div class="bars"><?php foreach ($days as $d => $v): ?><div class="bar"><small><?= $v ? '₱' . number_format($v) : '' ?></small><i style="height:<?= round($v / $maxDay * 100) ?>%"></i><small><?= date('D', strtotime($d)) ?></small></div><?php endforeach; ?></div></div>
  <div class="panel"><h3>Revenue by Service</h3>
    <table class="mini"><?php foreach ($names as $k => $l): ?><tr><td><?= e($l) ?></td><td><?= peso($byType[$k] ?? 0) ?></td></tr><?php endforeach; ?></table></div>
<div class="panel has-foot"><h3>Payments Waiting for You</h3>
    <table class="mini"><?php foreach ($pay as $p): ?><tr><td><?= e(ref_code($p['payment_type'], $p['reference_id'])) ?> &middot; <?= e($p['full_name']) ?></td><td><?= peso($p['amount']) ?></td></tr><?php endforeach; if (!$pay) echo '<tr><td>Nothing to verify.</td><td></td></tr>'; ?></table>
<div class="panel-foot"><a class="btn btn-sm" href="payments.php">Open Payments</a></div></div>
  <div class="panel"><h3>Upcoming Drop-offs</h3>
    <table class="mini"><?php foreach ($upcoming as $u): ?><tr><td><?= e($u['kind']) ?> &middot; <?= e($u['code']) ?></td><td><?= date('M d', strtotime($u['dt'])) ?></td></tr><?php endforeach; if (!$upcoming) echo '<tr><td>No upcoming drop-offs.</td><td></td></tr>'; ?></table></div>
  <div class="panel"><h3>Top Selling Products</h3>
    <table class="mini"><?php foreach ($top as $t): ?><tr><td><?= e($t['name']) ?></td><td><?= (int)$t['q'] ?> sold</td></tr><?php endforeach; if (!$top) echo '<tr><td>No sales yet.</td><td></td></tr>'; ?></table></div>
  <div class="panel"><h3>Low Stock</h3>
    <table class="mini"><?php foreach ($low as $p): ?><tr><td><a href="product_form.php?id=<?= (int)$p['product_id'] ?>"><?= e($p['name']) ?></a></td><td><?= (int)$p['stock'] ?> left</td></tr><?php endforeach; if (!$low) echo '<tr><td>All products are well stocked.</td><td></td></tr>'; ?></table></div>
  <div class="panel"><h3>Upcoming Events and Sign-ups</h3>
    <table class="mini"><?php foreach ($evs as $ev): ?><tr><td><?= e($ev['title']) ?><br><small><?= date('M d, Y', strtotime($ev['event_date'])) ?></small></td><td><?= (int)$ev['g'] ?> guests</td></tr><?php endforeach; if (!$evs) echo '<tr><td>No upcoming events.</td><td></td></tr>'; ?></table></div>
</div>
<?php include '../includes/footer.php'; ?>