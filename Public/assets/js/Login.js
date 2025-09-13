function togglePass() {
  const passField = document.querySelector(".passwordField");
  const input = document.getElementById("loginPass");
  const isVisible = passField.getAttribute("data-isvisible") === "true";

  passField.setAttribute("data-isvisible", !isVisible);
  input.type = isVisible ? "password" : "text";
}

function limitID() {
  const input = document.getElementById("idInput");
  const maxLength = parseInt(input.getAttribute("maxlength"), 10);
  if (input.value.length > maxLength) {
    input.value = input.value.slice(0, maxLength);
  }
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
  const username = document.getElementById("idInput").value.trim();
  const password = document.getElementById("loginPass").value.trim();

  clearFieldHighlights();

  if (!username && !password) {
    showErrorModal("Please fill in all required fields.");
    highlightField("idInput");
    highlightField("loginPass");
    return false;
  }

  if (!username) {
    showErrorModal("Please enter your username or student ID.");
    highlightField("idInput");
    return false;
  }

  if (!password) {
    showErrorModal("Please enter your password.");
    highlightField("loginPass");
    return false;
  }

  if (password.length > 8) {
    showErrorModal("Password must not exceed 8 characters.");
    highlightField("loginPass");
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
  clearFieldHighlight("idInput");
  clearFieldHighlight("loginPass");
}

window.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("page-transition-in");

  const loginSuccess = document.body.getAttribute("data-login-success");
  const redirectTo = document.body.getAttribute("data-redirect-to");

  if (loginSuccess && redirectTo) {
    document.body.classList.add("page-transition-out");

    setTimeout(() => {
      window.location.href = redirectTo;
    }, 1000);
    return;
  }

  const errorMessage = document.body.getAttribute("data-error-message");
  if (errorMessage && !loginSuccess) {
    showErrorModal(errorMessage);
  }

  const loginForm = document.querySelector("form");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const isValid = validateForm();
      if (!isValid) {
        e.preventDefault();
        return false;
      }

      // If validation passes, let the form submit normally to server
      // Server will handle authentication and set redirect attributes
      // Don't show error popup for valid forms
    });
  }

  // Add modern page transition to back button
  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    backButton.addEventListener("click", function (e) {
      e.preventDefault();

      // Add page transition
      document.body.classList.add("page-transition-out");

      // Navigate after animation
      setTimeout(() => {
        window.location.href = "../../LandingPage/LandingPage.html";
      }, 1000);
    });
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
