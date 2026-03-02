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
$calTitle  = urlencode('Innoventure Tour ' . $tourNum . ' — Child In Tech');
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
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="container nav-content">
      <a href="index.html" class="logo-link">
        <img src="assets/image/logo.png" alt="Child In Tech Logo" class="logo-img"/>
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
          <div class="ticket-strip-text">ADMIT ONE • CHILD IN TECH • INNOVENTURE</div>
        </div>

        <!-- Main Content -->
        <div class="ticket-body">
          <!-- Header -->
          <div class="ticket-header">
            <div class="ticket-logo-area">
              <img src="assets/image/logo.png" alt="CIT" class="ticket-logo" crossorigin="anonymous"/>
              <div>
                <div class="ticket-org">Child In Tech</div>
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
              <div id="qrCode"></div>
              <div class="ticket-qr-label">Scan to verify</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="ticket-actions">
        <button id="downloadBtn" class="btn btn-primary ticket-btn">
          ⬇ Download Ticket (PNG)
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

  <!-- QR Code library -->
  <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
  <!-- html2canvas for PNG download -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <script src="assets/js/main.js?v=2"></script>

  <script>
    const TICKET_ID = <?= json_encode($reg['ticket_id']) ?>;
    const TOUR_NUM  = <?= json_encode($tourNum) ?>;
    let qrReady     = false;

    // ── Generate QR Code ──────────────────────────────────────
    QRCode.toCanvas(document.createElement('canvas'), TICKET_ID, {
      width: 120,
      margin: 1,
      color: { dark: '#0d47a1', light: '#ffffff' }
    }, (err, canvas) => {
      if (!err) {
        canvas.style.borderRadius = '10px';
        canvas.style.border = '3px solid #e8f0ff';
        canvas.style.display = 'block';
        document.getElementById('qrCode').appendChild(canvas);
        qrReady = true;
      }
    });

    // ── Download as PNG ───────────────────────────────────────
    // We wait until QR is rendered, then use html2canvas.
    // backgroundColor must be white (not null) for a proper PNG.
    document.getElementById('downloadBtn').addEventListener('click', () => {
      const btn = document.getElementById('downloadBtn');

      function doCapture() {
        btn.textContent = 'Generating…';
        btn.disabled = true;

        // Short delay so any pending repaints finish
        setTimeout(() => {
          html2canvas(document.getElementById('ticketCard'), {
            scale: 3,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            onclone: (clonedDoc) => {
              // Make sure the cloned ticket has explicit white bg
              const el = clonedDoc.getElementById('ticketCard');
              if (el) el.style.backgroundColor = '#ffffff';
            }
          }).then(canvas => {
            const link      = document.createElement('a');
            link.download   = 'CIT-Ticket-' + TICKET_ID + '.png';
            link.href       = canvas.toDataURL('image/png');
            link.click();
            btn.innerHTML   = '⬇ Download Ticket (PNG)';
            btn.disabled    = false;
          }).catch(err => {
            console.error('Download failed:', err);
            btn.innerHTML = '⬇ Download Ticket (PNG)';
            btn.disabled  = false;
            alert('Download failed. Please try again or take a screenshot.');
          });
        }, 200);
      }

      // Wait for QR to be ready if not yet
      if (qrReady) {
        doCapture();
      } else {
        btn.textContent = 'Preparing…';
        btn.disabled = true;
        const check = setInterval(() => {
          if (qrReady) {
            clearInterval(check);
            doCapture();
          }
        }, 100);
        // Give up after 3s
        setTimeout(() => { clearInterval(check); doCapture(); }, 3000);
      }
    });
  </script>
</body>
</html>
