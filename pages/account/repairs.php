<?php
require_once '../../includes/auth.php';
require_login();
require_once '../../config/db.php';
$pageTitle = 'My Repairs';
include '../../includes/header.php';

$stmt = $pdo->prepare('SELECT * FROM repairs WHERE user_id=? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();
?>
<h1>My Repairs</h1>
<div class="table-wrap"><table>
  <tr><th>Repair No.</th><th>Camera</th><th>Issue</th><th>Status</th><th>Cost</th></tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['repair_number']) ?></td><td><?= e($r['camera_brand'] . ' ' . $r['camera_model']) ?></td>
      <td><?= e($r['issue']) ?></td><td><?= e($r['status']) ?></td>
      <td><?= $r['repair_cost'] !== null ? peso($r['repair_cost']) : '-' ?></td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="5">No repair requests yet.</td></tr>'; ?>
</table></div>
<?php include '../../includes/footer.php'; ?>