function togglePass(fieldId) {
  const passField = document
    .querySelector(`#${fieldId}`)
    .closest(".passwordField");
  const input = document.getElementById(fieldId);
  const isVisible = passField.getAttribute("data-isvisible") === "true";

  passField.setAttribute("data-isvisible", !isVisible);
  input.type = isVisible ? "password" : "text";
}

function showErrorModal(message, type = "error") {
  const errorModal = document.getElementById("errorModal");
  const errorMessage = document.getElementById("errorMessage");

  errorModal.className = `error-modal ${
    type === "success" ? "success-modal" : ""
  }`;

  errorMessage.textContent = message;
  errorModal.classList.add("show");

  setTimeout(() => {
    errorModal.querySelector(".error-modal-content").style.animation =
      "errorModalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards";
  }, 50);

  setTimeout(() => {
    hideErrorModal();
  }, 3500);
}

function hideErrorModal() {
  const errorModal = document.getElementById("errorModal");
  errorModal.classList.add("hide");

  setTimeout(() => {
    errorModal.classList.remove("show", "hide");
    errorModal.className = "error-modal";
  }, 400);
}

function validateForm() {
  const email = document.getElementById("emailaddress").value.trim();
  const password = document.getElementById("newpassword").value.trim();

  clearFieldHighlights();

  if (!email && !password) {
    showErrorModal("Please fill in all required fields.");
    highlightField("emailaddress");
    highlightField("newpassword");
    return false;
  }

  if (!email) {
    showErrorModal("Please enter your email address.");
    highlightField("emailaddress");
    return false;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showErrorModal("Please enter a valid email address.");
    highlightField("emailaddress");
    return false;
  }

  if (!password) {
    showErrorModal("Please enter your new password.");
    highlightField("newpassword");
    return false;
  }

  if (password.length > 8) {
    showErrorModal("Password must not exceed 8 characters.");
    highlightField("newpassword");
    return false;
  }

  if (password.length < 3) {
    showErrorModal("Password must be at least 3 characters long.");
    highlightField("newpassword");
    return false;
  }

  return true;
}

function highlightField(fieldId) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = "#dc3545";
    field.style.boxShadow = "0 0 0 3px rgba(220, 53, 69, 0.2)";
    field.style.transition = "all 0.3s ease";

    setTimeout(() => {
      clearFieldHighlight(fieldId);
    }, 3000);
  }
}

function clearFieldHighlight(fieldId) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = "";
    field.style.boxShadow = "";
  }
}

function clearFieldHighlights() {
  clearFieldHighlight("emailaddress");
  clearFieldHighlight("newpassword");
}

window.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("page-transition-in");

  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", function (e) {
      if (!validateForm()) {
        e.preventDefault();
        return false;
      }

      e.preventDefault();
      document.body.classList.add("page-transition-out");

      showErrorModal(
        "Password reset request submitted successfully!",
        "success"
      );
      setTimeout(() => {
        window.location.href = "Login.php";
      }, 2000);
    });
  }

  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    // No need to add custom event listener since we're using inline onclick
  }

  // Close modal when clicking outside
  const errorModal = document.getElementById("errorModal");
  if (errorModal) {
    errorModal.addEventListener("click", function (e) {
      if (e.target === errorModal) {
        hideErrorModal();
      }
    });
  }
});
