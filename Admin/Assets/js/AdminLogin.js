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

// Optional: Automatically fade out error message after a few seconds
window.addEventListener("DOMContentLoaded", () => {
  const errorMessage = document.getElementById("error-message");
  if (errorMessage && errorMessage.classList.contains("show")) {
    setTimeout(() => {
      errorMessage.classList.remove("show");
    }, 4000); // Hide after 4 seconds
  }
});
