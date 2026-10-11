<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE users SET status = IF(status='active','disabled','active') WHERE user_id=? AND role='customer'")->execute([(int)$_POST['user_id']]);
    set_flash('success', 'Account status changed.');
    header('Location: customers.php'); exit;
}
$pageTitle = 'Customers';
include '../includes/header.php';
$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("SELECT u.*,
  (SELECT COUNT(*) FROM service_bookings b WHERE b.user_id=u.user_id) AS bookings,
  (SELECT COUNT(*) FROM repairs r WHERE r.user_id=u.user_id) AS repairs,
  (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.user_id) AS orders
  FROM users u WHERE u.role='customer' AND (u.full_name LIKE ? OR u.email LIKE ?) ORDER BY u.created_at DESC");
$stmt->execute(["%$q%", "%$q%"]); $rows = $stmt->fetchAll();
?>
<h1 class="page-title">Customers</h1>
<form class="filter-box" method="get" style="margin-bottom:1rem"><div class="filters" style="grid-template-columns:1fr auto auto">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name or email..."><button class="btn" type="submit">Search</button><a class="btn btn-outline" href="customers.php">Clear</a></div></form>
<div class="table-wrap"><table class="zebra tbl-center">
  <tr><th>Account No.</th><th>Name</th><th>Email</th><th>Phone</th><th>Film</th><th>Repairs</th><th>Orders</th><th>Joined</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($rows as $u): ?>
    <tr><td><strong><?= e(account_no($u['user_id'])) ?></strong></td><td><?= e($u['full_name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td>
      <td><?= (int)$u['bookings'] ?></td><td><?= (int)$u['repairs'] ?></td><td><?= (int)$u['orders'] ?></td><td><?= e(substr($u['created_at'], 0, 10)) ?></td><td><?= status_badge($u['status']) ?></td>
      <td><form method="post"><input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>"><button class="btn btn-sm <?= $u['status'] === 'active' ? 'btn-danger' : '' ?>" data-confirm="Change this account's status?"><?= $u['status'] === 'active' ? 'Disable' : 'Enable' ?></button></form></td></tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="10">No customers found.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>