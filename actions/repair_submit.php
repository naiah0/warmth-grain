<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$ids = array_values(array_unique(array_map('intval', (array)($_POST['repair_service_id'] ?? []))));
$brand = trim($_POST['camera_brand'] ?? ''); $model = trim($_POST['camera_model'] ?? '');
$serial = trim($_POST['serial_number'] ?? ''); $concerns = trim($_POST['concerns'] ?? '');
$date = $_POST['repair_date'] ?? '';
$errors = []; $services = [];

if (count($ids) < 1 || count($ids) > 2) $errors[] = 'Choose 1 to 2 repair services.';
else foreach ($ids as $id) {
    $s = $pdo->prepare('SELECT * FROM repair_services WHERE repair_service_id=? AND is_active=1'); $s->execute([$id]);
    if ($row = $s->fetch()) $services[] = $row; else $errors[] = 'One of the selected services is not available.';
}
if ($brand === '' || $model === '') $errors[] = 'Camera brand and model are required.';
if ($serial === '') $errors[] = 'Serial number is required (type "N/A" if you can\'t find it).';
if (strlen($concerns) < 5) $errors[] = 'Describe the problem or your concerns.';
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $errors[] = 'Pick a repair date from the calendar.';
elseif (!date_ok($pdo, $date, true)) $errors[] = 'That date is not available. Please pick another.';

if ($errors) { set_flash('error', $errors); header('Location: ' . BASE_URL . 'pages/book-repair.php'); exit; }

$sub = 0; $lines = [];
foreach ($services as $s) { $sub += $s['price']; $lines[] = ['label' => $s['name'], 'amount' => $s['price']]; }
$_SESSION['pending'] = [
    'type' => 'repair', 'back' => BASE_URL . 'pages/book-repair.php', 'subtotal' => $sub, 'lines' => $lines,
    'data' => ['services' => array_map(fn($s) => (int)$s['repair_service_id'], $services), 'camera_brand' => $brand, 'camera_model' => $model, 'serial_number' => $serial, 'concerns' => $concerns, 'repair_date' => $date],
];
header('Location: ' . BASE_URL . 'pages/payment.php');
exit;