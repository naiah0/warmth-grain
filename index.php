<?php
require_once 'config/db.php';
$pageTitle = 'Home'; $showCart = true; $hasBanner = true;
include 'includes/header.php';

$products = $pdo->query("SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.is_active=1 AND p.stock>0 ORDER BY RAND() LIMIT 6")->fetchAll();
$events = $pdo->query("SELECT * FROM events WHERE status='Published' AND event_date >= NOW() ORDER BY event_date LIMIT 3")->fetchAll();
$slides = array_values(array_filter(array_map(fn($i) => find_img("hero/hero-$i"), [1, 2, 3])));
$tiles = [
  ['Developing', 'services/developing', 'pages/services.php#developing', 'Color, black and white, slide and cine film.'],
  ['Scanning', 'services/scanning', 'pages/services.php#scanning', 'From quick previews to RAW TIFF scans.'],
  ['Printing', 'services/printing', 'pages/services.php#printing', 'Prints, enlargements and canvas.'],
  ['Camera Repair', 'services/camera_repair', 'pages/repair.php', 'Seals, shutters, lenses and full service.'],
];
?>
<section class="hero">
  <?php foreach ($slides as $i => $s): ?><div class="hero-slide <?= $i === 0 ? 'active' : '' ?>" style="background-image:url('<?= e(BASE_URL . 'assets/img/' . $s) ?>')"></div><?php endforeach; ?>
  <?php if (is_logged_in()): ?><div class="greet">Hi, <strong><?= e($_SESSION['name']) ?></strong></div><?php endif; ?>
  <div class="hero-inner">
    <h1>Your film, from roll to print.</h1>
    <p>Developing, scanning, printing, camera repair and a curated analog shop, all in one place.</p>
    <a class="btn" href="<?= BASE_URL ?>pages/services.php">Process Your Film</a>
    <a class="btn btn-outline" href="<?= BASE_URL ?>pages/shop.php">Shop Now</a>
  </div>
</section>

<h2>Our Services</h2>
<div class="tiles-4">
  <?php foreach ($tiles as $t): ?>
    <a class="tile tall" href="<?= BASE_URL . $t[2] ?>">
      <img src="<?= img($t[1]) ?>" alt="<?= e($t[0]) ?>">
      <div class="tile-text"><h3><?= e($t[0]) ?></h3><p><?= e($t[3]) ?></p></div>
    </a>
  <?php endforeach; ?>
</div>

<h2>Featured Products</h2>
<div class="grid-wide">
  <?php foreach ($products as $p): ?>
    <a class="card card-wide card-link" href="<?= BASE_URL ?>pages/product.php?id=<?= (int)$p['product_id'] ?>">
      <div class="card-photo"><img src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat'])) ?>" alt="<?= e($p['name']) ?>"></div>
      <div class="card-body"><small><?= e($p['cat']) ?></small><h3><?= e($p['name']) ?></h3><p class="price"><?= peso($p['price']) ?></p><small><?= e($p['purpose']) ?></small></div>
    </a>
  <?php endforeach; if (!$products) echo '<p>No products yet. Run database/seed_products.php.</p>'; ?>
</div>

<h2>Upcoming Events</h2>
<div class="tiles-3">
  <?php foreach ($events as $ev): ?>
    <a class="tile sq" href="<?= BASE_URL ?>pages/event.php?id=<?= (int)$ev['event_id'] ?>">
      <img src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
      <div class="tile-text"><h3><?= e($ev['title']) ?></h3><p><?= date('M d, Y g:i A', strtotime($ev['event_date'])) ?><br><?= e($ev['location']) ?></p></div>
    </a>
  <?php endforeach; if (!$events) echo '<p>No upcoming events yet.</p>'; ?>
</div>
<?php include 'includes/footer.php'; ?>