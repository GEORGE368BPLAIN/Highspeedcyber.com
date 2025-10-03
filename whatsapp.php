
<?php
// WhatsApp Cloud API helper
require_once __DIR__ . '/config.php';

function wa_send_text($text){
  global $WHATSAPP_ENABLE, $WHATSAPP_TOKEN, $WHATSAPP_PHONE_ID, $WHATSAPP_TO;
  if (!isset($WHATSAPP_ENABLE) || !$WHATSAPP_ENABLE) return ['ok'=>false, 'error'=>'WA disabled'];
  if (!$WHATSAPP_TOKEN || !$WHATSAPP_PHONE_ID || !$WHATSAPP_TO) return ['ok'=>false, 'error'=>'WA config missing'];

  $url = "https://graph.facebook.com/v19.0/{$WHATSAPP_PHONE_ID}/messages";
  $payload = [
    "messaging_product" => "whatsapp",
    "to" => $WHATSAPP_TO,
    "type" => "text",
    "text" => ["preview_url" => false, "body" => $text]
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$WHATSAPP_TOKEN}",
    "Content-Type: application/json"
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $res = curl_exec($ch);
  $err = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($err) return ['ok'=>false, 'error'=>$err];
  if ($code >= 200 && $code < 300) return ['ok'=>true];
  return ['ok'=>false, 'error'=>"HTTP $code: $res"];
}
?>
