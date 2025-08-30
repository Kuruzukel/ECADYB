// ================================
// StudentList.js (Railway-ready)
// ================================

// ----------------------
// Tab State Management
// ----------------------
function updateUrlWithTab(tabName) {
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    // Reset to first page when changing tabs
    url.searchParams.set('page', '1');
    window.history.pushState({}, '', url);
    window.location.href = url.toString();
}

function setActiveTab(tabName) {
    // Remove active class from all tabs and tab contents
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    // Add active class to selected tab and its content
    const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
    const activeContent = document.getElementById(tabName);
    
    if (activeTab) activeTab.classList.add('active');
    if (activeContent) activeContent.classList.add('active');
}

// Initialize tabs from URL parameter
function initializeTabs() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'all'; // Default to 'all' if no tab specified
    setActiveTab(activeTab);

    // Add click handlers to tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            updateUrlWithTab(tabName);
        });
    });
}

// Call initializeTabs when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTabs();
    
    // Update department filter to preserve tab parameter
    const deptFilter = document.getElementById("department-filter");
    if (deptFilter) {
        deptFilter.addEventListener("change", function() {
            const dept = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('department', dept);
            // Reset to first page when changing departments
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        });
    }
});

// ----------------------
// Themes
// ----------------------
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

function applyTheme(themeName) {
  const theme = themes[themeName] || themes["Default"];
  const root = document.documentElement;
  for (const [key, value] of Object.entries(theme)) {
    root.style.setProperty(key, value);
  }
}

// ----------------------
// Global constants
// ----------------------
const STATUS_ENDPOINT = (() => {
  const origin = window.location.origin;
  const pathSegments = window.location.pathname.split("/").filter(Boolean);

  if (pathSegments[0] !== "ECADYB") {
    return `${origin}/Connection/UpdateStatus.php`;
  }
  return `${origin}/ECADYB/Connection/UpdateStatus.php`;
})();

const STUDENT_UPDATE_ENDPOINT = (() => {
  const origin = window.location.origin;
  const pathSegments = window.location.pathname.split("/").filter(Boolean);

  if (pathSegments[0] !== "ECADYB") {
    return `${origin}/Connection/UpdateStudent.php`;
  }
  return `${origin}/ECADYB/Connection/UpdateStudent.php`;
})();

// ----------------------
// Initialize on page load
// ----------------------
window.addEventListener("DOMContentLoaded", () => {
  console.log("StudentList.js loaded successfully");

  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  initializeSelectAll();
  initializeFilters();
  initializeStatusUpdates();
  initializeDeleteModal();

  // Test if submitStudentForm function is available
  if (typeof submitStudentForm === "function") {
    console.log("submitStudentForm function is available");
  } else {
    console.error("submitStudentForm function is NOT available");
  }
});

// ----------------------
// Select all checkboxes
// ----------------------
function initializeSelectAll() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  const studentCheckboxes = document.querySelectorAll(".student-checkbox");
  if (!selectAllCheckbox) return;

  selectAllCheckbox.addEventListener("change", function () {
    studentCheckboxes.forEach((checkbox) => {
      const was = checkbox.checked;
      checkbox.checked = this.checked;
      if (was !== this.checked) {
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });
  });

  studentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      const allChecked = Array.from(studentCheckboxes).every(
        (cb) => cb.checked
      );
      const anyChecked = Array.from(studentCheckboxes).some((cb) => cb.checked);
      selectAllCheckbox.checked = allChecked;
      selectAllCheckbox.indeterminate = anyChecked && !allChecked;
    });
  });
}

// ----------------------
// Filters
// ----------------------
function initializeFilters() {
  const entriesCount = document.getElementById("entries-count");
  const departmentFilter = document.getElementById("department-filter");
  const statusFilter = document.getElementById("status-filter");

  [entriesCount, departmentFilter, statusFilter].forEach((filter) => {
    if (filter) filter.addEventListener("change", applyFilters);
  });

  applyFilters();
}

function applyFilters() {
  const deptVal = (
    document.getElementById("department-filter")?.value || ""
  ).trim();
  const statusVal = (
    document.getElementById("status-filter")?.value || ""
  ).trim();
  const studentRows = document.querySelectorAll(".student-row");

  studentRows.forEach((row) => {
    if (row.classList.contains("header")) return;

    let showRow = true;

    if (deptVal) {
      const deptValue =
        row.querySelector(".student-checkbox")?.dataset.collection;
      if (deptValue !== deptVal) showRow = false;
    }

    if (statusVal) {
      const statusAttr =
        row.querySelector(".student-checkbox")?.dataset.status || "";
      if (statusAttr.toLowerCase() !== statusVal.toLowerCase()) showRow = false;
    }

    row.style.display = showRow ? "" : "none";
  });
}

// ----------------------
// Notifications
// ----------------------
function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const notif = document.createElement("div");
  notif.className = `notification ${type} show`;
  notif.innerHTML = `
    <i class="fas ${
      type === "success" ? "fa-check-circle" : "fa-exclamation-circle"
    }"></i>
    <span>${message}</span>
  `;
  container.appendChild(notif);

  setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => notif.remove(), 500);
  }, 3000);
}

// ----------------------
// Delete student modal
// ----------------------
const deleteModal = document.getElementById("delete-modal-overlay");
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const cancelDeleteBtn = document.getElementById("cancel-delete-btn");

let selectedStudentId = null;
let selectedCollection = null;

function openDeleteModal(studentId, collection) {
  selectedStudentId = studentId?.trim();
  selectedCollection = collection?.trim();
  if (deleteModal) deleteModal.style.display = "flex";
}

function closeDeleteModal() {
  selectedStudentId = null;
  selectedCollection = null;
  if (deleteModal) deleteModal.style.display = "none";
}

async function confirmDeleteStudent() {
  if (!selectedStudentId || !selectedCollection) return;

  confirmDeleteBtn.disabled = true;

  try {
    const res = await fetch(
      `${window.location.origin}/ECADYB/Connection/DeleteStudent.php`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          student_id: selectedStudentId,
          collection: selectedCollection,
        }),
      }
    );

    const data = await res.json();
    showNotification(data.message, data.success ? "success" : "error");

    if (data.success) {
      const row = document
        .querySelector(
          `.student-checkbox[data-student-id="${selectedStudentId}"]`
        )
        ?.closest("tr");
      if (row) row.remove();
    }
  } catch (err) {
    console.error("Error deleting student:", err);
    showNotification("Error deleting student.", "error");
  } finally {
    confirmDeleteBtn.disabled = false;
    closeDeleteModal();
  }
}

function initializeDeleteModal() {
  if (!confirmDeleteBtn || !cancelDeleteBtn || !deleteModal) return;

  cancelDeleteBtn.addEventListener("click", closeDeleteModal);
  deleteModal.addEventListener("click", (e) => {
    if (e.target === deleteModal) closeDeleteModal();
  });
  confirmDeleteBtn.addEventListener("click", confirmDeleteStudent);
}

// ----------------------
// Toggle password
// ----------------------
function togglePass(icon) {
  const studentRow = icon.closest(".student-row");
  if (!studentRow) return;

  const passwordText = studentRow.querySelector(".password-text");
  const eyeOpen = studentRow.querySelector(".eyeIcon.open.eyeIcon-list");
  const eyeClose = studentRow.querySelector(".eyeIcon.close.eyeIcon-list");

  if (!passwordText) return;

  if (eyeClose && eyeClose.style.display !== "none") {
    passwordText.textContent = passwordText.getAttribute("data-password");
    eyeClose.style.display = "none";
    if (eyeOpen) eyeOpen.style.display = "flex";
  } else {
    passwordText.textContent = "********";
    if (eyeClose) eyeClose.style.display = "flex";
    if (eyeOpen) eyeOpen.style.display = "none";
  }
}

// ----------------------
// Update status
// ----------------------
function initializeStatusUpdates() {
  const studentCheckboxes = document.querySelectorAll(".student-checkbox");
  if (!studentCheckboxes.length) return;

  studentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", async function () {
      if (this.dataset.busy === "1") return;
      this.dataset.busy = "1";

      const studentId = this.dataset.studentId?.trim();
      const collection = this.dataset.collection?.trim();
      const status = this.checked ? "Active" : "Pending";

      if (!studentId || !collection) {
        showNotification("Student ID or collection missing", "error");
        this.checked = !this.checked;
        this.dataset.busy = "0";
        return;
      }

      try {
        const res = await fetch(STATUS_ENDPOINT, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ student_id: studentId, collection, status }),
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

        let data;
        try {
          data = await res.json();
        } catch (err) {
          console.error("[UpdateStatus] Invalid JSON:", err);
          showNotification(
            "Server error: Invalid JSON response from UpdateStatus.php",
            "error"
          );
          this.checked = !this.checked;
          return;
        }

        if (data && data.success) {
          this.dataset.status = status.toLowerCase();
          const row = this.closest("tr");
          const statusCell = row?.querySelector(".student-status");
          if (statusCell) {
            statusCell.textContent = status;
            statusCell.className = `student-status ${
              status.toLowerCase() === "active"
                ? "status-active"
                : "status-pending"
            }`;
          }
          applyFilters();
          showNotification(
            data.message || "Status updated successfully",
            "success"
          );
        } else {
          showNotification(data.message || "Failed to update status", "error");
          this.checked = !this.checked;
        }
      } catch (err) {
        console.error("[UpdateStatus] Fetch error:", err);
        showNotification("Error updating status. Check console.", "error");
        this.checked = !this.checked;
      } finally {
        this.dataset.busy = "0";
      }
    });
  });
}
// ----------------------
// Update student details
// ----------------------
async function updateStudentDetails(studentId, fields) {
  if (!studentId) return;

  // Include hidden collection field
  const collectionEl = document.getElementById(
    `collection-hidden-${studentId}`
  );
  if (collectionEl) fields["collection"] = collectionEl.value;

  try {
    const res = await fetch(STUDENT_UPDATE_ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      // Send original ID separately for lookup; allow new ID in fields
      body: JSON.stringify({ original_student_id: studentId, ...fields }),
    });

    const data = await res.json().catch(() => null);

    if (data?.success) {
      showNotification(
        data.message || "Student Details Saved Successfully",
        "success"
      );

      // Auto-refresh the page after successful save
      setTimeout(() => {
        window.location.reload();
      }, 1500); // Wait 1.5 seconds to show the success message
    } else {
      showNotification(
        data?.message || "Failed to save student details",
        "error"
      );
    }
  } catch {
    showNotification("Error saving student details", "error");
  }
}

// ----------------------
// Submit student form
// ----------------------
function submitStudentForm(studentId) {
  console.log("submitStudentForm called with studentId:", studentId);

  if (!studentId) {
    console.error("No studentId provided");
    return;
  }

  const fields = {};
  // Create a mapping of form field names to MongoDB keys
  const fieldMapping = {
    first_name: "first name",
    middle_name: "middle name",
    last_name: "last name",
    email: "email",
    password: "password",
    academic_year: "academic year",
    program: "program",
    section: "section",
    // student_id handled explicitly below to allow editing
    motto: "motto",
    honors: "honors",
    milestone: "milestone",
    batch_name: "batch name",
    department_section: "department section", // included
    status: "status", // added for completeness
  };

  // Iterate over the mapping to collect field values
  for (const [key, mongoKey] of Object.entries(fieldMapping)) {
    const el = document.getElementById(`${key}${studentId}`);
    if (el) {
      fields[mongoKey] = el.value.trim(); // Use mongoKey for the final object
      console.log(`Field ${key} (${mongoKey}):`, el.value.trim());
    } else {
      console.warn(`Element not found for field: ${key}${studentId}`);
    }
  }

  // Capture potentially edited Student ID value
  const studentIdEl = document.getElementById(`student_id${studentId}`);
  if (studentIdEl) {
    const newStudentId = studentIdEl.value.trim();
    // Send new ID using the normalized key used in MongoDB
    fields["student id"] = newStudentId;
    console.log("New Student ID value:", newStudentId);
  } else {
    console.warn(`Student ID input not found: student_id${studentId}`);
  }

  const collectionEl = document.getElementById(
    `collection-hidden-${studentId}`
  );
  if (collectionEl) {
    fields["collection"] = collectionEl.value; // Assuming you have a hidden field for collection
    console.log("Collection field:", collectionEl.value);
  } else {
    console.error(
      `Collection hidden field not found: collection-hidden-${studentId}`
    );
    return;
  }

  console.log("Final fields object:", fields);

  // Close modal immediately after sending save request
  const modal = document.getElementById(`editModal_${studentId}`);
  if (modal) modal.classList.remove("active");

  updateStudentDetails(studentId, fields);
}

// ----------------------
// Test function for debugging
// ----------------------
function testFunction() {
  console.log("Test function called successfully!");
  alert(
    "JavaScript is working! submitStudentForm function: " +
      (typeof submitStudentForm === "function" ? "Available" : "NOT Available")
  );
}

// ----------------------
// Utility functions for form validation
// ----------------------

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
