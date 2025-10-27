function togglePass(id) {
  const input = document.getElementById(id);
  const passField = input.parentElement;
  const isVisible = passField.getAttribute("data-isvisible") === "true";

  input.type = isVisible ? "password" : "text";
  passField.setAttribute("data-isvisible", !isVisible);
}

function limitID() {
  const input = document.getElementById("idInput");
  if (!input) return;
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

  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

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

function validateForm(event) {
  event.preventDefault();

  const currentPassword = document
    .getElementById("currrentpassword")
    .value.trim();
  const newPassword = document.getElementById("newpassword").value.trim();
  const confirmPassword = document
    .getElementById("confirmpassword")
    .value.trim();

  if (!currentPassword || !newPassword || !confirmPassword) {
    showNotification("Please fill in all password fields.", "error");
    return false;
  }

  if (newPassword !== confirmPassword) {
    showNotification(
      "New password and confirm password do not match.",
      "error"
    );
    return false;
  }

  if (newPassword.length > 8) {
    showNotification("Password must not exceed 8 characters.", "error");
    return false;
  }

  document.getElementById("changePasswordForm").submit();
  return true;
}

window.addEventListener("DOMContentLoaded", () => {
  const errorMessage = document.getElementById("error-message");
  const successMessage = document.getElementById("success-message");

  if (errorMessage && errorMessage.classList.contains("show")) {
    setTimeout(() => {
      closeNotification("error-message");
    }, 4000);
  }

  if (successMessage && successMessage.classList.contains("show")) {
    setTimeout(() => {
      window.location.href = basePath + "/Login";
    }, 3000);
  }
});
