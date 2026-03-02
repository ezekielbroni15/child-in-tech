// ============================================================
//  scan.js — QR Camera Scanner + Manual Search
// ============================================================

const resultPanel = document.getElementById("scanResultPanel");
let html5QrCode = null;
let scanning = false;

// ── Tab Switching ────────────────────────────────────────────
document.querySelectorAll(".scan-tab").forEach((tab) => {
  tab.addEventListener("click", () => {
    document
      .querySelectorAll(".scan-tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelectorAll(".scan-tab-content")
      .forEach((c) => (c.style.display = "none"));
    tab.classList.add("active");
    document.getElementById(
      "tab" +
        tab.dataset.tab.charAt(0).toUpperCase() +
        tab.dataset.tab.slice(1),
    ).style.display = "";
    if (tab.dataset.tab !== "qr" && scanning) stopCamera();
  });
});

// ── QR Camera ───────────────────────────────────────────────
document
  .getElementById("startCameraBtn")
  .addEventListener("click", startCamera);
document.getElementById("stopCameraBtn").addEventListener("click", stopCamera);

function startCamera() {
  if (scanning) return;
  html5QrCode = new Html5Qrcode("qr-reader");
  html5QrCode
    .start(
      { facingMode: "environment" },
      { fps: 10, qrbox: { width: 250, height: 250 } },
      (decodedText) => {
        if (!scanning) return;
        verifyTicket(decodedText);
      },
      () => {},
    )
    .then(() => {
      scanning = true;
      document.getElementById("startCameraBtn").style.display = "none";
      document.getElementById("stopCameraBtn").style.display = "";
      document.getElementById("qr-status").textContent =
        "🟢 Camera active — scanning…";
    })
    .catch((err) => {
      document.getElementById("qr-status").textContent =
        "⚠ Could not start camera: " + err;
    });
}

function stopCamera() {
  if (html5QrCode && scanning) {
    html5QrCode.stop().then(() => {
      scanning = false;
      document.getElementById("startCameraBtn").style.display = "";
      document.getElementById("stopCameraBtn").style.display = "none";
      document.getElementById("qr-status").textContent = "Camera stopped.";
    });
  }
}

// ── Manual Search ────────────────────────────────────────────
const manualInput = document.getElementById("manualInput");
const manualSearchBtn = document.getElementById("manualSearchBtn");
const searchResultsDiv = document.getElementById("searchResults");

manualSearchBtn.addEventListener("click", () => {
  const val = manualInput.value.trim();
  if (!val) return;

  // If it looks like a ticket ID, verify directly
  if (/^CIT-/i.test(val)) {
    verifyTicket(val.toUpperCase());
  } else {
    // Search by name via AJAX
    searchByName(val);
  }
});

manualInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter") manualSearchBtn.click();
});

function searchByName(name) {
  const formData = new FormData();
  formData.append("ticket_id", "__NAME_SEARCH__" + name); // sentinel prefix
  // Use GET-based search endpoint instead
  fetch("search.php?q=" + encodeURIComponent(name))
    .then((r) => r.json())
    .then((data) => {
      searchResultsDiv.innerHTML = "";
      if (!data.results || data.results.length === 0) {
        searchResultsDiv.innerHTML =
          '<div class="search-no-results">No registrations found for "' +
          escapeHtml(name) +
          '"</div>';
        return;
      }
      data.results.forEach((reg) => {
        const item = document.createElement("div");
        item.className = "search-result-item";
        item.innerHTML = `
          <div class="srn">${escapeHtml(reg.full_name)}</div>
          <div class="src">${escapeHtml(reg.ticket_id)} · Tour ${escapeHtml(reg.tour_number)}</div>
        `;
        item.addEventListener("click", () => verifyTicket(reg.ticket_id));
        searchResultsDiv.appendChild(item);
      });
    })
    .catch(() => {
      searchResultsDiv.innerHTML =
        '<div class="search-no-results">Search error. Try again.</div>';
    });
}

// ── Verify Ticket via AJAX ───────────────────────────────────
function verifyTicket(ticketId) {
  showResultLoading();
  if (scanning) stopCamera();

  const formData = new FormData();
  formData.append("ticket_id", ticketId);

  fetch("scan.php", { method: "POST", body: formData })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        showResultSuccess(data);
      } else {
        showResultError(data.error || "Ticket not found.");
      }
    })
    .catch(() => showResultError("Network error. Try again."));
}

// ── Result Rendering ─────────────────────────────────────────
function showResultLoading() {
  resultPanel.innerHTML = `
    <div class="scan-empty-state">
      <div class="scan-spinner"></div>
      <p style="margin-top:16px;color:#666">Verifying…</p>
    </div>`;
}

function showResultSuccess(data) {
  const a = data.attendee;
  const msg = data.just_checked_in
    ? {
        icon: "✅",
        title: "Verified & Checked In!",
        cls: "result-success",
        badge: "Just Checked In",
      }
    : {
        icon: "⚠️",
        title: "Already Checked In",
        cls: "result-warning",
        badge: "Already In",
      };

  resultPanel.innerHTML = `
    <div class="scan-result ${msg.cls}">
      <div class="result-icon">${msg.icon}</div>
      <h2 class="result-title">${msg.title}</h2>
      <div class="result-name">${escapeHtml(a.full_name)}</div>
      <div class="result-badge">${msg.badge}</div>
      <div class="result-grid">
        <div class="result-item"><span>Ticket</span><strong>${escapeHtml(a.ticket_id)}</strong></div>
        <div class="result-item"><span>Tour</span><strong>Innoventure ${escapeHtml(a.tour_number)}</strong></div>
        <div class="result-item"><span>Date</span><strong>${escapeHtml(a.tour_date)}</strong></div>
        <div class="result-item"><span>Time</span><strong>${escapeHtml(a.time)}</strong></div>
        <div class="result-item"><span>Location</span><strong>${escapeHtml(a.location)}</strong></div>
        <div class="result-item"><span>Age</span><strong>${escapeHtml(a.age_group || "—")}</strong></div>
        <div class="result-item"><span>School</span><strong>${escapeHtml(a.school || "—")}</strong></div>
        <div class="result-item"><span>Email</span><strong>${escapeHtml(a.email)}</strong></div>
      </div>
      <button class="admin-btn admin-btn-outline" style="margin-top:20px;width:100%" onclick="resetScan()">
        Scan Next Attendee
      </button>
    </div>`;
}

function showResultError(msg) {
  resultPanel.innerHTML = `
    <div class="scan-result result-error">
      <div class="result-icon">❌</div>
      <h2 class="result-title">Not Found</h2>
      <p style="color:#c62828;margin-bottom:20px">${escapeHtml(msg)}</p>
      <button class="admin-btn admin-btn-outline" style="width:100%" onclick="resetScan()">Try Again</button>
    </div>`;
}

function resetScan() {
  resultPanel.innerHTML = `
    <div class="scan-empty-state">
      <div style="font-size:64px;margin-bottom:16px">🎟</div>
      <h3>Awaiting Scan</h3>
      <p>Scan a QR code or search for an attendee to verify their ticket</p>
    </div>`;
  if (manualInput) manualInput.value = "";
  if (searchResultsDiv) searchResultsDiv.innerHTML = "";
}

function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}
