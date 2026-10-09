<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Bookings';
include '../../includes/header.php';

$stmt = $pdo->prepare('SELECT b.*, s.name AS service FROM service_bookings b JOIN services s ON s.service_id=b.service_id WHERE b.user_id=? ORDER BY b.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();
?>
<h1>My Service Bookings</h1>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Service</th><th>Format</th><th>Rolls</th><th>Drop-off</th><th>Total</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $b): ?>
    <tr>
      <td><?= (int)$b['booking_id'] ?></td><td><?= e($b['service']) ?></td><td><?= e($b['film_format']) ?></td>
      <td><?= (int)$b['roll_count'] ?></td><td><?= e($b['dropoff_date']) ?></td><td><?= peso($b['total_price']) ?></td>
      <td><?= e($b['status']) ?></td>
      <td>
        <?php if (in_array($b['status'], ['Pending','Confirmed'])): ?>
          <form method="post" action="<?= BASE_URL ?>actions/booking_cancel.php" onsubmit="return confirm('Cancel this booking?')">
            <input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
            <button class="btn btn-danger">Cancel</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="8">No bookings yet.</td></tr>'; ?>
</table></div>
<?php include '../../includes/footer.php'; ?>