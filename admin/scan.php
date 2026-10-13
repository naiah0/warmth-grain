<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

$map = ['B' => ['booking', 'service_bookings', 'booking_id', 'booking_code', BOOKING_FLOW], 'R' => ['repair', 'repairs', 'repair_id', 'repair_code', REPAIR_FLOW], 'O' => ['order', 'orders', 'order_id', 'order_code', ORDER_FLOW]];
$code = strtoupper(trim($_GET['code'] ?? $_POST['code'] ?? ''));
$rec = null; $cfg = null; $cust = null; $info = [];
if (preg_match('/^WG-([BRO])-\d{4,}$/', $code, $m)) {
    $cfg = $map[$m[1]];
    [$stype, $table, $key, $codeCol, $flow] = $cfg;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cur = get_record($pdo, $stype, (int)($_POST['id'] ?? 0));
        if (!$cur) set_flash('error', 'Record not found.');
        elseif (($cur['pay_status'] ?? '') !== 'Paid') set_flash('error', 'Payment has not been received yet. Click "Payment Received" in Payments first.');
        else {
            $st = isset($_POST['advance']) ? next_status($stype, $cur) : ($_POST['status'] ?? '');
            if ($st && in_array($st, array_merge($flow, ['Cancelled']), true)) {
                $pdo->prepare("UPDATE $table SET status=? WHERE $key=?")->execute([$st, (int)$cur[$key]]);
                set_flash('success', "Status updated to $st.");
            }
        }
        header('Location: scan.php?code=' . urlencode($code)); exit;
    }
    $s = $pdo->prepare("SELECT $key FROM $table WHERE $codeCol=?"); $s->execute([$code]);
    $rid = $s->fetchColumn();
    if ($rid) {
        $rec = get_record($pdo, $stype, $rid);
        $u = $pdo->prepare("SELECT u.full_name, u.phone FROM users u JOIN $table t ON t.user_id=u.user_id WHERE t.$key=?"); $u->execute([$rid]); $cust = $u->fetch();
        if ($stype === 'booking') {
            $q = $pdo->prepare('SELECT d.name dn, sc.name sn, pr.name pn FROM service_bookings b JOIN services d ON d.service_id=b.developing_id LEFT JOIN services sc ON sc.service_id=b.scanning_id LEFT JOIN services pr ON pr.service_id=b.printing_id WHERE b.booking_id=?'); $q->execute([$rid]); $n = $q->fetch();
            $info[] = 'Developing: ' . $n['dn']; if ($n['sn']) $info[] = 'Scanning: ' . $n['sn']; if ($n['pn']) $info[] = 'Printing: ' . $n['pn'];
            $info[] = $rec['roll_count'] . ' roll(s), ' . $rec['film_format'] . ' (' . $rec['output_option'] . ')'; $info[] = 'Drop-off: ' . $rec['dropoff_date'] . ', ' . $rec['time_slot'];
        } elseif ($stype === 'repair') {
            $q = $pdo->prepare('SELECT ' . repair_names_sql('r') . ' FROM repairs r WHERE r.repair_id=?'); $q->execute([$rid]);
            $info[] = 'Service: ' . $q->fetchColumn(); $info[] = 'Camera: ' . $rec['camera_brand'] . ' ' . $rec['camera_model']; $info[] = 'Repair date: ' . $rec['repair_date']; $info[] = 'Concerns: ' . $rec['concerns'];
        } else {
            $q = $pdo->prepare('SELECT oi.quantity, p.name FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?'); $q->execute([$rid]);
            foreach ($q->fetchAll() as $it) $info[] = $it['name'] . ' × ' . $it['quantity'];
            $info[] = $rec['fulfillment'] . ($rec['delivery_address'] ? ': ' . $rec['delivery_address'] : '');
        }
    }
}
$pageTitle = 'Scan QR';
include '../includes/header.php';
$locked = $rec && ($rec['pay_status'] ?? '') !== 'Paid';
?>
<?= back_button(BASE_URL . 'admin/index.php') ?>
<h1 class="page-title">Scan QR Code</h1>
<div class="scan-grid">
  <div>
    <div class="panel"><h3>Camera Scanner</h3><div id="reader" style="max-width:420px;margin:0 auto"></div></div>
    <form class="form wide" method="get" style="margin-top:1.2rem">
      <label for="code">Enter the Code Manually</label><span class="hint">Type the reference printed under the QR code, for example WG-B-0001, or use a USB scanner here.</span>
      <input type="text" id="code" name="code" value="<?= e($code) ?>" autocomplete="off">
      <button class="btn" type="submit">Look Up</button>
    </form>
  </div>
  <div class="panel">
    <h3>Information and Status</h3>
    <?php if (!$code): ?><p class="muted">Scan a QR code or enter a reference code to see the details here.</p>
    <?php elseif (!$rec): ?><div class="alert alert-error">No record found for “<?= e($code) ?>”.</div>
    <?php else: $next = next_status($stype, $rec); ?>
      <h2 class="plain" style="margin-top:0"><?= e($code) ?></h2>
      <p><strong><?= e($cust['full_name']) ?></strong> &middot; <?= e($cust['phone']) ?></p>
      <p>Current status: <?= status_badge($rec['status']) ?> &nbsp; Payment: <?= e(pay_label($rec['method'])) ?> <?= $rec['pay_status'] ? status_badge($rec['pay_status']) : '' ?></p>
      <ul class="detail-list" style="margin:.8rem 0"><?php foreach ($info as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
      <?php if ($locked): ?>
        <div class="alert alert-error">The payment has not been received yet, so the status is locked. <a href="payments.php">Open Payments</a> and click "Payment Received" first.</div>
      <?php else: ?>
        <form method="post" class="actions" style="margin-top:1rem">
          <input type="hidden" name="code" value="<?= e($code) ?>"><input type="hidden" name="id" value="<?= (int)$rec[$cfg[2]] ?>">
          <?php if ($next): ?><button class="btn" name="advance" value="1">Move to: <?= e($next) ?></button><?php endif; ?>
          <select name="status" class="tbl-select"><?php foreach (array_merge($cfg[4], ['Cancelled']) as $s): ?><option <?= $s === $rec['status'] ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select>
          <button class="btn btn-outline" type="submit">Set Status</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
  if (window.Html5QrcodeScanner) new Html5QrcodeScanner('reader', { fps: 10, qrbox: 220 }, false).render(t => { location.href = 'scan.php?code=' + encodeURIComponent(t); });
  else document.getElementById('reader').textContent = 'Camera scanner could not load. Use the code box below.';
</script>
<?php include '../includes/footer.php'; ?>