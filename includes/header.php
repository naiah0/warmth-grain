<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Home';
$cartCount = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Warmth&amp;Grain</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@500;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">&#9776; Menu</button>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <a class="logo" href="<?= BASE_URL ?>index.php">Warmth&amp;Grain</a>
    <nav>
      <a href="<?= BASE_URL ?>index.php">Home</a>
      <a href="<?= BASE_URL ?>pages/services.php">Film Processing</a>
      <a href="<?= BASE_URL ?>pages/shop.php">Store</a>
      <a href="<?= BASE_URL ?>pages/repair.php">Repair</a>
      <a href="<?= BASE_URL ?>pages/events.php">Events</a>
      <a href="<?= BASE_URL ?>pages/about.php">About</a>
      <?php if (is_logged_in()): ?>
        <a href="<?= BASE_URL ?>pages/account/purchases.php">My Purchases</a>
        <a href="<?= BASE_URL ?>pages/account/dashboard.php">My Account</a>
        <?php if (is_admin()): ?><a href="<?= BASE_URL ?>admin/index.php">Admin</a><?php endif; ?>
        <a href="<?= BASE_URL ?>pages/logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>pages/login.php">Login / Register</a>
      <?php endif; ?>
    </nav>
    <div class="sidebar-bottom">
      <a href="<?= BASE_URL ?>pages/contact.php">Contact</a>
      <a href="<?= BASE_URL ?>pages/faqs.php">FAQs</a>
    </div>
  </aside>
  <main class="content">
    <div class="topbar">
      <span class="topbar-user"><?= is_logged_in() ? 'Hi, ' . e($_SESSION['name']) : '' ?></span>
      <a class="cart-link" href="<?= BASE_URL ?>pages/cart.php" aria-label="Cart">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
        Cart <span class="cart-badge"><?= (int)$cartCount ?></span>
      </a>
    </div>
    <?php show_flash(); ?>