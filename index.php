<?php
require_once 'config/db.php';
$pageTitle = 'Home';
include 'includes/header.php';

$products = $pdo->query("SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE p.is_active=1 AND p.stock>0 ORDER BY RAND() LIMIT 4")->fetchAll();
$events = $pdo->query("SELECT * FROM events WHERE status='Published' AND event_date >= NOW() ORDER BY event_date LIMIT 3")->fetchAll();
$tiles = [
  ['Developing', 'developing', 'pages/services.php#developing', 'Color, black and white, slide and cine film.'],
  ['Scanning',   'scanning',   'pages/services.php#scanning',   'From quick previews to RAW TIFF scans.'],
  ['Printing',   'printing',   'pages/services.php#printing',   'Prints, enlargements and canvas.'],
  ['Camera Repair', 'repair',  'pages/repair.php',              'Seals, shutters, lenses and full service.'],
];
?>

<section class="hero">
  <h1>Your film, from roll to print.</h1>
  <p>Developing, scanning, printing, camera repair and a curated analog shop, all in one place.</p>
  <a class="btn" href="<?= BASE_URL ?>pages/services.php">Process Your Film</a>
  <a class="btn btn-outline" style="color:#F4EBDD;border-color:#F4EBDD" href="<?= BASE_URL ?>pages/shop.php">Shop Now</a>
</section>

<h2>Our Services</h2>
<div class="grid">
  <?php foreach ($tiles as $t): ?>
    <a class="photo-card" href="<?= BASE_URL . $t[2] ?>">
      <img src="<?= img('services/' . $t[1] . '.jpg') ?>" alt="<?= e($t[0]) ?>">
      <div class="overlay"><h3><?= e($t[0]) ?></h3><p><?= e($t[3]) ?></p></div>
    </a>
  <?php endforeach; ?>
</div>

<h2>Featured Products</h2>
<div class="grid">
  <?php foreach ($products as $p): ?>
    <a class="card card-link" href="<?= BASE_URL ?>pages/product.php?id=<?= (int)$p['product_id'] ?>">
      <img src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat']) . '.jpg') ?>" alt="<?= e($p['name']) ?>">
      <h3><?= e($p['name']) ?></h3>
      <p class="price"><?= peso($p['price']) ?></p>
    </a>
  <?php endforeach; ?>
</div>

<h2>Upcoming Events</h2>
<div class="grid">
  <?php foreach ($events as $ev): ?>
    <a class="card card-link" href="<?= BASE_URL ?>pages/events.php">
      <img src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
      <h3><?= e($ev['title']) ?></h3>
      <p><?= date('M d, Y g:i A', strtotime($ev['event_date'])) ?><br><?= e($ev['location']) ?></p>
    </a>
  <?php endforeach; if (!$events) echo '<p>No upcoming events yet.</p>'; ?>
</div>
<?php include 'includes/footer.php'; ?>