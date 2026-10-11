<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$type = $_GET['type'] ?? ''; $id = (int)($_GET['id'] ?? 0);
$r = get_record($pdo, $type, $id, $_SESSION['user_id']);
if (!$r) { set_flash('error', 'Record not found.'); header('Location: ' . BASE_URL . 'pages/account/purchases.php'); exit; }
$new = isset($_GET['new']);
$pageTitle = $new ? 'Thank You' : 'Receipt'; $showCart = true; $scripts = [];
include '../includes/header.php';

$total = $r['total_price'] ?? $r['total_amount'];
$t = ['subtotal' => $r['subtotal'], 'service_charge' => $r['service_charge'], 'vat' => $r['vat'], 'total' => $total];
$lines = [];
if ($type === 'booking') {
    $s = $pdo->prepare('SELECT d.name dn, sc.name sn, pr.name pn FROM service_bookings b JOIN services d ON d.service_id=b.developing_id LEFT JOIN services sc ON sc.service_id=b.scanning_id LEFT JOIN services pr ON pr.service_id=b.printing_id WHERE b.booking_id=?');
    $s->execute([$id]); $n = $s->fetch();
    $lines[] = 'Developing: ' . $n['dn'];
    if ($n['sn']) $lines[] = 'Scanning: ' . $n['sn'];
    if ($n['pn']) $lines[] = 'Printing: ' . $n['pn'];
    $lines[] = $r['roll_count'] . ' roll(s), ' . $r['film_format'] . ', ' . $r['exposures'] . ' exposures (' . $r['output_option'] . ')';
    $lines[] = 'Drop-off: ' . date('F d, Y', strtotime($r['dropoff_date'])) . ', ' . $r['time_slot'];
} elseif ($type === 'repair') {
    $s = $pdo->prepare('SELECT ' . repair_names_sql('r') . ' FROM repairs r WHERE r.repair_id=?'); $s->execute([$id]);
    $lines[] = 'Service: ' . $s->fetchColumn();
    $lines[] = 'Camera: ' . $r['camera_brand'] . ' ' . $r['camera_model'] . ' (Serial: ' . ($r['serial_number'] ?: 'N/A') . ')';
    $lines[] = 'Repair date: ' . date('F d, Y', strtotime($r['repair_date']));
    if ($r['concerns']) $lines[] = 'Concerns: ' . $r['concerns'];
} else {
    $s = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?'); $s->execute([$id]);
    foreach ($s->fetchAll() as $it) $lines[] = $it['name'] . ' × ' . $it['quantity'] . ' (' . peso($it['unit_price']) . ')';
    $lines[] = $r['fulfillment'] . ($r['delivery_address'] ? ': ' . $r['delivery_address'] : '');
}
$flow = ['booking' => BOOKING_FLOW, 'repair' => REPAIR_FLOW, 'order' => ORDER_FLOW][$type];
$pos = array_search($r['status'], $flow, true);
$tab = ['booking' => 'bookings', 'repair' => 'repairs', 'order' => 'orders'][$type];
?>
<?= back_button(BASE_URL . 'pages/account/purchases.php?tab=' . $tab) ?>
<div class="two-col">
  <div>
    <div class="ty-head">
      <div>
        <h1><?= $new ? 'Thank you, ' . e($_SESSION['name']) . '!' : 'Your receipt' ?></h1>
        <p><?= $new ? ($r['method'] === 'Cash' ? 'We received your order and will wait for your cash payment on site.' : 'We received your payment and proof of payment. Our team will verify it shortly.') : 'Here are the details of this transaction.' ?></p>
      </div>
      <div class="ref-box"><small>Reference No.</small><strong><?= e($r['code']) ?></strong></div>
    </div>

    <div class="box">
      <h3>Payment info and details</h3>
      <p>Payment: <strong><?= e($r['method']) ?></strong> <?= $r['pay_status'] ? status_badge($r['pay_status']) : '' ?></p>
      <ul class="detail-list" style="margin-top:.6rem"><?php foreach ($lines as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
    </div>

    <div class="ty-grid">
      <div><h3>Total breakdown</h3><?= breakdown_html($t) ?></div>
      <div class="box qr-box">
        <h3>Your QR code</h3>
        <div class="qr-print"><div id="qrcode"></div><p class="qr-code"><?= e($r['code']) ?></p></div>
        <p class="small">Show this at the lab. Staff scan it to update your status.</p>
        <button type="button" class="btn btn-sm" data-print-qr>Print QR code</button>
      </div>
    </div>
  </div>

  <div class="side"><div class="side-box">
    <h3>Status</h3>
    <?php if ($r['status'] === 'Cancelled'): ?><p><?= status_badge('Cancelled') ?></p>
    <?php else: ?>
      <ol class="status-v">
        <?php foreach ($flow as $i => $st):
          if ($type === 'booking' && (($st === 'Scanning' && !$r['scanning_id']) || ($st === 'Printing' && !$r['printing_id']))) continue; ?>
          <li class="<?= $i < $pos ? 'done' : ($i === $pos ? 'now' : '') ?>"><?= $i < $pos ? '&#10003; ' : ($i === $pos ? '&#9679; ' : '') ?><?= e($st) ?></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById('qrcode'), { text: <?= json_encode($r['code']) ?>, width: 170, height: 170 });</script>
<?php include '../includes/footer.php'; ?>