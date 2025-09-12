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

// Enhanced Error Modal Functions
function showErrorModal(message, type = 'error') {
  const errorModal = document.getElementById('errorModal');
  const errorMessage = document.getElementById('errorMessage');
  
  // Set modal type for styling
  errorModal.className = `error-modal ${type === 'success' ? 'success-modal' : ''}`;
  
  errorMessage.textContent = message;
  errorModal.classList.add('show');
  
  // Add entrance animation delay
  setTimeout(() => {
    errorModal.querySelector('.error-modal-content').style.animation = 
      'errorModalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards';
  }, 50);
  
  // Auto-hide after 3.5 seconds with smooth transition
  setTimeout(() => {
    hideErrorModal();
  }, 3500);
}

function hideErrorModal() {
  const errorModal = document.getElementById('errorModal');
  errorModal.classList.add('hide');
  
  setTimeout(() => {
    errorModal.classList.remove('show', 'hide');
    errorModal.className = 'error-modal'; // Reset to default
  }, 400);
}

// Enhanced client-side form validation
function validateForm() {
  const username = document.getElementById('idInput').value.trim();
  const password = document.getElementById('loginPass').value.trim();
  
  // Clear any existing highlights
  clearFieldHighlights();
  
  if (!username && !password) {
    showErrorModal('Please fill in all required fields.');
    highlightField('idInput');
    highlightField('loginPass');
    return false;
  }
  
  if (!username) {
    showErrorModal('Please enter your username or student ID.');
    highlightField('idInput');
    return false;
  }
  
  if (!password) {
    showErrorModal('Please enter your password.');
    highlightField('loginPass');
    return false;
  }
  
  if (password.length > 8) {
    showErrorModal('Password must not exceed 8 characters.');
    highlightField('loginPass');
    return false;
  }
  
  return true;
}

// Field highlighting functions
function highlightField(fieldId) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = '#dc3545';
    field.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.2)';
    field.style.transition = 'all 0.3s ease';
    
    // Remove highlight after 3 seconds
    setTimeout(() => {
      clearFieldHighlight(fieldId);
    }, 3000);
  }
}

function clearFieldHighlight(fieldId) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = '';
    field.style.boxShadow = '';
  }
}

function clearFieldHighlights() {
  clearFieldHighlight('idInput');
  clearFieldHighlight('loginPass');
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
  
  // Add modern page transition to back button
  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    backButton.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Add page transition
      document.body.classList.add('page-transition-out');
      
      // Navigate after animation
      setTimeout(() => {
        window.location.href = '../../LandingPage/LandingPage.html';
      }, 1000);
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
