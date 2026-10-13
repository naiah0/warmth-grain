<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

$recMap = ['booking' => ['service_bookings', 'booking_id'], 'repair' => ['repairs', 'repair_id'], 'order' => ['orders', 'order_id']];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['payment_id'];
    $act = $_POST['action'] ?? '';
    if ($act === 'received') {
        $pdo->prepare("UPDATE payments SET status='Paid', paid_at=NOW() WHERE payment_id=? AND status IN ('For Verification','Pending')")->execute([$id]);
        set_flash('success', 'Payment marked as received. The status can now be updated.');
    } elseif ($act === 'reject') {
        $q = $pdo->prepare("SELECT * FROM payments WHERE payment_id=? AND status IN ('For Verification','Pending')"); $q->execute([$id]);
        if ($p = $q->fetch()) {
            [$t, $k] = $recMap[$p['payment_type']];
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE payments SET status='Rejected' WHERE payment_id=?")->execute([$id]);
            $cur = $pdo->prepare("SELECT status FROM $t WHERE $k=?"); $cur->execute([$p['reference_id']]);
            if ($cur->fetchColumn() !== 'Cancelled') {
                $pdo->prepare("UPDATE $t SET status='Cancelled' WHERE $k=?")->execute([$p['reference_id']]);
                if ($p['payment_type'] === 'order') {
                    $it = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id=?'); $it->execute([$p['reference_id']]);
                    foreach ($it->fetchAll() as $row) $pdo->prepare('UPDATE products SET stock = stock + ? WHERE product_id=?')->execute([$row['quantity'], $row['product_id']]);
                }
            }
            $pdo->commit();
            set_flash('success', 'Payment rejected. The record was cancelled and any reserved stock was returned.');
        }
    }
    header('Location: payments.php'); exit;
}
$pageTitle = 'Payments';
include '../includes/header.php';

$tot = $pdo->query("SELECT
  COALESCE(SUM(CASE WHEN status='Paid' THEN amount END),0) paid_total, COALESCE(SUM(status='Paid'),0) paid_count,
  COALESCE(SUM(CASE WHEN status='Paid' AND DATE(paid_at)=CURDATE() THEN amount END),0) today,
  COALESCE(SUM(CASE WHEN status='Paid' AND paid_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01') THEN amount END),0) month,
  COALESCE(SUM(CASE WHEN status IN ('For Verification','Pending') THEN amount END),0) wait_amt, COALESCE(SUM(status IN ('For Verification','Pending')),0) wait_cnt
  FROM payments")->fetch();
$by = $pdo->query("SELECT payment_type, SUM(amount) FROM payments WHERE status='Paid' GROUP BY payment_type")->fetchAll(PDO::FETCH_KEY_PAIR);

$status = $_GET['status'] ?? ''; $ptype = $_GET['ptype'] ?? '';
$where = ['1=1']; $params = [];
if (in_array($status, ['For Verification', 'Pending', 'Paid', 'Rejected', 'Cancelled'], true)) { $where[] = 'p.status=?'; $params[] = $status; }
if (in_array($ptype, ['booking', 'repair', 'order'], true)) { $where[] = 'p.payment_type=?'; $params[] = $ptype; }
$stmt = $pdo->prepare('SELECT p.*, u.full_name FROM payments p JOIN users u ON u.user_id=p.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY FIELD(p.status,"For Verification","Pending","Paid","Rejected","Cancelled"), p.created_at DESC');
$stmt->execute($params); $rows = $stmt->fetchAll();
?>
<h1 class="page-title">Payments</h1>
<div class="pay-stats">
  <div class="stat hot"><small>Total Collected (Paid)</small><strong><?= peso($tot['paid_total']) ?></strong><span><?= (int)$tot['paid_count'] ?> paid transactions</span></div>
  <div class="stat"><small>Collected Today</small><strong><?= peso($tot['today']) ?></strong></div>
  <div class="stat"><small>Collected This Month</small><strong><?= peso($tot['month']) ?></strong></div>
  <div class="stat"><small>Awaiting Verification</small><strong><?= peso($tot['wait_amt']) ?></strong><span><?= (int)$tot['wait_cnt'] ?> payments to review</span></div>
</div>
<form class="filter-box" method="get" style="margin-bottom:1rem"><div class="filters" style="grid-template-columns:1fr 1fr auto auto">
  <select name="ptype"><option value="">All types</option><option value="booking" <?= $ptype === 'booking' ? 'selected' : '' ?>>Film processing</option><option value="repair" <?= $ptype === 'repair' ? 'selected' : '' ?>>Repairs</option><option value="order" <?= $ptype === 'order' ? 'selected' : '' ?>>Store orders</option></select>
  <select name="status"><option value="">All statuses</option><?php foreach (['For Verification', 'Pending', 'Paid', 'Rejected', 'Cancelled'] as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit">Filter</button><a class="btn btn-outline" href="payments.php">Clear</a>
</div></form>
<p class="found"><?= count($rows) ?> payment<?= count($rows) === 1 ? '' : 's' ?> shown</p>
<div class="table-wrap"><table class="zebra">
  <tr><th>#</th><th>Customer</th><th>For</th><th>Method</th><th>Proof</th><th>Amount</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($rows as $p): $open = in_array($p['status'], ['For Verification', 'Pending']); ?>
    <tr>
      <td><?= (int)$p['payment_id'] ?></td><td><?= e($p['full_name']) ?></td><td><?= e(ref_code($p['payment_type'], $p['reference_id'])) ?></td><td><?= e(pay_label($p['method'])) ?></td>
      <td><?php if ($p['proof_file']): ?><a href="proof.php?f=<?= e($p['proof_file']) ?>" target="_blank"><img class="thumb" src="proof.php?f=<?= e($p['proof_file']) ?>" alt="proof"></a><?php else: ?>-<?php endif; ?></td>
      <td><?= peso($p['amount']) ?></td><td><?= status_badge($p['status']) ?></td>
      <td><?php if ($open): ?><form method="post" class="actions"><input type="hidden" name="payment_id" value="<?= (int)$p['payment_id'] ?>">
        <button class="btn btn-ok btn-sm" name="action" value="received" data-confirm="Mark this payment as received?">Payment Received</button>
        <button class="btn btn-danger btn-sm" name="action" value="reject" data-confirm="Reject this payment? The booking, repair or order will be cancelled.">Reject</button></form><?php endif; ?></td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="8">No payments found.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>