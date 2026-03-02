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
      { passive: true },
    );

    lightboxModal.addEventListener(
      "touchend",
      (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
      },
      { passive: true },
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
  // ============================================
  // TEAM CAROUSEL
  // ============================================
  const track = document.querySelector(".team-carousel-track");

  if (track) {
    const slides = Array.from(track.children);
    const nextButton = document.querySelector(".next-btn");
    const prevButton = document.querySelector(".prev-btn");

    // We need to know how many slides are visible at once to prevent overscrolling
    let visibleSlides = 4;

    function updateVisibleSlides() {
      if (window.innerWidth <= 480) visibleSlides = 1;
      else if (window.innerWidth <= 768) visibleSlides = 2;
      else if (window.innerWidth <= 1024) visibleSlides = 3;
      else visibleSlides = 4;
    }

    let currentSlide = 0;

    function updateCarouselPosition() {
      // Recalculate width on every move to ensure accuracy
      const slideWidth = slides[0].getBoundingClientRect().width;
      track.style.transform =
        "translateX(-" + slideWidth * currentSlide + "px)";

      // Update button states
      if (currentSlide === 0) {
        prevButton.style.opacity = "0.5";
        prevButton.style.cursor = "not-allowed";
      } else {
        prevButton.style.opacity = "1";
        prevButton.style.cursor = "pointer";
      }

      if (currentSlide >= slides.length - visibleSlides) {
        nextButton.style.opacity = "0.5";
        nextButton.style.cursor = "not-allowed";
      } else {
        nextButton.style.opacity = "1";
        nextButton.style.cursor = "pointer";
      }
    }

    if (nextButton) {
      nextButton.addEventListener("click", () => {
        updateVisibleSlides();
        if (currentSlide < slides.length - visibleSlides) {
          currentSlide++;
          updateCarouselPosition();
        }
      });
    }

    if (prevButton) {
      prevButton.addEventListener("click", () => {
        updateVisibleSlides();
        if (currentSlide > 0) {
          currentSlide--;
          updateCarouselPosition();
        }
      });
    }

    window.addEventListener("resize", () => {
      updateVisibleSlides();
      // Reset or adjust position to ensure we don't end up in whitespace
      if (currentSlide > slides.length - visibleSlides) {
        currentSlide = Math.max(0, slides.length - visibleSlides);
      }
      updateCarouselPosition();
    });

    // Initial check
    // setTimeout to ensure layout is done
    setTimeout(() => {
      updateVisibleSlides();
      updateCarouselPosition();
    }, 100);
  }
});
