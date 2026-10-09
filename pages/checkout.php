<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';
$cart = $_SESSION['cart'] ?? [];
if (!$cart) { header('Location: cart.php'); exit; }
$pageTitle = 'Checkout';
$scripts = ['payment.js', 'checkout.js'];
include '../includes/header.php';

$in = implode(',', array_fill(0, count($cart), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($in)");
$stmt->execute(array_keys($cart));
$items = $stmt->fetchAll();
$total = 0;
$u = $pdo->prepare('SELECT full_name, phone FROM users WHERE user_id=?');
$u->execute([$_SESSION['user_id']]);
$user = $u->fetch();
?>
<h1>Checkout</h1>
<h2>Order Summary</h2>
<div class="table-wrap"><table>
  <tr><th>Product</th><th>Qty</th><th>Subtotal</th></tr>
  <?php foreach ($items as $i): $sub = $cart[$i['product_id']] * $i['price']; $total += $sub; ?>
    <tr><td><?= e($i['name']) ?></td><td><?= (int)$cart[$i['product_id']] ?></td><td><?= peso($sub) ?></td></tr>
  <?php endforeach; ?>
  <tr><th colspan="2">Total</th><th><?= peso($total) ?></th></tr>
</table></div>

<h2>Your Details and Payment</h2>
<form class="form" id="checkoutForm" method="post" action="<?= BASE_URL ?>actions/order_place.php">
  <label for="contact_name">Full Name</label>
  <input type="text" id="contact_name" name="contact_name" value="<?= e($user['full_name']) ?>" required>
  <label for="contact_phone">Phone</label>
  <input type="text" id="contact_phone" name="contact_phone" value="<?= e($user['phone']) ?>" pattern="09[0-9]{9}" required>
  <label for="fulfillment">Pickup or Delivery</label>
  <select id="fulfillment" name="fulfillment"><option>Pickup</option><option>Delivery</option></select>
  <div id="addressBox" style="display:none">
    <label for="delivery_address">Delivery Address</label>
    <input type="text" id="delivery_address" name="delivery_address">
  </div>
  <?php include __DIR__ . '/../includes/payment_methods.php'; ?>
  <button class="btn" type="submit">Place Order</button>
</form>
<?php include '../includes/footer.php'; ?>