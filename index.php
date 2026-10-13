<?php
require_once 'config/db.php';
$pageTitle = 'Home'; $showCart = true; $hasBanner = true;
include 'includes/header.php';
$notices = [];
if (is_logged_in()) {
    $nq = $pdo->prepare("SELECT message_id, subject FROM messages WHERE user_id=? AND status='Replied' AND customer_ack=0 ORDER BY replied_at DESC LIMIT 3");
    $nq->execute([$_SESSION['user_id']]);
    $notices = $nq->fetchAll();
}

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

<?php if ($notices): ?>
<div class="notice-stack">
  <?php foreach ($notices as $nt): ?>
  <div class="notice">
    <h3>Your inquiry has been answered</h3>
    <p>We replied to "<?= e($nt['subject']) ?>". <a href="<?= BASE_URL ?>pages/account/inquiries.php">Read the reply</a></p>
    <form class="notice-foot" method="post" action="<?= BASE_URL ?>actions/ack_inquiry.php" data-ack>
      <input type="hidden" name="id" value="<?= (int)$nt['message_id'] ?>">
      <span>Do you acknowledge this reply?</span>
      <button class="ack-btn" type="submit" title="Yes, I acknowledge" aria-label="Acknowledge reply"><?= icon('check', 18) ?></button>
    </form>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

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

<div class="box">
  <h1 style="margin-bottom: 10px; text-align: center;">Keep the Grain. Keep the Memories.</h1>
  <p style="text-align:justify;">Warmth&amp;Grain is your all-in-one home for analog photography. From developing your film and scanning your negatives to finding vintage cameras, repairing your favorite equipment, and meeting other film enthusiasts, we bring the different parts of the analog experience together in one place.</p>
  <p style="text-align:justify;">We believe that film photography is more than just taking a picture. It is about slowing down, being intentional with every shot, and keeping something physical from a moment that would otherwise only exist digitally. Whether you are shooting your first roll or have been using film for years, Warmth&amp;Grain is here to help you enjoy the process from start to finish.</p>
  </div>

<h2>Bring Your Film to Life</h2>
<div class="tiles-4">
  <?php foreach ($tiles as $t): ?>
    <a class="tile tall" href="<?= BASE_URL . $t[2] ?>">
      <img src="<?= img($t[1]) ?>" alt="<?= e($t[0]) ?>">
      <div class="tile-text"><h3><?= e($t[0]) ?></h3><p><?= e($t[3]) ?></p></div>
    </a>
  <?php endforeach; ?>
</div>

<h2>Find Your Next Piece of Analog Gear</h2>
<div class="grid-wide">
  <?php foreach ($products as $p): ?>
    <a class="card card-wide card-link" href="<?= BASE_URL ?>pages/product.php?id=<?= (int)$p['product_id'] ?>">
      <div class="card-photo"><img src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat'])) ?>" alt="<?= e($p['name']) ?>"></div>
      <div class="card-body"><small><?= e($p['cat']) ?></small><h3><?= e($p['name']) ?></h3><p class="price"><?= peso($p['price']) ?></p><small><?= e($p['purpose']) ?></small></div>
    </a>
  <?php endforeach; if (!$products) echo '<p>No products yet. Run database/seed_products.php.</p>'; ?>
</div>

<h2>Meet. Trade. Shoot. Repeat.</h2>
<div class="tiles-3">
  <?php foreach ($events as $ev): ?>
    <a class="tile sq" href="<?= BASE_URL ?>pages/event.php?id=<?= (int)$ev['event_id'] ?>">
      <img src="<?= img('events/' . $ev['image']) ?>" alt="<?= e($ev['title']) ?>">
      <div class="tile-text"><h3><?= e($ev['title']) ?></h3><p><?= date('M d, Y g:i A', strtotime($ev['event_date'])) ?><br><?= e($ev['location']) ?></p></div>
    </a>
  <?php endforeach; if (!$events) echo '<p>No upcoming events yet.</p>'; ?>
</div>

  <div class="box" style="margin-top: 30px;">
  <p style="text-align:justify;">We believe that film photography is more than just taking a picture. It is about slowing down, being intentional with every shot, and keeping something physical from a moment that would otherwise only exist digitally. Whether you are shooting your first roll or have been using film for years, Warmth&amp;Grain is here to help you enjoy the process from start to finish.</p>
  </div>
<?php include 'includes/footer.php'; ?>