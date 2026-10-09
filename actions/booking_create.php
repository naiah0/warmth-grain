<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$serviceId = (int)($_POST['service_id'] ?? 0);
$filmType  = $_POST['film_type'] ?? '';
$format    = $_POST['film_format'] ?? '';
$rolls     = (int)($_POST['roll_count'] ?? 0);
$expo      = (int)($_POST['exposures'] ?? 0);
$rush      = isset($_POST['rush']) ? 1 : 0;
$return    = $_POST['return_method'] ?? '';
$address   = trim($_POST['delivery_address'] ?? '');
$date      = $_POST['dropoff_date'] ?? '';
$slot      = $_POST['time_slot'] ?? '';
$notes     = trim($_POST['notes'] ?? '');
$method    = $_POST['method'] ?? '';
$refNo     = trim($_POST['reference_no'] ?? '');
$errors    = payment_errors($method, $refNo);

$stmt = $pdo->prepare('SELECT * FROM services WHERE service_id=? AND is_active=1');
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) $errors[] = 'Please select a valid service.';
if (!in_array($filmType, ['Color Negative (C-41)', 'Black & White', 'Slide (E-6)', 'Cine (ECN-2)', 'Other'], true)) $errors[] = 'Invalid film type.';
if (!in_array($format, ['35mm', '120', 'APS', 'Other'], true)) $errors[] = 'Invalid film format.';
if ($rolls < 1 || $rolls > 50) $errors[] = 'Rolls must be between 1 and 50.';
if (!in_array($expo, [12, 24, 36], true)) $errors[] = 'Invalid number of exposures.';
if (!in_array($return, ['Pickup', 'Delivery'], true)) $errors[] = 'Choose pickup or delivery.';
if ($return === 'Delivery' && $address === '') $errors[] = 'Delivery address is required.';
if (!in_array($slot, ['10:00 AM - 12:00 PM', '12:00 PM - 3:00 PM', '3:00 PM - 6:00 PM'], true)) $errors[] = 'Choose a time slot.';
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $errors[] = 'Enter a valid drop-off date.';
elseif ($date <= date('Y-m-d')) $errors[] = 'Drop-off date must be in the future.';

if ($errors) {
    set_flash('error', $errors);
    header('Location: ' . BASE_URL . 'pages/services.php?book=' . $serviceId);
    exit;
}

$total = $service['price_per_roll'] * $rolls * ($rush ? 1.5 : 1);   // always recomputed on the server

$pdo->beginTransaction();
$pdo->prepare('INSERT INTO service_bookings (user_id,service_id,film_type,film_format,roll_count,exposures,rush,return_method,delivery_address,dropoff_date,time_slot,notes,total_price) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')
    ->execute([$_SESSION['user_id'], $serviceId, $filmType, $format, $rolls, $expo, $rush, $return, $return === 'Delivery' ? $address : null, $date, $slot, $notes, $total]);
$bookingId = $pdo->lastInsertId();
record_payment($pdo, $_SESSION['user_id'], 'booking', $bookingId, $method, $refNo, $total);
$pdo->commit();

set_flash('success', "Booking #$bookingId confirmed. Total: " . peso($total));
header('Location: ' . BASE_URL . 'pages/account/purchases.php?tab=bookings');
exit;