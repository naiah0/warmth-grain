<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['delete_id'] ?? 0);
    try { $pdo->prepare('DELETE FROM products WHERE product_id=?')->execute([$id]); set_flash('success', 'Product deleted.'); }
    catch (PDOException $e) { $pdo->prepare('UPDATE products SET is_active=0 WHERE product_id=?')->execute([$id]); set_flash('success', 'Product is part of past orders, so it was archived instead.'); }
    header('Location: products.php'); exit;
}
$pageTitle = 'Manage Products';
include '../includes/header.php';

$q = trim($_GET['q'] ?? ''); $cat = (int)($_GET['cat'] ?? 0); $st = $_GET['st'] ?? '';
$where = ['1=1']; $params = [];
if ($q !== '') { $where[] = 'p.name LIKE ?'; $params[] = "%$q%"; }
if ($cat) { $where[] = 'p.category_id=?'; $params[] = $cat; }
if ($st === 'out') $where[] = 'p.stock=0'; if ($st === 'low') $where[] = 'p.stock BETWEEN 1 AND 5'; if ($st === 'archived') $where[] = 'p.is_active=0';
$stmt = $pdo->prepare('SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE ' . implode(' AND ', $where) . ' ORDER BY p.category_id, p.name');
$stmt->execute($params); $rows = $stmt->fetchAll();
$cats = $pdo->query('SELECT * FROM categories ORDER BY category_id')->fetchAll();
?>
<h1 class="page-title">Products</h1>
<div class="filter-box" style="margin-bottom:1rem">
  <form class="filters" method="get" style="grid-template-columns:2fr 1fr 1fr auto auto auto">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name...">
    <select name="cat"><option value="0">All categories</option><?php foreach ($cats as $c): ?><option value="<?= (int)$c['category_id'] ?>" <?= $cat == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
    <select name="st"><option value="">Any stock</option><option value="low" <?= $st === 'low' ? 'selected' : '' ?>>Low (1-5)</option><option value="out" <?= $st === 'out' ? 'selected' : '' ?>>Out of stock</option><option value="archived" <?= $st === 'archived' ? 'selected' : '' ?>>Archived</option></select>
    <button class="btn" type="submit">Filter</button><a class="btn btn-outline" href="products.php">Clear</a><a class="btn" href="product_form.php">Add Product</a>
  </form>
</div>
<p class="found"><?= count($rows) ?> product<?= count($rows) === 1 ? '' : 's' ?></p>
<div class="table-wrap"><table class="zebra">
  <tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($rows as $p): ?>
    <tr>
      <td><img class="thumb" src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat'])) ?>" alt=""></td>
      <td><strong><?= e($p['name']) ?></strong></td><td><?= e($p['cat']) ?></td><td><?= peso($p['price']) ?></td><td><?= (int)$p['stock'] ?></td>
      <td><?= status_badge($p['is_active'] ? 'Active' : 'Disabled') ?></td>
      <td><div class="actions"><a class="btn btn-sm" href="product_form.php?id=<?= (int)$p['product_id'] ?>">Edit</a>
        <form method="post"><input type="hidden" name="delete_id" value="<?= (int)$p['product_id'] ?>"><button class="btn btn-danger btn-sm" data-confirm="Delete this product?">Delete</button></form></div></td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="7" class="center">No products found.</td></tr>'; ?>
</table></div>
<?php include '../includes/footer.php'; ?>