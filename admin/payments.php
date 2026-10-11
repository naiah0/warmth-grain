<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['payment_id'];
    if (($_POST['action'] ?? '') === 'received') {
        $pdo->prepare("UPDATE payments SET status='Paid', paid_at=NOW() WHERE payment_id=? AND status IN ('For Verification','Pending')")->execute([$id]);
        set_flash('success', 'Payment marked as received. The customer now sees it as Paid.');
    } elseif (($_POST['action'] ?? '') === 'reject') {
        $pdo->prepare("UPDATE payments SET status='Rejected' WHERE payment_id=? AND status IN ('For Verification','Pending')")->execute([$id]);
        set_flash('success', 'Payment rejected.');
    }
    header('Location: payments.php'); exit;
}
$pageTitle = 'Payments';
include '../includes/header.php';

$status = $_GET['status'] ?? ''; $ptype = $_GET['ptype'] ?? '';
$where = ['1=1']; $params = [];
if (in_array($status, ['For Verification', 'Pending', 'Paid', 'Rejected', 'Cancelled'], true)) { $where[] = 'p.status=?'; $params[] = $status; }
if (in_array($ptype, ['booking', 'repair', 'order'], true)) { $where[] = 'p.payment_type=?'; $params[] = $ptype; }
$stmt = $pdo->prepare('SELECT p.*, u.full_name FROM payments p JOIN users u ON u.user_id=p.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY FIELD(p.status,"For Verification","Pending","Paid","Rejected","Cancelled"), p.created_at DESC');
$stmt->execute($params); $rows = $stmt->fetchAll();
?>
<h1 class="page-title">Payments</h1>
<form class="filter-box" method="get" style="margin-bottom:1rem"><div class="filters" style="grid-template-columns:1fr 1fr auto auto">
  <select name="ptype"><option value="">All types</option><option value="booking" <?= $ptype === 'booking' ? 'selected' : '' ?>>Film processing</option><option value="repair" <?= $ptype === 'repair' ? 'selected' : '' ?>>Repairs</option><option value="order" <?= $ptype === 'order' ? 'selected' : '' ?>>Store orders</option></select>
  <select name="status"><option value="">All statuses</option><?php foreach (['For Verification', 'Pending', 'Paid', 'Rejected', 'Cancelled'] as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit">Filter</button><a class="btn btn-outline" href="payments.php">Clear</a>
</div></form>
<div class="table-wrap"><table class="zebra">
  <tr><th>#</th><th>Customer</th><th>For</th><th>Method</th><th>Proof</th><th>Amount</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($rows as $p): $open = in_array($p['status'], ['For Verification', 'Pending']); ?>
    <tr>
      <td><?= (int)$p['payment_id'] ?></td><td><?= e($p['full_name']) ?></td><td><?= e(ref_code($p['payment_type'], $p['reference_id'])) ?></td><td><?= e($p['method']) ?></td>
      <td><?php if ($p['proof_file']): ?><a href="<?= BASE_URL ?>uploads/proofs/<?= e($p['proof_file']) ?>" target="_blank"><img class="thumb" src="<?= BASE_URL ?>uploads/proofs/<?= e($p['proof_file']) ?>" alt="proof"></a><?php else: ?>-<?php endif; ?></td>
      <td><?= peso($p['amount']) ?></td><td><?= status_badge($p['status']) ?></td>
      <td><?php if ($open): ?><form method="post" class="actions"><input type="hidden" name="payment_id" value="<?= (int)$p['payment_id'] ?>">
        <button class="btn btn-sm" name="action" value="received" data-confirm="Mark this payment as received?">Payment received</button>
        <button class="btn btn-danger btn-sm" name="action" value="reject" data-confirm="Reject this payment?">Reject</button></form><?php endif; ?></td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="8" class="center">No payments found.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>