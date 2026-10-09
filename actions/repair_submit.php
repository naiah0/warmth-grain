<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$sid     = (int)($_POST['repair_service_id'] ?? 0);
$brand   = trim($_POST['camera_brand'] ?? '');
$model   = trim($_POST['camera_model'] ?? '');
$serial  = trim($_POST['serial_number'] ?? '');
$issue   = $_POST['issue'] ?? '';
$details = trim($_POST['details'] ?? '');
$date    = $_POST['dropoff_date'] ?? '';
$errors  = [];

$chk = $pdo->prepare('SELECT COUNT(*) FROM repair_services WHERE repair_service_id=? AND is_active=1');
$chk->execute([$sid]);
if (!$chk->fetchColumn()) $errors[] = 'Please select a valid repair service.';
if ($brand === '' || $model === '') $errors[] = 'Camera brand and model are required.';
if (!in_array($issue, ['Light leaks', 'Shutter stuck or inaccurate', 'Film advance or rewind jammed', 'Foggy lens or fungus', 'Light meter not working', 'Other'], true)) $errors[] = 'Select the main problem.';
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $errors[] = 'Enter a valid drop-off date.';
elseif ($date <= date('Y-m-d')) $errors[] = 'Drop-off date must be in the future.';

if ($errors) {
    set_flash('error', $errors);
    header('Location: ' . BASE_URL . 'pages/repair.php?book=' . $sid);
    exit;
}

$pdo->prepare('INSERT INTO repairs (repair_number,user_id,repair_service_id,camera_brand,camera_model,serial_number,issue,details,dropoff_date) VALUES (?,?,?,?,?,?,?,?,?)')
    ->execute(['TMP' . uniqid(), $_SESSION['user_id'], $sid, $brand, $model, $serial ?: null, $issue, $details, $date]);
$id = $pdo->lastInsertId();
$number = sprintf('WG-R-%04d', $id);
$pdo->prepare('UPDATE repairs SET repair_number=? WHERE repair_id=?')->execute([$number, $id]);

set_flash('success', "Request submitted. Your repair number is $number. Keep it for tracking.");
header('Location: ' . BASE_URL . 'pages/account/purchases.php?tab=repairs');
exit;