document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const activeTab = urlParams.get("tab") || "all";

  const tabs = document.querySelectorAll(".tab-button");
  tabs.forEach((tab) => {
    if (tab.getAttribute("data-tab") === activeTab) {
      tab.classList.add("active");
    } else {
      tab.classList.remove("active");
    }
  });

  const tabContents = document.querySelectorAll(".tab-content");
  tabContents.forEach((content) => {
    if (content.id === activeTab) {
      content.classList.add("active");
    } else {
      content.classList.remove("active");
    }
  });

  const deptFilter = document.getElementById("department-filter");
  if (deptFilter) {
    deptFilter.addEventListener("change", function () {
      const dept = this.value;
      const url = new URL(window.location.href);
      url.searchParams.set("department", dept);
      url.searchParams.set("pageNum", "1");
      window.location.href = url.toString();
    });
  }

  document.querySelectorAll(".tab-button").forEach((button) => {
    button.addEventListener("click", function () {
      const tabName = this.getAttribute("data-tab");
      const url = new URL(window.location.href);
      url.searchParams.set("tab", tabName);
      url.searchParams.set("pageNum", "1");
      window.location.href = url.toString();
    });
  });
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
}

const STATUS_ENDPOINT = "/Connection/Student/UpdateStatus.php";
const STUDENT_UPDATE_ENDPOINT = "/Connection/Student/UpdateStudent.php";
const DELETE_STUDENT_ENDPOINT = "/Connection/Student/DeleteStudent.php";
const BULK_STATUS_ENDPOINT = "/Connection/Student/BulkUpdateStatus.php";

window.addEventListener("DOMContentLoaded", () => {
  console.log("StudentList.js loaded successfully");

  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  if (savedTheme) {
    applyTheme(savedTheme);
  }

  initializeSelectAll();
  initializeFilters();
  initializeStatusUpdates();
  initializeDeleteModal();
  
  // Update select all state on page load
  setTimeout(updateSelectAllState, 100);
});

let isSelectAllActive = false;
let isSelectAllOperation = false;
let selectAllProcessedCount = 0;
let selectAllTotalCount = 0;

// Function to update the select all checkbox state based on individual checkbox states
function updateSelectAllState() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (!selectAllCheckbox) return;

  const visibleCheckboxes = getVisibleStudentCheckboxes();
  if (visibleCheckboxes.length === 0) {
    selectAllCheckbox.checked = false;
    return;
  }

  // Check if all visible checkboxes are checked
  const allChecked = visibleCheckboxes.every(checkbox => checkbox.checked);
  selectAllCheckbox.checked = allChecked;
  
  // Also update localStorage
  if (allChecked) {
    localStorage.setItem("selectAllState", "true");
  } else {
    localStorage.removeItem("selectAllState");
  }
}

function initializeSelectAll() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (!selectAllCheckbox) return;

  const savedSelectAllState = localStorage.getItem("selectAllState");
  if (savedSelectAllState === "true") {
    selectAllCheckbox.checked = true;
    isSelectAllActive = true;

    setTimeout(() => {
      const visibleStudentCheckboxes = getVisibleStudentCheckboxes();
      visibleStudentCheckboxes.forEach((checkbox) => {
        if (!checkbox.checked) {
          checkbox.checked = true;
          checkbox.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
      // Removed notification when select all is clicked
    }, 100);
  }

  selectAllCheckbox.addEventListener("change", function () {
    console.log("Select all clicked:", this.checked);

    isSelectAllActive = this.checked;

    if (this.checked) {
      localStorage.setItem("selectAllState", "true");
    } else {
      localStorage.removeItem("selectAllState");
    }

    const visibleStudentCheckboxes = getVisibleStudentCheckboxes();
    console.log("Found", visibleStudentCheckboxes.length, "visible checkboxes");

    if (visibleStudentCheckboxes.length > 0) {
      if (this.checked) {
        // For select all, we want to update ALL students in the department, not just visible ones
        // Get current department and template from filters
        const departmentFilter = document.getElementById("department-filter");
        const statusFilter = document.getElementById("status-filter");
        const templateFilter = document.getElementById("template-filter");
        
        const department = departmentFilter ? departmentFilter.value : "";
        const status = statusFilter ? statusFilter.value : "";
        const template = templateFilter ? templateFilter.value : "1";
        
        if (department) {
          // Use bulk update for all students in department
          updateAllStudentsStatus(department, "Active", template, status);
        } else {
          // Fall back to individual updates for visible students only
          // Set up for select all operation
          isSelectAllOperation = true;
          selectAllProcessedCount = 0;
          selectAllTotalCount = visibleStudentCheckboxes.length;

          visibleStudentCheckboxes.forEach((checkbox) => {
            const was = checkbox.checked;
            checkbox.checked = this.checked;
            if (was !== this.checked) {
              checkbox.dispatchEvent(new Event("change", { bubbles: true }));
            }
          });
          // Removed notification when select all is clicked
        }
      } else {
        // For unselect all, we want to update ALL students in the department, not just visible ones
        // Get current department and template from filters
        const departmentFilter = document.getElementById("department-filter");
        const statusFilter = document.getElementById("status-filter");
        const templateFilter = document.getElementById("template-filter");
        
        const department = departmentFilter ? departmentFilter.value : "";
        const status = statusFilter ? statusFilter.value : "";
        const template = templateFilter ? templateFilter.value : "1";
        
        if (department) {
          // Use bulk update for all students in department
          updateAllStudentsStatus(department, "Pending", template, status);
        } else {
          // Fall back to individual updates for visible students only
          // Uncheck all
          visibleStudentCheckboxes.forEach((checkbox) => {
            if (checkbox.checked) {
              checkbox.checked = false;
              checkbox.dispatchEvent(new Event("change", { bubbles: true }));
            }
          });
          // Removed notification when unselect all is clicked
        }
      }
    } else {
      // Show notification immediately if no checkboxes found
      if (this.checked) {
        _showNotification("No students found to update", "info");
      }
    }
  });
}

// New function to update all students in a department
async function updateAllStudentsStatus(collection, status, template, statusFilter) {
  try {
    // Ensure we're using the correct base URL
    const baseUrl = window.location.origin + (window.location.pathname.includes('/ECADYB/') ? '/ECADYB' : '');
    const endpoint = baseUrl + BULK_STATUS_ENDPOINT;
    
    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ 
        collection: collection, 
        status: status, 
        template: template,
        status_filter: statusFilter
      }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

    const data = await res.json();
    
    if (data && data.success) {
      _showNotification(data.message || `All students status updated to ${status}`, "success");
      // Reload the page to reflect changes
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      _showNotification(data.message || "Failed to update all students status", "error");
    }
  } catch (err) {
    console.error("[BulkUpdateStatus] Fetch error:", err);
    _showNotification("Error updating all students status. Check console.", "error");
  }
}

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

function clearSelectAllState() {
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
  
  // Update select all state
  setTimeout(updateSelectAllState, 0);
}

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

  if (deptVal || statusVal) {
    clearSelectAllState();
  }

  const tableBody = document.querySelector("tbody");
  if (!tableBody) return;

  const studentRows = tableBody.querySelectorAll("tr");
  console.log("Found", studentRows.length, "student rows");

  studentRows.forEach((row, index) => {
    const checkbox = row.querySelector(".student-checkbox");
    if (!checkbox) {
      console.log("Row", index, "has no checkbox, skipping");
      return;
    }

    let showRow = true;

    if (deptVal) {
      const deptValue = checkbox.dataset.collection;
      console.log("Row", index, "dept check:", deptValue, "vs", deptVal);
      if (deptValue !== deptVal) showRow = false;
    }

    if (statusVal) {
      const statusAttr = checkbox.dataset.status || "";
      console.log("Row", index, "status check:", statusAttr, "vs", statusVal);
      if (statusAttr.toLowerCase() !== statusVal.toLowerCase()) showRow = false;
    }

    console.log("Row", index, "will be", showRow ? "shown" : "hidden");
    row.style.display = showRow ? "" : "none";
  });
  
  // Update select all state after filtering
  setTimeout(updateSelectAllState, 0);
}

// Modify the showNotification function to handle select all operations
function showNotification(message, type = "success") {
  // If this is a select all operation, suppress individual notifications
  if (isSelectAllOperation && message.includes("Status updated")) {
    selectAllProcessedCount++;
    
    // When all operations are complete, show a single summary notification
    if (selectAllProcessedCount >= selectAllTotalCount) {
      const finalMessage = `All ${selectAllTotalCount} student statuses updated successfully`;
      _showNotification(finalMessage, type);
      isSelectAllOperation = false; // Reset for next operation
      selectAllProcessedCount = 0;
      selectAllTotalCount = 0;
    }
    return; // Don't show individual notifications during select all
  }
  
  // For all other cases, show the notification normally
  _showNotification(message, type);
}

// The actual notification display function
function _showNotification(message, type = "success") {
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

  // Make notification visible longer (5 seconds instead of 3)
  setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => notif.remove(), 500);
  }, 5000);
}

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
    // Get template information from URL or default to 1
    const urlParams = new URLSearchParams(window.location.search);
    const template = urlParams.get('template') || '1';
    
    // Ensure we're using the correct base URL
    const baseUrl = window.location.origin + (window.location.pathname.includes('/ECADYB/') ? '/ECADYB' : '');
    const endpoint = baseUrl + DELETE_STUDENT_ENDPOINT;
    
    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        student_id: selectedStudentId,
        collection: selectedCollection,
        template: template
      }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);

    const data = await res.json().catch(() => null);

    if (data?.success) {
      _showNotification(
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
      _showNotification(data?.message || "Failed to delete student", "error");
    }
  } catch (err) {
    console.error("Error deleting student:", err);
    _showNotification("Error deleting student. Check console.", "error");
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
    passwordText.textContent = passwordText.getAttribute("data-password");
    eyeClose.style.display = "none";
    if (eyeOpen) eyeOpen.style.display = "flex";
  } else {
    passwordText.textContent = "********";
    if (eyeClose) eyeClose.style.display = "flex";
    if (eyeOpen) eyeOpen.style.display = "none";
  }
}

function initializeStatusUpdates() {
  const studentCheckboxes = document.querySelectorAll(".student-checkbox");
  console.log("Found", studentCheckboxes.length, "student checkboxes");
  if (!studentCheckboxes.length) return;

  studentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", async function () {
      console.log("Checkbox changed", this.checked);
      console.log("Dataset:", this.dataset);

      // Update select all state
      setTimeout(updateSelectAllState, 0);

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
        _showNotification("Student ID or collection missing", "error");
        this.checked = !this.checked;
        this.dataset.busy = "0";
        return;
      }

      try {
        // Get template information from URL or default to 1
        const urlParams = new URLSearchParams(window.location.search);
        const template = urlParams.get('template') || '1';
        
        // Ensure we're using the correct base URL
        const baseUrl = window.location.origin + (window.location.pathname.includes('/ECADYB/') ? '/ECADYB' : '');
        const endpoint = baseUrl + STATUS_ENDPOINT;
        
        const res = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ student_id: studentId, collection, status, template }),
        });

        if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

        let data;
        try {
          data = await res.json();
        } catch (err) {
          console.error("[UpdateStatus] Invalid JSON:", err);
          if (!isSelectAllOperation) {
            _showNotification(
              "Server error: Invalid JSON response from UpdateStatus.php",
              "error"
            );
          }
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
          
          // Only show individual notifications if not in select all mode
          if (!isSelectAllOperation) {
            _showNotification(
              data.message || "Status updated successfully",
              "success"
            );
          }
        } else {
          if (!isSelectAllOperation) {
            _showNotification(data.message || "Failed to update status", "error");
          }
          this.checked = !this.checked;
        }
      } catch (err) {
        console.error("[UpdateStatus] Fetch error:", err);
        if (!isSelectAllOperation) {
          _showNotification("Error updating status. Check console.", "error");
        }
        this.checked = !this.checked;
      } finally {
        this.dataset.busy = "0";
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

async function updateStudentDetails(studentId, fields) {
  if (!studentId) return;

  const collectionEl = document.getElementById(
    `collection-hidden-${studentId}`
  );
  if (collectionEl) fields["collection"] = collectionEl.value;

  // Get template information from URL or default to 1
  const urlParams = new URLSearchParams(window.location.search);
  const template = urlParams.get('template') || '1';
  fields["template"] = template;

  try {
    // Ensure we're using the correct base URL
    const baseUrl = window.location.origin + (window.location.pathname.includes('/ECADYB/') ? '/ECADYB' : '');
    const endpoint = baseUrl + STUDENT_UPDATE_ENDPOINT;
    
    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ original_student_id: studentId, ...fields }),
    });

    const data = await res.json().catch(() => null);

    if (data?.success) {
      _showNotification(
        data.message || "Student Details Saved Successfully",
        "success"
      );
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      _showNotification(
        data?.message || "Failed to save student details",
        "error"
      );
    }
  } catch {
    _showNotification("Error saving student details", "error");
  }
}

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

  // Get current values
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
    _showNotification("Collection information missing", "error");
    return;
  }

  const modal = document.getElementById(`editModal_${studentId}`);
  if (modal) modal.classList.remove("active");

  updateStudentDetails(studentId, fields);
}

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
