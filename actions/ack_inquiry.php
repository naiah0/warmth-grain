<?php
require_once '../includes/auth.php';
require_login();
require_once '../config/db.php';

$ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
$id = (int)($_POST['id'] ?? 0);
$s = $pdo->prepare("UPDATE messages SET customer_ack=1, acked_at=NOW() WHERE message_id=? AND user_id=? AND status='Replied'");
$s->execute([$id, $_SESSION['user_id']]);
$ok = $s->rowCount() > 0;
if ($ajax) { header('Content-Type: application/json'); echo json_encode(['ok' => $ok, 'message' => $ok ? 'Acknowledged.' : 'Could not find that inquiry.']); exit; }
set_flash($ok ? 'success' : 'error', $ok ? 'Reply acknowledged. Thank you.' : 'Could not find that inquiry.');
header('Location: ' . BASE_URL . 'index.php');
exit;