document.addEventListener("DOMContentLoaded", () => {
  const contactForm = document.getElementById("contactForm");
  const sendBtn = document.getElementById("sendBtn");
  const nameInput = document.getElementById("name");
  const emailInput = document.getElementById("email");
  const subjectInput = document.getElementById("subject");
  const messageInput = document.getElementById("message");

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

  if (contactForm) {
    contactForm.addEventListener("submit", (e) => {
      e.preventDefault(); // Prevent default submission

      let isValid = true;

      // Validate Name
      if (!nameInput.value.trim()) {
        nameInput.style.borderColor = "var(--destructive)";
        isValid = false;
      } else {
        nameInput.style.borderColor = "#e2e8f0";
      }

      // Validate Email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailInput.value.trim() || !emailRegex.test(emailInput.value)) {
        emailInput.style.borderColor = "var(--destructive)";
        isValid = false;
      } else {
        emailInput.style.borderColor = "#e2e8f0";
      }

      // Validate Subject
      if (!subjectInput.value.trim()) {
        subjectInput.style.borderColor = "var(--destructive)";
        isValid = false;
      }

      // Validate Message
      if (!messageInput.value.trim()) {
        messageInput.style.borderColor = "var(--destructive)";
        isValid = false;
      }

      if (isValid) {
        // Send to local PHP script
        const formData = new FormData();
        formData.append("name", nameInput.value.trim());
        formData.append("email", emailInput.value.trim());
        formData.append("subject", subjectInput.value.trim());
        formData.append("message", messageInput.value.trim());

        // Show loading state
        const originalBtnText = sendBtn.innerText;
        sendBtn.innerText = "Sending...";
        sendBtn.disabled = true;

        fetch("send-mail.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Show Success Modal
              showModal();
              // Clear inputs
              nameInput.value = "";
              emailInput.value = "";
              subjectInput.value = "";
              messageInput.value = "";
            } else {
              const errorMsg = document.getElementById("errorMsg");
              if (errorMsg)
                errorMsg.textContent =
                  data.error ||
                  "Oops! There was a problem submitting your form";
              showErrorModal();
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            const errorMsg = document.getElementById("errorMsg");
            if (errorMsg)
              errorMsg.textContent =
                "Oops! There was a problem submitting your form. Make sure you are running on a server (localhost).";
            showErrorModal();
          })
          .finally(() => {
            sendBtn.innerText = originalBtnText;
            sendBtn.disabled = false;
          });
      }
    });
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
});
