// ================================
// StudentList.js (Railway-ready)
// ================================

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
// Dynamic detection for local / Railway
const STATUS_ENDPOINT = (() => {
  const origin = window.location.origin;
  const pathSegments = window.location.pathname.split("/").filter(Boolean);

  // If deployed root-level (Railway), assume /Connection folder is at root
  if (pathSegments[0] !== "ECADYB") {
    return `${origin}/Connection/UpdateStatus.php`;
  }
  // If local dev under ECADYB folder
  return `${origin}/ECADYB/Connection/UpdateStatus.php`;
})();

// ----------------------
// Initialize all DOM events on page load
// ----------------------
window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  initializeSelectAll();
  initializeFilters();
  initializeStatusUpdates();
  initializeDeleteModal();
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
// Filters (department, status, entries)
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
// Toggle password visibility
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
// Update status (Railway-ready)
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
