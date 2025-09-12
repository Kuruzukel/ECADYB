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
  const errorMessage = document.getElementById("error-message");
  if (errorMessage && errorMessage.classList.contains("show")) {
    setTimeout(() => {
      errorMessage.classList.remove("show");
    }, 4000); // Hide after 4 seconds
  }

  // Add form submission handler
  const loginForm = document.querySelector('form');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      // Only prevent default if we're handling the animation
      if (!e.defaultPrevented) {
        e.preventDefault();
        
        // Add the modern page transition class to body
        document.body.classList.add('page-transition-out');
        
        // Submit the form after the animation completes
        setTimeout(() => {
          loginForm.submit();
        }, 1000); // Match this with the CSS animation duration
      }
    });
  }
});
