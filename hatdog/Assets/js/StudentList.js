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

  // Initialize select all functionality
  initializeSelectAll();

  // Initialize filters
  initializeFilters();
});

// Select all functionality
function initializeSelectAll() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  const studentCheckboxes = document.querySelectorAll(".student-checkbox");

  selectAllCheckbox.addEventListener("change", function () {
    studentCheckboxes.forEach((checkbox) => {
      checkbox.checked = this.checked;
    });
  });

  // Update select all when individual checkboxes change
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

// Filter functionality
function initializeFilters() {
  const entriesCount = document.getElementById("entries-count");
  const departmentFilter = document.getElementById("department-filter");
  const statusFilter = document.getElementById("status-filter");

  // Add event listeners for filters
  [entriesCount, departmentFilter, statusFilter].forEach((filter) => {
    if (filter) {
      filter.addEventListener("change", function () {
        applyFilters();
      });
    }
  });
}

function applyFilters() {
  const departmentFilter = document.getElementById("department-filter").value;
  const statusFilter = document.getElementById("status-filter").value;
  const studentRows = document.querySelectorAll(".student-row");

  studentRows.forEach((row) => {
    let showRow = true;

    // Department filter
    if (departmentFilter && departmentFilter !== "") {
      const deptText = row
        .querySelector(".student-dept")
        .textContent.toLowerCase();
      if (!deptText.includes(departmentFilter.toLowerCase())) {
        showRow = false;
      }
    }

    // Status filter
    if (statusFilter && statusFilter !== "") {
      const statusText = row
        .querySelector(".student-status")
        .textContent.toLowerCase();
      if (statusText !== statusFilter.toLowerCase()) {
        showRow = false;
      }
    }

    row.style.display = showRow ? "flex" : "none";
  });
}

// Edit student function
function editStudent(studentId, collection) {
  // Redirect to edit page with student ID and collection
  window.location.href = `editstudentinfo.php?student_id=${encodeURIComponent(
    studentId
  )}&collection=${encodeURIComponent(collection)}`;
}

// Delete student function
function deleteStudent(studentId, collection) {
  if (
    confirm(
      "Are you sure you want to delete this student? This action cannot be undone."
    )
  ) {
    // Send delete request to server
    fetch("../../Connection/DeleteStudent.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        student_id: studentId,
        collection: collection,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Student deleted successfully!");
          location.reload(); // Reload the page to update the list
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

function togglePass(icon) {
  const studentRow = icon.closest(".student-row");
  if (!studentRow) return;

  const passwordText = studentRow.querySelector(".password-text");
  if (!passwordText) return;

  const eyeOpen = studentRow.querySelector(".eyeIcon.open.eyeIcon-list");
  const eyeClose = studentRow.querySelector(".eyeIcon.close.eyeIcon-list");

  if (eyeClose && eyeClose.style.display !== "none") {
    passwordText.textContent = passwordText.getAttribute("data-password");
    passwordText.style.color = "#FFFFFF";
    passwordText.style.fontSize = "14px";
    passwordText.style.filter = "";
    eyeClose.style.display = "none";
    if (eyeOpen) eyeOpen.style.display = "flex";
  } else {
    passwordText.textContent = "********";
    passwordText.style.color = "#FFFFFF";
    passwordText.style.fontSize = "14px";
    passwordText.style.filter = "";
    if (eyeClose) eyeClose.style.display = "flex";
    if (eyeOpen) eyeOpen.style.display = "none";
  }
}
