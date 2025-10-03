
<?php
// Minimal SMTP (implicit TLS) mailer for Gmail.
// Not as fully featured as PHPMailer but OK for simple text emails.
require_once __DIR__ . '/config.php';

function smtp_send($to, $subject, $body, $replyToEmail=null, $replyToName=null){
  global $SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS, $SMTP_FROM, $SMTP_FROM_NAME;

  $context = stream_context_create([
    'ssl' => [
      'verify_peer' => true,
      'verify_peer_name' => true,
      'allow_self_signed' => false,
      'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT
    ]
  ]);
  $fp = stream_socket_client("ssl://{$SMTP_HOST}:{$SMTP_PORT}", $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
  if (!$fp) { return ['ok'=>false, 'error'=>"Connection failed: $errstr ($errno)"]; }

  stream_set_timeout($fp, 20);

  $expect = function($code) use($fp){
    $line = '';
    while (($l = fgets($fp, 515)) !== false) {
      $line .= $l;
      if (preg_match('/^\d{3} /', $l)) break;
    }
    if (substr($line,0,3) != (string)$code) return [false, $line];
    return [true, $line];
  };

  $cmd = function($c) use($fp, $expect){
    fwrite($fp, $c . "\r\n");
    return $expect(substr($c,0,4) === 'DATA' ? 354 : null);
  };

  list($ok, $greet) = $expect(220); if(!$ok){ fclose($fp); return ['ok'=>false,'error'=>"Greet fail: $greet"]; }
  fwrite($fp, "EHLO hispeed.local\r\n");
  $resp=''; while(($l=fgets($fp,515))!==false){ $resp.=$l; if(preg_match('/^\d{3} /',$l)) break; }
  if (substr($resp,0,3)!='250'){ fclose($fp); return ['ok'=>false,'error'=>"EHLO fail: $resp"]; }

  fwrite($fp, "AUTH LOGIN\r\n");
  $auth = fgets($fp,515);
  if (substr($auth,0,3)!='334'){ fclose($fp); return ['ok'=>false,'error'=>"AUTH fail: $auth"]; }
  fwrite($fp, base64_encode($SMTP_USER) . "\r\n");
  $auth = fgets($fp,515);
  fwrite($fp, base64_encode($SMTP_PASS) . "\r\n");
  $auth = fgets($fp,515);
  if (substr($auth,0,3)!='235'){ fclose($fp); return ['ok'=>false,'error'=>"AUTH bad: $auth"]; }

  fwrite($fp, "MAIL FROM:<{$SMTP_FROM}>\r\n");
  $mfrom = fgets($fp,515); if(substr($mfrom,0,3)!='250'){ fclose($fp); return ['ok'=>false,'error'=>"MAIL FROM fail: $mfrom"]; }

  // Allow comma-separated recipients
  $recips = array_map('trim', explode(',', $to));
  foreach($recips as $rcpt){
    fwrite($fp, "RCPT TO:<{$rcpt}>\r\n");
    $rc = fgets($fp,515); if(substr($rc,0,3)!='250' && substr($rc,0,3)!='251'){ fclose($fp); return ['ok'=>false,'error'=>"RCPT TO fail: $rc"]; }
  }

  fwrite($fp, "DATA\r\n");
  $dataAns = fgets($fp,515);
  if(substr($dataAns,0,3)!='354'){ fclose($fp); return ['ok'=>false,'error'=>"DATA not accepted: $dataAns"]; }

  $headers = [];
  $headers[] = "From: {$SMTP_FROM_NAME} <{$SMTP_FROM}>";
  $headers[] = "To: {$to}";
  if ($replyToEmail){
    $headers[] = "Reply-To: " . ($replyToName ? "$replyToName <$replyToEmail>" : $replyToEmail);
  }
  $headers[] = "MIME-Version: 1.0";
  $headers[] = "Content-Type: text/plain; charset=UTF-8";
  $headers[] = "Content-Transfer-Encoding: 8bit";
  $headers[] = "Subject: " . $subject;
  $headers[] = "Date: " . date('r');
  $headers[] = "Message-ID: <" . uniqid() . "@" . "hispeed.local" . ">";

  $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
  fwrite($fp, $data . "\r\n");
  $final = fgets($fp,515);
  if(substr($final,0,3)!='250'){ fclose($fp); return ['ok'=>false,'error'=>"DATA send fail: $final"]; }

  fwrite($fp, "QUIT\r\n");
  fclose($fp);
  return ['ok'=>true];
}
?>
