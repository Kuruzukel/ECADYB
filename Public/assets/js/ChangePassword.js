let otpSent = false;
let otpCode = null;
let emailVerified = false;
let emailExists = false;

const emailInput = document.getElementById("New Password");
const verificationCodeInput = document.getElementById("Confirm Password");
const submitButton = document.querySelector(".submit-button");
const form = document.querySelector("form");

window.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("page-transition-in");

  submitButton.disabled = false;
  submitButton.style.opacity = "1";
  submitButton.style.cursor = "pointer";

  setupEventListeners();
  setupPasswordToggles();

  checkForServerMessages();
});

function setupEventListeners() {
  emailInput.addEventListener("input", function () {
    limitID();
    updateSubmitButton();
  });

  verificationCodeInput.addEventListener("input", function () {
    updateSubmitButton();
  });

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    handleFormSubmission();
  });

  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    backButton.addEventListener("click", function (e) {
      e.preventDefault();
      const baseUrl = window.location.pathname.includes("/ECADYB/")
        ? "/ECADYB/"
        : "/";
      window.location.href = baseUrl + "ForgotPassword";
    });
  }

  const errorModal = document.getElementById("errorModal");
  if (errorModal) {
    errorModal.addEventListener("click", function (e) {
      if (e.target === errorModal) {
        hideErrorModal();
      }
    });
  }
}

function setupPasswordToggles() {
  const containers = document.querySelectorAll(".input-container");
  containers.forEach((container) => {
    const openIcon = container.querySelector(".eyeIcon.open");
    const closeIcon = container.querySelector(".eyeIcon.close");
    const input = container.querySelector("input");
    if (!openIcon || !closeIcon || !input) return;

    const toggle = () => {
      const isVisible = container.getAttribute("data-isvisible") === "true";
      container.setAttribute("data-isvisible", (!isVisible).toString());
      input.type = isVisible ? "password" : "text";
    };

    openIcon.addEventListener("click", toggle);
    closeIcon.addEventListener("click", toggle);
  });
}

function limitID() {
  const maxLength = parseInt(emailInput.getAttribute("maxlength"), 10) || 8;
  if (emailInput.value.length > maxLength) {
    emailInput.value = emailInput.value.slice(0, maxLength);
  }
}

function validatePasswords() {
  const newPass = emailInput.value.trim();
  const confirmPass = verificationCodeInput.value.trim();

  if (newPass.length !== 8) {
    return false;
  }
  if (confirmPass.length !== 8) {
    return false;
  }
  if (newPass !== confirmPass) {
    return false;
  }
  return true;
}

function updateSubmitButton() {
  submitButton.disabled = false;
  submitButton.style.opacity = "1";
  submitButton.style.cursor = "pointer";
}

async function handleFormSubmission() {
  const newPass = emailInput.value.trim();
  const confirmPass = verificationCodeInput.value.trim();

  if (!validatePasswords()) {
    showErrorModal("Passwords must be 8 characters and match.");
    return;
  }

  submitButton.textContent = "Processing...";
  submitButton.disabled = true;
  try {
    setTimeout(() => {
      showErrorModal("Password changed successfully.", "success");
      submitButton.textContent = "Change Password";
      submitButton.disabled = false;
    }, 800);
  } catch (error) {
    showErrorModal("Network error. Please try again.");
    submitButton.textContent = "Change Password";
    submitButton.disabled = false;
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

  setTimeout(
    () => {
      hideErrorModal();
    },
    type === "success" ? 5000 : 3500
  );
}

function hideErrorModal() {
  const errorModal = document.getElementById("errorModal");
  errorModal.classList.add("hide");

  setTimeout(() => {
    errorModal.classList.remove("show", "hide");
    errorModal.className = "error-modal";
  }, 400);
}

function highlightField(fieldId, message = null) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = "#dc3545";
    field.style.boxShadow = "0 0 0 3px rgba(220, 53, 69, 0.2)";
    field.style.transition = "all 0.3s ease";

    if (message) {
      showErrorModal(message);
    }

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

function checkForServerMessages() {
  const errorMessage = document.body.getAttribute("data-error-message");
  if (errorMessage) {
    showErrorModal(errorMessage);
  }

  const successMessage = document.body.getAttribute("data-success-message");
  if (successMessage) {
    showErrorModal(successMessage, "success");
  }
}
