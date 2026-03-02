<?php
require_once 'auth.php';
require_once '../db/connect.php';

// Handle form submission (create/update tour)
$msg = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action      = $_POST['action'] ?? '';
    $tour_number = trim($_POST['tour_number'] ?? '');
    $tour_date   = $_POST['tour_date'] ?? '';
    $time_start  = $_POST['time_start'] ?? '09:00';
    $time_end    = $_POST['time_end'] ?? '14:00';
    $location    = trim($_POST['location'] ?? 'TBA');
    $max_slots   = max(1, (int)($_POST['max_slots'] ?? 50));
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'create' && $tour_number && $tour_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tours (tour_number, tour_date, time_start, time_end, location, max_slots, is_active) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$tour_number, $tour_date, $time_start, $time_end, $location, $max_slots, $is_active]);
            $msg = 'Tour ' . $tour_number . ' created successfully!';
        } catch (PDOException $e) {
            $msg = 'Error: ' . $e->getMessage();
            $msgType = 'error';
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['tour_id'] ?? 0);
        $active = (int)($_POST['new_active'] ?? 0);
        $pdo->prepare("UPDATE tours SET is_active = ? WHERE id = ?")->execute([$active, $id]);
        $msg = 'Tour status updated.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['tour_id'] ?? 0);
        $pdo->prepare("DELETE FROM tours WHERE id = ?")->execute([$id]);
        $msg = 'Tour deleted.';
    }
}

$tours = $pdo->query("
    SELECT t.*, COUNT(r.id) AS registered_count
    FROM tours t
    LEFT JOIN registrations r ON r.tour_id = t.id
    GROUP BY t.id
    ORDER BY t.tour_date DESC
")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Tours — CIT Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/admin.css"/>
</head>
<body class="admin-body">
  <?php include 'partials/sidebar.php'; ?>
  <?php include 'partials/topbar.php'; ?>
  <main class="admin-main">
    <div class="admin-topbar">
      <div>
        <h1 class="admin-page-title">Manage Tours</h1>
        <p class="admin-page-sub">Create and manage Innoventure Saturday tour slots</p>
      </div>
    </div>

    <?php if ($msg): ?>
      <div class="admin-alert <?= $msgType === 'error' ? 'alert-error' : 'alert-success' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Create Tour Form -->
    <div class="admin-section">
      <div class="admin-section-header"><h2>Create New Tour</h2></div>
      <form method="POST" class="admin-form-grid">
        <input type="hidden" name="action" value="create"/>
        <div class="admin-field">
          <label>Tour Number *</label>
          <input type="text" name="tour_number" placeholder="e.g. 3.0" required/>
        </div>
        <div class="admin-field">
          <label>Date (Saturday) *</label>
          <input type="date" name="tour_date" required/>
        </div>
        <div class="admin-field">
          <label>Start Time</label>
          <input type="time" name="time_start" value="09:00"/>
        </div>
        <div class="admin-field">
          <label>End Time</label>
          <input type="time" name="time_end" value="14:00"/>
        </div>
        <div class="admin-field">
          <label>Location</label>
          <input type="text" name="location" placeholder="TBA or specific venue"/>
        </div>
        <div class="admin-field">
          <label>Max Slots</label>
          <input type="number" name="max_slots" value="50" min="1" max="500"/>
        </div>
        <div class="admin-field admin-field-full">
          <label class="checkbox-label">
            <input type="checkbox" name="is_active" checked/>
            Active (visible to users)
          </label>
        </div>
        <div class="admin-field admin-field-full">
          <button type="submit" class="admin-btn admin-btn-primary">Create Tour</button>
        </div>
      </form>
    </div>

    <!-- Tours Table -->
    <div class="admin-section">
      <div class="admin-section-header"><h2>All Tours</h2></div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Tour</th><th>Date</th><th>Time</th><th>Location</th>
              <th>Slots</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tours as $t):
              $reg = (int)$t['registered_count'];
              $max = (int)$t['max_slots'];
              $pct = $max > 0 ? round(($reg/$max)*100) : 0;
            ?>
            <tr>
              <td><strong>Tour <?= htmlspecialchars($t['tour_number']) ?></strong></td>
              <td><?= date('D, M j, Y', strtotime($t['tour_date'])) ?></td>
              <td><?= date('g:i A', strtotime($t['time_start'])) ?> – <?= date('g:i A', strtotime($t['time_end'])) ?></td>
              <td><?= htmlspecialchars($t['location']) ?></td>
              <td>
                <div class="mini-slot-bar">
                  <div class="mini-slot-fill" style="width:<?= min($pct,100) ?>%"></div>
                </div>
                <?= $reg ?>/<?= $max ?>
              </td>
              <td>
                <span class="badge <?= $t['is_active'] ? 'badge-green' : 'badge-grey' ?>">
                  <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td class="action-cell">
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action" value="toggle"/>
                  <input type="hidden" name="tour_id" value="<?= $t['id'] ?>"/>
                  <input type="hidden" name="new_active" value="<?= $t['is_active'] ? 0 : 1 ?>"/>
                  <button class="admin-btn-sm admin-btn-outline" type="submit">
                    <?= $t['is_active'] ? 'Disable' : 'Enable' ?>
                  </button>
                </form>
                <?php if ($reg === 0): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this tour?')">
                  <input type="hidden" name="action" value="delete"/>
                  <input type="hidden" name="tour_id" value="<?= $t['id'] ?>"/>
                  <button class="admin-btn-sm admin-btn-danger" type="submit">Delete</button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <script src="../assets/js/admin-mobile.js"></script>
</body>
</html>
