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

// Handle form submission with fade out animation
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
        
        // Add the fade-out class to trigger the animation
        const loginCard = document.querySelector('.loginCard');
        if (loginCard) {
          loginCard.classList.add('fade-out');
          
          // Submit the form after the animation completes
          setTimeout(() => {
            loginForm.submit();
          }, 500); // Match this with the CSS animation duration
        } else {
          // Fallback in case .loginCard is not found
          loginForm.submit();
        }
      }
    });
  }
});
