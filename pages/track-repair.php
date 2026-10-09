<?php
require_once '../config/db.php';
$pageTitle = 'Track Repair';
include '../includes/header.php';

$number = strtoupper(trim($_GET['repair_number'] ?? ''));
$r = null;
if ($number !== '') {
    $stmt = $pdo->prepare('SELECT * FROM repairs WHERE repair_number=?');
    $stmt->execute([$number]);
    $r = $stmt->fetch();
}
?>
<h1>Track Your Repair</h1>
<form class="form" method="get">
  <label for="repair_number">Repair Number</label>
  <input type="text" id="repair_number" name="repair_number" value="<?= e($number) ?>" placeholder="WG-R-0001" required>
  <button class="btn" type="submit">Track</button>
</form>
<?php if ($number !== ''): ?>
  <?php if (!$r): ?>
    <p class="alert alert-error" style="margin-top:1rem">No repair found with that number.</p>
  <?php else: ?>
    <h2>Repair <?= e($r['repair_number']) ?></h2>
    <div class="table-wrap"><table>
      <tr><th>Camera</th><td><?= e($r['camera_brand'] . ' ' . $r['camera_model']) ?></td></tr>
      <tr><th>Issue</th><td><?= e($r['issue']) ?></td></tr>
      <tr><th>Status</th><td><strong><?= e($r['status']) ?></strong></td></tr>
      <tr><th>Diagnosis</th><td><?= e($r['diagnosis'] ?: 'Pending inspection') ?></td></tr>
      <tr><th>Repair Cost</th><td><?= $r['repair_cost'] !== null ? peso($r['repair_cost']) : 'To be set' ?></td></tr>
    </table></div>
  <?php endif; ?>
<?php endif;
include '../includes/footer.php'; ?>