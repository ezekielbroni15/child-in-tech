<?php
// ticket-png.php — Server-side PNG ticket generator using PHP GD
// Usage: ticket-png.php?id=CIT-XXXXXXX-XXXX
require_once __DIR__ . '/db/connect.php';

$ticket_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$ticket_id) { http_response_code(400); exit('Missing ticket ID'); }

$stmt = $pdo->prepare("
    SELECT r.*, t.tour_number, t.tour_date, t.time_start, t.time_end, t.location
    FROM registrations r JOIN tours t ON t.id = r.tour_id
    WHERE r.ticket_id = ?
");
$stmt->execute([$ticket_id]);
$reg = $stmt->fetch();
if (!$reg) { http_response_code(404); exit('Not found'); }

$name      = $reg['full_name'];
$tourNum   = $reg['tour_number'];
$tourDate  = date('D, M j, Y', strtotime($reg['tour_date']));
$timeRange = date('g:i A', strtotime($reg['time_start'])) . ' – ' . date('g:i A', strtotime($reg['time_end']));
$location  = $reg['location'];
$ticketId  = $reg['ticket_id'];

// -- Canvas Setup ---------------------------------------------
$W = 900; $H = 440;
$img = imagecreatetruecolor($W, $H);
imageantialias($img, true);

// -- Colors ---------------------------------------------------
$white   = imagecolorallocate($img, 255, 255, 255);
$navy    = imagecolorallocate($img, 13,  71,  161);
$blue    = imagecolorallocate($img, 26,  115, 232);
$purple  = imagecolorallocate($img, 123, 47,  247);
$dark    = imagecolorallocate($img, 26,  26,  46);
$muted   = imagecolorallocate($img, 136, 153, 187);
$lightbg = imagecolorallocate($img, 240, 244, 255);
$border  = imagecolorallocate($img, 232, 238, 248);
$black10 = imagecolorallocatealpha($img, 0, 0, 0, 115);

// -- Background: white rounded rect ---------------------------
imagefill($img, 0, 0, $white);

// -- Left gradient strip (46px wide, full height) -------------
for ($y = 0; $y < $H; $y++) {
    $ratio = $y / $H;
    $r = (int)(13  + ($ratio * (123 - 13)));
    $g = (int)(71  + ($ratio * (47  - 71)));
    $b = (int)(161 + ($ratio * (247 - 161)));
    $c = imagecolorallocate($img, $r, $g, $b);
    imagefilledrectangle($img, 0, $y, 46, $y, $c);
}

// -- "ADMIT ONE" vertical text on strip -----------------------
// (skip if imagettftext not available)
$body = $img; // main canvas

// -- Header area ----------------------------------------------
$x0 = 70; // content start X

// Logo
$logoPath = __DIR__ . '/assets/image/logo.png';
if (file_exists($logoPath)) {
    $logo = imagecreatefrompng($logoPath);
    if ($logo) {
        $lw = imagesx($logo); $lh = imagesy($logo);
        $targetH = 44;
        $targetW = (int)($lw * $targetH / $lh);
        imagecopyresampled($img, $logo, $x0, 24, 0, 0, $targetW, $targetH, $lw, $lh);
        imagedestroy($logo);
        $x0 += $targetW + 14;
    }
}

// Org name + tag
imagestring($img, 5, $x0, 28,  'Child-In-Tech',        $dark);
imagestring($img, 2, $x0, 50,  'INNOVENTURE TOUR',     $blue);

// Tour badge circle (top right)
$bx = $W - 80; $by = 44; $br = 40;
imagefilledellipse($img, $bx, $by, $br*2, $br*2, $blue);
imagestring($img, 5, $bx - 14, $by - 8, $tourNum, $white);

// -- Attendee section -----------------------------------------
$sectionX = 70; $sectionY = 90;
imagefilledrectangle($img, $sectionX, $sectionY, $W - 20, $sectionY + 65, $lightbg);
imagerectangle($img, $sectionX, $sectionY, $W - 20, $sectionY + 65, $border);

imagestring($img, 2, $sectionX + 10, $sectionY + 8,  'ATTENDEE', $muted);

// Name — use imagettftext for quality if font available
$fontPath = __DIR__ . '/assets/fonts/SpaceGrotesk-Bold.ttf';
if (function_exists('imagettftext') && file_exists($fontPath)) {
    imagettftext($img, 22, 0, $sectionX + 10, $sectionY + 52, $dark, $fontPath, $name);
} else {
    imagestring($img, 5, $sectionX + 10, $sectionY + 30, $name, $dark);
}

// -- Details row ----------------------------------------------
$detY = 175; $col = ($W - 90) / 3;
imagerectangle($img, 70, $detY, $W - 20, $detY + 70, $border);

// Col borders
imageline($img, 70 + (int)$col, $detY, 70 + (int)$col, $detY + 70, $border);
imageline($img, 70 + (int)($col*2), $detY, 70 + (int)($col*2), $detY + 70, $border);

imagestring($img, 2, 80,             $detY + 8,  'DATE',     $muted);
imagestring($img, 2, 80 + (int)$col, $detY + 8,  'TIME',     $muted);
imagestring($img, 2, 80 + (int)($col*2), $detY + 8, 'LOCATION', $muted);

imagestring($img, 3, 80,             $detY + 30, $tourDate,  $dark);
imagestring($img, 3, 80 + (int)$col, $detY + 30, $timeRange, $dark);
imagestring($img, 3, 80 + (int)($col*2), $detY + 30, $location, $dark);

// -- Dashed divider -------------------------------------------
$divY = 265;
for ($xx = 70; $xx < $W - 20; $xx += 14) {
    imagefilledrectangle($img, $xx, $divY, $xx + 8, $divY + 2, $border);
}

// -- Ticket ID ------------------------------------------------
imagestring($img, 2, 70, 280, 'TICKET ID', $muted);
imagefilledrectangle($img, 70, 298, 70 + (int)(strlen($ticketId) * 9) + 16, 322, $lightbg);
imagestring($img, 4, 78, 302, $ticketId, $blue);

// -- QR Code using QR library ---------------------------------
// Write QR as temp PNG and composite it
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=' . urlencode($ticketId);
$qrData = @file_get_contents($qrApiUrl);
if ($qrData) {
    $qr = imagecreatefromstring($qrData);
    if ($qr) {
        $qrSize = 110;
        imagerectangle($img, $W - 30 - $qrSize - 3, 270 - 3, $W - 27, 270 + $qrSize + 3, $border);
        imagecopyresampled($img, $qr, $W - 30 - $qrSize, 270, 0, 0, $qrSize, $qrSize, imagesx($qr), imagesy($qr));
        imagedestroy($qr);
    }
}

imagestring($img, 1, $W - 30 - 52, 385, 'Scan to verify', $muted);

// -- Footer strip ---------------------------------------------
imagefilledrectangle($img, 70, 405, $W - 20, 415, $border);
imagestring($img, 1, 72, 420, 'Child-In-Tech  |  Innoventure Saturday Tour  |  childintechhq.com', $muted);

// -- Output PNG -----------------------------------------------
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="CIT-Ticket-' . $ticketId . '.png"');
header('Cache-Control: no-cache');
imagepng($img, null, 1); // compression 1 = fast & good quality
imagedestroy($img);
