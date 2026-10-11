<?php
require_once '../config/db.php';
$pageTitle = 'Repair'; $hasBanner = true;
include '../includes/header.php';
$rs = $pdo->query('SELECT * FROM repair_services WHERE is_active=1 ORDER BY repair_service_id')->fetchAll();
echo banner('Repair Services', 'Bring us your film camera. We inspect it, fix it and keep you updated on every step.', ['banners/repair', 'services/camera_repair'],
  '<a class="btn" href="' . BASE_URL . 'pages/book-repair.php">Book Repair</a>');
?>
<div style="display:grid;gap:1.1rem">
  <?php foreach ($rs as $r): ?>
    <div class="rcard">
      <div><h3><?= e($r['name']) ?></h3><p class="small"><?= e($r['description']) ?></p></div>
      <div><strong>Inclusions</strong><ul class="small"><?php foreach (array_filter(explode('|', (string)$r['inclusions'])) as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul></div>
      <div><span class="pill"><?= peso($r['price']) ?></span><span class="pill alt">Turnaround: <?= e($r['turnaround']) ?></span></div>
    </div>
  <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>