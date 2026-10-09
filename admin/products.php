<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['delete_id'] ?? 0);
    try {
        $pdo->prepare('DELETE FROM products WHERE product_id=?')->execute([$id]);
        set_flash('success', 'Product deleted.');
    } catch (PDOException $e) {
        $pdo->prepare('UPDATE products SET is_active=0 WHERE product_id=?')->execute([$id]);
        set_flash('success', 'Product is part of past orders, so it was archived instead.');
    }
    header('Location: products.php');
    exit;
}

$pageTitle = 'Manage Products';
include '../includes/header.php';
include __DIR__ . '/_nav.php';

$q = trim($_GET['q'] ?? ''); $cat = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1)); $per = 20;
$where = ['1=1']; $params = [];
if ($q !== '') { $where[] = 'p.name LIKE ?'; $params[] = "%$q%"; }
if ($cat) { $where[] = 'p.category_id=?'; $params[] = $cat; }
$w = implode(' AND ', $where);
$cnt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $w"); $cnt->execute($params);
$pages = max(1, (int)ceil($cnt->fetchColumn() / $per)); $page = min($page, $pages); $off = ($page - 1) * $per;
$stmt = $pdo->prepare("SELECT p.*, c.name AS cat FROM products p JOIN categories c ON c.category_id=p.category_id WHERE $w ORDER BY p.product_id DESC LIMIT $per OFFSET $off");
$stmt->execute($params); $rows = $stmt->fetchAll();
$cats = $pdo->query('SELECT * FROM categories ORDER BY category_id')->fetchAll();
?>
<h1>Products</h1>
<p><a class="btn" href="product_form.php">Add Product</a></p>
<form class="filters" method="get" style="grid-template-columns:2fr 1fr auto">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search by name...">
  <select name="cat"><option value="0">All Categories</option>
    <?php foreach ($cats as $c): ?><option value="<?= (int)$c['category_id'] ?>" <?= $cat == $c['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <button class="btn" type="submit">Filter</button>
</form>
<div class="table-wrap"><table>
  <tr><th></th><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Active</th><th></th></tr>
  <?php foreach ($rows as $p): ?>
    <tr>
      <td><img class="thumb" src="<?= img('products/' . $p['image'], 'categories/' . slug($p['cat']) . '.jpg') ?>" alt=""></td>
      <td><?= (int)$p['product_id'] ?></td><td><?= e($p['name']) ?></td><td><?= e($p['cat']) ?></td>
      <td><?= peso($p['price']) ?></td><td><?= (int)$p['stock'] ?></td><td><?= $p['is_active'] ? 'Yes' : 'No' ?></td>
      <td>
        <a class="btn" href="product_form.php?id=<?= (int)$p['product_id'] ?>">Edit</a>
        <form method="post" style="display:inline">
          <input type="hidden" name="delete_id" value="<?= (int)$p['product_id'] ?>">
          <button class="btn btn-danger" data-confirm="Delete this product?">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; if (!$rows) echo '<tr><td colspan="8">No products found.</td></tr>'; ?>
</table></div>
<?php if ($pages > 1): ?><div class="pagination">
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i === $page): ?><span class="current"><?= $i ?></span><?php else: ?><a href="?<?= e(http_build_query(['q' => $q ?: null, 'cat' => $cat ?: null, 'page' => $i])) ?>"><?= $i ?></a><?php endif; ?>
  <?php endfor; ?></div><?php endif; ?>
<?php include '../includes/footer.php'; ?>