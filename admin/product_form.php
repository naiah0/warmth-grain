<?php
require_once '../includes/auth.php';
require_admin();
require_once '../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$cats = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['product_id'] ?? 0); $cat = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? ''); $desc = trim($_POST['description'] ?? ''); $purpose = trim($_POST['purpose'] ?? ''); $for = trim($_POST['applicable_to'] ?? '');
    $price = $_POST['price'] ?? ''; $stock = $_POST['stock'] ?? '';
    $image = trim($_POST['current_image'] ?? '') ?: 'placeholder'; $active = isset($_POST['is_active']) ? 1 : 0; $errors = [];
    if ($name === '') $errors[] = 'Name is required.';
    if (!$cat) $errors[] = 'Choose a category.';
    if (!is_numeric($price) || $price < 0) $errors[] = 'Price cannot be negative.';
    if (!ctype_digit((string)$stock)) $errors[] = 'Stock must be a whole number, 0 or more.';
    try { if ($up = save_upload('photo', 'products')) $image = $up; } catch (Exception $ex) { $errors[] = $ex->getMessage(); }
    if ($errors) { set_flash('error', $errors); header('Location: product_form.php' . ($id ? "?id=$id" : '')); exit; }
    if ($id) {
        $pdo->prepare('UPDATE products SET category_id=?,name=?,description=?,purpose=?,applicable_to=?,price=?,stock=?,image=?,is_active=? WHERE product_id=?')->execute([$cat, $name, $desc, $purpose, $for, $price, $stock, $image, $active, $id]);
        set_flash('success', 'Product updated.');
    } else {
        $pdo->prepare('INSERT INTO products (category_id,name,description,purpose,applicable_to,price,stock,image,is_active) VALUES (?,?,?,?,?,?,?,?,?)')->execute([$cat, $name, $desc, $purpose, $for, $price, $stock, $image, $active]);
        set_flash('success', 'Product added.');
    }
    header('Location: products.php'); exit;
}
$p = ['product_id' => 0, 'category_id' => 0, 'name' => '', 'description' => '', 'purpose' => '', 'applicable_to' => '', 'price' => '', 'stock' => 0, 'image' => '', 'is_active' => 1];
if ($id) { $s = $pdo->prepare('SELECT * FROM products WHERE product_id=?'); $s->execute([$id]); $p = $s->fetch() ?: $p; }
$pageTitle = $id ? 'Edit Product' : 'Add Product';
include '../includes/header.php';
?>
<?= back_button(BASE_URL . 'admin/products.php') ?>
<form class="form-h" method="post" enctype="multipart/form-data">
  <div class="form-head"><h2 class="plain"><?= e($pageTitle) ?></h2>
    <div class="actions"><button class="btn" type="submit">Save</button><a class="btn btn-outline" href="products.php">Cancel</a></div></div>
  <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>"><input type="hidden" name="current_image" value="<?= e($p['image']) ?>">
  <div class="fields">
    <div class="span2"><label for="name">Name</label><input type="text" id="name" name="name" value="<?= e($p['name']) ?>" required></div>
    <div><label for="category_id">Category</label><select id="category_id" name="category_id" required><option value="">-- Select --</option><?php foreach ($cats as $c): ?><option value="<?= (int)$c['category_id'] ?>" <?= $c['category_id'] == $p['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
    <div><label>&nbsp;</label><label class="check" style="display:flex;gap:.5rem;align-items:center;font-weight:400"><input type="checkbox" name="is_active" style="width:auto" <?= $p['is_active'] ? 'checked' : '' ?>> Active in store</label></div>
    <div><label for="price">Price (₱)</label><input type="number" id="price" name="price" step="0.01" min="0" value="<?= e($p['price']) ?>" required></div>
    <div><label for="stock">Stock</label><input type="number" id="stock" name="stock" min="0" value="<?= e($p['stock']) ?>" required></div>
    <div class="span2"><label for="photo">Photo (JPG, PNG or WEBP, max 2 MB)</label><input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp"></div>
    <div class="span2"><label for="purpose">Purpose</label><input type="text" id="purpose" name="purpose" value="<?= e($p['purpose']) ?>"></div>
    <div class="span2"><label for="applicable_to">Applicable to</label><input type="text" id="applicable_to" name="applicable_to" value="<?= e($p['applicable_to']) ?>"></div>
    <div class="span4"><label for="description">Description</label><textarea id="description" name="description" rows="3"><?= e($p['description']) ?></textarea></div>
  </div>
</form>
<?php include '../includes/footer.php'; ?>