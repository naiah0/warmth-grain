<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$svc = function ($id, $type) use ($pdo) {
    if (!$id) return null;
    $s = $pdo->prepare('SELECT * FROM services WHERE service_id=? AND service_type=? AND is_active=1'); $s->execute([$id, $type]); return $s->fetch() ?: null;
};
$pkg  = (int)($_POST['package_id'] ?? 0) ?: null;
$out  = $_POST['output_option'] ?? '';
$dev  = $svc((int)($_POST['developing_id'] ?? 0), 'Developing');
$scan = $out === 'Prints only' ? null : $svc((int)($_POST['scanning_id'] ?? 0), 'Scanning');
$prn  = $out === 'Digital only' ? null : $svc((int)($_POST['printing_id'] ?? 0), 'Printing');
$fmt = $_POST['film_format'] ?? ''; $rolls = (int)($_POST['roll_count'] ?? 0); $expo = (int)($_POST['exposures'] ?? 0);
$date = $_POST['dropoff_date'] ?? ''; $slot = $_POST['time_slot'] ?? ''; $notes = trim($_POST['notes'] ?? '');
$errors = [];

if (!$dev) $errors[] = 'Choose a developing process first.';
if (!in_array($out, ['Digital only', 'Prints only', 'Both'], true)) $errors[] = 'Choose an output option.';
if (in_array($out, ['Digital only', 'Both'], true) && !$scan) $errors[] = 'Choose a scanning option.';
if (in_array($out, ['Prints only', 'Both'], true) && !$prn) $errors[] = 'Choose a printing option.';
if (!in_array($fmt, ['35mm', '120', 'APS', 'Other'], true)) $errors[] = 'Invalid film format.';
if ($rolls < 1 || $rolls > 50) $errors[] = 'Rolls must be between 1 and 50.';
if (!in_array($expo, [12, 24, 36], true)) $errors[] = 'Invalid number of exposures.';
if (!in_array($slot, TIME_SLOTS, true)) $errors[] = 'Choose a time slot.';
if ($notes === '') $errors[] = 'Fill in the notes (type "None" if you have no special instructions).';
$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) $errors[] = 'Pick a drop-off date from the calendar.';
elseif (!date_ok($pdo, $date, false)) $errors[] = 'That drop-off date is not available (we are open Monday to Friday).';

if ($errors) { set_flash('error', $errors); header('Location: ' . BASE_URL . 'pages/book-service.php'); exit; }

$lines = []; $sub = 0;
foreach ([['Developing', $dev], ['Scanning', $scan], ['Printing', $prn]] as [$label, $s]) {
    if ($s) { $amt = $s['price_per_roll'] * $rolls; $sub += $amt; $lines[] = ['label' => "$label: {$s['name']} × $rolls", 'amount' => $amt]; }
}
$_SESSION['pending'] = [
    'type' => 'booking', 'back' => BASE_URL . 'pages/book-service.php', 'subtotal' => $sub, 'lines' => $lines,
    'data' => ['package_id' => $pkg, 'developing_id' => $dev['service_id'], 'scanning_id' => $scan['service_id'] ?? null, 'printing_id' => $prn['service_id'] ?? null,
               'output_option' => $out, 'film_format' => $fmt, 'roll_count' => $rolls, 'exposures' => $expo, 'dropoff_date' => $date, 'time_slot' => $slot, 'notes' => $notes],
];
header('Location: ' . BASE_URL . 'pages/payment.php');
exit;