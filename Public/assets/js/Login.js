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

// Handle form submission with modern page transition
window.addEventListener("DOMContentLoaded", () => {
  // Add entrance animation to body when page loads
  document.body.classList.add('page-transition-in');
  
  const errorMessage = document.getElementById("error-message");
  if (errorMessage && errorMessage.classList.contains("show")) {
    setTimeout(() => {
      errorMessage.classList.remove("show");
    }, 4000); // Hide after 4 seconds
  }

  // Check if login was successful and trigger transition
  const loginSuccess = document.body.getAttribute('data-login-success');
  const redirectTo = document.body.getAttribute('data-redirect-to');
  
  if (loginSuccess && redirectTo) {
    // Add the modern page transition class to body
    document.body.classList.add('page-transition-out');
    
    // Redirect after the animation completes
    setTimeout(() => {
      window.location.href = redirectTo;
    }, 1000); // Match this with the CSS animation duration
  }

  // Add form submission handler for normal form submission
  const loginForm = document.querySelector('form');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      // Let the form submit normally to process login
      // The transition will be handled after successful login detection
    });
  }
});
