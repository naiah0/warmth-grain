<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$pageTitle = 'Book a Service';
$scripts = ['booking.js'];
include '../includes/header.php';

$services = $pdo->query("SELECT * FROM services WHERE is_active=1 ORDER BY service_type, price_per_roll")->fetchAll();
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<h1>Book a Service</h1>
<form class="form" id="bookingForm" method="post" action="<?= BASE_URL ?>actions/booking_create.php">
  <label for="service_id">Service</label>
  <select id="service_id" name="service_id" required>
    <option value="">-- Select --</option>
    <?php foreach ($services as $s): ?>
      <option value="<?= (int)$s['service_id'] ?>" data-price="<?= e($s['price_per_roll']) ?>">
        <?= e($s['service_type'] . ': ' . $s['name']) ?> (<?= peso($s['price_per_roll']) ?>/roll)
      </option>
    <?php endforeach; ?>
  </select>

  <label for="film_format">Film Format</label>
  <select id="film_format" name="film_format">
    <option>35mm</option><option>120</option><option>APS</option><option>Other</option>
  </select>

  <label for="roll_count">Number of Rolls</label>
  <input type="number" id="roll_count" name="roll_count" min="1" max="50" value="1" required>

  <label for="dropoff_date">Drop-off Date</label>
  <input type="date" id="dropoff_date" name="dropoff_date" min="<?= $tomorrow ?>" required>

  <label for="notes">Notes (optional)</label>
  <textarea id="notes" name="notes" rows="3"></textarea>

  <label for="method">Payment Method</label>
  <select id="method" name="method">
    <option>GCash</option><option>Card</option><option>Cash</option>
  </select>

  <p><strong>Total: <span id="total">₱0.00</span></strong></p>
  <button class="btn" type="submit">Confirm Booking</button>
</form>
<?php include '../includes/footer.php'; ?>