<?php
require_once '../config/db.php';
$pageTitle = 'Cart'; $showCart = true; $scripts = ['cart.js'];
include '../includes/header.php';

$cart = $_SESSION['cart'] ?? []; $items = [];
if ($cart) {
    $in = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.product_id IN ($in)");
    $stmt->execute(array_keys($cart));
    $items = $stmt->fetchAll();
}
$user = ['full_name' => '', 'phone' => ''];
if (is_logged_in()) { $u = $pdo->prepare('SELECT full_name, phone FROM users WHERE user_id=?'); $u->execute([$_SESSION['user_id']]); $user = $u->fetch() ?: $user; }
?>
<?= back_button(BASE_URL . 'pages/shop.php') ?>
<h1 class="page-title">Shopping Cart</h1>
<?php if (!$items): ?>
  <div class="box center"><p>Your cart is empty.</p><p style="margin-top:.8rem"><a class="btn" href="shop.php">Browse the Store</a></p></div>
<?php else: ?>
<div id="cartErrors" class="alert alert-error" style="display:none"></div>
<form id="removeForm" method="post" action="<?= BASE_URL ?>actions/cart_update.php"></form>
<form id="cartForm" method="post" action="<?= BASE_URL ?>actions/cart_update.php" novalidate>
<div class="two-col">
  <div>
    <div class="table-wrap"><table class="zebra">
      <tr><th></th><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
      <?php foreach ($items as $i): $q = $cart[$i['product_id']]; ?>
        <tr class="cart-row" data-price="<?= e($i['price']) ?>">
          <td><img class="cart-thumb" src="<?= img('products/' . $i['image'], 'categories/' . slug($i['cat'])) ?>" alt=""></td>
          <td><a href="product.php?id=<?= (int)$i['product_id'] ?>"><?= e($i['name']) ?></a></td>
          <td><?= peso($i['price']) ?></td>
          <td><div class="qty-control"><button type="button" data-step="-1">&minus;</button>
            <input type="number" class="qty" name="qty[<?= (int)$i['product_id'] ?>]" value="<?= (int)$q ?>" min="1" max="<?= max(1, (int)$i['stock']) ?>">
            <button type="button" data-step="1">+</button></div></td>
          <td class="sub"><?= peso($q * $i['price']) ?></td>
          <td><button class="btn btn-danger btn-sm" type="submit" form="removeForm" name="remove" value="<?= (int)$i['product_id'] ?>" data-confirm="Remove this item from your cart?">Delete</button></td>
        </tr>
      <?php endforeach; ?>
    </table></div>
    <h2 class="plain">Pickup or Delivery</h2>
    <div class="form wide">
      <div class="form-grid">
        <div><label for="contact_name">Full name</label><span class="hint">The name of the person receiving the order.</span><input type="text" id="contact_name" name="contact_name" value="<?= e($user['full_name']) ?>"></div>
        <div><label for="contact_phone">Phone number</label><span class="hint">Format: 09XXXXXXXXX, so we can reach you.</span><input type="text" id="contact_phone" name="contact_phone" value="<?= e($user['phone']) ?>"></div>
      </div>
      <label for="fulfillment">Receive your order by</label>
      <span class="hint">Pickup is free at the lab. Delivery goes to the address you enter.</span>
      <select id="fulfillment" name="fulfillment"><option>Pickup</option><option>Delivery</option></select>
      <div id="addressBox" style="display:none"><label for="delivery_address">Delivery address</label><span class="hint">House or unit number, street, barangay, city.</span><input type="text" id="delivery_address" name="delivery_address"></div>
    </div>
  </div>
  <div class="side"><div class="side-box">
    <h3>Total breakdown</h3>
    <div class="summary" id="cartBreakdown"></div>
    <button class="btn btn-outline" type="submit" style="width:100%;margin-bottom:.6rem">Update Cart</button>
    <button class="btn" type="submit" name="go" value="1" style="width:100%">Proceed to Payment</button>
  </div></div>
</div>
</form>
<?php endif;
include '../includes/footer.php'; ?>