<?php
require_once '../config/db.php';
$stmt = $pdo->prepare('SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.product_id=? AND p.is_active=1');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$p = $stmt->fetch();
$pageTitle = $p['name'] ?? 'Product not found'; $showCart = true;
include '../includes/header.php';

if (!$p): ?>
  <?= back_button(BASE_URL . 'pages/shop.php') ?><h1 class="page-title">Product not found</h1>
<?php else:
  $rel = $pdo->prepare('SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.category_id=? AND p.product_id<>? AND p.is_active=1 ORDER BY RAND() LIMIT 4');
  $rel->execute([$p['category_id'], $p['product_id']]);
  $out = $p['stock'] < 1;
?>
  <?= back_button(BASE_URL . 'pages/shop.php?cat=' . (int)$p['category_id']) ?>
  <div class="pd">
    <div class="pd-img"><img src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat'])) ?>" alt="<?= e($p['name']) ?>"></div>
    <div class="pd-info">
      <small><?= e($p['cat']) ?></small>
      <h1><?= e($p['name']) ?></h1>
      <span class="price-box"><?= peso($p['price']) ?></span>
      <dl class="spec">
        <div><dt>Description</dt><dd><?= nl2br(e($p['description'])) ?></dd></div>
        <div><dt>Purpose</dt><dd><?= e($p['purpose'] ?: 'See description') ?></dd></div>
        <div><dt>Applicable to</dt><dd><?= e($p['applicable_to'] ?: 'General use') ?></dd></div>
        <div><dt>Availability</dt><dd><?= $out ? '<strong>Out of stock</strong>' : '<strong>In stock</strong> (' . (int)$p['stock'] . ' left)' ?></dd></div>
      </dl>
      <?php if (!$out): ?>
        <div class="buy-row">
          <form method="post" action="<?= BASE_URL ?>actions/cart_add.php">
            <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
            <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
            <div class="qty-control">
              <button type="button" data-step="-1" aria-label="Less">&minus;</button>
              <input type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>">
              <button type="button" data-step="1" aria-label="More">+</button>
            </div>
            <button class="btn" type="submit"><?= icon('cart', 18) ?> Add to Cart</button>
          </form>
        </div>
      <?php else: ?><p class="alert alert-error">This product is currently unavailable.</p><?php endif; ?>
    </div>
  </div>
  <h2>You may also like</h2>
  <div class="pgrid"><?php foreach ($rel->fetchAll() as $r) echo product_card($r); ?></div>
<?php endif;
include '../includes/footer.php'; ?>