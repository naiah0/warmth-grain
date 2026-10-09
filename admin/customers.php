<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE users SET status = IF(status='active','disabled','active') WHERE user_id=? AND role='customer'")->execute([(int)$_POST['user_id']]);
    set_flash('success', 'Account status changed.');
    header('Location: customers.php');
    exit;
}
$pageTitle = 'Customers';
include '../includes/header.php';
include __DIR__ . '/_nav.php';
$rows = $pdo->query("SELECT u.*,
  (SELECT COUNT(*) FROM service_bookings b WHERE b.user_id=u.user_id) AS bookings,
  (SELECT COUNT(*) FROM repairs r WHERE r.user_id=u.user_id) AS repairs,
  (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.user_id) AS orders
  FROM users u WHERE u.role='customer' ORDER BY u.created_at DESC")->fetchAll();
?>
<h1>Customers</h1>
<div class="table-wrap"><table>
  <tr><th>Name</th><th>Email</th><th>Phone</th><th>Bookings</th><th>Repairs</th><th>Orders</th><th>Joined</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $u): ?>
    <tr><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td>
      <td><?= (int)$u['bookings'] ?></td><td><?= (int)$u['repairs'] ?></td><td><?= (int)$u['orders'] ?></td>
      <td><?= e(substr($u['created_at'], 0, 10)) ?></td><td><?= status_badge($u['status']) ?></td>
      <td><form method="post"><input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
        <button class="btn <?= $u['status'] === 'active' ? 'btn-danger' : '' ?>" data-confirm="Change this account's status?"><?= $u['status'] === 'active' ? 'Disable' : 'Enable' ?></button></form></td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="9">No customers yet.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>