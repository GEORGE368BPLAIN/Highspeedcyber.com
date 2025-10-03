
<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_mail.php';
require_once __DIR__ . '/whatsapp.php';

$ref = trim($_POST['ref'] ?? '');
$service = trim($_POST['service'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$details = trim($_POST['details'] ?? '');
$name = trim($_POST['name'] ?? 'Customer');
$phone = trim($_POST['phone'] ?? '');

if ($ref==='' || $service===''){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing fields']); exit; }

$subject = "New Booking — Ref $ref";
$body = "New booking received:\n\nRef: $ref\nService: $service\nWhen: $date $time\nName/Phone: $name / $phone\n\nDetails:\n$details\n";
$send = smtp_send($SMTP_TO, $subject, $body, null, null);
if($send['ok']){
  @wa_send_text("BOOKING " . $ref . ": " . $service . "\nWhen: " . $date . " " . $time . (strlen($phone)?"\nPhone: ".$phone:"") );
  echo json_encode(['ok'=>true]); } else { http_response_code(500); echo json_encode($send); }
?>
