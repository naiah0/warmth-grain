<?php
require_once '../config/db.php';
$pageTitle = 'Cart';
$scripts = ['cart.js'];
include '../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$items = []; $total = 0;
if ($cart) {
    $in = implode(',', array_fill(0, count($cart), '?'));
    $stmt = $pdo->prepare("SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.product_id IN ($in)");
    $stmt->execute(array_keys($cart));
    $items = $stmt->fetchAll();
}
?>
<h1>Shopping Cart</h1>
<?php if (!$items): ?>
  <p>Your cart is empty. <a href="shop.php">Browse the store</a>.</p>
<?php else: ?>
<form id="removeForm" method="post" action="<?= BASE_URL ?>actions/cart_update.php"></form>
<form method="post" action="<?= BASE_URL ?>actions/cart_update.php">
  <div class="table-wrap"><table>
    <tr><th></th><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
    <?php foreach ($items as $i):
      $q = $cart[$i['product_id']]; $sub = $q * $i['price']; $total += $sub; ?>
      <tr class="cart-row" data-price="<?= e($i['price']) ?>">
        <td><img class="cart-thumb" src="<?= img('products/' . $i['image'], 'categories/' . slug($i['cat']) . '.jpg') ?>" alt=""></td>
        <td><a href="product.php?id=<?= (int)$i['product_id'] ?>"><?= e($i['name']) ?></a></td>
        <td><?= peso($i['price']) ?></td>
        <td><div class="qty-control">
          <button type="button" data-step="-1">&minus;</button>
          <input type="number" class="qty" name="qty[<?= (int)$i['product_id'] ?>]" value="<?= (int)$q ?>" min="1" max="<?= max(1, (int)$i['stock']) ?>">
          <button type="button" data-step="1">+</button>
        </div></td>
        <td class="sub"><?= peso($sub) ?></td>
        <td><button class="btn btn-danger" type="submit" form="removeForm" name="remove" value="<?= (int)$i['product_id'] ?>" data-confirm="Remove this item from your cart?">Remove</button></td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <h2>Total: <span id="cartTotal"><?= peso($total) ?></span></h2>
  <p class="muted small">We accept GCash, Maya, debit/credit card and cash on pickup. You choose at checkout.</p>
  <a class="btn btn-outline" href="shop.php">Continue Shopping</a>
  <button class="btn btn-outline" type="submit">Update Cart</button>
  <button class="btn" type="submit" name="go" value="checkout">Proceed to Checkout</button>
</form>
<?php endif;
include '../includes/footer.php'; ?>