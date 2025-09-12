function updateUrlWithTab(tabName) {
  const url = new URL(window.location.href);
  url.searchParams.set("tab", tabName);
  url.searchParams.set("page", "1");
  window.history.pushState({}, "", url);
  window.location.href = url.toString();
}

function setActiveTab(tabName) {
  document
    .querySelectorAll(".tab-button")
    .forEach((btn) => btn.classList.remove("active"));
  document
    .querySelectorAll(".tab-content")
    .forEach((content) => content.classList.remove("active"));

  const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
  const activeContent = document.getElementById(tabName);

  if (activeTab) activeTab.classList.add("active");
  if (activeContent) activeContent.classList.add("active");
}

function initializeTabs() {
  const urlParams = new URLSearchParams(window.location.search);
  const activeTab = urlParams.get("tab") || "all";
  setActiveTab(activeTab);

  document.querySelectorAll(".tab-button").forEach((button) => {
    button.addEventListener("click", function () {
      const tabName = this.getAttribute("data-tab");
      updateUrlWithTab(tabName);
    });
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initializeTabs();

  const deptFilter = document.getElementById("department-filter");
  if (deptFilter) {
    deptFilter.addEventListener("change", function () {
      const dept = this.value;
      const url = new URL(window.location.href);
      url.searchParams.set("department", dept);
      url.searchParams.set("page", "1");
      window.location.href = url.toString();
    });
  }
});

const themes = {
  "Light Mode": {
    "--primary-bg": "#ffffff",
    "--header-bg": "#94a3b8",
    "--accent": "#fcda15",
    "--section-bg": "#f8fafc",
    "--section-header": "#cbd5e1",
    "--body-bg": "#64748b",
    "--sidebar-bg": "#94a3b8",
    "--content-bg": "#ffffff",
    "--menu-bg-active": "#cbd5e1",
    "--menu-border-active": "#64748b",
    "--menu-hover-bg": "#e2e8f0",
  },
  "Dark Mode": {
    "--primary-bg": "#0f172a",
    "--header-bg": "#1e293b",
    "--accent": "#fcda15",
    "--section-bg": "#334155",
    "--section-header": "#475569",
    "--body-bg": "#0f172a",
    "--sidebar-bg": "#1e293b",
    "--content-bg": "#334155",
    "--menu-bg-active": "#475569",
    "--menu-border-active": "#334155",
    "--menu-hover-bg": "#64748b",
  },
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
    "--accent": "#fcda15",
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

let pendingTheme = null;

function selectColor(el) {
  document
    .querySelectorAll(".color-box")
    .forEach((box) => box.classList.remove("selected"));
  el.classList.add("selected");

  pendingTheme = el.getAttribute("data-label");
  document.getElementById("modal-overlay").style.display = "flex";
}

function applyTheme(theme) {
  console.log("Applying theme:", theme);
  const root = document.documentElement;
  const selectedTheme = themes[theme] || themes["Default"];

  console.log("Selected theme data:", selectedTheme);

  for (const [varName, color] of Object.entries(selectedTheme)) {
    root.style.setProperty(varName, color);
  }

  const currentSectionBg =
    selectedTheme["--section-bg"] || themes["Default"]["--section-bg"];
  const modal = document.querySelector(".modal");
  if (modal) modal.style.background = currentSectionBg;

  const body = document.body;
  body.classList.remove("theme-light-mode", "theme-dark-mode");

  if (theme === "Light Mode") {
    body.classList.add("theme-light-mode");
  } else if (theme === "Dark Mode") {
    body.classList.add("theme-dark-mode");
  }

  localStorage.setItem("dashboard-theme", theme);

  console.log("Theme applied and saved:", theme);

  document.body.style.display = "none";
  document.body.offsetHeight;
  document.body.style.display = "";
}

const STATUS_ENDPOINT = (() => {
  const origin = window.location.origin;
  const pathSegments = window.location.pathname.split("/").filter(Boolean);
  if (pathSegments[0] !== "ECADYB")
    return `${origin}/Connection/UpdateStatus.php`;
  return `${origin}/ECADYB/Connection/UpdateStatus.php`;
})();

const STUDENT_UPDATE_ENDPOINT = (() => {
  const origin = window.location.origin;
  const pathSegments = window.location.pathname.split("/").filter(Boolean);
  if (pathSegments[0] !== "ECADYB")
    return `${origin}/Connection/UpdateStudent.php`;
  return `${origin}/ECADYB/Connection/UpdateStudent.php`;
})();

const DELETE_STUDENT_ENDPOINT = (() => {
  const origin = window.location.origin;
  const pathSegments = window.location.pathname.split("/").filter(Boolean);
  if (pathSegments[0] !== "ECADYB")
    return `${origin}/Connection/DeleteStudent.php`;
  return `${origin}/ECADYB/Connection/DeleteStudent.php`;
})();

// Initialize on page load
window.addEventListener("DOMContentLoaded", () => {
  console.log("StudentList.js loaded successfully");

  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  initializeSelectAll();
  initializeFilters();
  initializeStatusUpdates();
  initializeDeleteModal();
});

// Select all checkboxes
let isSelectAllActive = false; // Flag to track select all operations

function initializeSelectAll() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (!selectAllCheckbox) return;

  // Restore select all state from localStorage on page load
  const savedSelectAllState = localStorage.getItem("selectAllState");
  if (savedSelectAllState === "true") {
    selectAllCheckbox.checked = true;
    isSelectAllActive = true;
    // Apply the saved state to all visible checkboxes
    setTimeout(() => {
      const visibleStudentCheckboxes = getVisibleStudentCheckboxes();
      visibleStudentCheckboxes.forEach((checkbox) => {
        if (!checkbox.checked) {
          checkbox.checked = true;
          checkbox.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
    }, 100);
  }

  selectAllCheckbox.addEventListener("change", function () {
    console.log("Select all clicked:", this.checked);

    // Set the flag to indicate select all is being used
    isSelectAllActive = this.checked;

    // Save select all state to localStorage
    localStorage.setItem("selectAllState", this.checked.toString());

    // Get only visible student checkboxes (not filtered out)
    const visibleStudentCheckboxes = getVisibleStudentCheckboxes();
    console.log("Found", visibleStudentCheckboxes.length, "visible checkboxes");

    // Simple toggle: set all visible checkboxes to match select all state
    visibleStudentCheckboxes.forEach((checkbox) => {
      const was = checkbox.checked;
      checkbox.checked = this.checked;
      if (was !== this.checked) {
        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
      }
    });
  });
}

// Helper function to get only visible student checkboxes
function getVisibleStudentCheckboxes() {
  const tableBody = document.querySelector("tbody");
  if (!tableBody) return [];

  const visibleRows = Array.from(tableBody.querySelectorAll("tr")).filter(
    (row) => {
      return (
        row.style.display !== "none" && row.querySelector(".student-checkbox")
      );
    }
  );

  return visibleRows
    .map((row) => row.querySelector(".student-checkbox"))
    .filter(Boolean);
}

// Helper function to clear select all state when individual changes occur
function clearSelectAllState() {
  // Don't clear if select all is actively being used
  if (isSelectAllActive) {
    console.log("Select all is active, not clearing state");
    return;
  }

  console.log("Clearing select all state due to individual change");
  localStorage.removeItem("selectAllState");
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (selectAllCheckbox) {
    selectAllCheckbox.checked = false;
  }
}

// Filters
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

  console.log("Applying filters - Department:", deptVal, "Status:", statusVal);

  // Clear select all state when filters are applied
  if (deptVal || statusVal) {
    clearSelectAllState();
  }

  // Get all table rows in tbody, excluding header
  const tableBody = document.querySelector("tbody");
  if (!tableBody) return;

  const studentRows = tableBody.querySelectorAll("tr");
  console.log("Found", studentRows.length, "student rows");

  studentRows.forEach((row, index) => {
    // Skip empty rows or rows without student data
    const checkbox = row.querySelector(".student-checkbox");
    if (!checkbox) {
      console.log("Row", index, "has no checkbox, skipping");
      return;
    }

    let showRow = true;

    // Department filter
    if (deptVal) {
      const deptValue = checkbox.dataset.collection;
      console.log("Row", index, "dept check:", deptValue, "vs", deptVal);
      if (deptValue !== deptVal) showRow = false;
    }

    // Status filter
    if (statusVal) {
      const statusAttr = checkbox.dataset.status || "";
      console.log("Row", index, "status check:", statusAttr, "vs", statusVal);
      if (statusAttr.toLowerCase() !== statusVal.toLowerCase()) showRow = false;
    }

    console.log("Row", index, "will be", showRow ? "shown" : "hidden");
    row.style.display = showRow ? "" : "none";
  });

  // Update select all state after filtering
  // Note: No automatic state update for simple toggle mode
}

// Notifications
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

// Delete student modal
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
    const res = await fetch(DELETE_STUDENT_ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        student_id: selectedStudentId,
        collection: selectedCollection,
      }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);

    const data = await res.json().catch(() => null);

    if (data?.success) {
      showNotification(
        data.message || "Student deleted successfully",
        "success"
      );
      const row = document
        .querySelector(
          `.student-checkbox[data-student-id="${selectedStudentId}"]`
        )
        ?.closest("tr");
      if (row) row.remove();
    } else {
      showNotification(data?.message || "Failed to delete student", "error");
    }
  } catch (err) {
    console.error("Error deleting student:", err);
    showNotification("Error deleting student. Check console.", "error");
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

// Toggle password
function togglePass(icon) {
  const tableRow = icon.closest("tr");
  if (!tableRow) return;

  const passwordText = tableRow.querySelector(".password-text");
  const actionsContainer = icon.closest(".actions-container");
  const eyeOpen = actionsContainer.querySelector(".eyeIcon.open.eyeIcon-list");
  const eyeClose = actionsContainer.querySelector(
    ".eyeIcon.close.eyeIcon-list"
  );

  if (!passwordText) return;

  if (eyeClose && eyeClose.style.display !== "none") {
    // Show password
    passwordText.textContent = passwordText.getAttribute("data-password");
    eyeClose.style.display = "none";
    if (eyeOpen) eyeOpen.style.display = "flex";
  } else {
    // Hide password
    passwordText.textContent = "********";
    if (eyeClose) eyeClose.style.display = "flex";
    if (eyeOpen) eyeOpen.style.display = "none";
  }
}

// Update status
function initializeStatusUpdates() {
  const studentCheckboxes = document.querySelectorAll(".student-checkbox");
  console.log("Found", studentCheckboxes.length, "student checkboxes");
  if (!studentCheckboxes.length) return;

  studentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", async function () {
      console.log("Checkbox changed", this.checked);
      console.log("Dataset:", this.dataset);

      // If this change wasn't caused by select all, clear the select all state
      if (!isSelectAllActive) {
        clearSelectAllState();
      }

      if (this.dataset.busy === "1") return;
      this.dataset.busy = "1";

      const studentId = this.dataset.studentId?.trim();
      const collection = this.dataset.collection?.trim();
      const status = this.checked ? "Active" : "Pending";

      console.log("Student ID:", studentId);
      console.log("Collection:", collection);
      console.log("Status:", status);

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
          console.log("Status update successful");
          this.dataset.status = status.toLowerCase();
          const row = this.closest("tr");
          const statusCell = row?.querySelector(".student-status");
          console.log("Found status cell:", statusCell);
          if (statusCell) {
            console.log("Updating status cell text to:", status);
            statusCell.textContent = status;
            statusCell.className = `student-status ${
              status.toLowerCase() === "active"
                ? "status-active"
                : "status-pending"
            }`;
            console.log("New status cell className:", statusCell.className);
          }
          applyFilters();
          // Note: No automatic select all state update for simple toggle mode
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
        // Reset the select all flag after status update is complete
        if (isSelectAllActive) {
          setTimeout(() => {
            isSelectAllActive = false;
            console.log("Select all operation completed, flag reset");
          }, 100);
        }
      }
    });
  });
}

// Update student details
async function updateStudentDetails(studentId, fields) {
  if (!studentId) return;

  const collectionEl = document.getElementById(
    `collection-hidden-${studentId}`
  );
  if (collectionEl) fields["collection"] = collectionEl.value;

  try {
    const res = await fetch(STUDENT_UPDATE_ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ original_student_id: studentId, ...fields }),
    });

    const data = await res.json().catch(() => null);

    if (data?.success) {
      showNotification(
        data.message || "Student Details Saved Successfully",
        "success"
      );
      setTimeout(() => {
        window.location.reload();
      }, 1500);
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

// Submit student form
function submitStudentForm(studentId) {
  console.log("submitStudentForm called with studentId:", studentId);

  if (!studentId) {
    console.error("No studentId provided");
    return;
  }

  const fields = {};
  const fieldMapping = {
    first_name: "first name",
    middle_name: "middle name",
    last_name: "last name",
    email: "email",
    password: "password",
    academic_year: "academic year",
    program: "program",
    section: "section",
    motto: "motto",
    honors: "honors",
    milestone: "milestone",
    batch_name: "batch name",
    department_section: "department section",
    status: "status",
  };

  for (const [key, mongoKey] of Object.entries(fieldMapping)) {
    const el = document.getElementById(`${key}${studentId}`);
    if (el) {
      fields[mongoKey] = el.value.trim();
    }
  }

  const studentIdEl = document.getElementById(`student_id${studentId}`);
  if (studentIdEl) {
    fields["student id"] = studentIdEl.value.trim();
  }

  const collectionEl = document.getElementById(
    `collection-hidden-${studentId}`
  );
  if (collectionEl) {
    fields["collection"] = collectionEl.value;
  } else {
    return;
  }

  const modal = document.getElementById(`editModal_${studentId}`);
  if (modal) modal.classList.remove("active");

  updateStudentDetails(studentId, fields);
}

// Utility validation functions
function allowOnlyLetters(input) {
  let sanitized = input.value
    .replace(/[^a-zA-Z\s]/g, "")
    .replace(/\s+/g, " ")
    .trim();
  input.value = sanitized;
}

function formatAcademicYear(input) {
  let value = input.value.replace(/\D/g, "").slice(0, 8);
  if (value.length > 4) value = value.slice(0, 4) + "-" + value.slice(4);
  input.value = value;
}

function formatStudentID(input) {
  let value = input.value.replace(/\D/g, "").slice(0, 10);
  if (value.length > 4) value = value.slice(0, 4) + "-" + value.slice(4);
  input.value = value;
}

function removeSpaces(input) {
  input.value = input.value.replace(/\s+/g, "");
}
