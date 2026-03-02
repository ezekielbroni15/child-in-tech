<?php require_once 'auth.php'; ?>
<aside class="admin-sidebar">
  <div class="admin-sidebar-logo">
    <img src="../assets/image/logo.png" alt="CIT"/>
    <span>Admin Portal</span>
  </div>
  <nav class="admin-nav">
    <?php $page = basename($_SERVER['PHP_SELF']); ?>
    <a href="dashboard.php"   class="admin-nav-link <?= $page==='dashboard.php'   ? 'active':'' ?>">📊 Dashboard</a>
    <a href="tours.php"       class="admin-nav-link <?= $page==='tours.php'       ? 'active':'' ?>">📅 Manage Tours</a>
    <a href="registrants.php" class="admin-nav-link <?= $page==='registrants.php' ? 'active':'' ?>">👥 Registrants</a>
    <a href="scan.php"        class="admin-nav-link <?= $page==='scan.php'        ? 'active':'' ?>">📷 Scan & Verify</a>
  </nav>
  <div class="admin-sidebar-footer">
    <div class="admin-user-badge">
      <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_username'],0,1)) ?></div>
      <div>
        <div class="admin-user-name"><?= htmlspecialchars($_SESSION['admin_username']) ?></div>
        <a href="logout.php" class="admin-logout-link">Sign out</a>
      </div>
    </div>
    <a href="../index.html" class="admin-site-link" target="_blank">↗ View Site</a>
  </div>
</aside>
