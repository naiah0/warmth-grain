<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Home';
$showCart  = $showCart ?? false;
$hasBanner = $hasBanner ?? false;
$adminMode = in_admin_area() && is_admin();
$hdrCart   = array_sum($_SESSION['cart'] ?? []);
$hdrCur    = $_SERVER['SCRIPT_NAME'];
$hdrIs     = fn($n) => strpos($hdrCur, $n) !== false ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Warmth&amp;Grain</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="<?= $adminMode ? 'admin-body' : '' ?>">
<?php if ($adminMode):
  if (!isset($pdo)) require_once __DIR__ . '/../config/db.php';
  $hdrNew = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE status='New'")->fetchColumn();
  $admCur = basename($hdrCur); $admType = $_GET['type'] ?? '';
  $admCur = ['product_form.php' => 'products.php', 'event_registrations.php' => 'events.php', 'scan.php' => 'index.php'][$admCur] ?? $admCur;
  if ($admCur === 'records.php' && $admType === '') $admType = 'booking';
  $admLinks = [
    ['index.php', 'Dashboard', ''], ['records.php', 'Film Processing', 'booking'], ['records.php', 'Repairs', 'repair'], ['records.php', 'Orders', 'order'],
    ['payments.php', 'Payments', ''], ['products.php', 'Products', ''], ['events.php', 'Events', ''], ['customers.php', 'Customers', ''], ['messages.php', 'Messages', ''],
  ];
?>
<header class="admin-bar">
  <a class="admin-logo" href="<?= BASE_URL ?>admin/index.php">Warmth&amp;Grain <small>Admin</small></a>
  <button type="button" class="admin-menu-btn" id="adminMenuBtn" aria-label="Menu"><?= icon('menu') ?></button>
  <nav class="admin-links" id="adminLinks">
    <?php foreach ($admLinks as $admL): ?>
      <a href="<?= BASE_URL ?>admin/<?= $admL[0] . ($admL[2] ? '?type=' . $admL[2] : '') ?>" class="<?= ($admCur === $admL[0] && ($admL[2] === '' || $admL[2] === $admType)) ? 'active' : '' ?>">
        <?= e($admL[1]) ?><?= ($admL[1] === 'Messages' && $hdrNew) ? ' <span class="cart-badge">' . $hdrNew . '</span>' : '' ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="admin-user">
    <a class="admin-profile" href="<?= BASE_URL ?>admin/profile.php"><?= icon('user', 18) ?> <?= e($_SESSION['name']) ?></a>
    <a class="btn btn-danger btn-sm btn-logout" href="<?= BASE_URL ?>pages/logout.php" data-confirm="Log out of your account?">LOGOUT</a>
  </div>
</header>
<?php else: ?>
<script>if (window.innerWidth < 900 || localStorage.getItem('wgNavClosed') === '1') document.body.classList.add('nav-closed');</script>
<button type="button" class="nav-toggle nav-fab" aria-label="Open navigation"><?= icon('menu') ?></button>
<aside class="sidebar" id="sidebar">
  <div class="side-head">
    <a class="logo" href="<?= BASE_URL ?>index.php">Warmth&amp;Grain</a>
    <button type="button" class="nav-toggle" aria-label="Collapse navigation"><?= icon('menu') ?></button>
  </div>
  <nav class="side-nav">
    <a class="nav-item <?= $hdrCur === BASE_URL . 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php" title="Home"><?= icon('home') ?><span class="lbl">Home</span></a>
    <a class="nav-item <?= $hdrIs('services.php') . $hdrIs('book-service.php') ?>" href="<?= BASE_URL ?>pages/services.php" title="Film Processing"><?= icon('film') ?><span class="lbl">Film Processing</span></a>
    <a class="nav-item <?= $hdrIs('shop.php') . $hdrIs('product.php') . $hdrIs('cart.php') ?>" href="<?= BASE_URL ?>pages/shop.php" title="Store"><?= icon('store') ?><span class="lbl">Store</span></a>
    <a class="nav-item <?= $hdrIs('repair.php') . $hdrIs('book-repair.php') ?>" href="<?= BASE_URL ?>pages/repair.php" title="Repair"><?= icon('wrench') ?><span class="lbl">Repair</span></a>
    <a class="nav-item <?= $hdrIs('events.php') . $hdrIs('event.php') ?>" href="<?= BASE_URL ?>pages/events.php" title="Events"><?= icon('calendar') ?><span class="lbl">Events</span></a>
    <a class="nav-item <?= $hdrIs('about.php') ?>" href="<?= BASE_URL ?>pages/about.php" title="About"><?= icon('info') ?><span class="lbl">About</span></a>
    <a class="nav-item <?= $hdrIs('faqs.php') ?>" href="<?= BASE_URL ?>pages/faqs.php" title="FAQs"><?= icon('help') ?><span class="lbl">FAQs</span></a>
    <?php if (!is_logged_in()): ?>
      <a class="nav-item <?= $hdrIs('login.php') . $hdrIs('register.php') ?>" href="<?= BASE_URL ?>pages/login.php" title="Login / Register"><?= icon('login') ?><span class="lbl">Login / Register</span></a>
    <?php endif; ?>
  </nav>
  <?php if (is_logged_in()): ?>
  <div class="sidebar-bottom">
    <a class="nav-item <?= $hdrIs('purchases.php') . $hdrIs('thank-you.php') . $hdrIs('payment.php') ?>" href="<?= BASE_URL ?>pages/account/purchases.php" title="My Purchases"><?= icon('bag') ?><span class="lbl">My Purchases</span></a>
    <a class="account-card" href="<?= BASE_URL ?>pages/account/profile.php" title="My Account">
      <span class="acct-icon"><?= icon('user') ?></span>
      <span class="lbl acct-text"><strong><?= e($_SESSION['name']) ?></strong><span class="acct-no"><?= e(account_no($_SESSION['user_id'])) ?></span></span>
    </a>
    <a class="nav-item logout-item" href="<?= BASE_URL ?>pages/logout.php" title="Logout" data-confirm="Log out of your account?"><?= icon('logout') ?><span class="lbl">LOGOUT</span></a>
  </div>
  <?php endif; ?>
</aside>
<?php if ($showCart): ?>
  <a class="cart-link" href="<?= BASE_URL ?>pages/cart.php" aria-label="Cart" title="Cart"><?= icon('cart', 22) ?><span class="cart-badge"><?= (int)$hdrCart ?></span></a>
<?php endif; ?>
<?php endif; ?>
<?php show_flash(); ?>
<main class="content <?= ($hasBanner || $adminMode) ? '' : 'pad-top' ?>">