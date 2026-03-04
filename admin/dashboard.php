<?php
require_once 'auth.php';
require_once '../db/connect.php';

// Stats
$totalReg   = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$totalTours = $pdo->query("SELECT COUNT(*) FROM tours WHERE is_active=1")->fetchColumn();
$checkedIn  = $pdo->query("SELECT COUNT(*) FROM registrations WHERE checked_in=1")->fetchColumn();
$todayReg   = $pdo->query("SELECT COUNT(*) FROM registrations WHERE DATE(registered_at)=CURDATE()")->fetchColumn();

// Tour list with slot counts
$tours = $pdo->query("
    SELECT t.*, COUNT(r.id) AS registered_count
    FROM tours t
    LEFT JOIN registrations r ON r.tour_id = t.id
    GROUP BY t.id
    ORDER BY t.tour_date ASC
")->fetchAll();

// Recent registrations
$recent = $pdo->query("
    SELECT r.*, t.tour_number, t.tour_date
    FROM registrations r
    JOIN tours t ON t.id = r.tour_id
    ORDER BY r.registered_at DESC
    LIMIT 10
")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — CIT Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="icon" type="image/x-icon" href="../assets/image/favicon_io/favicon.ico"/>
  <link rel="icon" type="image/png" sizes="32x32" href="../assets/image/favicon_io/favicon-32x32.png"/>
  <link rel="icon" type="image/png" sizes="16x16" href="../assets/image/favicon_io/favicon-16x16.png"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/admin.css"/>
</head>
<body class="admin-body">

  <?php include 'partials/sidebar.php'; ?>
  <?php include 'partials/topbar.php'; ?>

  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-sub">Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></p>
      </div>
      <a href="tours.php" class="admin-btn admin-btn-primary">+ Create Tour</a>
    </div>

    <!-- Stats Cards -->
    <div class="admin-stats-grid">
      <div class="stat-card-admin">
        <div class="stat-icon-admin blue">🎟</div>
        <div>
          <div class="stat-val"><?= $totalReg ?></div>
          <div class="stat-lbl">Total Registered</div>
        </div>
      </div>
      <div class="stat-card-admin">
        <div class="stat-icon-admin green">✅</div>
        <div>
          <div class="stat-val"><?= $checkedIn ?></div>
          <div class="stat-lbl">Checked In</div>
        </div>
      </div>
      <div class="stat-card-admin">
        <div class="stat-icon-admin purple">📅</div>
        <div>
          <div class="stat-val"><?= $totalTours ?></div>
          <div class="stat-lbl">Active Tours</div>
        </div>
      </div>
      <div class="stat-card-admin">
        <div class="stat-icon-admin orange">🆕</div>
        <div>
          <div class="stat-val"><?= $todayReg ?></div>
          <div class="stat-lbl">Today's Registrations</div>
        </div>
      </div>
    </div>

    <!-- Tour Slot Overview -->
    <div class="admin-section">
      <div class="admin-section-header">
        <h2>Tour Slots</h2>
        <a href="tours.php" class="admin-link">Manage Tours →</a>
      </div>
      <div class="tour-slots-grid">
        <?php foreach ($tours as $tour):
          $reg = (int)$tour['registered_count'];
          $max = (int)$tour['max_slots'];
          $pct = $max > 0 ? round(($reg/$max)*100) : 0;
          $color = $pct >= 100 ? 'red' : ($pct >= 80 ? 'orange' : 'blue');
        ?>
        <div class="tour-slot-card">
          <div class="tour-slot-header">
            <span class="tour-num-badge">Tour <?= htmlspecialchars($tour['tour_number']) ?></span>
            <?php if (!$tour['is_active']): ?>
              <span class="badge badge-grey">Inactive</span>
            <?php elseif ($pct >= 100): ?>
              <span class="badge badge-red">FULL</span>
            <?php endif; ?>
          </div>
          <div class="tour-slot-date"><?= date('D, M j, Y', strtotime($tour['tour_date'])) ?></div>
          <div class="tour-slot-loc">📍 <?= htmlspecialchars($tour['location']) ?></div>
          <div class="slot-progress">
            <div class="slot-progress-bar">
              <div class="slot-progress-fill <?= $color ?>" style="width:<?= min($pct,100) ?>%"></div>
            </div>
            <div class="slot-progress-text"><?= $reg ?> / <?= $max ?> registered</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recent Registrations -->
    <div class="admin-section">
      <div class="admin-section-header">
        <h2>Recent Registrations</h2>
        <a href="registrants.php" class="admin-link">View All →</a>
      </div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Ticket ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Tour</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
            <tr>
              <td><code class="ticket-code"><?= htmlspecialchars($r['ticket_id']) ?></code></td>
              <td><?= htmlspecialchars($r['full_name']) ?></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td>Tour <?= htmlspecialchars($r['tour_number']) ?></td>
              <td><?= date('M j, Y', strtotime($r['registered_at'])) ?></td>
              <td>
                <span class="badge <?= $r['checked_in'] ? 'badge-green' : 'badge-blue' ?>">
                  <?= $r['checked_in'] ? '✓ Checked In' : 'Registered' ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recent)): ?>
            <tr><td colspan="6" style="text-align:center;color:#999;padding:40px">No registrations yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="https://code.iconify.design/iconify-icon/3.0.0/iconify-icon.min.js"></script>
  <script src="../assets/js/admin-mobile.js"></script>
</body>
</html>
