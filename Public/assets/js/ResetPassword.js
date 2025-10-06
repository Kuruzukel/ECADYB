// Global variables
let otpSent = false;
let otpCode = null;
let emailVerified = false;

// DOM elements
const emailInput = document.getElementById("idInput");
const verificationCodeInput = document.getElementById("verificationCodeInput");
const submitButton = document.querySelector(".submit-button");
const getCodeText = document.querySelector(".get-code-text");
const form = document.querySelector("form");

// Initialize the page
window.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("page-transition-in");
  
  // Disable submit button initially
  updateSubmitButton();
  
  // Add event listeners
  setupEventListeners();
  
  // Check for error messages from server
  checkForServerMessages();
});

function setupEventListeners() {
  // Email input validation
  emailInput.addEventListener("input", function() {
    limitID();
    validateEmail();
    updateSubmitButton();
  });

  // Verification code input validation
  verificationCodeInput.addEventListener("input", function() {
    validateVerificationCode();
    updateSubmitButton();
  });

  // Get Code button click
  getCodeText.addEventListener("click", function() {
    handleGetCode();
  });

  // Form submission
  form.addEventListener("submit", function(e) {
    e.preventDefault();
    handleFormSubmission();
  });

  // Back button
  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    backButton.addEventListener("click", function(e) {
      e.preventDefault();
      window.location.href = "/Public/Components/Login.php";
    });
  }

  // Error modal click outside to close
  const errorModal = document.getElementById("errorModal");
  if (errorModal) {
    errorModal.addEventListener("click", function(e) {
      if (e.target === errorModal) {
        hideErrorModal();
      }
    });
  }
}

function limitID() {
  const maxLength = parseInt(emailInput.getAttribute("maxlength"), 10) || 100;
  if (emailInput.value.length > maxLength) {
    emailInput.value = emailInput.value.slice(0, maxLength);
  }
}

function validateEmail() {
  const email = emailInput.value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  if (!email) {
    clearFieldHighlight("idInput");
    return false;
  }
  
  if (!emailRegex.test(email)) {
    highlightField("idInput", "Please enter a valid email address.");
    return false;
  }
  
  clearFieldHighlight("idInput");
  return true;
}

function validateVerificationCode() {
  const code = verificationCodeInput.value.trim();
  
  if (!code) {
    clearFieldHighlight("verificationCodeInput");
    return false;
  }
  
  if (code.length !== 6) {
    highlightField("verificationCodeInput", "Verification code must be 6 digits.");
    return false;
  }
  
  if (!/^\d{6}$/.test(code)) {
    highlightField("verificationCodeInput", "Verification code must contain only numbers.");
    return false;
  }
  
  clearFieldHighlight("verificationCodeInput");
  return true;
}

function updateSubmitButton() {
  const emailValid = validateEmail();
  const codeValid = validateVerificationCode();
  const canSubmit = emailValid && codeValid && otpSent && emailVerified;
  
  if (canSubmit) {
    submitButton.disabled = false;
    submitButton.style.opacity = "1";
    submitButton.style.cursor = "pointer";
  } else {
    submitButton.disabled = true;
    submitButton.style.opacity = "0.6";
    submitButton.style.cursor = "not-allowed";
  }
}

async function handleGetCode() {
  const email = emailInput.value.trim();
  
  if (!email) {
    showErrorModal("Please enter your email address first.");
    highlightField("idInput");
    return;
  }
  
  if (!validateEmail()) {
    return;
  }
  
  // Show loading state
  getCodeText.textContent = "Sending...";
  getCodeText.style.pointerEvents = "none";
  
  try {
    // Check if email exists in database
    const emailCheckResponse = await fetch('/Connection/Student/CheckEmail.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email: email })
    });
    
    const emailCheckResult = await emailCheckResponse.json();
    
    if (!emailCheckResult.exists) {
      showErrorModal("Email address not found in our database. Please check your email or contact support.");
      highlightField("idInput");
      resetGetCodeButton();
      return;
    }
    
    // Generate and send OTP
    const otpResponse = await fetch('/Connection/Student/SendOTP.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email: email })
    });
    
    const otpResult = await otpResponse.json();
    
    if (otpResult.success) {
      otpCode = otpResult.otp;
      otpSent = true;
      emailVerified = true;
      
      showErrorModal("Verification code sent to your email address.", "success");
      getCodeText.textContent = "Code Sent";
      getCodeText.style.color = "#28a745";
      
      // Focus on verification code input
      verificationCodeInput.focus();
    } else {
      showErrorModal(otpResult.message || "Failed to send verification code. Please try again.");
      resetGetCodeButton();
    }
    
  } catch (error) {
    console.error('Error:', error);
    showErrorModal("Network error. Please check your connection and try again.");
    resetGetCodeButton();
  }
}

function resetGetCodeButton() {
  getCodeText.textContent = "Get Code";
  getCodeText.style.pointerEvents = "auto";
  getCodeText.style.color = "#6366f1";
}

async function handleFormSubmission() {
  const email = emailInput.value.trim();
  const verificationCode = verificationCodeInput.value.trim();
  
  // Final validation
  if (!validateEmail() || !validateVerificationCode()) {
    return;
  }
  
  if (!otpSent || !emailVerified) {
    showErrorModal("Please get and verify your code first.");
    return;
  }
  
  // Verify OTP
  if (verificationCode !== otpCode) {
    showErrorModal("Invalid verification code. Please check and try again.");
    highlightField("verificationCodeInput");
    return;
  }
  
  // Show loading state
  submitButton.textContent = "Processing...";
  submitButton.disabled = true;
  
  try {
    // Submit the form with verification
    const response = await fetch('/Connection/Student/ResetPassword.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ 
        email: email, 
        verificationCode: verificationCode 
      })
    });
    
    const result = await response.json();
    
    if (result.success) {
      showErrorModal("Password reset successful! Please check your email for your new password.", "success");
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        window.location.href = "/Public/Components/Login.php";
      }, 3000);
    } else {
      showErrorModal(result.message || "Password reset failed. Please try again.");
      submitButton.textContent = "Submit";
      submitButton.disabled = false;
    }
    
  } catch (error) {
    console.error('Error:', error);
    showErrorModal("Network error. Please check your connection and try again.");
    submitButton.textContent = "Submit";
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

  setTimeout(() => {
    hideErrorModal();
  }, type === "success" ? 5000 : 3500);
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