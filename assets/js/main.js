document.addEventListener("DOMContentLoaded", () => {
  const mobileToggle = document.querySelector(".mobile-toggle");
  const mobileMenu = document.querySelector(".mobile-menu");
  const closeMenu = document.querySelector(".close-menu");
  const body = document.body;

  if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener("click", () => {
      mobileMenu.classList.add("active");
      body.style.overflow = "hidden"; // Prevent scrolling
    });
  }

  if (closeMenu && mobileMenu) {
    closeMenu.addEventListener("click", () => {
      mobileMenu.classList.remove("active");
      body.style.overflow = "";
    });
  }

  // Close menu when clicking a link
  if (mobileMenu) {
    const links = mobileMenu.querySelectorAll("a");
    links.forEach((link) => {
      link.addEventListener("click", () => {
        mobileMenu.classList.remove("active");
        body.style.overflow = "";
      });
    });
  }

  // Animation Observer
  const observerOptions = {
    threshold: 0.15, // Trigger when 15% visible
    rootMargin: "0px 0px -50px 0px", // Offset slightly so it doesn't trigger too early at very bottom
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("animate-visible");
        observer.unobserve(entry.target); // Only animate once
      }
    });
  }, observerOptions);

  const animatedElements = document.querySelectorAll(".animate-on-scroll");
  animatedElements.forEach((el) => observer.observe(el));
});
