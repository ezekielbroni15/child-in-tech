<?php
// Email test script — DELETE AFTER USE!
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<h2>📧 Email SMTP Test</h2>";

$configs = [
    ['host' => 'smtp.hostinger.com', 'port' => 465, 'enc' => PHPMailer::ENCRYPTION_SMTPS,  'label' => 'Hostinger SMTP SSL :465'],
    ['host' => 'smtp.hostinger.com', 'port' => 587, 'enc' => PHPMailer::ENCRYPTION_STARTTLS,'label' => 'Hostinger SMTP TLS :587'],
    ['host' => 'mail.childintech.org','port' => 465, 'enc' => PHPMailer::ENCRYPTION_SMTPS,  'label' => 'Domain SMTP SSL :465'],
    ['host' => 'mail.childintech.org','port' => 587, 'enc' => PHPMailer::ENCRYPTION_STARTTLS,'label' => 'Domain SMTP TLS :587'],
];

$username = 'info@childintech.org';
$password = 'j$UeC/nCeS7';
$testTo   = 'info@childintech.org'; // sends to yourself as a test

foreach ($configs as $cfg) {
    echo "<b>Testing: {$cfg['label']}...</b><br>";
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = $cfg['enc'];
        $mail->Port       = $cfg['port'];
        $mail->Timeout    = 10;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

        $mail->setFrom($username, 'Child In Tech Test');
        $mail->addAddress($testTo);
        $mail->Subject = 'CIT Test Email';
        $mail->Body    = 'This is a test. SMTP config: ' . $cfg['label'];

        $mail->send();
        echo "✅ <span style='color:green'><b>SUCCESS!</b> This config works → use this one.</span><br><br>";
        echo "<b>Working settings:</b><br>";
        echo "Host: <b>{$cfg['host']}</b><br>";
        echo "Port: <b>{$cfg['port']}</b><br>";
        echo "Encryption: <b>" . ($cfg['port'] == 465 ? 'SMTPS (SSL)' : 'STARTTLS') . "</b><br>";
        echo "<br>✅ Test email sent to <b>$testTo</b> — check your inbox!<br>";
        break; // stop after first success
    } catch (Exception $e) {
        echo "❌ Failed: " . $mail->ErrorInfo . "<br><br>";
    }
}
echo "<hr><p style='color:red'><b>⚠️ DELETE this file after use!</b></p>";
?>
