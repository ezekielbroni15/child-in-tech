<?php
require_once 'auth.php';
require_once '../db/connect.php';

// AJAX verify endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $ticket_id = trim($_POST['ticket_id'] ?? '');

    if (!$ticket_id) {
        echo json_encode(['success' => false, 'error' => 'No ticket ID provided']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT r.*, t.tour_number, t.tour_date, t.time_start, t.time_end, t.location
        FROM registrations r JOIN tours t ON t.id = r.tour_id
        WHERE r.ticket_id = ?
    ");
    $stmt->execute([$ticket_id]);
    $reg = $stmt->fetch();

    if (!$reg) {
        echo json_encode(['success' => false, 'error' => 'Ticket not found. Not registered.']);
        exit;
    }

    // Mark checked in
    if (!$reg['checked_in']) {
        $pdo->prepare("UPDATE registrations SET checked_in = 1 WHERE ticket_id = ?")->execute([$ticket_id]);
        $reg['checked_in'] = 1;
        $reg['just_checked_in'] = true;
    } else {
        $reg['just_checked_in'] = false;
    }

    echo json_encode([
        'success'      => true,
        'just_checked_in' => $reg['just_checked_in'],
        'already_in'   => !$reg['just_checked_in'],
        'attendee'     => [
            'ticket_id'   => $reg['ticket_id'],
            'full_name'   => $reg['full_name'],
            'email'       => $reg['email'],
            'phone'       => $reg['phone'],
            'age_group'   => $reg['age_group'],
            'school'      => $reg['school'],
            'tour_number' => $reg['tour_number'],
            'tour_date'   => date('D, M j, Y', strtotime($reg['tour_date'])),
            'time'        => date('g:i A', strtotime($reg['time_start'])) . ' – ' . date('g:i A', strtotime($reg['time_end'])),
            'location'    => $reg['location'],
        ]
    ]);
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scan & Verify — CIT Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/admin.css"/>
</head>
<body class="admin-body">
  <?php include 'partials/sidebar.php'; ?>
  <?php include 'partials/topbar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 class="admin-page-title">📷 Scan & Verify</h1>
        <p class="admin-page-sub">Scan QR codes or search by name/ticket ID to verify attendees</p>
      </div>
    </div>

    <div class="scan-layout">

      <!-- Left: QR Scanner + Manual Search -->
      <div class="scan-input-panel">
        <!-- Tab Switcher -->
        <div class="scan-tabs">
          <button class="scan-tab active" data-tab="qr">📷 QR Scan</button>
          <button class="scan-tab" data-tab="manual">🔍 Search</button>
        </div>

        <!-- QR Camera -->
        <div class="scan-tab-content" id="tabQr">
          <div id="qr-reader" class="qr-reader-box"></div>
          <div id="qr-status" class="qr-status-msg">Point camera at a ticket QR code</div>
          <button id="startCameraBtn" class="admin-btn admin-btn-primary" style="width:100%;margin-top:12px">Start Camera</button>
          <button id="stopCameraBtn" class="admin-btn admin-btn-outline" style="width:100%;margin-top:8px;display:none">Stop Camera</button>
        </div>

        <!-- Manual Search -->
        <div class="scan-tab-content" id="tabManual" style="display:none">
          <div class="admin-field">
            <label>Ticket ID or Full Name</label>
            <input type="text" id="manualInput" class="admin-search-input" style="width:100%;box-sizing:border-box" placeholder="e.g. CIT-20260307-0001 or 'Ama Owusu'"/>
          </div>
          <button id="manualSearchBtn" class="admin-btn admin-btn-primary" style="width:100%;margin-top:8px">Search & Verify</button>
          <!-- Name search results -->
          <div id="searchResults" class="search-results-list"></div>
        </div>
      </div>

      <!-- Right: Result Panel -->
      <div class="scan-result-panel" id="scanResultPanel">
        <div class="scan-empty-state">
          <div style="font-size:64px;margin-bottom:16px">🎟</div>
          <h3>Awaiting Scan</h3>
          <p>Scan a QR code or search for an attendee to verify their ticket</p>
        </div>
      </div>
    </div>
  </main>

  <!-- html5-qrcode library -->
  <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
  <script src="../assets/js/scan.js"></script>
  <script src="../assets/js/admin-mobile.js"></script>
</body>
</html>
