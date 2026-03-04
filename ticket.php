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
    @media print {
      /* Hide everything except the ticket card */
      body * { visibility: hidden; }
      #ticketCard, #ticketCard * { visibility: visible; }
      #ticketCard {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        box-shadow: none !important;
        border-radius: 0 !important;
      }
      .ticket-strip { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .ticket-tour-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .ticket-name-section { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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

  <main class="ticket-page-wrapper">
    <!-- Success Banner -->
    <div class="ticket-success-banner">
      <div class="success-check">✓</div>
      <div>
        <h1>Registration Confirmed!</h1>
        <p>Your spot is secured for Innoventure Tour <?= $tourNum ?>. Save your ticket below.</p>
      </div>
    </div>

    <!-- Ticket Card (for canvas rendering) -->
    <div class="ticket-outer">
      <div id="ticketCard" class="ticket-card">
        <!-- Left strip -->
        <div class="ticket-strip">
          <div class="ticket-strip-text">ADMIT ONE • Child-In-Tech • INNOVENTURE</div>
        </div>

        <!-- Main Content -->
        <div class="ticket-body">
          <!-- Header -->
          <div class="ticket-header">
            <div class="ticket-logo-area">
              <img src="assets/image/logo.png" alt="CIT" class="ticket-logo" crossorigin="anonymous"/>
              <div>
                <div class="ticket-org">Child-In-Tech</div>
                <div class="ticket-event-tag">INNOVENTURE TOUR</div>
              </div>
            </div>
            <div class="ticket-tour-badge">
              <span class="ticket-tour-num"><?= $tourNum ?></span>
            </div>
          </div>

          <!-- Name -->
          <div class="ticket-name-section">
            <div class="ticket-label">ATTENDEE</div>
            <div class="ticket-name"><?= $name ?></div>
          </div>

          <!-- Details Row -->
          <div class="ticket-details-row">
            <div class="ticket-detail-item">
              <div class="ticket-label">DATE</div>
              <div class="ticket-detail-value"><?= $tourDate ?></div>
            </div>
            <div class="ticket-detail-item">
              <div class="ticket-label">TIME</div>
              <div class="ticket-detail-value"><?= $timeStart ?> – <?= $timeEnd ?></div>
            </div>
            <div class="ticket-detail-item">
              <div class="ticket-label">LOCATION</div>
              <div class="ticket-detail-value"><?= $location ?></div>
            </div>
          </div>

          <!-- Divider -->
          <div class="ticket-perforation"></div>

          <!-- Bottom: QR + Ticket ID -->
          <div class="ticket-bottom">
            <div class="ticket-id-section">
              <div class="ticket-label">TICKET ID</div>
              <div class="ticket-id-code"><?= $ticket_id ?></div>
              <div class="ticket-label" style="margin-top:8px">TOUR</div>
              <div class="ticket-detail-value">Innoventure <?= $tourNum ?></div>
            </div>
            <div class="ticket-qr-section">
              <?php
                $qrData = urlencode($ticket_id);
                $qrUrl  = "https://api.qrserver.com/v1/create-qr-code/?size=140x140&margin=6&color=0d47a1&bgcolor=ffffff&data={$qrData}";
              ?>
              <img
                src="<?= $qrUrl ?>"
                alt="QR Code for <?= htmlspecialchars($ticket_id) ?>"
                id="qrCode"
                width="120" height="120"
                style="border-radius:10px;border:3px solid #e8f0ff;display:block;"
              />
              <div class="ticket-qr-label">Scan to verify</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="ticket-actions">
        <button onclick="printTicket()" class="btn btn-primary ticket-btn">
          🖨️ Download / Print Ticket
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
    // ── Print / Save ticket ────────────────────────────────────
    function printTicket() { window.print(); }
  </script>
</body>
</html>

