
<?php
// ===== SMTP CONFIG (Gmail) =====
// Create a Gmail App Password (Google Account → Security → App passwords)
// Then set these values and upload the site to a PHP host (PHP 8+ recommended).

$SMTP_HOST = 'smtp.gmail.com';
$SMTP_PORT = 465;              // 465 = implicit TLS (recommended)
$SMTP_USER = 'yourgmail@gmail.com';    // <-- CHANGE
$SMTP_PASS = 'your-app-password';      // <-- CHANGE (16-char app password)
$SMTP_FROM = 'yourgmail@gmail.com';    // Sender email (same as user for Gmail)
$SMTP_FROM_NAME = 'Highspeed Cyber';
$SMTP_TO = 'georgekahugunjogu@gmail.com'; // Default recipient inbox

// Optional: additional recipients (CC/BCC)
// $SMTP_CC = 'someone@example.com';
// $SMTP_BCC = 'audit@example.com';
?>


/* ===== WhatsApp Cloud API (optional but recommended) =====
   Steps:
   1) Create/Use a Meta App → WhatsApp.
   2) Get a permanent token and your Phone Number ID.
   3) Set the values below. Number format must be international (e.g., 254700592619).
*/
$WHATSAPP_TOKEN = 'your_whatsapp_permanent_token';  // <-- CHANGE
$WHATSAPP_PHONE_ID = 'your_phone_number_id';        // <-- CHANGE
$WHATSAPP_TO = '254700592619';                      // Owner/recipient number (your WhatsApp)
$WHATSAPP_ENABLE = true;                            // Toggle
