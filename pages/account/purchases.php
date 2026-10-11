<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Purchases'; $showCart = true; $hasBanner = true;
include '../../includes/header.php';

$uid = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'bookings';
if (!in_array($tab, ['bookings', 'repairs', 'orders'], true)) $tab = 'bookings';
$tabs = ['bookings' => 'Film Processing', 'repairs' => 'Repairs', 'orders' => 'Products'];
$type = ['bookings' => 'booking', 'repairs' => 'repair', 'orders' => 'order'][$tab];

if ($tab === 'bookings') {
    $sql = "SELECT b.booking_id id, b.booking_code code, CONCAT_WS(' + ', d.name, sc.name, pr.name) details, b.dropoff_date dt, b.total_price total, b.status, p.method, p.status pay_status
      FROM service_bookings b JOIN services d ON d.service_id=b.developing_id LEFT JOIN services sc ON sc.service_id=b.scanning_id LEFT JOIN services pr ON pr.service_id=b.printing_id
      LEFT JOIN payments p ON p.payment_type='booking' AND p.reference_id=b.booking_id WHERE b.user_id=? ORDER BY b.created_at DESC";
} elseif ($tab === 'repairs') {
    $sql = "SELECT r.repair_id id, r.repair_code code, CONCAT(" . repair_names_sql('r') . ", ' (', r.camera_brand, ' ', r.camera_model, ')') details, r.repair_date dt, r.total_price total, r.status, p.method, p.status pay_status
      FROM repairs r LEFT JOIN payments p ON p.payment_type='repair' AND p.reference_id=r.repair_id WHERE r.user_id=? ORDER BY r.created_at DESC";
} else {
    $sql = "SELECT o.order_id id, o.order_code code, (SELECT GROUP_CONCAT(CONCAT(pr.name, ' x', oi.quantity) SEPARATOR ', ') FROM order_items oi JOIN products pr ON pr.product_id=oi.product_id WHERE oi.order_id=o.order_id) details,
      DATE(o.created_at) dt, o.total_amount total, o.status, p.method, p.status pay_status
      FROM orders o LEFT JOIN payments p ON p.payment_type='order' AND p.reference_id=o.order_id WHERE o.user_id=? ORDER BY o.created_at DESC";
}
$stmt = $pdo->prepare($sql); $stmt->execute([$uid]); $rows = $stmt->fetchAll();
echo banner('My Purchases', 'Your film processing bookings, repairs and store orders, with live status updates.', ['banners/purchases']);
?>
<div class="tabs"><?php foreach ($tabs as $k => $label): ?><a href="?tab=<?= $k ?>" class="<?= $tab === $k ? 'active' : '' ?>"><?= $label ?></a><?php endforeach; ?></div>
<div class="table-wrap"><table class="tbl-center zebra">
  <tr><th>Reference</th><th>Details</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><strong><?= e($r['code']) ?></strong></td>
      <td><?= e($r['details']) ?></td>
      <td><?= e(date('M d, Y', strtotime($r['dt']))) ?></td>
      <td><?= peso($r['total']) ?></td>
      <td><?= e($r['method']) ?><br><?= $r['pay_status'] ? status_badge($r['pay_status']) : '' ?></td>
      <td><?= status_badge($r['status']) ?></td>
      <td><div class="actions" style="justify-content:center">
        <a class="btn btn-sm" href="<?= BASE_URL ?>pages/thank-you.php?type=<?= $type ?>&id=<?= (int)$r['id'] ?>">View</a>
        <?php if (can_cancel($type, $r['status'], $r['pay_status'])): ?>
          <form method="post" action="<?= BASE_URL ?>actions/cancel_service.php"><input type="hidden" name="type" value="<?= $type ?>"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-danger btn-sm" data-confirm="Cancel this? Once we review or process it, it can no longer be cancelled and there are no refunds.">Cancel</button></form>
        <?php endif; ?>
      </div></td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="7">Nothing here yet.</td></tr>'; ?>
</table></div>
<p class="small muted" style="margin-top:.8rem">You can cancel only before we review your payment or start processing. No refunds once a service or product has been reviewed or processed.</p>
<?php include '../../includes/footer.php'; ?>