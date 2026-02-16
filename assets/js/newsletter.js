document.addEventListener("DOMContentLoaded", () => {
  const subscribeForm = document.getElementById("subscribeForm");
  const subscribeBtn = document.getElementById("subscribeBtn");
  const emailInput = document.getElementById("subscribeEmail");
  const successModal = document.getElementById("successModal");
  const errorModal = document.getElementById("errorModal");
  const modalCloseBtn = document.getElementById("modalCloseBtn");
  const modalOkBtn = document.getElementById("modalOkBtn");
  const errorModalCloseBtn = document.getElementById("errorModalCloseBtn");
  const errorModalOkBtn = document.getElementById("errorModalOkBtn");

  function showModal() {
    successModal.classList.add("active");
  }

  function hideModal() {
    successModal.classList.remove("active");
  }

  function showErrorModal() {
    errorModal.classList.add("active");
  }

  function hideErrorModal() {
    errorModal.classList.remove("active");
  }

  if (modalCloseBtn) modalCloseBtn.addEventListener("click", hideModal);
  if (modalOkBtn) modalOkBtn.addEventListener("click", hideModal);

  if (errorModalCloseBtn)
    errorModalCloseBtn.addEventListener("click", hideErrorModal);
  if (errorModalOkBtn)
    errorModalOkBtn.addEventListener("click", hideErrorModal);

  // Close on overlay click
  if (successModal) {
    successModal.addEventListener("click", (e) => {
      if (e.target === successModal) hideModal();
    });
  }

  if (errorModal) {
    errorModal.addEventListener("click", (e) => {
      if (e.target === errorModal) hideErrorModal();
    });
  }

  if (subscribeForm) {
    subscribeForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Prevent default form submission

      const email = emailInput.value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!email || !emailRegex.test(email)) {
        alert("Please enter a valid email address.");
        return;
      }

      // Show loading state
      const originalBtnText = subscribeBtn.innerText;
      subscribeBtn.innerText = "Subscribing...";
      subscribeBtn.disabled = true;

      const formData = new FormData();
      formData.append("email", email);

      fetch("subscribe.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Show Success Modal
            showModal();
            emailInput.value = "";
          } else {
            // Show Error Modal with message
            const errorMsg = document.getElementById("errorMsg");
            if (errorMsg)
              errorMsg.textContent =
                data.error || "Oops! Something went wrong.";
            showErrorModal();
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          // Show Error Modal with default message
          const errorMsg = document.getElementById("errorMsg");
          if (errorMsg)
            errorMsg.textContent =
              "Oops! Something went wrong. Please try again later.";
          showErrorModal();
        })
        .finally(() => {
          subscribeBtn.innerText = originalBtnText;
          subscribeBtn.disabled = false;
        });
    });
  }
});
