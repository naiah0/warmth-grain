<?php
require_once __DIR__ . '/auth.php';

function mail_log($line) {
    $dir = dirname(__DIR__) . '/uploads';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($dir . '/mail.log', date('Y-m-d H:i:s') . ' ' . $line . "\n", FILE_APPEND);
}

function send_mail($toEmail, $toName, $subject, $html) {
    $file = __DIR__ . '/../config/mail.php';
    $cfg = file_exists($file) ? (require $file) : [];
    $key = $cfg['api_key'] ?? '';
    if ($key === '' || !function_exists('curl_init')) { mail_log("NOT SENT (mail not configured) to $toEmail: $subject"); return false; }

    $payload = json_encode([
        'sender' => ['name' => $cfg['from_name'] ?? 'Warmth&Grain', 'email' => $cfg['from_email'] ?? ''],
        'to' => [['email' => $toEmail, 'name' => $toName]],
        'subject' => $subject,
        'htmlContent' => $html,
    ]);
    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    $opts = [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_POSTFIELDS => $payload,
             CURLOPT_HTTPHEADER => ['accept: application/json', 'content-type: application/json', 'api-key: ' . $key]];
    if (!empty($cfg['ca_bundle']) && file_exists($cfg['ca_bundle'])) $opts[CURLOPT_CAINFO] = $cfg['ca_bundle'];
    curl_setopt_array($ch, $opts);
    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    $ok = $code >= 200 && $code < 300;
    mail_log(($ok ? 'SENT' : "FAILED ($code $err " . substr((string)$res, 0, 200) . ')') . " to $toEmail: $subject");
    return $ok;
}

function send_welcome_email($email, $name, $accountNo) {
    $n = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;border:2px solid #4C4541;background:#FCF0DA;color:#4C4541">'
      . '<div style="background:#F2C46A;padding:18px 24px;border-bottom:2px solid #4C4541"><h2 style="margin:0">Warmth&amp;Grain</h2></div>'
      . '<div style="padding:24px;line-height:1.6"><p>Hi ' . $n . ',</p>'
      . '<p>Thank you for creating an account with <strong>Warmth&amp;Grain</strong>. Your account is ready.</p>'
      . '<p><strong>Account number:</strong> ' . htmlspecialchars($accountNo) . '</p>'
      . '<p>You can now book film processing and camera repairs, shop in our store, and register for community events.</p>'
      . '<p>If you did not create this account, please contact us at ' . htmlspecialchars(BIZ_EMAIL) . '.</p>'
      . '<p>Warm regards,<br>The Warmth&amp;Grain Team</p></div></div>';
    return send_mail($email, $name, 'Welcome to Warmth&Grain, your account is ready', $html);
}