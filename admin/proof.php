<?php
require_once '../includes/auth.php';
require_admin();
$f = basename($_GET['f'] ?? '');
$path = dirname(__DIR__) . '/uploads/proofs/' . $f;
if (!preg_match('/^proof_[a-f0-9]{16}\.(jpg|png|webp)$/', $f) || !is_file($path)) { http_response_code(404); exit('Not found'); }
$types = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
header('Content-Type: ' . $types[pathinfo($f, PATHINFO_EXTENSION)]);
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
readfile($path);