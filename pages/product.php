<?php
require_once '../config/db.php';
$stmt = $pdo->prepare('SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.product_id=? AND p.is_active=1');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$p = $stmt->fetch();
$pageTitle = $p['name'] ?? 'Product not found';
include '../includes/header.php';

if (!$p): ?>
  <h1>Product not found</h1><p><a href="shop.php">Back to the store</a></p>
<?php else:
  $rel = $pdo->prepare('SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.category_id=? AND p.product_id<>? AND p.is_active=1 ORDER BY RAND() LIMIT 4');
  $rel->execute([$p['category_id'], $p['product_id']]);
?>
  <p><a href="shop.php">&larr; Store</a> / <a href="shop.php?cat=<?= (int)$p['category_id'] ?>"><?= e($p['cat']) ?></a></p>
  <div class="product-detail">
    <img src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat']) . '.jpg') ?>" alt="<?= e($p['name']) ?>">
    <div>
      <h1><?= e($p['name']) ?></h1>
      <p class="price" style="font-size:1.5rem"><?= peso($p['price']) ?></p>
      <dl class="spec">
        <dt>Description</dt><dd><?= nl2br(e($p['description'])) ?></dd>
        <dt>Purpose</dt><dd><?= e($p['purpose'] ?: 'See description') ?></dd>
        <dt>Applicable to</dt><dd><?= e($p['applicable_to'] ?: 'General use') ?></dd>
        <dt>Availability</dt><dd><?= $p['stock'] > 0 ? '<strong>In stock</strong> (' . (int)$p['stock'] . ' left)' : '<strong>Out of stock</strong>' ?></dd>
      </dl>
      <?php if ($p['stock'] > 0): ?>
        <form method="post" action="<?= BASE_URL ?>actions/cart_add.php">
          <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
          <div class="qty-control">
            <button type="button" data-step="-1">&minus;</button>
            <input type="number" name="qty" value="1" min="1" max="<?= (int)$p['stock'] ?>">
            <button type="button" data-step="1">+</button>
          </div>
          <button class="btn" type="submit">Add to Cart</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <h2>You may also like</h2>
  <div class="grid">
    <?php foreach ($rel->fetchAll() as $r): ?>
      <a class="card card-link" href="product.php?id=<?= (int)$r['product_id'] ?>">
        <img src="<?= img('products/' . $r['image'], 'categories/' . slug($r['cat']) . '.jpg') ?>" alt="<?= e($r['name']) ?>">
        <h3><?= e($r['name']) ?></h3><p class="price"><?= peso($r['price']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif;
include '../includes/footer.php'; ?>