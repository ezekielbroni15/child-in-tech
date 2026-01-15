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

  // --- Calendar Invite Logic ---
  const registerBtn = document.getElementById("registerBtn");

  if (registerBtn) {
    registerBtn.addEventListener("click", () => {
      const event = {
        title: "INNOVENTURE TOUR 1.0",
        description:
          "One day of exploration, discovery, and fun! Kids visit real tech companies, ask questions, try hands-on activities, and take home awesome souvenirs.",
        location: "LetiArt",
        startTime: "20260130T090000", // YYYYMMDDTHHMMSS
        endTime: "20260130T140000",
      };

      const icsContent = [
        "BEGIN:VCALENDAR",
        "VERSION:2.0",
        "PRODID:-//ChildInTech//Events//EN",
        "BEGIN:VEVENT",
        `SUMMARY:${event.title}`,
        `DESCRIPTION:${event.description}`,
        `LOCATION:${event.location}`,
        `DTSTART:${event.startTime}`,
        `DTEND:${event.endTime}`,
        "BEGIN:VALARM",
        "TRIGGER:-PT24H", // 1 day before
        "ACTION:DISPLAY",
        `DESCRIPTION:Reminder: ${event.title} is tomorrow!`,
        "END:VALARM",
        "BEGIN:VALARM",
        "TRIGGER:-PT1H", // 1 hour before
        "ACTION:DISPLAY",
        `DESCRIPTION:Reminder: ${event.title} starts in 1 hour!`,
        "END:VALARM",
        "END:VEVENT",
        "END:VCALENDAR",
      ].join("\r\n");

      const blob = new Blob([icsContent], {
        type: "text/calendar;charset=utf-8",
      });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "innoventure_tour.ics";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    });
  }

  // ============================================
  // GALLERY LIGHTBOX & FILTERING
  // ============================================

  // Gallery Filtering
  const filterBtns = document.querySelectorAll(".filter-btn");
  const galleryItems = document.querySelectorAll(".gallery-item");

  // Initialize all gallery items to be visible
  if (galleryItems.length > 0) {
    galleryItems.forEach((item) => {
      item.style.display = "block";
    });
  }

  if (filterBtns.length > 0) {
    filterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        // Update active state
        filterBtns.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");

        const filter = btn.getAttribute("data-filter");

        // Filter gallery items
        galleryItems.forEach((item) => {
          if (
            filter === "all" ||
            item.getAttribute("data-category") === filter
          ) {
            item.style.display = "block";
          } else {
            item.style.display = "none";
          }
        });
      });
    });
  }

  // Lightbox Functionality
  const lightboxModal = document.getElementById("lightbox");
  const lightboxImage = document.getElementById("lightboxImage");
  const lightboxClose = document.querySelector(".lightbox-close");
  const lightboxPrev = document.querySelector(".lightbox-prev");
  const lightboxNext = document.querySelector(".lightbox-next");

  let currentImageIndex = 0;
  let visibleImages = [];

  // Get all visible gallery images
  function updateVisibleImages() {
    visibleImages = Array.from(galleryItems)
      .filter((item) => item.style.display !== "none")
      .map((item) => item.querySelector("img").src);
  }

  // Open lightbox
  if (galleryItems.length > 0) {
    galleryItems.forEach((item, index) => {
      item.addEventListener("click", () => {
        updateVisibleImages();
        const imgSrc = item.querySelector("img").src;
        currentImageIndex = visibleImages.indexOf(imgSrc);

        if (currentImageIndex !== -1) {
          showImage(currentImageIndex);
          lightboxModal.classList.add("active");
          document.body.style.overflow = "hidden";
        }
      });
    });
  }

  // Show image at index
  function showImage(index) {
    if (index < 0) index = visibleImages.length - 1;
    if (index >= visibleImages.length) index = 0;

    currentImageIndex = index;
    lightboxImage.src = visibleImages[index];
  }

  // Close lightbox
  if (lightboxClose) {
    lightboxClose.addEventListener("click", () => {
      lightboxModal.classList.remove("active");
      document.body.style.overflow = "";
    });
  }

  // Close on overlay click
  if (lightboxModal) {
    lightboxModal.addEventListener("click", (e) => {
      if (e.target === lightboxModal) {
        lightboxModal.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  }

  // Previous image
  if (lightboxPrev) {
    lightboxPrev.addEventListener("click", (e) => {
      e.stopPropagation();
      showImage(currentImageIndex - 1);
    });
  }

  // Next image
  if (lightboxNext) {
    lightboxNext.addEventListener("click", (e) => {
      e.stopPropagation();
      showImage(currentImageIndex + 1);
    });
  }

  // Keyboard navigation
  document.addEventListener("keydown", (e) => {
    if (!lightboxModal || !lightboxModal.classList.contains("active")) return;

    if (e.key === "Escape") {
      lightboxModal.classList.remove("active");
      document.body.style.overflow = "";
    } else if (e.key === "ArrowLeft") {
      showImage(currentImageIndex - 1);
    } else if (e.key === "ArrowRight") {
      showImage(currentImageIndex + 1);
    }
  });

  // Touch swipe support for mobile
  let touchStartX = 0;
  let touchEndX = 0;

  if (lightboxModal) {
    lightboxModal.addEventListener(
      "touchstart",
      (e) => {
        touchStartX = e.changedTouches[0].screenX;
      },
      { passive: true }
    );

    lightboxModal.addEventListener(
      "touchend",
      (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
      },
      { passive: true }
    );
  }

  function handleSwipe() {
    const swipeThreshold = 50;
    const diff = touchStartX - touchEndX;

    if (Math.abs(diff) > swipeThreshold) {
      if (diff > 0) {
        // Swiped left - next image
        showImage(currentImageIndex + 1);
      } else {
        // Swiped right - previous image
        showImage(currentImageIndex - 1);
      }
    }
  }
});
