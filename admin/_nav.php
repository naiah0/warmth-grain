<?php
$cur = basename($_SERVER['SCRIPT_NAME']);
$links = ['index.php' => 'Dashboard', 'bookings.php' => 'Bookings', 'repairs.php' => 'Repairs', 'orders.php' => 'Orders',
          'products.php' => 'Products', 'events.php' => 'Events', 'customers.php' => 'Customers', 'payments.php' => 'Payments', 'messages.php' => 'Messages'];
?>
<nav class="admin-nav">
  <?php foreach ($links as $file => $label): ?>
    <a href="<?= BASE_URL ?>admin/<?= $file ?>" class="<?= $cur === $file ? 'active' : '' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</nav>