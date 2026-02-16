const eventsData = {
  chocolate: {
    title: "Chocolate with Tech",
    description:
      "A sweet journey into technology and innovation. Relive the moments of discovery and fun!",
    stats: [
      { value: "180+", label: "Students Participants" },
      { value: "2+", label: "Hours Exploring" },
      { value: "1", label: "Unforgettable Day" },
    ],
    images: [
      "IMG-6656.jpg",
      "IMG-6657.jpg",
      "IMG-6658.jpg",
      "IMG-6659.jpg",
      "IMG-6662.jpg",
      "IMG-6663.jpg",
      "IMG-6668.jpg",
      "IMG-6669.jpg",
      "IMG-6670.jpg",
      "IMG-6671.jpg",
      "IMG-6673.jpg",
      "IMG-6674.jpg",
      "IMG-6676.jpg",
      "IMG-6677.jpg",
      "IMG-6678.jpg",
      "IMG-6679.jpg",
      "IMG-6680.jpg",
      "IMG-6681.jpg",
    ],
  },
  innoventure1: {
    title: "Innoventure Tour 1.0",
    description:
      "Relive the excitement, creativity, and discovery from our past LearnLab sessions and cohorts.",
    stats: [
      { value: "50+", label: "Students Participants" },
      { value: "3+", label: "Hours Exploring" },
      { value: "8+", label: "Sessions" },
    ],
    images: [
      "IMG_6627.JPG",
      "IMG_6580.JPG",
      "IMG_6575.JPG",
      "IMG_6594.JPG",
      "IMG_6600.JPG",
      "IMG_6655.JPG",
    ],
  },
};

function loadEvent() {
  const params = new URLSearchParams(window.location.search);
  const eventId = params.get("id");
  const data = eventsData[eventId] || eventsData["innoventure1"]; // Default or specific

  // Update Text
  if (document.getElementById("page-title")) {
    document.getElementById("page-title").textContent = data.title;
  }
  if (document.getElementById("page-desc")) {
    document.getElementById("page-desc").textContent = data.description;
  }
  document.title = data.title + " - Child In Tech";

  // Update Stats
  const statsContainer = document.getElementById("stats-container");
  if (statsContainer) {
    statsContainer.innerHTML = data.stats
      .map(
        (stat) => `
    <div class="stat-item">
      <div class="stat-number">${stat.value}</div>
      <div>${stat.label}</div>
    </div>
  `,
      )
      .join("");
  }

  // Update Gallery
  const galleryGrid = document.getElementById("gallery-grid");
  if (galleryGrid) {
    galleryGrid.innerHTML = data.images
      .map(
        (img, index) => `
    <div 
      class="highlight-item" 
      style="
        min-height: 250px; 
        width: 100%; 
        opacity: 0; 
        animation: slideInUp 0.6s ease-out forwards; 
        animation-delay: ${index * 0.1}s
      "
    >
      <img
        src="assets/image/${img}"
        alt="${data.title} Moment"
      />
    </div>
  `,
      )
      .join("");
  }
}

// Load on page load
document.addEventListener("DOMContentLoaded", loadEvent);
