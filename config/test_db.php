<?php
require 'config/db.php';
$products = $pdo->query("SELECT name, price FROM products")->fetchAll();
foreach ($products as $p) {
    echo htmlspecialchars($p['name']) . ' - ₱' . number_format($p['price'], 2) . '<br>';
}