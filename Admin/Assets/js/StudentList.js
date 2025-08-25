// ================================
// StudentList.js (clean + updated)
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

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  initializeSelectAll();
  initializeFilters();
  initializeStatusUpdates();
});

// ----------------------
// Helpers
// ----------------------
const STATUS_ENDPOINT = "/ECADYB/Connection/UpdateStatus.php";

function toForm(bodyObj) {
  return Object.entries(bodyObj)
    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
    .join("&");
}

// ----------------------
// Select all functionality
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
// Filter functionality
// ----------------------
function initializeFilters() {
  const entriesCount = document.getElementById("entries-count");
  const departmentFilter = document.getElementById("department-filter");
  const statusFilter = document.getElementById("status-filter");

  [entriesCount, departmentFilter, statusFilter].forEach((filter) => {
    if (filter) {
      filter.addEventListener("change", applyFilters);
    }
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
      const deptValue = row
        .querySelector(".student-checkbox")
        ?.getAttribute("data-collection");
      if (deptValue !== deptVal) showRow = false;
    }

    if (statusVal) {
      const statusAttr =
        row.querySelector(".student-checkbox")?.getAttribute("data-status") ||
        "";
      if (statusAttr.toLowerCase() !== statusVal.toLowerCase()) showRow = false;
    }

    row.style.display = showRow ? "" : "none";
  });
}

// ----------------------
// Edit student
// ----------------------
function editStudent(studentId, collection) {
  window.location.href = `EditStudentInformation.php?student_id=${encodeURIComponent(
    studentId
  )}&collection=${encodeURIComponent(collection)}`;
}

// ----------------------
// Delete student
// ----------------------
function deleteStudent(studentId, collection) {
  if (
    confirm(
      "Are you sure you want to delete this student? This action cannot be undone."
    )
  ) {
    fetch("../../Connection/DeleteStudent.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ student_id: studentId, collection }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Student deleted successfully!");
          location.reload();
        } else {
          alert("Error deleting student: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error deleting student. Please try again.");
      });
  }
}

// ----------------------
// Toggle password view
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

  studentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", async function () {
      if (this.dataset.busy === "1") return;
      this.dataset.busy = "1";

      const studentId = this.dataset.studentId;
      const collection = this.dataset.collection;
      const status = this.checked ? "Active" : "Pending";

      try {
        const res = await fetch(STATUS_ENDPOINT, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: toForm({ student_id: studentId, collection, status }),
        });

        const text = await res.text();
        let data;
        try {
          data = JSON.parse(text);
        } catch (e) {
          console.error("Non-JSON response from UpdateStatus.php:", text);
          throw new Error("Invalid response from server.");
        }

        if (data && data.success) {
          this.setAttribute("data-status", status.toLowerCase());

          // update status cell in table
          const row = this.closest("tr");
          const statusCell = row.querySelector(".student-status");
          if (statusCell) {
            statusCell.textContent = status;
            statusCell.className =
              "student-status " +
              (status.toLowerCase() === "active"
                ? "status-active"
                : "status-pending");
          }

          applyFilters();
        } else {
          console.error("Update failed:", data);
          alert("Failed to update status.");
          this.checked = !this.checked;
        }
      } catch (err) {
        console.error("Network/Server error:", err);
        alert("Error updating status. Check console for details.");
        this.checked = !this.checked;
      } finally {
        this.dataset.busy = "0";
      }
    });
  });
}
