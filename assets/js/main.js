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
});
