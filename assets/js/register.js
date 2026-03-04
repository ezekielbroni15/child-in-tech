// ============================================================
//  register.js — Registration Modal Logic
// ============================================================

(function () {
  const overlay = document.getElementById("regOverlay");
  const modal = document.getElementById("regModal");
  const closeBtn = document.getElementById("regCloseBtn");
  const form = document.getElementById("regForm");
  const tourSel = document.getElementById("regTourSelect");
  const slotWrap = document.getElementById("slotBarWrap");
  const slotFill = document.getElementById("slotBarFill");
  const slotText = document.getElementById("slotText");
  const errorMsg = document.getElementById("regError");
  const submitBtn = document.getElementById("regSubmitBtn");

  let toursData = [];

  // ── Open / Close helpers ─────────────────────────────────
  function openModal() {
    overlay.classList.add("open");
    document.body.style.overflow = "hidden";
    loadTours();
  }

  function closeModal() {
    overlay.classList.remove("open");
    document.body.style.overflow = "";
    errorMsg.classList.remove("visible");
    // Reset any success state back to form
    const success = document.getElementById("regSuccessCard");
    if (success) success.remove();
    document.getElementById("regFormWrap").style.display = "";
    document.querySelector(".reg-modal-header").style.display = "";
  }

  // Trigger buttons
  document.querySelectorAll("[data-open-register]").forEach((btn) => {
    btn.addEventListener("click", openModal);
  });

  if (closeBtn) closeBtn.addEventListener("click", closeModal);
  if (overlay)
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) closeModal();
    });

  // ── Load tours from API ───────────────────────────────────
  function loadTours() {
    tourSel.innerHTML = '<option value="">Loading available dates…</option>';
    fetch("get-tours.php")
      .then((r) => r.json())
      .then((data) => {
        toursData = data.tours || [];
        tourSel.innerHTML = '<option value="">Select a date →</option>';
        if (toursData.length === 0) {
          tourSel.innerHTML =
            '<option value="">No upcoming tours available</option>';
          return;
        }
        toursData.forEach((tour) => {
          const opt = document.createElement("option");
          opt.value = tour.id;
          opt.textContent = tour.label;
          if (tour.is_full) opt.disabled = true;
          tourSel.appendChild(opt);
        });
      })
      .catch(() => {
        tourSel.innerHTML =
          '<option value="">Error loading tours. Try again.</option>';
      });
  }

  // ── Update slot bar on tour change ────────────────────────
  if (tourSel) {
    tourSel.addEventListener("change", () => {
      const id = parseInt(tourSel.value);
      const tour = toursData.find((t) => t.id === id);
      if (!tour) {
        slotWrap.classList.remove("visible");
        return;
      }

      const pct = Math.round((tour.registered / tour.max_slots) * 100);
      slotFill.style.width = pct + "%";
      slotFill.className = "slot-bar-fill" + (pct >= 80 ? " almost-full" : "");
      slotText.textContent =
        tour.remaining + " of " + tour.max_slots + " slots left";
      slotWrap.classList.add("visible");
    });
  }

  // ── Form submit ──────────────────────────────────────────
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      errorMsg.classList.remove("visible");

      const data = new FormData(form);

      if (!data.get("tour_id")) {
        showError("Please choose a date.");
        return;
      }
      if (!data.get("full_name").trim()) {
        showError("Please enter your full name.");
        return;
      }
      if (!data.get("email").trim()) {
        showError("Please enter a valid email address.");
        return;
      }

      submitBtn.disabled = true;
      submitBtn.classList.add("loading");
      submitBtn.querySelector(".reg-btn-text").textContent =
        "Securing your slot…";

      fetch("register.php", { method: "POST", body: data })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            showSuccessCard(res);
          } else {
            showError(res.error || "Registration failed. Please try again.");
            submitBtn.disabled = false;
            submitBtn.classList.remove("loading");
            submitBtn.querySelector(".reg-btn-text").textContent =
              "Secure Your Slot →";
          }
        })
        .catch(() => {
          showError(
            "Network error. Please check your connection and try again.",
          );
          submitBtn.disabled = false;
          submitBtn.classList.remove("loading");
          submitBtn.querySelector(".reg-btn-text").textContent =
            "Secure Your Slot →";
        });
    });
  }

  // ── Show success card inside modal ───────────────────────
  function showSuccessCard(res) {
    // Hide form + header
    document.getElementById("regFormWrap").style.display = "none";
    document.querySelector(".reg-modal-header").style.display = "none";

    const card = document.createElement("div");
    card.id = "regSuccessCard";
    card.innerHTML = `
      <div class="reg-success-wrap">
        <div class="reg-success-icon">🎉</div>
        <h2 class="reg-success-title">You're In!</h2>
        <p class="reg-success-sub">
          Your slot is secured. Your digital ticket is ready — tap below to view, download or share it.
        </p>
        <div class="reg-success-ticket-id">${escapeHtml(res.ticket_id)}</div>
        <div class="reg-success-actions">
          <a href="${escapeHtml(res.ticket_url)}" class="reg-action-btn reg-action-primary">
            🎟 View My Ticket
          </a>
        </div>
        <button class="reg-close-success" id="regCloseDone">Close</button>
      </div>
    `;

    modal.appendChild(card);
    document
      .getElementById("regCloseDone")
      .addEventListener("click", closeModal);
  }

  function showError(msg) {
    errorMsg.textContent = msg;
    errorMsg.classList.add("visible");
    errorMsg.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  function escapeHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }
})();
