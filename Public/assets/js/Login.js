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

// Error Modal Functions
function showErrorModal(message) {
  const errorModal = document.getElementById('errorModal');
  const errorMessage = document.getElementById('errorMessage');
  
  errorMessage.textContent = message;
  errorModal.classList.add('show');
  
  // Auto-hide after 3 seconds
  setTimeout(() => {
    hideErrorModal();
  }, 3000);
}

function hideErrorModal() {
  const errorModal = document.getElementById('errorModal');
  errorModal.classList.add('hide');
  
  setTimeout(() => {
    errorModal.classList.remove('show', 'hide');
  }, 300);
}

// Client-side form validation
function validateForm() {
  const username = document.getElementById('idInput').value.trim();
  const password = document.getElementById('loginPass').value.trim();
  
  if (!username || !password) {
    showErrorModal('Please fill in all fields.');
    return false;
  }
  
  if (password.length > 8) {
    showErrorModal('Password must not exceed 8 characters.');
    return false;
  }
  
  return true;
}

// Handle form submission with modern page transition
window.addEventListener("DOMContentLoaded", () => {
  // Add entrance animation to body when page loads
  document.body.classList.add('page-transition-in');
  
  // Check for server-side error message
  const errorMessage = document.body.getAttribute('data-error-message');
  if (errorMessage) {
    showErrorModal(errorMessage);
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

  // Add form submission handler
  const loginForm = document.querySelector('form');
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      // Validate form before submission
      if (!validateForm()) {
        e.preventDefault();
        return false;
      }
      
      // Let the form submit normally to process login
      // The transition will be handled after successful login detection
    });
  }
  
  // Close modal when clicking outside
  const errorModal = document.getElementById('errorModal');
  if (errorModal) {
    errorModal.addEventListener('click', function(e) {
      if (e.target === errorModal) {
        hideErrorModal();
      }
    });
  }
});
