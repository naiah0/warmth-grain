<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$pageTitle = 'Book Repair'; $scripts = ['calendar.js', 'repair.js'];
include '../includes/header.php';
$rs = $pdo->query('SELECT * FROM repair_services WHERE is_active=1 ORDER BY repair_service_id')->fetchAll();
?>
<?= back_button(BASE_URL . 'pages/repair.php') ?>
<h1 class="page-title">Book a Repair</h1>
<div id="formErrors" class="alert alert-error" style="display:none"></div>
<form id="repairForm" method="post" action="<?= BASE_URL ?>actions/repair_submit.php" novalidate>
<div class="two-col">
  <div class="form wide">
    <h3 class="step-title">Step 1: Choose 1 to 2 services</h3>
    <span class="hint">Tick the repairs your camera needs. Not sure? Choose the closest one and describe the problem in Step 4.</span>
    <?php foreach ($rs as $r): ?>
      <label class="repair-option">
        <input type="checkbox" name="repair_service_id[]" value="<?= (int)$r['repair_service_id'] ?>" data-price="<?= e($r['price']) ?>" data-name="<?= e($r['name']) ?>">
        <span><strong><?= e($r['name']) ?></strong> &mdash; <?= peso($r['price']) ?><br><small><?= e($r['description']) ?> (<?= e($r['turnaround']) ?>)</small></span>
      </label>
    <?php endforeach; ?>

    <div class="split" style="margin-top:1.4rem">
      <div>
        <h3 class="step-title">Step 2: Your camera</h3>
        <label for="camera_brand">Camera brand</label><span class="hint">Example: Canon, Nikon, Olympus.</span>
        <input type="text" id="camera_brand" name="camera_brand" maxlength="60">
        <label for="camera_model">Camera model</label><span class="hint">Example: AE-1 Program, FM2, Trip 35.</span>
        <input type="text" id="camera_model" name="camera_model" maxlength="60">
        <label for="serial_number">Serial number</label><span class="hint">Usually engraved on the camera body or under the lens. Type "N/A" if you can't find it.</span>
        <input type="text" id="serial_number" name="serial_number" maxlength="60">
      </div>
      <div>
        <h3 class="step-title">Step 3: Pick a repair date</h3>
        <span class="hint">Open Monday to Friday. Gray dates are unavailable or fully booked.</span>
        <div class="calendar" id="calendar"></div>
        <input type="hidden" id="repair_date" name="repair_date" value="">
        <p style="margin-top:.5rem">Selected: <strong id="dateLabel">none yet</strong></p>
      </div>
    </div>

    <h3 class="step-title">Step 4: Concerns or questions</h3>
    <label for="concerns">What happened to your camera?</label>
    <span class="hint">Describe the problem, when it started, and any questions about the repair.</span>
    <textarea id="concerns" name="concerns" rows="4"></textarea>
  </div>

  <div class="side"><div class="side-box">
    <h3>How it works</h3>
    <ol class="how"><li>Choose your repair service and enter your camera details.</li><li>Pick an available repair date.</li><li>Review your total and pay using the QR code.</li><li>Drop off your camera and keep your QR code.</li></ol>
    <p class="small muted">Please remove the film and battery before drop-off.</p>
    <h3>Total breakdown</h3>
    <div class="summary" id="breakdown"></div>
    <button class="btn" type="submit" style="width:100%">Proceed to Payment</button>
  </div></div>
</div>
</form>
<script>window.UNAVAILABLE = <?= json_encode(unavailable_dates($pdo, true)) ?>;</script>
<?php include '../includes/footer.php'; ?>