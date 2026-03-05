<?php
// ============================================================
//  POST /register.php — handle registration form
// ============================================================
// Buffer all output so PHP warnings don't corrupt our JSON
ob_start();

header('Content-Type: application/json');
set_time_limit(60);
require_once __DIR__ . '/db/connect.php';

// --- Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// --- Read & sanitize input
$tour_id   = filter_input(INPUT_POST, 'tour_id',   FILTER_VALIDATE_INT);
$full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_SPECIAL_CHARS));
$email     = trim(filter_input(INPUT_POST, 'email',     FILTER_VALIDATE_EMAIL));
$phone     = trim(filter_input(INPUT_POST, 'phone',     FILTER_SANITIZE_SPECIAL_CHARS));
$age_group = trim(filter_input(INPUT_POST, 'age_group', FILTER_SANITIZE_SPECIAL_CHARS));
$school    = trim(filter_input(INPUT_POST, 'school',    FILTER_SANITIZE_SPECIAL_CHARS));

// --- Validate required fields
if (!$tour_id || !$full_name || !$email) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Please fill in all required fields.']);
    exit;
}

try {
    // --- Check tour exists and is active
    $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ? AND is_active = 1");
    $stmt->execute([$tour_id]);
    $tour = $stmt->fetch();

    if (!$tour) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Selected tour is not available.']);
        exit;
    }

    // --- Check slot availability
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE tour_id = ?");
    $stmt->execute([$tour_id]);
    $count = (int)$stmt->fetchColumn();

    if ($count >= $tour['max_slots']) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Sorry, this tour is fully booked!']);
        exit;
    }

    // --- Duplicate check: same email on the same tour date ? block with clear error
    $stmt = $pdo->prepare("
        SELECT ticket_id FROM registrations
        WHERE tour_id = ? AND email = ?
    ");
    $stmt->execute([$tour_id, $email]);
    $existing = $stmt->fetch();
    if ($existing) {
        ob_end_clean();
        echo json_encode([
            'success' => false,
            'error'   => "You are already registered for this date using \"$full_name\" ($email). "
                       . "Your ticket ID is: " . $existing['ticket_id'] . ". "
                       . "You may register for a different date if you wish.",
        ]);
        exit;
    }

    // --- Generate unique ticket ID: CIT-YYYYMMDD-XXXX
    $dateStr   = date('Ymd', strtotime($tour['tour_date']));
    $seq       = str_pad(($count + 1), 4, '0', STR_PAD_LEFT);
    $ticket_id = 'CIT-' . $dateStr . '-' . $seq;

    // --- Insert registration
    $stmt = $pdo->prepare("
        INSERT INTO registrations (ticket_id, tour_id, full_name, email, phone, age_group, school)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$ticket_id, $tour_id, $full_name, $email, $phone, $age_group, $school]);
    $reg_id = $pdo->lastInsertId();

    // --- Build calendar links
    $calStart = date('Ymd', strtotime($tour['tour_date'])) . 'T' . str_replace(':', '', substr($tour['time_start'], 0, 5)) . '00';
    $calEnd   = date('Ymd', strtotime($tour['tour_date'])) . 'T' . str_replace(':', '', substr($tour['time_end'],   0, 5)) . '00';
    $calTitle = urlencode('Innoventure Tour ' . $tour['tour_number'] . ' — Child-In-Tech');
    $calLoc   = urlencode($tour['location']);
    $calDesc  = urlencode('Ticket ID: ' . $ticket_id . '. One day of exploration at real tech companies!');

    $googleCal = "https://calendar.google.com/calendar/render?action=TEMPLATE"
               . "&text={$calTitle}&dates={$calStart}/{$calEnd}"
               . "&location={$calLoc}&details={$calDesc}";
    $icsUrl    = "calendar.php?ticket_id=" . urlencode($ticket_id);

    // --- Build the success JSON response
    $response = json_encode([
        'success'    => true,
        'ticket_id'  => $ticket_id,
        'email_sent' => false,  // will attempt after responding
        'ticket_url' => 'ticket.php?id=' . urlencode($ticket_id),
        'google_cal' => $googleCal,
        'ics_url'    => $icsUrl,
        'registration' => [
            'full_name'   => $full_name,
            'email'       => $email,
            'tour_number' => $tour['tour_number'],
            'tour_date'   => $tour['tour_date'],
            'time_start'  => $tour['time_start'],
            'time_end'    => $tour['time_end'],
            'location'    => $tour['location'],
        ]
    ]);

    // =========================================================
    //  SEND EMAIL (synchronous, short timeout so it doesn't hang long)
    // =========================================================
    $email_sent = false;
    $email_error = '';
    $logFile = __DIR__ . '/email_log.txt';

    try {
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';
        require_once __DIR__ . '/PHPMailer/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com'; // Your domain SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@childintechhq.com';
        $mail->Password   = 'j$UeC/nCeS7'; // Set your cPanel/hosting email password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->Timeout    = 10;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom('info@childintechhq.com', 'Child-In-Tech');
        $mail->addAddress($email, $full_name);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $tourDate  = date('l, F j, Y', strtotime($tour['tour_date']));
        $timeStart = date('g:i A', strtotime($tour['time_start']));
        $timeEnd   = date('g:i A', strtotime($tour['time_end']));

        $mail->Subject = '?? Your Innoventure Tour ' . $tour['tour_number'] . ' Ticket — ' . $ticket_id;
        $mail->Body    = buildEmailHTML($full_name, $ticket_id, $tour, $tourDate, $timeStart, $timeEnd);
        $mail->send();

        $email_sent = true;
        $pdo->prepare("UPDATE registrations SET email_sent = 1 WHERE id = ?")->execute([$reg_id]);
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " EMAIL OK to $email for $ticket_id\n", FILE_APPEND);

    } catch (Exception $e) {
        $email_error = $e->getMessage();
        file_put_contents($logFile, date('[Y-m-d H:i:s]') . " EMAIL FAIL to $email for $ticket_id: $email_error\n", FILE_APPEND);
    }

    // =========================================================
    //  RESPOND TO BROWSER
    // =========================================================
    ob_end_clean();
    echo $response;

} catch (PDOException $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Registration failed. Please try again.']);
}

// ============================================================
//  Email HTML Template
// ============================================================
function buildEmailHTML($name, $ticket_id, $tour, $tourDate, $timeStart, $timeEnd) {
    $tourNum  = htmlspecialchars($tour['tour_number']);
    $location = htmlspecialchars($tour['location']);
    return "
    <div style='font-family: Inter, Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8faff; padding: 32px 16px;'>
      <div style='background: linear-gradient(135deg, #1a73e8, #0d47a1); border-radius: 20px 20px 0 0; padding: 40px 32px; text-align: center;'>
        <img src='https://childintechhq.com/assets/image/logo.png' alt='CIT Logo' style='height: 50px; margin-bottom: 16px;'>
        <h1 style='color: white; margin: 0; font-size: 28px;'>You're Registered! ??</h1>
        <p style='color: rgba(255,255,255,0.85); margin-top: 8px;'>Innoventure Tour {$tourNum}</p>
      </div>
      <div style='background: white; border-radius: 0 0 20px 20px; padding: 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);'>
        <p style='font-size: 16px; color: #333;'>Hi <strong>" . htmlspecialchars($name) . "</strong>,</p>
        <p style='color: #555; line-height: 1.6;'>Your spot is confirmed for Innoventure Tour {$tourNum}. Show your ticket at the event for entry.</p>
        <div style='background: #f0f4ff; border-radius: 12px; padding: 20px; margin: 24px 0; text-align: center;'>
          <div style='font-family: monospace; font-size: 22px; font-weight: 700; color: #1a73e8; letter-spacing: 2px;'>{$ticket_id}</div>
          <div style='color: #888; font-size: 12px; margin-top: 4px;'>Ticket ID</div>
        </div>
        <table style='width: 100%; border-collapse: collapse;'>
          <tr><td style='padding: 8px 0; color: #888; font-size: 14px;'>?? Date</td><td style='padding: 8px 0; font-weight: 600;'>{$tourDate}</td></tr>
          <tr><td style='padding: 8px 0; color: #888; font-size: 14px;'>? Time</td><td style='padding: 8px 0; font-weight: 600;'>{$timeStart} – {$timeEnd}</td></tr>
          <tr><td style='padding: 8px 0; color: #888; font-size: 14px;'>?? Location</td><td style='padding: 8px 0; font-weight: 600;'>{$location}</td></tr>
        </table>
        <a href='https://childintechhq.com/ticket.php?id={$ticket_id}' style='display: block; background: linear-gradient(135deg, #1a73e8, #0d47a1); color: white; text-align: center; padding: 14px; border-radius: 10px; text-decoration: none; font-weight: 600; margin-top: 24px;'>View &amp; Download Your Ticket ?</a>
        <p style='color: #aaa; font-size: 12px; text-align: center; margin-top: 24px;'>Child-In-Tech Academy · info@childintechhq.com</p>
      </div>
    </div>";
}
