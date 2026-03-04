<?php
require_once 'auth.php';
require_once '../db/connect.php';

// Filters
$tour_filter = filter_input(INPUT_GET, 'tour_id', FILTER_VALIDATE_INT) ?? '';
$search      = trim(filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

// Build query
$where  = '1=1';
$params = [];
if ($tour_filter) { $where .= ' AND r.tour_id = ?'; $params[] = $tour_filter; }
if ($search)      { $where .= ' AND (r.full_name LIKE ? OR r.email LIKE ? OR r.ticket_id LIKE ?)';
                    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$regs = $pdo->prepare("
    SELECT r.*, t.tour_number, t.tour_date, t.location
    FROM registrations r
    JOIN tours t ON t.id = r.tour_id
    WHERE $where
    ORDER BY r.registered_at DESC
");
$regs->execute($params);
$registrations = $regs->fetchAll();

$tours = $pdo->query("SELECT id, tour_number, tour_date FROM tours ORDER BY tour_date ASC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registrants — CIT Admin</title>
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
        <h1 class="admin-page-title">Registrants</h1>
        <p class="admin-page-sub"><?= count($registrations) ?> registration(s) found</p>
      </div>
      <a href="?export=1&tour_id=<?= $tour_filter ?>&q=<?= urlencode($search) ?>" class="admin-btn admin-btn-outline">⬇ Export CSV</a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" class="admin-filter-bar">
      <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, email, ticket ID…" class="admin-search-input"/>
      <select name="tour_id" class="admin-select">
        <option value="">All Tours</option>
        <?php foreach ($tours as $t): ?>
          <option value="<?= $t['id'] ?>" <?= $tour_filter == $t['id'] ? 'selected' : '' ?>>
            Tour <?= htmlspecialchars($t['tour_number']) ?> — <?= date('M j', strtotime($t['tour_date'])) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
      <a href="registrants.php" class="admin-btn admin-btn-outline">Clear</a>
    </form>

    <!-- Table -->
    <div class="admin-section">
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>#</th><th>Ticket ID</th><th>Name</th><th>Email</th><th>Phone</th>
              <th>Age</th><th>School</th><th>Tour</th><th>Registered</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($registrations as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><code class="ticket-code"><?= htmlspecialchars($r['ticket_id']) ?></code></td>
              <td><strong><?= htmlspecialchars($r['full_name']) ?></strong></td>
              <td><?= htmlspecialchars($r['email']) ?></td>
              <td><?= htmlspecialchars($r['phone'] ?? '—') ?></td>
              <td><?= htmlspecialchars($r['age_group'] ?? '—') ?></td>
              <td><?= htmlspecialchars($r['school'] ?? '—') ?></td>
              <td>Tour <?= htmlspecialchars($r['tour_number']) ?><br/>
                <small style="color:#999"><?= date('M j', strtotime($r['tour_date'])) ?></small></td>
              <td><?= date('M j, g:ia', strtotime($r['registered_at'])) ?></td>
              <td>
                <span class="badge <?= $r['checked_in'] ? 'badge-green' : 'badge-blue' ?>">
                  <?= $r['checked_in'] ? '✓ In' : 'Reg' ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($registrations)): ?>
            <tr><td colspan="10" style="text-align:center;color:#999;padding:40px">No registrations found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <script src="../assets/js/admin-mobile.js"></script>
</body>
</html>
<?php
// CSV Export
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="cit-registrations-' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Ticket ID','Name','Email','Phone','Age Group','School','Tour','Date','Registered At','Checked In']);
    foreach ($registrations as $r) {
        fputcsv($out, [
            $r['ticket_id'], $r['full_name'], $r['email'], $r['phone'],
            $r['age_group'], $r['school'], 'Tour '.$r['tour_number'],
            $r['tour_date'], $r['registered_at'], $r['checked_in'] ? 'Yes' : 'No'
        ]);
    }
    fclose($out);
    exit;
}
?>
