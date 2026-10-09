<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Purchases';
include '../../includes/header.php';

$uid = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'bookings';
if (!in_array($tab, ['bookings', 'repairs', 'orders'], true)) $tab = 'bookings';
$tabs = ['bookings' => 'Service Bookings', 'repairs' => 'Repairs', 'orders' => 'Purchased Products'];
?>
<h1>My Purchases</h1>
<div class="tabs">
  <?php foreach ($tabs as $k => $label): ?><a href="?tab=<?= $k ?>" class="<?= $tab === $k ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?>
</div>

<?php if ($tab === 'bookings'):
  $stmt = $pdo->prepare("SELECT b.*, s.name AS service, p.method, p.status AS pay_status FROM service_bookings b
    JOIN services s ON s.service_id=b.service_id
    LEFT JOIN payments p ON p.payment_type='booking' AND p.reference_id=b.booking_id
    WHERE b.user_id=? ORDER BY b.created_at DESC");
  $stmt->execute([$uid]); $rows = $stmt->fetchAll(); ?>
  <div class="table-wrap"><table>
    <tr><th>#</th><th>Service</th><th>Film</th><th>Drop-off</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
    <?php foreach ($rows as $b): ?>
      <tr>
        <td><?= (int)$b['booking_id'] ?></td>
        <td><?= e($b['service']) ?><?= $b['rush'] ? ' <small>(Rush)</small>' : '' ?></td>
        <td><?= e($b['film_type']) ?><br><small><?= e($b['film_format']) ?> &middot; <?= (int)$b['roll_count'] ?> roll(s) &middot; <?= (int)$b['exposures'] ?> exp</small></td>
        <td><?= e($b['dropoff_date']) ?><br><small><?= e($b['time_slot']) ?></small></td>
        <td><?= peso($b['total_price']) ?></td>
        <td><?= e($b['method']) ?><br><?= $b['pay_status'] ? status_badge($b['pay_status']) : '' ?></td>
        <td><?= status_badge($b['status']) ?></td>
        <td><?php if (in_array($b['status'], ['Pending', 'Confirmed'])): ?>
          <form method="post" action="<?= BASE_URL ?>actions/booking_cancel.php">
            <input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
            <button class="btn btn-danger" data-confirm="Cancel this booking?">Cancel</button>
          </form><?php endif; ?></td>
      </tr>
    <?php endforeach; if (!$rows) echo '<tr><td colspan="8">No bookings yet. <a href="' . BASE_URL . 'pages/services.php">Book a service</a></td></tr>'; ?>
  </table></div>

<?php elseif ($tab === 'repairs'):
  $stmt = $pdo->prepare('SELECT r.*, rs.name AS service FROM repairs r LEFT JOIN repair_services rs ON rs.repair_service_id=r.repair_service_id WHERE r.user_id=? ORDER BY r.created_at DESC');
  $stmt->execute([$uid]); $rows = $stmt->fetchAll(); ?>
  <div class="table-wrap"><table>
    <tr><th>Repair No.</th><th>Service</th><th>Camera</th><th>Issue</th><th>Diagnosis</th><th>Cost</th><th>Status</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><a href="<?= BASE_URL ?>pages/track-repair.php?repair_number=<?= urlencode($r['repair_number']) ?>"><?= e($r['repair_number']) ?></a></td>
        <td><?= e($r['service'] ?? '-') ?></td>
        <td><?= e($r['camera_brand'] . ' ' . $r['camera_model']) ?></td>
        <td><?= e($r['issue']) ?></td>
        <td><?= e($r['diagnosis'] ?: 'Pending inspection') ?></td>
        <td><?= $r['repair_cost'] !== null ? peso($r['repair_cost']) : '-' ?></td>
        <td><?= status_badge($r['status']) ?></td>
      </tr>
    <?php endforeach; if (!$rows) echo '<tr><td colspan="7">No repair requests yet. <a href="' . BASE_URL . 'pages/repair.php">Book a repair</a></td></tr>'; ?>
  </table></div>

<?php else:
  $stmt = $pdo->prepare("SELECT o.*, p.method, p.status AS pay_status FROM orders o
    LEFT JOIN payments p ON p.payment_type='order' AND p.reference_id=o.order_id
    WHERE o.user_id=? ORDER BY o.created_at DESC");
  $stmt->execute([$uid]); $orders = $stmt->fetchAll();
  $itemStmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?');
  foreach ($orders as $o): $itemStmt->execute([$o['order_id']]); ?>
    <div class="card" style="margin-bottom:1rem">
      <h3>Order #<?= (int)$o['order_id'] ?> <?= status_badge($o['status']) ?></h3>
      <p><?= e($o['created_at']) ?> &middot; <?= e($o['fulfillment']) ?> &middot; <?= e($o['method']) ?> <?= $o['pay_status'] ? status_badge($o['pay_status']) : '' ?> &middot; <strong><?= peso($o['total_amount']) ?></strong></p>
      <ul><?php foreach ($itemStmt->fetchAll() as $it): ?><li><?= e($it['name']) ?> &times; <?= (int)$it['quantity'] ?> (<?= peso($it['unit_price']) ?>)</li><?php endforeach; ?></ul>
    </div>
  <?php endforeach;
  if (!$orders) echo '<p>No purchases yet. <a href="' . BASE_URL . 'pages/shop.php">Visit the store</a></p>';
endif; ?>
<?php include '../../includes/footer.php'; ?>