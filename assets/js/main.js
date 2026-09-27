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
