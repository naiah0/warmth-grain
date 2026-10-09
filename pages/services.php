<?php
require_once '../includes/auth.php';
if (isset($_GET['book'])) require_login();
require_once '../config/db.php';
$pageTitle = 'Film Processing';
$scripts = ['payment.js', 'booking.js'];
include '../includes/header.php';

$rows = $pdo->query("SELECT * FROM services WHERE is_active=1 ORDER BY FIELD(service_type,'Developing','Scanning','Printing'), price_per_roll")->fetchAll();
$grouped = [];
foreach ($rows as $r) $grouped[$r['service_type']][] = $r;
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>
<h1>Film Processing</h1>
<p>Developing, scanning and printing for every kind of film. Open a process to read the details, or book straight away.</p>
<p>
<?php if (is_logged_in()): ?>
  <button type="button" class="btn" data-modal-open="bookingModal">Book A Service</button>
<?php else: ?>
  <a class="btn" href="login.php?next=<?= urlencode(BASE_URL . 'pages/services.php?book=0') ?>">Book A Service</a>
<?php endif; ?>
</p>

<?php foreach ($grouped as $type => $list): ?>
  <h2 id="<?= e(slug($type)) ?>"><?= e($type) ?></h2>
  <div class="grid">
    <?php foreach ($list as $s): ?>
      <a class="card card-link" href="service.php?id=<?= (int)$s['service_id'] ?>">
        <img src="<?= img('services/' . slug($type) . '.jpg') ?>" alt="<?= e($s['name']) ?>">
        <h3><?= e($s['name']) ?></h3>
        <p class="price"><?= peso($s['price_per_roll']) ?> / roll</p>
        <small class="muted">Turnaround: <?= e($s['turnaround'] ?: 'TBA') ?></small>
      </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<?php if (is_logged_in()): ?>
<div class="modal" id="bookingModal">
  <div class="modal-box">
    <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
    <h2>Book a Film Service</h2>
    <div class="info-box">
      <strong>What to bring:</strong> your exposed rolls, ideally in their canisters or cases, with your name and contact details written on the packaging. Drop-off is at the Warmth&amp;Grain lab on the date and time slot you choose.
    </div>
    <div id="bookingErrors" class="alert alert-error" style="display:none"></div>
    <form class="form wide" id="bookingForm" method="post" action="<?= BASE_URL ?>actions/booking_create.php" novalidate>
      <label for="service_id">Service</label>
      <select id="service_id" name="service_id" required>
        <option value="">-- Select a service --</option>
        <?php foreach ($rows as $s): ?>
          <option value="<?= (int)$s['service_id'] ?>" data-price="<?= e($s['price_per_roll']) ?>"
                  data-desc="<?= e($s['description']) ?>" data-incl="<?= e($s['inclusions']) ?>" data-turn="<?= e($s['turnaround']) ?>">
            <?= e($s['service_type'] . ': ' . $s['name']) ?> (<?= peso($s['price_per_roll']) ?>/roll)
          </option>
        <?php endforeach; ?>
      </select>
      <div class="info-box" id="serviceInfo">Select a service to see what is included.</div>

      <div class="form-row">
        <div><label for="film_type">Film Type</label>
          <select id="film_type" name="film_type">
            <option>Color Negative (C-41)</option><option>Black &amp; White</option><option>Slide (E-6)</option><option>Cine (ECN-2)</option><option>Other</option>
          </select></div>
        <div><label for="film_format">Film Format</label>
          <select id="film_format" name="film_format"><option>35mm</option><option>120</option><option>APS</option><option>Other</option></select></div>
      </div>
      <div class="form-row">
        <div><label for="roll_count">Number of Rolls</label>
          <input type="number" id="roll_count" name="roll_count" min="1" max="50" value="1" required></div>
        <div><label for="exposures">Exposures per Roll</label>
          <select id="exposures" name="exposures"><option>12</option><option>24</option><option selected>36</option></select></div>
      </div>
      <label class="check"><input type="checkbox" name="rush" value="1"> Rush processing (+50%, prioritized in the queue)</label>

      <div class="form-row">
        <div><label for="return_method">Return Method</label>
          <select id="return_method" name="return_method"><option>Pickup</option><option>Delivery</option></select></div>
        <div><label for="time_slot">Drop-off Time Slot</label>
          <select id="time_slot" name="time_slot"><option>10:00 AM - 12:00 PM</option><option>12:00 PM - 3:00 PM</option><option>3:00 PM - 6:00 PM</option></select></div>
      </div>
      <div id="addrWrap" style="display:none">
        <label for="delivery_address">Delivery Address</label>
        <input type="text" id="delivery_address" name="delivery_address" maxlength="255">
      </div>
      <label for="dropoff_date">Drop-off Date</label>
      <input type="date" id="dropoff_date" name="dropoff_date" min="<?= $tomorrow ?>" required>
      <label for="notes">Notes (optional)</label>
      <textarea id="notes" name="notes" rows="2" placeholder="Special instructions, e.g. push 1 stop"></textarea>

      <h3>Payment</h3>
      <?php include __DIR__ . '/../includes/payment_methods.php'; ?>

      <div class="summary">
        <div><span>Processing</span><span id="sumSub">₱0.00</span></div>
        <div><span>Rush fee</span><span id="sumRush">₱0.00</span></div>
        <div class="total"><span>Total</span><span id="sumTotal">₱0.00</span></div>
      </div>
      <button class="btn" type="submit">Confirm Booking</button>
    </form>
  </div>
</div>
<script>window.OPEN_BOOKING = <?= isset($_GET['book']) ? 'true' : 'false' ?>; window.PRESELECT = <?= (int)($_GET['book'] ?? 0) ?>;</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>