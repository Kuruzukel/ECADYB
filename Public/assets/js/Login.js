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

function showNotification(message, type) {
  const existingNotification = document.querySelector(".notification");
  if (existingNotification) {
    existingNotification.remove();
  }

  const notification = document.createElement("div");
  notification.className = `notification ${type}-message`;
  notification.id = `${type}-message`;

  notification.innerHTML = `
    <span class="notification-message">${message}</span>
    <button class="notification-close" onclick="closeNotification('${type}-message')">
      <i class="fas fa-times"></i>
    </button>
  `;

  document.body.appendChild(notification);

  // Trigger animation
  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

  // Auto-hide after 4 seconds
  setTimeout(() => {
    closeNotification(`${type}-message`);
  }, 4000);
}

function closeNotification(id) {
  const notification = document.getElementById(id);
  if (notification) {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 500);
  }
}

function validateForm() {
  const username = document.getElementById("idInput").value.trim();
  const password = document.getElementById("loginPass").value.trim();

  clearFieldHighlights();

  if (!username && !password) {
    showNotification("Please fill in all required fields.", "error");
    highlightField("idInput");
    highlightField("loginPass");
    return false;
  }

  if (!username) {
    showNotification("Please enter your username or student ID.", "error");
    highlightField("idInput");
    return false;
  }

  if (!password) {
    showNotification("Please enter your password.", "error");
    highlightField("loginPass");
    return false;
  }

  if (password.length > 8) {
    showNotification("Password must not exceed 8 characters.", "error");
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

  const errorMessage = document.getElementById("error-message");
  if (errorMessage && errorMessage.classList.contains("show")) {
    setTimeout(() => {
      closeNotification("error-message");
    }, 4000);
  }

  const loginForm = document.querySelector("form");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      const isValid = validateForm();
      if (!isValid) {
        e.preventDefault();
        return false;
      }
    });
  }

  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    backButton.addEventListener("click", function (e) {
      e.preventDefault();

      document.body.classList.add("page-transition-out");

      // Auto-detect base URL for Railway vs Localhost
      const isLocalhost =
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1";
      const BASE_URL = isLocalhost ? "/ECADYB/" : "/";

      setTimeout(() => {
        window.location.href = BASE_URL + "LandingPage/";
      }, 1000);
    });
  }
});
