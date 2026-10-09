<?php
require_once '../config/db.php';
$pageTitle = 'Store';
$scripts = ['shop.js'];
include '../includes/header.php';

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$sort = $_GET['sort'] ?? 'name';
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 12;

$sorts = ['name' => 'p.name ASC', 'low' => 'p.price ASC', 'high' => 'p.price DESC', 'new' => 'p.created_at DESC, p.product_id DESC'];
$orderBy = $sorts[$sort] ?? $sorts['name'];

$where = ['p.is_active=1']; $params = [];
if ($q !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ? OR p.applicable_to LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if ($cat) { $where[] = 'p.category_id=?'; $params[] = $cat; }
$whereSql = implode(' AND ', $where);

$cnt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $whereSql");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$offset = ($page - 1) * $per;

$stmt = $pdo->prepare("SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE $whereSql ORDER BY $orderBy LIMIT $per OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();
$cats = $pdo->query('SELECT * FROM categories ORDER BY category_id')->fetchAll();

function shop_url($page) {
    global $q, $cat, $sort;
    return 'shop.php?' . http_build_query(['q' => $q ?: null, 'cat' => $cat ?: null, 'sort' => $sort !== 'name' ? $sort : null, 'page' => $page > 1 ? $page : null]);
}
?>
<h1>Store</h1>
<form class="filters" id="shopForm" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products...">
  <select name="cat">
    <option value="0">All Categories</option>
    <?php foreach ($cats as $c): ?><option value="<?= (int)$c['category_id'] ?>" <?= $cat == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
  </select>
  <select name="sort">
    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name A-Z</option>
    <option value="low" <?= $sort === 'low' ? 'selected' : '' ?>>Price: low to high</option>
    <option value="high" <?= $sort === 'high' ? 'selected' : '' ?>>Price: high to low</option>
    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Newest</option>
  </select>
  <button class="btn" type="submit">Search</button>
</form>
<p class="muted"><?= $total ?> product<?= $total === 1 ? '' : 's' ?> found</p>

<div class="grid">
  <?php foreach ($products as $p): ?>
    <a class="card card-link" href="product.php?id=<?= (int)$p['product_id'] ?>">
      <img src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat']) . '.jpg') ?>" alt="<?= e($p['name']) ?>" loading="lazy">
      <small class="muted"><?= e($p['cat']) ?></small>
      <h3><?= e($p['name']) ?></h3>
      <p class="price"><?= peso($p['price']) ?></p>
      <small><?= $p['stock'] > 0 ? 'In stock' : 'Out of stock' ?></small>
    </a>
  <?php endforeach; ?>
</div>
<?php if (!$products): ?><p>No products match your search. <a href="shop.php">Clear filters</a></p><?php endif; ?>

<?php if ($pages > 1): ?>
<div class="pagination">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i === $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="<?= e(shop_url($i)) ?>"><?= $i ?></a><?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>