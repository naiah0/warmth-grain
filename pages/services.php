<?php
require_once '../config/db.php';
$pageTitle = 'Film Processing'; $hasBanner = true;
include '../includes/header.php';

$rows = $pdo->query("SELECT * FROM services WHERE is_active=1 ORDER BY FIELD(service_type,'Developing','Scanning','Printing'), service_id")->fetchAll();
$grouped = [];
foreach ($rows as $r) $grouped[$r['service_type']][] = $r;
$packs = $pdo->query("SELECT pk.*, d.name dn, d.price_per_roll dp, s.name sn, s.price_per_roll sp, pr.name pn, pr.price_per_roll prp
  FROM packages pk JOIN services d ON d.service_id=pk.developing_id LEFT JOIN services s ON s.service_id=pk.scanning_id LEFT JOIN services pr ON pr.service_id=pk.printing_id ORDER BY pk.package_id LIMIT 4")->fetchAll();

echo banner('Film Processing', 'Developing, scanning and printing for every kind of film. Pick a usual package or build your own order.',
  ['banners/film-processing', 'services/developing'], '<a class="btn" href="' . BASE_URL . 'pages/book-service.php">Book a Service</a>');
?>
<h2>Packages</h2>
<p class="lead">Our usual combinations, priced per roll.</p>
<div class="pack-grid">
  <?php foreach ($packs as $k): $per = $k['dp'] + ($k['sp'] ?? 0) + ($k['prp'] ?? 0); ?>
    <div class="card">
      <h3><?= e($k['name']) ?></h3>
      <p class="small"><?= e($k['description']) ?></p>
      <ul><li><?= e($k['dn']) ?></li><?php if ($k['sn']): ?><li><?= e($k['sn']) ?></li><?php endif; ?><?php if ($k['pn']): ?><li><?= e($k['pn']) ?></li><?php endif; ?></ul>
      <p class="small"><?= e($k['output_option']) ?></p>
      <p class="price"><?= peso($per) ?> / roll</p>
    </div>
  <?php endforeach; ?>
</div>

<h2>Our Processes</h2>
<div data-tabs>
  <div class="tabs">
    <?php foreach (array_keys($grouped) as $type): ?><button type="button" class="tab" data-tab="<?= e(slug($type)) ?>"><?= e($type) ?></button><?php endforeach; ?>
  </div>
  <?php foreach ($grouped as $type => $list): ?>
    <div class="tab-panel" id="<?= e(slug($type)) ?>" data-panel="<?= e(slug($type)) ?>">
      <div class="svc-grid">
        <?php foreach ($list as $s): ?>
          <div class="svc-card">
            <h3><?= e($s['name']) ?></h3>
            <p class="small"><?= e($s['description']) ?></p>
            <ul><?php foreach (array_filter(explode('|', (string)$s['inclusions'])) as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
            <div class="svc-foot"><span class="price"><?= peso($s['price_per_roll']) ?> / roll</span><span class="small">Turnaround: <?= e($s['turnaround'] ?: 'TBA') ?></span></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>