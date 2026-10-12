<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

$view = $_GET['type'] ?? 'booking';
$cfgs = [
  'booking' => ['table' => 'service_bookings', 'key' => 'booking_id', 'code' => 'booking_code', 'flow' => BOOKING_FLOW, 'title' => 'Film Processing',
    'sql' => "SELECT r.booking_id id, r.booking_code code, r.status, r.total_price total, r.dropoff_date dt, CONCAT_WS(' + ', d.name, sc.name, pr.name) details, u.full_name, p.method, p.status pay_status
      FROM service_bookings r JOIN users u ON u.user_id=r.user_id JOIN services d ON d.service_id=r.developing_id LEFT JOIN services sc ON sc.service_id=r.scanning_id LEFT JOIN services pr ON pr.service_id=r.printing_id
      LEFT JOIN payments p ON p.payment_type='booking' AND p.reference_id=r.booking_id WHERE 1=1"],
  'repair' => ['table' => 'repairs', 'key' => 'repair_id', 'code' => 'repair_code', 'flow' => REPAIR_FLOW, 'title' => 'Repairs',
    'sql' => "SELECT r.repair_id id, r.repair_code code, r.status, r.total_price total, r.repair_date dt, CONCAT(" . repair_names_sql('r') . ", ' (', r.camera_brand, ' ', r.camera_model, ')') details, u.full_name, p.method, p.status pay_status
      FROM repairs r JOIN users u ON u.user_id=r.user_id LEFT JOIN payments p ON p.payment_type='repair' AND p.reference_id=r.repair_id WHERE 1=1"],
  'order' => ['table' => 'orders', 'key' => 'order_id', 'code' => 'order_code', 'flow' => ORDER_FLOW, 'title' => 'Product Orders',
    'sql' => "SELECT r.order_id id, r.order_code code, r.status, r.total_amount total, DATE(r.created_at) dt, u.full_name, p.method, p.status pay_status,
      (SELECT GROUP_CONCAT(CONCAT(pr.name, ' x', oi.quantity) SEPARATOR ', ') FROM order_items oi JOIN products pr ON pr.product_id=oi.product_id WHERE oi.order_id=r.order_id) details
      FROM orders r JOIN users u ON u.user_id=r.user_id LEFT JOIN payments p ON p.payment_type='order' AND p.reference_id=r.order_id WHERE 1=1"],
];
if (!isset($cfgs[$view])) $view = 'booking';
$cfg = $cfgs[$view];
$statusList = array_merge($cfg['flow'], ['Cancelled']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $_POST['status'] ?? '';
    if (in_array($st, $statusList, true)) {
        $pdo->prepare('UPDATE ' . $cfg['table'] . ' SET status=? WHERE ' . $cfg['key'] . '=?')->execute([$st, (int)$_POST['id']]);
        set_flash('success', 'Status updated.');
    }
    header('Location: records.php?type=' . $view . '&status=' . urlencode($_GET['status'] ?? '')); exit;
}
$pageTitle = $cfg['title'];
include '../includes/header.php';

$fStatus = $_GET['status'] ?? ''; $fq = trim($_GET['q'] ?? '');
$sql = $cfg['sql']; $params = [];
if (in_array($fStatus, $statusList, true)) { $sql .= ' AND r.status=?'; $params[] = $fStatus; }
if ($fq !== '') { $sql .= ' AND (u.full_name LIKE ? OR r.' . $cfg['code'] . ' LIKE ?)'; array_push($params, "%$fq%", "%$fq%"); }
$stmt = $pdo->prepare($sql . ' ORDER BY r.created_at DESC'); $stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<h1 class="page-title"><?= e($cfg['title']) ?></h1>
<form class="filter-box" method="get" style="margin-bottom:1rem">
  <input type="hidden" name="type" value="<?= e($view) ?>">
  <div class="filters" style="grid-template-columns:2fr 1fr auto auto">
    <input type="search" name="q" value="<?= e($fq) ?>" placeholder="Search by customer or reference...">
    <select name="status"><option value="">All statuses</option><?php foreach ($statusList as $s): ?><option <?= $fStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
    <button class="btn" type="submit">Filter</button><a class="btn btn-outline" href="records.php?type=<?= e($view) ?>">Clear</a>
  </div>
</form>
<div class="table-wrap"><table class="zebra">
  <tr><th>Reference</th><th>Customer</th><th>Details</th><th>Date</th><th>Total</th><th>Payment</th><th>Update Status</th></tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><strong><?= e($r['code']) ?></strong></td><td><?= e($r['full_name']) ?></td><td><?= e($r['details']) ?></td>
      <td><?= e(date('M d, Y', strtotime($r['dt']))) ?></td><td><?= peso($r['total']) ?></td>
      <td><?= e(pay_label($r['method'])) ?><br><?= $r['pay_status'] ? status_badge($r['pay_status']) : '' ?></td>
      <td><form method="post" class="status-form"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <select name="status" class="tbl-select"><?php foreach ($statusList as $s): ?><option <?= $s === $r['status'] ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
        <button class="btn btn-sm">Save</button></form></td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="7">Nothing here yet.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>