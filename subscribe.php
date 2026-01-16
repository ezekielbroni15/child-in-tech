<?php
header('Content-Type: application/json');

// prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get POST data
$email = $_POST['email'] ?? '';

// Basic validation
if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

// Email configuration
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'broniezekiel0@gmail.com'; // Your Gmail address (Sender)
    $mail->Password   = 'xdas kahv agtl cgdo'; // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Recipients
    $mail->setFrom('broniezekiel0@gmail.com', 'Child in Tech Website');
    $mail->addAddress('broniezekiel0@gmail.com');     // Destination Address
    $mail->addReplyTo($email);

    // Content
    $mail->isHTML(false);
    $mail->Subject = "New Newsletter Subscription";
    $mail->Body    = "You have a new subscriber for your newsletter.\n\n".
                     "Email: $email\n\n".
                     "Please add them to your mailing list.";

    $mail->send();
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Subscription successful']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>
