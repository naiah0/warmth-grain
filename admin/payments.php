<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['payment_id'];
    $pdo->prepare("UPDATE payments SET status='Paid', paid_at=NOW() WHERE payment_id=? AND status='Pending'")->execute([$id]);
    $p = $pdo->prepare('SELECT * FROM payments WHERE payment_id=?'); $p->execute([$id]); $p = $p->fetch();
    if ($p && $p['payment_type'] === 'order')
        $pdo->prepare("UPDATE orders SET status='Paid' WHERE order_id=? AND status='Pending'")->execute([$p['reference_id']]);
    set_flash('success', 'Payment marked as paid.');
    header('Location: payments.php');
    exit;
}
$pageTitle = 'Payments';
include '../includes/header.php';
include __DIR__ . '/_nav.php';

$type = $_GET['type'] ?? ''; $status = $_GET['status'] ?? '';
$where = ['1=1']; $params = [];
if (in_array($type, ['booking', 'repair', 'order'], true)) { $where[] = 'p.payment_type=?'; $params[] = $type; }
if (in_array($status, ['Pending', 'Paid', 'Failed'], true)) { $where[] = 'p.status=?'; $params[] = $status; }
$stmt = $pdo->prepare('SELECT p.*, u.full_name FROM payments p JOIN users u ON u.user_id=p.user_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.created_at DESC');
$stmt->execute($params); $rows = $stmt->fetchAll();
?>
<h1>Payments</h1>
<form class="filters" method="get" style="grid-template-columns:1fr 1fr auto">
  <select name="type"><option value="">All types</option><?php foreach (['booking', 'repair', 'order'] as $t): ?><option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select>
  <select name="status"><option value="">All statuses</option><?php foreach (['Pending', 'Paid', 'Failed'] as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit">Filter</button>
</form>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Customer</th><th>For</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $p): ?>
    <tr><td><?= (int)$p['payment_id'] ?></td><td><?= e($p['full_name']) ?></td><td><?= e(ucfirst($p['payment_type'])) ?> #<?= (int)$p['reference_id'] ?></td>
      <td><?= e($p['method']) ?></td><td><?= e($p['reference_no'] ?: '-') ?></td><td><?= peso($p['amount']) ?></td><td><?= status_badge($p['status']) ?></td>
      <td><?php if ($p['status'] === 'Pending'): ?><form method="post"><input type="hidden" name="payment_id" value="<?= (int)$p['payment_id'] ?>"><button class="btn" data-confirm="Mark this payment as paid?">Mark Paid</button></form><?php endif; ?></td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="8">No payments found.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>