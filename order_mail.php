
<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/smtp_mail.php';
require_once __DIR__ . '/whatsapp.php';

$orderId = trim($_POST['orderId'] ?? '');
$summary = trim($_POST['summary'] ?? '');
$name = trim($_POST['name'] ?? 'Customer');
$phone = trim($_POST['phone'] ?? '');
$amount = trim($_POST['amount'] ?? '');

if ($orderId === '' || $summary === ''){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing fields']); exit; }

$subject = "New Print Order — Ref $orderId";
$body = "New print order received:\n\nRef: $orderId\nName/Phone: $name / $phone\nAmount: KES $amount\n\nSummary:\n$summary\n";
$send = smtp_send($SMTP_TO, $subject, $body, null, null);
if($send['ok']){
  @wa_send_text("ORDER " . $orderId . ": KES " . $amount . "\n" . $summary . (strlen($phone)?"\nPhone: ".$phone:"") );
  echo json_encode(['ok'=>true]); } else { http_response_code(500); echo json_encode($send); }
?>
