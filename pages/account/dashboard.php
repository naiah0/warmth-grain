<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Account';
include '../../includes/header.php';

$uid = $_SESSION['user_id'];
$count = function ($table) use ($pdo, $uid) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM $table WHERE user_id=?");
    $s->execute([$uid]);
    return $s->fetchColumn();
};
?>
<h1>Welcome, <?= e($_SESSION['name']) ?></h1>
<div class="grid">
  <div class="card"><h3>Service Bookings</h3><p class="price"><?= (int)$count('service_bookings') ?></p><a href="bookings.php">View</a></div>
  <div class="card"><h3>Repairs</h3><p class="price"><?= (int)$count('repairs') ?></p><a href="repairs.php">View</a></div>
  <div class="card"><h3>Orders</h3><p class="price"><?= (int)$count('orders') ?></p><a href="orders.php">View</a></div>
  <div class="card"><h3>Profile</h3><p>Edit your details</p><a href="profile.php">Edit</a></div>
</div>
<?php include '../../includes/footer.php'; ?>