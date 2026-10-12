<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$pageTitle = 'Book a Service'; $scripts = ['calendar.js', 'booking.js'];
include '../includes/header.php';

$svc = $pdo->query('SELECT * FROM services WHERE is_active=1 ORDER BY service_id')->fetchAll();
$by = ['Developing' => [], 'Scanning' => [], 'Printing' => []];
foreach ($svc as $s) $by[$s['service_type']][] = $s;
$packs = $pdo->query('SELECT * FROM packages ORDER BY package_id LIMIT 4')->fetchAll();
$pre = (int)($_GET['package'] ?? 0);
$opt = function ($list) {
    $h = '<option value="">-- Select --</option>';
    foreach ($list as $s) $h .= '<option value="' . (int)$s['service_id'] . '" data-price="' . e($s['price_per_roll']) . '">' . e($s['name']) . ' — ' . peso($s['price_per_roll']) . '/roll</option>';
    return $h;
};
?>
<?= back_button(BASE_URL . 'pages/services.php') ?>
<h1 class="page-title">Book a Film Service</h1>
<div id="formErrors" class="alert alert-error" style="display:none"></div>
<form id="bookingForm" method="post" action="<?= BASE_URL ?>actions/booking_create.php" novalidate>
<div class="two-col">
  <div class="form wide">
    <h3 class="step-title">Step 1: Choose Your Process</h3>
    <label for="package_id">Usual Package (Optional)</label>
    <span class="hint">Select a ready-made combination, or leave it on Custom Order to choose each process yourself.</span>
    <select id="package_id" name="package_id">
      <option value="">Custom Order</option>
      <?php foreach ($packs as $k): ?><option value="<?= (int)$k['package_id'] ?>" data-dev="<?= (int)$k['developing_id'] ?>" data-scan="<?= (int)$k['scanning_id'] ?>" data-print="<?= (int)$k['printing_id'] ?>" data-output="<?= e($k['output_option']) ?>" <?= $pre === (int)$k['package_id'] ? 'selected' : '' ?>><?= e($k['name']) ?> (<?= e($k['output_option']) ?>)</option><?php endforeach; ?>
    </select>
    <label for="developing_id">Developing Process</label>
    <span class="hint">Select this first. The process depends on your film type, for example C-41 for regular color negatives.</span>
    <select id="developing_id" name="developing_id"><?= $opt($by['Developing']) ?></select>
    <label for="output_option">Output Option</label>
    <span class="hint">Digital only provides scans, Prints only provides printed photos, and Both provides scans and prints.</span>
    <select id="output_option" name="output_option"><option>Digital only</option><option>Prints only</option><option selected>Both</option></select>
    <div class="form-grid">
      <div><label for="scanning_id">Scanning Option</label><span class="hint" id="scanHint"></span><select id="scanning_id" name="scanning_id"><?= $opt($by['Scanning']) ?></select></div>
      <div><label for="printing_id">Printing Option</label><span class="hint" id="printHint"></span><select id="printing_id" name="printing_id"><?= $opt($by['Printing']) ?></select></div>
    </div>

    <h3 class="step-title">Step 2: Film Details</h3>
    <div class="form-grid">
      <div><label for="film_format">Film Format</label><span class="hint">Most film cameras use 35mm. Medium format cameras use 120.</span>
        <select id="film_format" name="film_format"><option>35mm</option><option>120</option><option>APS</option><option>Other</option></select></div>
      <div><label for="exposures">Exposures per Roll</label><span class="hint">The number printed on the film box, usually 24 or 36.</span>
        <select id="exposures" name="exposures"><option>12</option><option>24</option><option selected>36</option></select></div>
    </div>
    <label for="roll_count">Number of Rolls</label>
    <span class="hint">Enter the total number of rolls you will drop off, from 1 to 50.</span>
    <input type="number" id="roll_count" name="roll_count" min="1" max="50" value="1">

    <h3 class="step-title">Step 3: Drop-off Schedule</h3>
    <div class="split">
      <div>
        <label>Drop-off Date</label>
        <span class="hint">We are open Monday to Friday. Gray dates are unavailable.</span>
        <div class="calendar" id="calendar"></div>
        <input type="hidden" id="dropoff_date" name="dropoff_date" value="">
        <p style="margin-top:.5rem">Selected: <strong id="dateLabel">none yet</strong></p>
      </div>
      <div>
        <label for="time_slot">Time Slot</label>
        <span class="hint">Select the time you plan to arrive at the lab.</span>
        <select id="time_slot" name="time_slot"><?php foreach (TIME_SLOTS as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select>
      </div>
    </div>

    <h3 class="step-title">Step 4: Special Instructions</h3>
    <label for="notes">Special Instructions</label>
    <span class="hint">For example, "Push 1 stop" or "Cut negatives in strips of 4". Enter "None" if you have no instructions.</span>
    <textarea id="notes" name="notes" rows="3"></textarea>
  </div>

  <div class="side"><div class="side-box">
    <h3>How It Works</h3>
    <ol class="how"><li>Choose a package or build your own order.</li><li>Pick a drop-off date and time slot.</li><li>Review your total and pay using the QR code.</li><li>Bring your film to the lab and keep your QR code.</li></ol>
    <h3>Total Breakdown</h3>
    <div class="summary" id="breakdown"></div>
    <button class="btn" type="submit" style="width:100%">Proceed to Payment</button>
  </div></div>
</div>
</form>
<script>window.UNAVAILABLE = <?= json_encode(unavailable_dates($pdo, false)) ?>;</script>
<?php include '../includes/footer.php'; ?>