<?php include __DIR__ . '/_nav.php';
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';


$statuses = ['Pending','Confirmed','Received','Processing','Completed','Cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    if (in_array($status, $statuses, true)) {
        $pdo->prepare('UPDATE service_bookings SET status=? WHERE booking_id=?')->execute([$status, (int)$_POST['booking_id']]);
        set_flash('success', 'Booking status updated.');
    }
    header('Location: bookings.php');
    exit;
}

$pageTitle = 'Manage Bookings';
include '../includes/header.php';
$rows = $pdo->query('SELECT b.*, u.full_name, s.name AS service FROM service_bookings b JOIN users u ON u.user_id=b.user_id JOIN services s ON s.service_id=b.service_id ORDER BY b.created_at DESC')->fetchAll();
?>
<h1>Service Bookings</h1>
<p><a class="btn btn-outline" href="index.php">Back</a></p>
<div class="table-wrap"><table>
  <tr><th>#</th><th>Customer</th><th>Service</th><th>Rolls</th><th>Drop-off</th><th>Total</th><th>Status</th></tr>
  <?php foreach ($rows as $b): ?>
    <tr>
      <td><?= (int)$b['booking_id'] ?></td><td><?= e($b['full_name']) ?></td><td><?= e($b['service']) ?></td>
      <td><?= (int)$b['roll_count'] ?></td><td><?= e($b['dropoff_date']) ?></td><td><?= peso($b['total_price']) ?></td>
      <td>
        <form method="post" style="display:flex;gap:.4rem">
          <input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
          <select name="status">
            <?php foreach ($statuses as $s): ?><option <?= $s === $b['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
          </select>
          <button class="btn">Save</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
</table></div>
<?php include '../includes/footer.php'; ?>