<?php include __DIR__ . '/_nav.php';
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

$statuses = ['Submitted','Received','Under Inspection','Repairing','Ready for Pickup','Completed'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['repair_id'];
    $status = $_POST['status'] ?? '';
    $diag = trim($_POST['diagnosis'] ?? '');
    $cost = $_POST['repair_cost'] ?? '';
    $errors = [];
    if (!in_array($status, $statuses, true)) $errors[] = 'Invalid status.';
    if ($cost !== '' && (!is_numeric($cost) || $cost < 0)) $errors[] = 'Cost cannot be negative.';
    if ($errors) set_flash('error', $errors);
    else {
        $pdo->prepare('UPDATE repairs SET status=?, diagnosis=?, repair_cost=? WHERE repair_id=?')
            ->execute([$status, $diag ?: null, $cost === '' ? null : $cost, $id]);
        set_flash('success', 'Repair updated.');
    }
    header('Location: repairs.php' . ($errors ? "?id=$id" : ''));
    exit;
}

$pageTitle = 'Manage Repairs';
include '../includes/header.php';
$editId = (int)($_GET['id'] ?? 0);
$edit = null;
if ($editId) {
    $s = $pdo->prepare('SELECT r.*, u.full_name FROM repairs r JOIN users u ON u.user_id=r.user_id WHERE repair_id=?');
    $s->execute([$editId]);
    $edit = $s->fetch();
}
$rows = $pdo->query('SELECT r.*, u.full_name FROM repairs r JOIN users u ON u.user_id=r.user_id ORDER BY r.created_at DESC')->fetchAll();
?>
<h1>Repairs</h1>
<p><a class="btn btn-outline" href="index.php">Back</a></p>

<?php if ($edit): ?>
<h2>Update <?= e($edit['repair_number']) ?> (<?= e($edit['full_name']) ?>)</h2>
<p><?= e($edit['camera_brand'] . ' ' . $edit['camera_model']) ?>: <?= e($edit['issue']) ?><br><?= e($edit['details']) ?></p>
<form class="form" method="post">
  <input type="hidden" name="repair_id" value="<?= (int)$edit['repair_id'] ?>">
  <label for="status">Status</label>
  <select id="status" name="status">
    <?php foreach ($statuses as $s): ?><option <?= $s === $edit['status'] ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
  </select>
  <label for="diagnosis">Diagnosis / Notes</label>
  <textarea id="diagnosis" name="diagnosis" rows="3"><?= e($edit['diagnosis']) ?></textarea>
  <label for="repair_cost">Repair Cost (₱)</label>
  <input type="number" id="repair_cost" name="repair_cost" step="0.01" min="0" value="<?= e($edit['repair_cost']) ?>">
  <button class="btn" type="submit">Save</button>
  <a class="btn btn-outline" href="repairs.php">Cancel</a>
</form>
<?php endif; ?>

<div class="table-wrap"><table>
  <tr><th>Repair No.</th><th>Customer</th><th>Camera</th><th>Issue</th><th>Status</th><th>Cost</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['repair_number']) ?></td><td><?= e($r['full_name']) ?></td>
      <td><?= e($r['camera_brand'] . ' ' . $r['camera_model']) ?></td><td><?= e($r['issue']) ?></td>
      <td><?= e($r['status']) ?></td><td><?= $r['repair_cost'] !== null ? peso($r['repair_cost']) : '-' ?></td>
      <td><a class="btn" href="repairs.php?id=<?= (int)$r['repair_id'] ?>">Update</a></td>
    </tr>
  <?php endforeach; ?>
</table></div>
<?php include '../includes/footer.php'; ?>