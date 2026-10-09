<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Orders';
include '../../includes/header.php';

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
$itemStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?');
?>
<h1>My Orders</h1>
<?php foreach ($orders as $o): $itemStmt->execute([$o['order_id']]); ?>
  <div class="card" style="margin-bottom:1rem">
    <h3>Order #<?= (int)$o['order_id'] ?> &middot; <?= e($o['status']) ?></h3>
    <p><?= e($o['created_at']) ?> &middot; <?= e($o['fulfillment']) ?> &middot; <strong><?= peso($o['total_amount']) ?></strong></p>
    <ul>
      <?php foreach ($itemStmt->fetchAll() as $it): ?>
        <li><?= e($it['name']) ?> &times; <?= (int)$it['quantity'] ?> (<?= peso($it['unit_price']) ?>)</li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endforeach; if (!$orders) echo '<p>No orders yet.</p>'; ?>
<?php include '../../includes/footer.php'; ?>