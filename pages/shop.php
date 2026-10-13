<?php
require_once '../config/db.php';
$pageTitle = 'Store'; $showCart = true; $hasBanner = true; $scripts = ['shop.js'];
include '../includes/header.php';

$q = trim($_GET['q'] ?? ''); $cat = (int)($_GET['cat'] ?? 0); $stock = $_GET['stock'] ?? '';
$min = $_GET['min'] ?? ''; $max = $_GET['max'] ?? ''; $sort = $_GET['sort'] ?? 'name';
$sorts = ['name' => 'p.name ASC', 'low' => 'p.price ASC', 'high' => 'p.price DESC', 'new' => 'p.product_id DESC'];
$orderBy = $sorts[$sort] ?? $sorts['name'];

$where = ['p.is_active=1']; $params = [];
if ($q !== '') { $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.applicable_to LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%"); }
if ($cat) { $where[] = 'p.category_id=?'; $params[] = $cat; }
if ($stock === 'in') $where[] = 'p.stock>0';
if ($stock === 'out') $where[] = 'p.stock=0';
if (is_numeric($min)) { $where[] = 'p.price>=?'; $params[] = $min; }
if (is_numeric($max)) { $where[] = 'p.price<=?'; $params[] = $max; }
$stmt = $pdo->prepare('SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE ' . implode(' AND ', $where)  . " ORDER BY (p.stock < 1), $orderBy");
$stmt->execute($params);
$products = $stmt->fetchAll();
$cats = $pdo->query('SELECT * FROM categories ORDER BY category_id')->fetchAll();

function shop_url($over = []) {
    $base = ['q' => $_GET['q'] ?? '', 'cat' => $_GET['cat'] ?? '', 'stock' => $_GET['stock'] ?? '', 'min' => $_GET['min'] ?? '', 'max' => $_GET['max'] ?? '', 'sort' => $_GET['sort'] ?? ''];
    return 'shop.php?' . http_build_query(array_filter(array_merge($base, $over), fn($v) => $v !== '' && $v !== null && $v !== 0));
}
echo banner('Store', 'Browse cameras, film stocks, accessories, and other equipment.', ['banners/store', 'categories/cameras']);
?>

<div class="box" style="margin-bottom: 30px;">
  <p style="text-align:justify;">Welcome to the Warmth&Grain shop, where you can find cameras, film products, analog accessories, vintage equipment, and merchandise selected for people who appreciate the analog process. Whether you are looking for your first film camera, trying a new film stock, replacing an accessory, or simply adding something to your collection, browse our available products below.</p>
</div>

<div class="filter-box">
  <form class="filters" id="shopForm" method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products...">
    <select name="stock"><option value="">All availability</option><option value="in" <?= $stock === 'in' ? 'selected' : '' ?>>In stock</option><option value="out" <?= $stock === 'out' ? 'selected' : '' ?>>Out of stock</option></select>
    <input type="number" name="min" value="<?= e($min) ?>" min="0" placeholder="Min price">
    <input type="number" name="max" value="<?= e($max) ?>" min="0" placeholder="Max price">
    <select name="sort">
      <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option><option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: low to high</option>
      <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: high to low</option><option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest</option>
    </select>
    <input type="hidden" name="cat" value="<?= (int)$cat ?>">
  </form>
  <div class="chips-fit">
    <a href="<?= e(shop_url(['cat' => ''])) ?>" class="<?= !$cat ? 'active' : '' ?>">All</a>
    <?php foreach ($cats as $c): ?><a href="<?= e(shop_url(['cat' => $c['category_id']])) ?>" class="<?= $cat == $c['category_id'] ? 'active' : '' ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
    <a class="clear" href="shop.php" title="Clear filters" aria-label="Clear filters"><?= icon('refresh', 18) ?></a>
  </div>
</div>
<p class="found"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?> found</p>

<div class="pgrid"><?php foreach ($products as $p) echo product_card($p); ?></div>
<?php if (!$products): ?><p>No products match your search. <a href="shop.php">Clear filters</a></p><?php endif; ?>
<?php include '../includes/footer.php'; ?>