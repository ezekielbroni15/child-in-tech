<?php
// admin/partials/topbar.php — shared mobile topbar with hamburger
// Include AFTER auth.php in every admin page
?>
<div class="admin-mobile-topbar">
  <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu">
    <span></span><span></span><span></span>
  </button>
  <div class="admin-mobile-logo">
    <img src="../assets/image/logo.png" alt="CIT"/>
    <span>Admin</span>
  </div>
  <div style="width:44px"></div><!-- spacer to center logo -->
</div>

<!-- Mobile sidebar overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
