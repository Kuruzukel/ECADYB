function togglePass(id) {
  const input = document.getElementById(id);
  const passField = input.parentElement; // .passwordField div
  const isVisible = passField.getAttribute("data-isvisible") === "true";

  input.type = isVisible ? "password" : "text";
  passField.setAttribute("data-isvisible", !isVisible);
}

// Limit input length for an ID field
function limitID() {
  const input = document.getElementById("idInput");
  if (!input) return;
  const maxLength = parseInt(input.getAttribute("maxlength"), 10);
  if (input.value.length > maxLength) {
    input.value = input.value.slice(0, maxLength);
  }
}

// Auto fade out error message after a few seconds
window.addEventListener("DOMContentLoaded", () => {
  const errorMessage = document.getElementById("error-message");
  if (errorMessage && errorMessage.classList.contains("show")) {
    setTimeout(() => {
      errorMessage.classList.remove("show");
    }, 4000); // Hide after 4 seconds
  }
});
