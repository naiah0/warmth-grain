<?php
require_once '../includes/auth.php';
if (isset($_GET['book'])) require_login();
require_once '../config/db.php';
$pageTitle = 'Repair';
$scripts = ['repair.js'];
include '../includes/header.php';

$rs = $pdo->query('SELECT * FROM repair_services WHERE is_active=1 ORDER BY repair_service_id')->fetchAll();
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$loginNext = 'login.php?next=' . urlencode(BASE_URL . 'pages/repair.php?book=');
?>
<h1>Camera Repair</h1>
<p>Bring us your film camera and we will inspect it, diagnose the problem and fix it.</p>

<div class="section-head">
  <h2>Repair Services</h2>
  <?php if (is_logged_in()): ?>
    <button type="button" class="btn" data-modal-open="repairModal">Book Repair</button>
  <?php else: ?>
    <a class="btn" href="<?= $loginNext ?>0">Book Repair</a>
  <?php endif; ?>
</div>
<div class="grid">
  <?php foreach ($rs as $r): ?>
    <div class="card">
      <h3><?= e($r['name']) ?></h3>
      <p><?= e($r['description']) ?></p>
      <ul><?php foreach (array_filter(explode('|', (string)$r['inclusions'])) as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
      <p class="price">From <?= peso($r['price_from']) ?></p>
      <small class="muted">Turnaround: <?= e($r['turnaround']) ?></small><br><br>
      <?php if (is_logged_in()): ?>
        <button type="button" class="btn btn-outline" data-modal-open="repairModal" data-repair="<?= (int)$r['repair_service_id'] ?>">Book this repair</button>
      <?php else: ?>
        <a class="btn btn-outline" href="<?= $loginNext . (int)$r['repair_service_id'] ?>">Book this repair</a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<h2>Track Your Repair</h2>
<form class="form" method="get" action="track-repair.php">
  <label for="repair_number">Repair Number</label>
  <input type="text" id="repair_number" name="repair_number" placeholder="WG-R-0001" required>
  <button class="btn" type="submit">Track</button>
</form>

<?php if (is_logged_in()): ?>
<div class="modal" id="repairModal">
  <div class="modal-box">
    <button type="button" class="modal-close" data-modal-close aria-label="Close">&times;</button>
    <h2>Book a Repair</h2>
    <div class="info-box">
      <strong>How it works:</strong> drop off your camera on the date you choose. We inspect it for free, record the diagnosis and cost, and update the status in <em>My Purchases</em> and Track Repair. Please remove the film and battery before drop-off, and tell us about any accessories you leave with the camera.
    </div>
    <div id="repairErrors" class="alert alert-error" style="display:none"></div>
    <form class="form wide" id="repairForm" method="post" action="<?= BASE_URL ?>actions/repair_submit.php" novalidate>
      <label for="repair_service_id">Repair Service</label>
      <select id="repair_service_id" name="repair_service_id" required>
        <option value="">-- Select a repair service --</option>
        <?php foreach ($rs as $r): ?>
          <option value="<?= (int)$r['repair_service_id'] ?>" data-desc="<?= e($r['description']) ?>" data-incl="<?= e($r['inclusions']) ?>"
                  data-price="<?= e(peso($r['price_from'])) ?>" data-turn="<?= e($r['turnaround']) ?>"><?= e($r['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="info-box" id="repairInfo">Select a repair service to see what is included.</div>
      <div class="form-row">
        <div><label for="camera_brand">Camera Brand</label><input type="text" id="camera_brand" name="camera_brand" maxlength="60" required></div>
        <div><label for="camera_model">Camera Model</label><input type="text" id="camera_model" name="camera_model" maxlength="60" required></div>
      </div>
      <div class="form-row">
        <div><label for="serial_number">Serial Number (optional)</label><input type="text" id="serial_number" name="serial_number" maxlength="60"></div>
        <div><label for="issue">Main Problem</label>
          <select id="issue" name="issue" required>
            <option value="">-- Select --</option>
            <?php foreach (['Light leaks','Shutter stuck or inaccurate','Film advance or rewind jammed','Foggy lens or fungus','Light meter not working','Other'] as $i): ?><option><?= e($i) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <label for="details">Describe the Problem</label>
      <textarea id="details" name="details" rows="3" placeholder="When did it start? What happens? Any accessories left with the camera?"></textarea>
      <label for="dropoff_date">Drop-off Date</label>
      <input type="date" id="dropoff_date" name="dropoff_date" min="<?= $tomorrow ?>" required>
      <button class="btn" type="submit">Submit Repair Request</button>
    </form>
  </div>
</div>
<script>window.OPEN_REPAIR = <?= isset($_GET['book']) ? 'true' : 'false' ?>; window.PRESELECT = <?= (int)($_GET['book'] ?? 0) ?>;</script>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>