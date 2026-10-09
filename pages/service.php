<?php
require_once '../config/db.php';
$stmt = $pdo->prepare('SELECT * FROM services WHERE service_id=? AND is_active=1');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$s = $stmt->fetch();
$pageTitle = $s['name'] ?? 'Service not found';
include '../includes/header.php';

if (!$s): ?>
  <h1>Service not found</h1><p><a href="services.php">Back to Film Processing</a></p>
<?php else:
  $others = $pdo->prepare('SELECT * FROM services WHERE service_type=? AND service_id<>? AND is_active=1 LIMIT 3');
  $others->execute([$s['service_type'], $s['service_id']]);
?>
  <p><a href="services.php">&larr; Film Processing</a> / <a href="services.php#<?= e(slug($s['service_type'])) ?>"><?= e($s['service_type']) ?></a></p>
  <div class="product-detail">
    <img src="<?= img('services/' . slug($s['service_type']) . '.jpg') ?>" alt="<?= e($s['name']) ?>">
    <div>
      <h1><?= e($s['name']) ?></h1>
      <p><?= e($s['description']) ?></p>
      <p class="price" style="font-size:1.4rem"><?= peso($s['price_per_roll']) ?> per roll</p>
      <dl class="spec">
        <dt>What's included</dt>
        <dd><ul><?php foreach (array_filter(explode('|', (string)$s['inclusions'])) as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul></dd>
        <dt>Turnaround</dt><dd><?= e($s['turnaround'] ?: 'TBA') ?></dd>
      </dl>
      <a class="btn" href="services.php?book=<?= (int)$s['service_id'] ?>">Book this service</a>
    </div>
  </div>
  <h2>Other <?= e($s['service_type']) ?> options</h2>
  <div class="grid">
    <?php foreach ($others->fetchAll() as $o): ?>
      <a class="card card-link" href="service.php?id=<?= (int)$o['service_id'] ?>">
        <h3><?= e($o['name']) ?></h3><p class="price"><?= peso($o['price_per_roll']) ?> / roll</p>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif;
include '../includes/footer.php'; ?>