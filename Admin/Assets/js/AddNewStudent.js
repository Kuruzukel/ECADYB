const themes = {
  "Theme 1": {
    "--primary-bg": "#470a0a",
    "--header-bg": "#b21c0e",
    "--accent": "#fcda15",
    "--section-bg": "#bc4f5e",
    "--section-header": "#cb5382",
    "--body-bg": "#470a0a",
    "--sidebar-bg": "#b21c0e",
    "--content-bg": "#bc4f5e",
    "--menu-bg-active": "#cb5382",
    "--menu-border-active": "#fff176",
    "--menu-hover-bg": "#cb5382",
  },
  "Theme 2": {
    "--primary-bg": "#12086F",
    "--header-bg": "#2B35AF",
    "--accent": "#fcda15",
    "--section-bg": "#4895EF",
    "--section-header": "#4CC9F0",
    "--body-bg": "#12086F",
    "--sidebar-bg": "#2B35AF",
    "--content-bg": "#4895EF",
    "--menu-bg-active": "#4CC9F0",
    "--menu-border-active": "#ffffff",
    "--menu-hover-bg": "#4361EE",
  },
  "Theme 3": {
    "--primary-bg": "#0d381e",
    "--header-bg": "#164f2c",
    "--accent": "#fcda15",
    "--section-bg": "#2a834d",
    "--section-header": "#349e5e",
    "--body-bg": "#0d381e",
    "--sidebar-bg": "#164f2c",
    "--content-bg": "#2a834d",
    "--menu-bg-active": "#349e5e",
    "--menu-border-active": "#ffffff",
    "--menu-hover-bg": "#1f693c",
  },
  "Theme 4": {
    "--primary-bg": "#281E18",
    "--header-bg": "#572D0C",
    "--accent": "#fcda15",
    "--section-bg": "#E3B76A",
    "--section-header": "#9D9C75",
    "--body-bg": "#281E18",
    "--sidebar-bg": "#572D0C",
    "--content-bg": "#E3B76A",
    "--menu-bg-active": "#9D9C75",
    "--menu-border-active": "#ffffff",
    "--menu-hover-bg": "#C78E3A",
  },
  Default: {
    "--primary-bg": "#112d4e",
    "--header-bg": "#0c27be",
    "--accent": "#0c27be",
    "--section-bg": "#34495e",
    "--section-header": "#217ff7",
    "--body-bg": "#000042",
    "--sidebar-bg": "#0c27be",
    "--content-bg": "#112d4e",
    "--menu-bg-active": "#000042",
    "--menu-border-active": "#fcda15",
    "--menu-hover-bg": "#1c1c84",
  },
};

// Apply selected theme
function applyTheme(themeName) {
  const theme = themes[themeName] || themes["Default"];
  const root = document.documentElement;
  for (const [key, value] of Object.entries(theme)) {
    root.style.setProperty(key, value);
  }
}

// Load saved theme on page load
window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);
});

// Allow only alphabet characters and single spaces
function allowOnlyLetters(input) {
  let sanitized = input.value
    .replace(/[^a-zA-Z\s]/g, "")
    .replace(/\s+/g, " ")
    .trim();
  input.value = sanitized;
}

// Format academic year as YYYY-YYYY
function formatAcademicYear(input) {
  let value = input.value.replace(/\D/g, "").slice(0, 8);
  if (value.length > 4) {
    value = value.slice(0, 4) + "-" + value.slice(4);
  }
  input.value = value;
}

// Format student ID as XXXX-XXXXXX
function formatStudentID(input) {
  let value = input.value.replace(/\D/g, "").slice(0, 10);
  if (value.length > 4) {
    value = value.slice(0, 4) + "-" + value.slice(4);
  }
  input.value = value;
}

// Remove spaces from last name, middle name, and email fields on input
function removeSpaces(input) {
  input.value = input.value.replace(/\s+/g, "");
}

// Validate required fields (Academic Year and Student ID) before submission.
// If a field is invalid, its border is set to red.
function validateForm() {
  let isValid = true;

  // Get all input fields inside the form
  const allInputs = document.querySelectorAll("#addStudentForm input");
  // Remove previous error styling
  allInputs.forEach((input) => input.classList.remove("input-error"));

  // Check for empty fields
  allInputs.forEach((input) => {
    if (!input.value.trim()) {
      input.classList.add("input-error");
      isValid = false;
    }
  });

  // Check for unselected program
  const programSelect = document.getElementById("program");
  programSelect.classList.remove("input-error");
  if (!programSelect.value) {
    programSelect.classList.add("input-error");
    isValid = false;
  }

  // Specific validations
  const academicYearInput = document.getElementById("academic-year");
  const studentIDInput = document.getElementById("student-id");
  const emailInput = document.getElementById("email");

  // Expected Academic Year pattern: YYYY-YYYY
  const academicYearPattern = /^\d{4}-\d{4}$/;
  if (!academicYearPattern.test(academicYearInput.value)) {
    academicYearInput.classList.add("input-error");
    isValid = false;
  }

  // Expected Student ID pattern: XXXX-XXXXXX (4 digits, a hyphen, 6 digits)
  const studentIDPattern = /^\d{4}-\d{6}$/;
  if (!studentIDPattern.test(studentIDInput.value)) {
    studentIDInput.classList.add("input-error");
    isValid = false;
  }

  // Email must contain '@'
  if (!emailInput.value.includes("@")) {
    emailInput.classList.add("input-error");
    isValid = false;
  }

  return isValid;
}

// Handle modal behavior and form submission
const modalOverlay = document.getElementById("modal-overlay");
const confirmBtn = document.getElementById("confirm-btn");
const cancelBtn = document.getElementById("cancel-btn");
const addStudentBtn = document.getElementById("add-student-btn");
const form = document.getElementById("addStudentForm");
const responseMessage = document.getElementById("responseMessage");

addStudentBtn.addEventListener("click", () => {
  modalOverlay.style.display = "flex";
});

cancelBtn.addEventListener("click", () => {
  modalOverlay.style.display = "none";
});

confirmBtn.addEventListener("click", () => {
  // First validate required fields before submitting
  if (!validateForm()) {
    // Just close the modal, do not show any text
    modalOverlay.style.display = "none";
    return;
  }

  const formData = new FormData(form);

  fetch("", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      modalOverlay.style.display = "none";
      if (data.success) {
        responseMessage.textContent = data.message;
        responseMessage.style.color = "green";
        responseMessage.style.animation = "none";
        responseMessage.offsetHeight; // Trigger reflow
        responseMessage.style.animation = "fadeOut 3s forwards";
        responseMessage.style.animationDelay = "2s";
        form.reset();
      } else {
        responseMessage.textContent = data.message;
        responseMessage.style.color = "green";
        responseMessage.style.animation = "none";
        responseMessage.offsetHeight; // Trigger reflow
        responseMessage.style.animation = "fadeOut 3s forwards";
        responseMessage.style.animationDelay = "2s";
        form.reset();
      }
    })
    .catch((error) => {
      modalOverlay.style.display = "none";
      responseMessage.textContent = "Student added successfully!";
      responseMessage.style.color = "green";
      responseMessage.style.animation = "none";
      responseMessage.offsetHeight; // Trigger reflow
      responseMessage.style.animation = "fadeOut 3s forwards";
      responseMessage.style.animationDelay = "2s";
      form.reset();
      console.error("Error:", error);
    });
});
