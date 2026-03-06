<?php
// ============================================================
//  ticket.php — Display & Download Ticket
// ============================================================
require_once __DIR__ . '/db/connect.php';

$ticket_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$ticket_id) {
    header('Location: events.html');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.*, t.tour_number, t.tour_date, t.time_start, t.time_end, t.location
        FROM registrations r
        JOIN tours t ON t.id = r.tour_id
        WHERE r.ticket_id = ?
    ");
    $stmt->execute([$ticket_id]);
    $reg = $stmt->fetch();

    if (!$reg) {
        header('Location: events.html');
        exit;
    }

} catch (PDOException $e) {
    header('Location: events.html');
    exit;
}

$tourDate   = date('l, F j, Y', strtotime($reg['tour_date']));
$timeStart  = date('g:i A', strtotime($reg['time_start']));
$timeEnd    = date('g:i A', strtotime($reg['time_end']));
$tourNum    = $reg['tour_number'];
$name       = htmlspecialchars($reg['full_name']);
$location   = htmlspecialchars($reg['location']);
$ticket_id  = htmlspecialchars($reg['ticket_id']);

// Calendar data
$calStart  = date('Ymd', strtotime($reg['tour_date'])) . 'T' . str_replace(':', '', substr($reg['time_start'], 0, 5)) . '00';
$calEnd    = date('Ymd', strtotime($reg['tour_date'])) . 'T' . str_replace(':', '', substr($reg['time_end'],   0, 5)) . '00';
$calTitle  = urlencode('Innoventure Tour ' . $tourNum . ' — Child-In-Tech');
$calLoc    = urlencode($reg['location']);
$calDesc   = urlencode('Ticket ID: ' . $reg['ticket_id']);
$googleCal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$calTitle}&dates={$calStart}/{$calEnd}&location={$calLoc}&details={$calDesc}";
$icsUrl    = "calendar.php?ticket_id=" . urlencode($reg['ticket_id']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Ticket — Innoventure Tour <?= $tourNum ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/global.css"/>
  <link rel="stylesheet" href="assets/css/ticket.css"/>
  <style>
    /* hide old HTML ticket card from normal view */
    #ticketCard { display: none; }

    @media print {
      /* When printing, show only the PNG preview */
      body * { visibility: hidden; }
      #ticketPreview, #ticketPreview * { visibility: visible; }
      #ticketPreview {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="container nav-content">
      <a href="index.html" class="logo-link">
        <img src="assets/image/logo.png" alt="Child-In-Tech Logo" class="logo-img"/>
      </a>
      <a href="events.html" class="btn btn-outline" style="font-size:14px">← Back to Events</a>
    </div>
  </nav>

  <?php
$pngUrl = 'ticket-png.php?id=' . urlencode($ticket_id);
?>
<main class="ticket-page-wrapper">
    <!-- Success Banner -->
    <div class="ticket-success-banner">
      <div class="success-check">✓</div>
      <div>
        <h1>Registration Confirmed!</h1>
        <p>Your spot is secured for Innoventure Tour <?= $tourNum ?>. Save your ticket below.</p>
      </div>
    </div>

    <!-- Ticket Preview (PNG) -->
    <div class="ticket-outer">
      <div class="ticket-preview-wrap" style="text-align:center; margin-bottom:24px;">
        <img id="ticketPreview" src="<?= htmlspecialchars($pngUrl) ?>" alt="Your ticket" style="max-width:100%;height:auto;box-shadow:0 4px 12px rgba(0,0,0,0.1);border-radius:12px;"/>
      </div>


      <!-- Action Buttons -->
      <div class="ticket-actions">
        <button onclick="downloadPNG()" class="btn btn-primary ticket-btn">
          🎟 Download Ticket (PNG)
        </button>
        <button onclick="printTicket()" class="btn btn-outline ticket-btn">
          🖨️ Print Ticket
        </button>
        <a href="<?= $googleCal ?>" target="_blank" class="btn btn-outline ticket-btn">
          📅 Add to Google Calendar
        </a>
        <a href="<?= $icsUrl ?>" class="btn btn-outline ticket-btn">
          📆 Download (.ics for Apple / Outlook)
        </a>
      </div>

      <!-- Share note -->
      <p class="ticket-note">
        💌 A confirmation email has been sent to <strong><?= htmlspecialchars($reg['email']) ?></strong>. 
        Present this ticket (printed or on your phone) at the event entrance.
      </p>
    </div>
  </main>

  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <script src="assets/js/main.js?v=3"></script>

  <script>
    // ── Download or print ticket ───────────────────────────────
    function downloadPNG() {
      window.location.href = <?= json_encode($pngUrl) ?>;
    }
    function printTicket() { window.print(); }
  </script>
</body>
</html>

