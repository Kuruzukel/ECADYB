let otpSent = false;
let otpCode = null;
let emailVerified = false;
let emailExists = false;

const emailInput = document.getElementById("idInput");
const verificationCodeInput = document.getElementById("verificationCodeInput");
const submitButton = document.querySelector(".submit-button");
const getCodeText = document.querySelector(".get-code-text");
const form = document.querySelector("form");

window.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("page-transition-in");

  submitButton.disabled = false;
  submitButton.style.opacity = "1";
  submitButton.style.cursor = "pointer";

  verificationCodeInput.disabled = true;
  verificationCodeInput.style.opacity = "0.5";
  verificationCodeInput.style.cursor = "not-allowed";

  updateGetCodeButton();

  setupEventListeners();

  // Check for error messages from server
  checkForServerMessages();
});

function setupEventListeners() {
  // Email input validation
  emailInput.addEventListener("input", function () {
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
  verificationCodeInput.addEventListener("input", function () {
    const sanitized = verificationCodeInput.value
      .replace(/\D+/g, "")
      .slice(0, 6);
    if (verificationCodeInput.value !== sanitized) {
      verificationCodeInput.value = sanitized;
    }
    // Don't validate on input - only validate on submit
    clearFieldHighlight("verificationCodeInput");
    updateSubmitButton();
  });

  // Get Code button click
  getCodeText.addEventListener("click", function () {
    handleGetCode();
  });

  // Form submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();
    handleFormSubmission();
  });

  // Back button
  const backButton = document.querySelector('button[type="back"]');
  if (backButton) {
    backButton.addEventListener("click", function (e) {
      e.preventDefault();

      // Auto-detect base URL for Railway vs Localhost
      const isLocalhost =
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1";
      const BASE_URL = isLocalhost ? "/ECADYB/" : "/";

      window.location.href = BASE_URL + "login";
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
    const response = await fetch("/Connection/Student/CheckEmail.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email: email }),
    });

    const result = await response.json();
    return result.exists;
  } catch (error) {
    console.error("Error checking email:", error);
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
    highlightField(
      "verificationCodeInput",
      "Verification code must be 6 digits."
    );
    return false;
  }

  if (!/^\d{6}$/.test(code)) {
    // Only show error if there's content but it contains non-digits
    highlightField(
      "verificationCodeInput",
      "Verification code must contain only numbers."
    );
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
    showNotification("Please enter your email address first.", "error");
    highlightField("idInput");
    return;
  }

  if (!validateEmail()) {
    showNotification(
      "Please enter a valid email address format (e.g., user@example.com).",
      "error"
    );
    highlightField("idInput");
    return;
  }

  // Show loading state
  getCodeText.textContent = "Checking...";
  getCodeText.style.pointerEvents = "none";

  try {
    // Check if email exists in database
    const emailCheckResponse = await fetch(
      "/Connection/Student/CheckEmail.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email: email }),
      }
    );

    if (!emailCheckResponse.ok) {
      throw new Error(`Server error: ${emailCheckResponse.status}`);
    }

    const emailCheckResult = await emailCheckResponse.json();

    if (!emailCheckResult.exists) {
      showNotification(
        "This email address is not registered in our system. Please check your email or contact support for assistance.",
        "error"
      );
      highlightField("idInput");
      resetGetCodeButton();
      return;
    }

    // Update loading text
    getCodeText.textContent = "Sending...";

    // Generate and send OTP (using SendGrid for Railway compatibility)
    const otpResponse = await fetch("/Connection/Student/SendOTPSendGrid.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email: email }),
    });

    if (!otpResponse.ok) {
      const errorText = await otpResponse.text();
      console.error("OTP Response error:", errorText);
      throw new Error(`Server error: ${otpResponse.status}`);
    }

    const otpResult = await otpResponse.json();

    if (otpResult.success) {
      otpCode = otpResult.otp;
      otpSent = true;
      emailVerified = true;
      emailExists = true;

      // Email was sent successfully
      showNotification(
        "Verification code sent successfully! Please check your email inbox.",
        "success"
      );

      // Start 60-second countdown
      let countdown = 60;
      getCodeText.textContent = `Resend (${countdown}s)`;
      getCodeText.style.color = "#28a745";
      getCodeText.style.pointerEvents = "none";

      const countdownInterval = setInterval(() => {
        countdown--;
        if (countdown > 0) {
          getCodeText.textContent = `Resend (${countdown}s)`;
        } else {
          clearInterval(countdownInterval);
          getCodeText.textContent = "Get Code";
          getCodeText.style.color = "#6366f1";
          getCodeText.style.pointerEvents = "auto";
          otpSent = false; // Allow resending after countdown
        }
      }, 1000);

      // Enable verification code input only after OTP is sent
      verificationCodeInput.disabled = false;
      verificationCodeInput.style.opacity = "1";
      verificationCodeInput.style.cursor = "text";

      // Focus on verification code input
      verificationCodeInput.focus();
    } else {
      showNotification(
        otpResult.message ||
          "Failed to send verification code. Please try again.",
        "error"
      );
      resetGetCodeButton();
    }
  } catch (error) {
    console.error("Error:", error);
    showNotification(
      "Network error. Please check your connection and try again.",
      "error"
    );
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
    showNotification("Please fill in all required fields.", "error");
    return;
  }

  // Check if only verification code is filled
  if (!email && verificationCode) {
    showNotification(
      "Please enter your email address first before entering the verification code.",
      "error"
    );
    highlightField("idInput");
    return;
  }

  // Check if email is empty
  if (!email) {
    showNotification("Please enter your email address.", "error");
    highlightField("idInput");
    return;
  }

  // Check if verification code input is disabled (OTP not sent)
  if (verificationCodeInput.disabled) {
    showNotification(
      "Please click 'Get Code' to receive your verification code first.",
      "error"
    );
    return;
  }

  // Check if verification code is empty
  if (!verificationCode) {
    showNotification("Please enter the verification code.", "error");
    highlightField("verificationCodeInput");
    return;
  }

  // Validate email format
  if (!validateEmail()) {
    showNotification(
      "Please enter a valid email address format (e.g., user@example.com).",
      "error"
    );
    highlightField("idInput");
    return;
  }

  // Validate verification code format
  if (!validateVerificationCode()) {
    return; // Error message already shown by validateVerificationCode
  }

  // Verify OTP if available
  if (otpCode && verificationCode !== otpCode) {
    showNotification(
      "Invalid verification code. Please check and try again.",
      "error"
    );
    highlightField("verificationCodeInput");
    return;
  }

  // Show loading state
  submitButton.textContent = "Processing...";
  submitButton.disabled = true;

  try {
    // Submit the form with verification
    const response = await fetch("/Connection/Student/ForgotPassword.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        email: email,
        verificationCode: verificationCode,
      }),
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error("Server error response:", errorText);
      throw new Error(`Server error: ${response.status}`);
    }

    let result;
    try {
      result = await response.json();
    } catch (jsonError) {
      console.error("JSON parse error:", jsonError);
      throw new Error("Invalid server response. Please try again.");
    }

    if (result.success) {
      showNotification(
        "Password reset successful! Please check your email for your new password.",
        "success"
      );

      // Auto-detect base URL for Railway vs Localhost
      const isLocalhost =
        window.location.hostname === "localhost" ||
        window.location.hostname === "127.0.0.1";
      const BASE_URL = isLocalhost ? "/ECADYB/" : "/";

      // Redirect to login after 3 seconds
      setTimeout(() => {
        window.location.href = BASE_URL + "login";
      }, 3000);
    } else {
      showNotification(
        result.message || "Password reset failed. Please try again.",
        "error"
      );
      submitButton.textContent = "Submit";
      submitButton.disabled = false;
    }
  } catch (error) {
    console.error("Error:", error);
    const errorMessage =
      error.message && error.message.includes("Invalid server response")
        ? error.message
        : "Network error. Please check your connection and try again.";
    showNotification(errorMessage, "error");
    submitButton.textContent = "Submit";
    submitButton.disabled = false;
  }
}

function showNotification(message, type) {
  // Remove any existing notification
  const existingNotification = document.querySelector(".notification");
  if (existingNotification) {
    existingNotification.remove();
  }

  // Create notification element
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

  // Trigger animation
  setTimeout(() => {
    notification.classList.add("show");
  }, 10);

  // Auto-hide after 4 seconds (or 15 seconds for success messages with OTP code)
  const autoHideDelay = message.includes("verification code is:")
    ? 15000
    : type === "success"
    ? 5000
    : 4000;
  setTimeout(() => {
    closeNotification(`${type}-message`);
  }, autoHideDelay);
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

function highlightField(fieldId, message = null) {
  const field = document.getElementById(fieldId);
  if (field) {
    field.style.borderColor = "#dc3545";
    field.style.boxShadow = "0 0 0 3px rgba(220, 53, 69, 0.2)";
    field.style.transition = "all 0.3s ease";

    if (message) {
      showNotification(message, "error");
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
    showNotification(errorMessage, "error");
  }

  const successMessage = document.body.getAttribute("data-success-message");
  if (successMessage) {
    showNotification(successMessage, "success");
  }
}
