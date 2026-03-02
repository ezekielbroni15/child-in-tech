// ============================================================
//  admin-mobile.js — hamburger sidebar toggle
// ============================================================
(function () {
  const hamburger = document.getElementById("hamburgerBtn");
  const sidebar = document.querySelector(".admin-sidebar");
  const overlay = document.getElementById("sidebarOverlay");

  if (!hamburger || !sidebar) return;

  function openSidebar() {
    sidebar.classList.add("open");
    overlay.classList.add("open");
    document.body.style.overflow = "hidden";
  }

  function closeSidebar() {
    sidebar.classList.remove("open");
    overlay.classList.remove("open");
    document.body.style.overflow = "";
  }

  hamburger.addEventListener("click", openSidebar);
  overlay.addEventListener("click", closeSidebar);

  // Close sidebar when a nav link is clicked
  document.querySelectorAll(".admin-nav-link").forEach((link) => {
    link.addEventListener("click", closeSidebar);
  });
})();
