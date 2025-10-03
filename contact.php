
<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_mail.php';
require_once __DIR__ . '/whatsapp.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$msg = trim($_POST['message'] ?? '');
if ($name==='' || $email==='' || $msg===''){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing fields']); exit; }

$subject = 'Highspeed Cyber — Website Contact';
$body = "New contact message:\n\nName: $name\nEmail: $email\n\nMessage:\n$msg\n";
$send = smtp_send($SMTP_TO, $subject, $body, $email, $name);
if($send['ok']){
  @wa_send_text("CONTACT: " . $name . " <" . $email . ">\n" . substr($msg,0,200));
  echo json_encode(['ok'=>true]); } else { http_response_code(500); echo json_encode($send); }
?>
