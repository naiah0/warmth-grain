<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$pend = $_SESSION['pending'] ?? null;
if (!$pend) { set_flash('error', 'There is nothing to pay for yet.'); header('Location: ' . BASE_URL . 'index.php'); exit; }
$t = calc_totals($pend['subtotal']);
$pageTitle = 'Payment'; $showCart = $pend['type'] === 'order'; $scripts = ['payment.js'];
include '../includes/header.php';
$qrs = ['GoTyme' => 'payment/gotyme', 'GCash' => 'payment/gcash', 'Maya' => 'payment/maya'];
?>
<?= back_button($pend['back']) ?>
<h1 class="page-title">Payment</h1>
<div class="two-col">
  <form id="payForm" method="post" action="<?= BASE_URL ?>actions/payment_submit.php" enctype="multipart/form-data">
    <div class="pay-box">
      <div>
        <h3>Choose a Payment Method</h3>
        <div class="pay-methods" style="margin-top:.6rem">
          <?php foreach (PAY_METHODS as $m): ?><label class="pay-option"><input type="radio" name="method" value="<?= e($m) ?>"><?= e(pay_label($m)) ?></label><?php endforeach; ?>
        </div>
      </div>
      <div class="pay-right">
        <p id="pickMsg" class="muted">Choose a payment method on the left to see how to pay.</p>
        <?php foreach ($qrs as $m => $path): ?>
          <div class="qr-panel" data-method="<?= e($m) ?>">
            <p>Scan to pay with <strong><?= e($m) ?></strong>. Send exactly <strong><?= peso($t['total']) ?></strong>.</p>
            <img src="<?= img($path) ?>" alt="<?= e($m) ?> QR code"><p class="small muted">Account name: Warmth&amp;Grain</p>
          </div>
        <?php endforeach; ?>
        <div class="qr-panel" data-method="Cash"><p>Pay <strong><?= peso($t['total']) ?></strong> at the lab during your visit. Your payment will show as Pending until we receive it.</p></div>
        <div id="proofBox" style="display:none;margin-top:.8rem">
          <label for="proof"><strong>Attach Proof of Payment</strong></label>
          <span class="hint">A screenshot of your payment (JPG, PNG or WEBP, maximum 3 MB).</span>
          <input type="file" id="proof" name="proof" accept="image/jpeg,image/png,image/webp">
          <p class="small muted" id="fileName">No file attached yet</p>
        </div>
        <div class="pay-actions">
          <button class="btn" type="submit" id="paidBtn" disabled>Paid</button>
          <p class="small muted">The Paid button unlocks once your proof of payment is attached.</p>
        </div>
      </div>
    </div>
  </form>
  <div class="side"><div class="side-box">
    <h3>Order Summary</h3>
    <div class="summary"><?php foreach ($pend['lines'] as $l): ?><div><span><?= e($l['label']) ?></span><span><?= peso($l['amount']) ?></span></div><?php endforeach; ?></div>
    <?= breakdown_html($t) ?>
  </div></div>
</div>
<?php include '../includes/footer.php'; ?>