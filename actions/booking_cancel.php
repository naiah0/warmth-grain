<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$id = (int)($_POST['booking_id'] ?? 0);
$stmt = $pdo->prepare("UPDATE service_bookings SET status='Cancelled' WHERE booking_id=? AND user_id=? AND status IN ('Pending','Confirmed')");
$stmt->execute([$id, $_SESSION['user_id']]);

set_flash($stmt->rowCount() ? 'success' : 'error',
          $stmt->rowCount() ? 'Booking cancelled.' : 'This booking can no longer be cancelled.');
header('Location: ' . BASE_URL . 'pages/account/purchases.php?tab=bookings');
exit;