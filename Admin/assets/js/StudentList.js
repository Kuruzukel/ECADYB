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
    "--text-color": "#000000",
    "--text-color-secondary": "#212529",
    "--text-color-muted": "#495057",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
  const root = document.documentElement;
  const selectedTheme = themes[theme] || themes["Default"];

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
}

const getBasePath = () => {
  return window.location.pathname.includes("/ECADYB/") ? "/ECADYB" : "";
};

const STATUS_ENDPOINT = getBasePath() + "/Connection/Student/UpdateStatus.php";
const STUDENT_UPDATE_ENDPOINT =
  getBasePath() + "/Connection/Student/UpdateStudent.php";
const DELETE_STUDENT_ENDPOINT =
  getBasePath() + "/Connection/Student/DeleteStudent.php";
const BULK_STATUS_ENDPOINT =
  getBasePath() + "/Connection/Student/BulkUpdateStatus.php";

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  if (savedTheme) {
    applyTheme(savedTheme);
  }

  const urlParams = new URLSearchParams(window.location.search);
  const currentAcademicYear = urlParams.get("academic_year");

  if (!currentAcademicYear) {
    const department = urlParams.get("department") || "bsme";
    const tab = urlParams.get("tab") || "all";
    const pageNum = urlParams.get("pageNum") || "1";

    const newUrl = `?page=student-list&academic_year=2024-2025&department=${department}&tab=${tab}&pageNum=${pageNum}`;
    window.history.replaceState({}, "", newUrl);
  }

  isInitializing = true;

  initializeSelectAll();
  initializeFilters();
  initializeStatusUpdates();
  initializeDeleteModal();

  document.addEventListener("submit", function (event) {
    event.preventDefault();
    event.stopPropagation();
  });

  setTimeout(() => {
    isInitializing = false;
  }, 1000);
});

let isSelectAllActive = false;
let isSelectAllOperation = false;
let selectAllProcessedCount = 0;
let selectAllTotalCount = 0;
let isInitializing = false;
let isBulkUpdateInProgress = false;
let notificationTimeout = null;
let currentOperation = null;

const pageCache = new Map();

function updateSelectAllState() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (!selectAllCheckbox) return;

  const visibleCheckboxes = getVisibleStudentCheckboxes();
  if (visibleCheckboxes.length === 0) {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = false;
    return;
  }

  const checkedCount = visibleCheckboxes.filter(
    (checkbox) => checkbox.checked
  ).length;
  const allChecked = checkedCount === visibleCheckboxes.length;
  const noneChecked = checkedCount === 0;

  if (allChecked) {
    selectAllCheckbox.checked = true;
    selectAllCheckbox.indeterminate = false;
    localStorage.setItem("selectAllState", "true");
  } else if (noneChecked) {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = true;
    localStorage.removeItem("selectAllState");
  } else {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = true;
    localStorage.removeItem("selectAllState");
  }
}

function initializeSelectAll() {
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (!selectAllCheckbox) {
    return;
  }

  const savedSelectAllState = localStorage.getItem("selectAllState");

  const visibleCheckboxes = getVisibleStudentCheckboxes();
  const checkedCount = visibleCheckboxes.filter((cb) => cb.checked).length;
  const allChecked =
    checkedCount === visibleCheckboxes.length && visibleCheckboxes.length > 0;
  const allPending = checkedCount === 0 && visibleCheckboxes.length > 0;

  if (allChecked) {
    selectAllCheckbox.checked = true;
    selectAllCheckbox.indeterminate = false;
    isSelectAllActive = true;
    localStorage.setItem("selectAllState", "true");
  } else if (allPending) {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = true;
    isSelectAllActive = false;
    localStorage.removeItem("selectAllState");
  } else if (checkedCount > 0) {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = true;
    isSelectAllActive = false;
    localStorage.removeItem("selectAllState");
  } else {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = false;
    isSelectAllActive = false;
    localStorage.removeItem("selectAllState");
  }

  const newSelectAllCheckbox = selectAllCheckbox.cloneNode(true);
  selectAllCheckbox.parentNode.replaceChild(
    newSelectAllCheckbox,
    selectAllCheckbox
  );

  newSelectAllCheckbox.addEventListener("change", function () {
    if (isBulkUpdateInProgress) {
      this.checked = !this.checked;
      return;
    }

    this.indeterminate = false;

    isSelectAllActive = this.checked;

    if (this.checked) {
      localStorage.setItem("selectAllState", "true");
    } else {
      localStorage.removeItem("selectAllState");
    }

    const visibleStudentCheckboxes = getVisibleStudentCheckboxes();

    if (visibleStudentCheckboxes.length > 0) {
      if (this.checked) {
        const departmentFilter = document.getElementById("department-filter");
        const statusFilter = document.getElementById("status-filter");
        const academicYearFilter = document.getElementById(
          "academic-year-filter"
        );

        const department = departmentFilter ? departmentFilter.value : "";
        const status = statusFilter ? statusFilter.value : "";
        const academicYear = academicYearFilter ? academicYearFilter.value : "";

        if (department) {
          currentOperation = "activating_all";
          updateAllStudentsStatus(department, "Active", academicYear, status);
        } else {
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
        }
      } else {
        const departmentFilter = document.getElementById("department-filter");
        const statusFilter = document.getElementById("status-filter");
        const academicYearFilter = document.getElementById(
          "academic-year-filter"
        );

        const department = departmentFilter ? departmentFilter.value : "";
        const status = statusFilter ? statusFilter.value : "";
        const academicYear = academicYearFilter ? academicYearFilter.value : "";

        if (department) {
          currentOperation = "pending_all";
          updateAllStudentsStatus(department, "Pending", academicYear, status);
        } else {
          visibleStudentCheckboxes.forEach((checkbox) => {
            if (checkbox.checked) {
              checkbox.checked = false;
              checkbox.dispatchEvent(new Event("change", { bubbles: true }));
            }
          });
        }
      }
    } else {
      if (this.checked) {
        _showNotification("No students found to update", "info");
      }
    }
  });
}

async function updateAllStudentsStatus(
  collection,
  status,
  academicYear,
  statusFilter
) {
  try {
    isBulkUpdateInProgress = true;

    const endpoint = window.location.origin + BULK_STATUS_ENDPOINT;

    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        collection: collection,
        status: status,
        academic_year: academicYear,
        status_filter: statusFilter,
      }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);

    const data = await res.json();

    if (data && data.success) {
      if (notificationTimeout) {
        clearTimeout(notificationTimeout);
      }

      notificationTimeout = setTimeout(() => {
        _showNotification(
          data.message || `All students status updated to ${status}`,
          "success"
        );
        notificationTimeout = null;
        currentOperation = null;
      }, 100);

      pageCache.clear();

      const urlParams = new URLSearchParams(window.location.search);
      const academicYear = urlParams.get("academic_year") || "";
      const department = urlParams.get("department") || "";
      const tab = urlParams.get("tab") || "all";
      const pageNum = urlParams.get("pageNum") || "1";

      loadStudentList(parseInt(pageNum), academicYear, department, tab);
    } else {
      if (notificationTimeout) {
        clearTimeout(notificationTimeout);
      }

      notificationTimeout = setTimeout(() => {
        _showNotification(
          data.message || "Failed to update all students status",
          "error"
        );
        notificationTimeout = null;
        currentOperation = null;
      }, 100);
    }
  } catch (err) {
    console.error("[BulkUpdateStatus] Fetch error:", err);

    if (notificationTimeout) {
      clearTimeout(notificationTimeout);
    }

    notificationTimeout = setTimeout(() => {
      _showNotification(
        "Error updating all students status. Check console.",
        "error"
      );
      notificationTimeout = null;
      currentOperation = null;
    }, 100);
  } finally {
    setTimeout(() => {
      isBulkUpdateInProgress = false;
    }, 1000);
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
    return;
  }

  localStorage.removeItem("selectAllState");
  const selectAllCheckbox = document.getElementById("select-all-header");
  if (selectAllCheckbox) {
    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = false;
  }

  setTimeout(updateSelectAllState, 0);
}

async function checkAndRefreshAcademicYearFilter() {
  try {
    const lastUpdated = localStorage.getItem("academicYearsLastUpdated");
    const cachedYears = localStorage.getItem("cachedAcademicYears");

    if (lastUpdated && cachedYears) {
      const timeSinceUpdate = Date.now() - parseInt(lastUpdated);
      if (timeSinceUpdate < 10000) {
        console.log(
          "🔄 Recent academic year update detected, refreshing filter..."
        );

        const academicYearFilter = document.getElementById(
          "academic-year-filter"
        );
        if (!academicYearFilter) return;

        const currentlySelected = academicYearFilter.value;
        const years = JSON.parse(cachedYears);

        const firstOption = academicYearFilter.options[0].cloneNode(true);

        // Clear and rebuild
        academicYearFilter.innerHTML = "";
        academicYearFilter.appendChild(firstOption);

        years.forEach((year) => {
          const option = document.createElement("option");
          option.value = year;
          option.textContent = `Batch Year ${year}`;
          if (year === currentlySelected) {
            option.selected = true;
          }
          academicYearFilter.appendChild(option);
        });

        console.log(
          "✅ Academic year filter refreshed with",
          years.length,
          "years"
        );

        // Clear the flag after refreshing
        localStorage.removeItem("academicYearsLastUpdated");
      }
    }
  } catch (error) {
    console.error("Error checking academic year updates:", error);
  }
}

function initializeFilters() {
  const entriesCount = document.getElementById("entries-count");
  const departmentFilter = document.getElementById("department-filter");
  const statusFilter = document.getElementById("status-filter");
  const academicYearFilter = document.getElementById("academic-year-filter");

  if (academicYearFilter) {
    // Check if academic years were recently updated (from CSV upload)
    checkAndRefreshAcademicYearFilter();

    academicYearFilter.addEventListener("change", function () {
      pageCache.clear();

      const urlParams = new URLSearchParams(window.location.search);
      const academicYear = this.value;
      const department = urlParams.get("department") || "bsme";
      const tab = urlParams.get("tab") || "all";

      const newUrl = `?page=student-list&academic_year=${academicYear}&department=${department}&tab=${tab}&pageNum=1`;
      window.history.pushState({}, "", newUrl);

      loadStudentList(1, academicYear, department, tab);
    });
  }

  if (departmentFilter) {
    departmentFilter.addEventListener("change", function () {
      pageCache.clear();

      const urlParams = new URLSearchParams(window.location.search);
      const academicYear = urlParams.get("academic_year") || "";
      const department = this.value;
      const tab = urlParams.get("tab") || "all";

      const newUrl = `?page=student-list&academic_year=${academicYear}&department=${department}&tab=${tab}&pageNum=1`;
      window.history.pushState({}, "", newUrl);

      loadStudentList(1, academicYear, department, tab);
    });
  }

  [entriesCount, statusFilter].forEach((filter) => {
    if (filter) filter.addEventListener("change", applyFilters);
  });
}

function applyFilters() {
  const statusVal = (
    document.getElementById("status-filter")?.value || ""
  ).trim();

  if (statusVal) {
    clearSelectAllState();
  }

  const tableBody = document.querySelector("tbody");
  if (!tableBody) return;

  const studentRows = tableBody.querySelectorAll("tr");

  studentRows.forEach((row, index) => {
    const checkbox = row.querySelector(".student-checkbox");
    if (!checkbox) {
      return;
    }

    let showRow = true;

    if (statusVal) {
      const statusAttr = checkbox.dataset.status || "";
      if (statusAttr.toLowerCase() !== statusVal.toLowerCase()) {
        showRow = false;
      }
    }

    row.style.display = showRow ? "" : "none";
  });

  if (!isInitializing) {
    setTimeout(updateSelectAllState, 0);
  }
}

function showNotification(message, type = "success") {
  if (isSelectAllOperation && message.includes("Status updated")) {
    selectAllProcessedCount++;

    if (selectAllProcessedCount >= selectAllTotalCount) {
      const finalMessage = `All ${selectAllTotalCount} student statuses updated successfully`;
      _showNotification(finalMessage, type);
      isSelectAllOperation = false;
      selectAllProcessedCount = 0;
      selectAllTotalCount = 0;
    }
    return;
  }

  _showNotification(message, type);
}

function _showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const existingNotifications = container.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  let messageClass = type === "error" ? "error-message" : "success-message";

  const notif = document.createElement("div");
  notif.className = `notification ${messageClass}`;
  notif.id = `${type}-notification`;
  notif.innerHTML = `
    <span class="notification-message">${message}</span>
    <button class="notification-close" onclick="closeNotification('${type}-notification')">
      <i class="fas fa-times"></i>
    </button>
  `;
  container.appendChild(notif);

  setTimeout(() => {
    notif.classList.add("show");
  }, 10);

  const duration = type === "info" ? 2000 : 5000;
  setTimeout(() => {
    closeNotification(`${type}-notification`);
  }, duration);
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

const deleteModal = document.getElementById("delete-modal-overlay");
let confirmDeleteBtn = document.getElementById("confirm-delete-btn");
let cancelDeleteBtn = document.getElementById("cancel-delete-btn");

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

async function confirmDeleteStudent(event) {
  if (!selectedStudentId || !selectedCollection) return;

  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  const studentIdToDelete = selectedStudentId;
  const collectionToDelete = selectedCollection;

  confirmDeleteBtn.disabled = true;

  closeDeleteModal();

  try {
    const urlParams = new URLSearchParams(window.location.search);
    const academicYear = urlParams.get("academic_year") || "";

    const endpoint = window.location.origin + DELETE_STUDENT_ENDPOINT;

    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        student_id: studentIdToDelete,
        collection: collectionToDelete,
        academic_year: academicYear,
      }),
    });

    const data = await res.json().catch(() => null);

    if (!res.ok) {
      const errorMsg = data?.message || `HTTP ${res.status} ${res.statusText}`;
      throw new Error(errorMsg);
    }

    if (data?.success) {
      pageCache.clear();

      const urlParams = new URLSearchParams(window.location.search);
      const academicYear = urlParams.get("academic_year") || "";
      const department = urlParams.get("department") || "bsme";
      const tab = urlParams.get("tab") || "all";
      const pageNum = urlParams.get("pageNum") || "1";

      _showNotification(
        data.message || "Student deleted successfully",
        "success"
      );

      loadStudentList(parseInt(pageNum), academicYear, department, tab);
    } else {
      _showNotification(data?.message || "Failed to delete student", "error");
    }
  } catch (err) {
    console.error("Error deleting student:", err);
    _showNotification("Error deleting student. Check console.", "error");
  } finally {
    confirmDeleteBtn.disabled = false;
  }
}

function initializeDeleteModal() {
  if (!confirmDeleteBtn || !cancelDeleteBtn || !deleteModal) return;

  const newConfirmBtn = confirmDeleteBtn.cloneNode(true);
  const newCancelBtn = cancelDeleteBtn.cloneNode(true);

  confirmDeleteBtn.parentNode.replaceChild(newConfirmBtn, confirmDeleteBtn);
  cancelDeleteBtn.parentNode.replaceChild(newCancelBtn, cancelDeleteBtn);

  confirmDeleteBtn = newConfirmBtn;
  cancelDeleteBtn = newCancelBtn;

  confirmDeleteBtn.addEventListener("click", (event) =>
    confirmDeleteStudent(event)
  );
  cancelDeleteBtn.addEventListener("click", closeDeleteModal);

  deleteModal.addEventListener("click", (e) => {
    if (e.target === deleteModal) closeDeleteModal();
  });
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
  if (!studentCheckboxes.length) return;

  studentCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", async function () {
      if (isInitializing) {
        return;
      }

      if (!isSelectAllActive && !isSelectAllOperation) {
        clearSelectAllState();
      }

      if (this.dataset.busy === "1") return;
      this.dataset.busy = "1";

      const studentId = this.dataset.studentId?.trim();
      const collection = this.dataset.collection?.trim();
      const status = this.checked ? "Active" : "Pending";

      if (!studentId || !collection) {
        _showNotification("Student ID or collection missing", "error");
        this.checked = !this.checked;
        this.dataset.busy = "0";
        return;
      }

      try {
        const urlParams = new URLSearchParams(window.location.search);
        const academicYear = urlParams.get("academic_year") || "";

        const endpoint = window.location.origin + STATUS_ENDPOINT;

        const res = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            student_id: studentId,
            collection,
            status,
            academic_year: academicYear,
          }),
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

          const urlParams = new URLSearchParams(window.location.search);
          const academicYear = urlParams.get("academic_year") || "";
          const department = urlParams.get("department") || "bsme";
          const pageNum = urlParams.get("pageNum") || "1";
          const cacheKey = `${academicYear}-${department}-${pageNum}`;
          pageCache.delete(cacheKey);

          applyFilters();

          if (!isSelectAllOperation) {
            _showNotification(
              data.message || "Status updated successfully",
              "success"
            );
          }
        } else {
          if (!isSelectAllOperation) {
            _showNotification(
              data.message || "Failed to update status",
              "error"
            );
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

  const urlParams = new URLSearchParams(window.location.search);
  const academicYear = urlParams.get("academic_year") || "";
  fields["academic_year"] = academicYear;

  try {
    const endpoint = window.location.origin + STUDENT_UPDATE_ENDPOINT;

    const res = await fetch(endpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ original_student_id: studentId, ...fields }),
    });

    if (!res.ok) {
      const errorText = await res.text();
      console.error("Response error:", errorText);
      _showNotification(
        "Failed to save student details. Server error: " + res.status,
        "error"
      );
      return;
    }

    let data;
    try {
      data = await res.json();
    } catch (jsonError) {
      console.error("JSON parse error:", jsonError);
      _showNotification("Failed to parse server response", "error");
      return;
    }

    if (data && data.success) {
      pageCache.clear();

      _showNotification(
        data.message || "Student Details Saved Successfully",
        "success"
      );

      const modal = document.getElementById(`editModal_${studentId}`);
      if (modal) {
        modal.classList.remove("active");
      }

      const urlParams = new URLSearchParams(window.location.search);
      const academicYear = urlParams.get("academic_year") || "";
      const department = urlParams.get("department") || "bsme";
      const tab = urlParams.get("tab") || "all";
      const pageNum = urlParams.get("pageNum") || "1";

      loadStudentList(parseInt(pageNum), academicYear, department, tab);
    } else {
      _showNotification(
        data?.message || "Failed to save student details",
        "error"
      );
    }
  } catch (error) {
    console.error("Fetch error:", error);
    _showNotification(
      "Error saving student details: " + error.message,
      "error"
    );
  }
}

function submitStudentForm(studentId, event) {
  if (!studentId) {
    console.error("No studentId provided");
    return;
  }

  if (event) {
    event.preventDefault();
    event.stopPropagation();
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
    department_section: "department section",
    status: "status",
  };

  for (const [key, mongoKey] of Object.entries(fieldMapping)) {
    const el = document.getElementById(`${key}${studentId}`);
    if (el) {
      fields[mongoKey] = el.value.trim();
    }
  }

  const programHiddenEl = document.getElementById(`program-hidden${studentId}`);
  if (programHiddenEl) {
    fields["program"] = programHiddenEl.value.trim();
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

function changePage(pageNum) {
  const urlParams = new URLSearchParams(window.location.search);
  urlParams.set("pageNum", pageNum);

  const academicYear = urlParams.get("academic_year") || "";
  const department = urlParams.get("department") || "bsme";
  const tab = urlParams.get("tab") || "all";

  const newUrl = `?page=student-list&academic_year=${academicYear}&department=${department}&tab=${tab}&pageNum=${pageNum}`;
  window.history.pushState({}, "", newUrl);

  loadStudentList(pageNum, academicYear, department, tab);
}

function loadStudentList(pageNum, academicYear, department, tab) {
  const tableBody = document.querySelector("tbody");

  const cacheKey = `${academicYear}-${department}-${pageNum}`;

  if (pageCache.has(cacheKey)) {
    const cachedData = pageCache.get(cacheKey);

    if (tableBody) {
      tableBody.innerHTML = cachedData.html;
    }

    const currentPageInfo = document.querySelector(".pagination-controls span");
    if (currentPageInfo) {
      currentPageInfo.textContent = cachedData.pageInfo;
    }

    updatePaginationButtons(
      pageNum,
      academicYear,
      department,
      tab,
      cachedData.totalPages
    );

    isInitializing = true;
    initializeSelectAll();
    initializeStatusUpdates();
    isInitializing = false;

    return;
  }

  if (tableBody) {
    tableBody.innerHTML =
      '<tr><td colspan="7" style="text-align: center; vertical-align: middle; padding: 0; height: 400px;"><div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 20px;"><i class="fas fa-spinner fa-spin" style="font-size: 3rem; color: #60a5fa;"></i><span style="font-size: 1.2rem; font-weight: 500; color: #fff;">Loading students...</span></div></td></tr>';
  }

  fetch(
    `?page=student-list&academic_year=${academicYear}&department=${department}&tab=${tab}&pageNum=${pageNum}&ajax=1`
  )
    .then((response) => response.text())
    .then((data) => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(data, "text/html");
      const newTableBody = doc.querySelector("tbody");
      const newPageInfo = doc.querySelector(".pagination-controls span");

      if (newTableBody && tableBody) {
        tableBody.innerHTML = newTableBody.innerHTML;
      }

      if (newPageInfo) {
        const currentPageInfo = document.querySelector(
          ".pagination-controls span"
        );
        if (currentPageInfo)
          currentPageInfo.textContent = newPageInfo.textContent;
      }

      const totalPagesInput = doc.getElementById("total-pages");
      const totalPages = totalPagesInput
        ? parseInt(totalPagesInput.value)
        : null;

      pageCache.set(cacheKey, {
        html: newTableBody ? newTableBody.innerHTML : "",
        pageInfo: newPageInfo ? newPageInfo.textContent : "",
        totalPages: totalPages,
      });

      updatePaginationButtons(
        pageNum,
        academicYear,
        department,
        tab,
        totalPages
      );

      isInitializing = true;
      initializeSelectAll();
      initializeStatusUpdates();
      isInitializing = false;
    })
    .catch((error) => {
      console.error("Error loading page:", error);
      if (tableBody) {
        tableBody.innerHTML =
          '<tr><td colspan="7" style="text-align: center; vertical-align: middle; padding: 0; height: 400px;"><div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; gap: 20px;"><i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ff4444;"></i><span style="font-size: 1.2rem; font-weight: 500; color: #fff;">Error loading page. Please refresh.</span></div></td></tr>';
      }
      _showNotification("Error loading students. Please try again.", "error");
    });
}

function updatePaginationButtons(
  pageNum,
  academicYear,
  department,
  tab,
  totalPages = null
) {
  const prevBtn = document.getElementById("prev-btn");
  const nextBtn = document.getElementById("next-btn");

  if (prevBtn) {
    if (pageNum > 1) {
      prevBtn.disabled = false;
      prevBtn.onclick = () => changePage(pageNum - 1);
    } else {
      prevBtn.disabled = true;
      prevBtn.onclick = null;
    }
  }

  if (nextBtn && totalPages) {
    if (pageNum < totalPages) {
      nextBtn.disabled = false;
      nextBtn.onclick = () => changePage(pageNum + 1);
    } else {
      nextBtn.disabled = true;
      nextBtn.onclick = null;
    }
  }
}

function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.add("active");
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove("active");
}

window.addEventListener("click", function (event) {
  const modals = document.querySelectorAll(".editStudentModal");
  modals.forEach((modal) => {
    if (event.target === modal) modal.classList.remove("active");
  });
});
