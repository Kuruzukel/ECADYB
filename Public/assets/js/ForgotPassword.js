// Global variables
let otpSent = false;
let otpCode = null;
let emailVerified = false;
let emailExists = false;

// DOM elements
const emailInput = document.getElementById("idInput");
const verificationCodeInput = document.getElementById("verificationCodeInput");
const submitButton = document.querySelector(".submit-button");
const getCodeText = document.querySelector(".get-code-text");
const form = document.querySelector("form");

// Initialize the page
window.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("page-transition-in");
  
  // Enable submit button initially
  submitButton.disabled = false;
  submitButton.style.opacity = "1";
  submitButton.style.cursor = "pointer";
  
  // Disable verification code input initially
  verificationCodeInput.disabled = true;
  verificationCodeInput.style.opacity = "0.5";
  verificationCodeInput.style.cursor = "not-allowed";
  
  updateGetCodeButton();
  
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
    updateGetCodeButton();
    // Disable verification input when email changes
    otpSent = false;
    emailVerified = false;
    verificationCodeInput.value = "";
    verificationCodeInput.disabled = true;
    verificationCodeInput.style.opacity = "0.5";
    verificationCodeInput.style.cursor = "not-allowed";
  });

  // Verification code input validation & sanitize to digits only, max 6
  verificationCodeInput.addEventListener("input", function() {
    const sanitized = verificationCodeInput.value.replace(/\D+/g, '').slice(0, 6);
    if (verificationCodeInput.value !== sanitized) {
      verificationCodeInput.value = sanitized;
    }
    // Don't validate on input - only validate on submit
    clearFieldHighlight("verificationCodeInput");
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
      window.location.href = "Login.php";
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
    emailExists = false;
    return false;
  }
  
  if (!emailRegex.test(email)) {
    // keep validation silent during typing; show errors on action (Get Code)
    emailExists = false;
    return false;
  }
  
  clearFieldHighlight("idInput");
  return true;
}

async function checkEmailExists(email) {
  try {
    const response = await fetch('../../Connection/Student/CheckEmail.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email: email })
    });
    
    const result = await response.json();
    return result.exists;
  } catch (error) {
    console.error('Error checking email:', error);
    return false;
  }
}

function validateVerificationCode() {
  const code = verificationCodeInput.value.trim();
  
  if (!code) {
    // Don't show error for empty field initially
    clearFieldHighlight("verificationCodeInput");
    return false;
  }
  
  if (code.length !== 6) {
    // Only show error if there's content but it's not 6 digits
    highlightField("verificationCodeInput", "Verification code must be 6 digits.");
    return false;
  }
  
  if (!/^\d{6}$/.test(code)) {
    // Only show error if there's content but it contains non-digits
    highlightField("verificationCodeInput", "Verification code must contain only numbers.");
    return false;
  }
  
  clearFieldHighlight("verificationCodeInput");
  return true;
}

function updateSubmitButton() {
  // Always keep submit button enabled
  submitButton.disabled = false;
  submitButton.style.opacity = "1";
  submitButton.style.cursor = "pointer";
}

function updateGetCodeButton() {
  const email = emailInput.value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const isValidEmail = email && emailRegex.test(email);
  
  if (!isValidEmail || otpSent) {
    getCodeText.style.opacity = "0.5";
    getCodeText.style.cursor = "not-allowed";
    getCodeText.style.pointerEvents = "none";
  } else {
    getCodeText.style.opacity = "1";
    getCodeText.style.cursor = "pointer";
    getCodeText.style.pointerEvents = "auto";
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
    showErrorModal("Please enter a valid email address.");
    highlightField("idInput");
    return;
  }
  
  // Show loading state
  getCodeText.textContent = "Checking...";
  getCodeText.style.pointerEvents = "none";
  
  try {
    // Check if email exists in database
    const emailCheckResponse = await fetch('../../Connection/Student/CheckEmail.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ email: email })
    });
    
    const emailCheckResult = await emailCheckResponse.json();
    
    if (!emailCheckResult.exists) {
      showErrorModal("Email address not found in our database. Please check your email address or contact support.");
      highlightField("idInput");
      resetGetCodeButton();
      return;
    }
    
    // Update loading text
    getCodeText.textContent = "Sending...";
    
    // Generate and send OTP
    const otpResponse = await fetch('../../Connection/Student/SendOTP.php', {
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
      emailExists = true;
      
      showErrorModal("Please check your email inbox for your verification code.", "success");
      getCodeText.textContent = "Code Sent";
      getCodeText.style.color = "#28a745";
      getCodeText.style.pointerEvents = "none";
      
      // Enable verification code input only after OTP is sent
      verificationCodeInput.disabled = false;
      verificationCodeInput.style.opacity = "1";
      verificationCodeInput.style.cursor = "text";
      
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
  updateGetCodeButton();
}

async function handleFormSubmission() {
  const email = emailInput.value.trim();
  const verificationCode = verificationCodeInput.value.trim();
  
  // Check if all fields are empty
  if (!email && !verificationCode) {
    showErrorModal("Please fill in all required fields.");
    return;
  }
  
  // Check if only verification code is filled
  if (!email && verificationCode) {
    showErrorModal("Please enter your email address first before entering the verification code.");
    highlightField("idInput");
    return;
  }
  
  // Check if email is empty
  if (!email) {
    showErrorModal("Please enter your email address.");
    highlightField("idInput");
    return;
  }
  
  // Check if verification code input is disabled (OTP not sent)
  if (verificationCodeInput.disabled) {
    showErrorModal("Please click 'Get Code' to receive your verification code first.");
    return;
  }
  
  // Check if verification code is empty
  if (!verificationCode) {
    showErrorModal("Please enter the verification code.");
    highlightField("verificationCodeInput");
    return;
  }
  
  // Validate email format
  if (!validateEmail()) {
    showErrorModal("Please enter a valid email address.");
    highlightField("idInput");
    return;
  }
  
  // Validate verification code format
  if (!validateVerificationCode()) {
    return; // Error message already shown by validateVerificationCode
  }
  
  // Verify OTP if available
  if (otpCode && verificationCode !== otpCode) {
    showErrorModal("Invalid verification code. Please check and try again.");
    highlightField("verificationCodeInput");
    return;
  }
  
  // Show loading state
  submitButton.textContent = "Processing...";
  submitButton.disabled = true;
  
  try {
    // Submit the form with verification
    const response = await fetch('../../Connection/Student/ForgotPassword.php', {
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
        window.location.href = "Login.php";
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