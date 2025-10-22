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

let notificationTimeout = null;
let currentOperation = null;

function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const existingNotifications = container.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }

  let icon = "fa-check-circle";
  if (type === "error") {
    icon = "fa-exclamation-circle";
  } else if (type === "info") {
    icon = "fa-info-circle";
  } else if (type === "warning") {
    icon = "fa-exclamation-triangle";
  }

  const notif = document.createElement("div");
  notif.className = `notification ${type} show`;
  notif.innerHTML = `
    <i class="fas ${icon}"></i>
    <span>${message}</span>
  `;
  container.appendChild(notif);

  const duration = type === "info" ? 2000 : 5000;
  notificationTimeout = setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => {
      notif.remove();
      notificationTimeout = null;
      currentOperation = null;
    }, 500);
  }, duration);
}

function getBasePath() {
  const currentPath = window.location.pathname;

  if (currentPath.includes("/Admin/")) {
    const adminIndex = currentPath.indexOf("/Admin/");
    return currentPath.substring(0, adminIndex);
  }

  return window.location.origin;
}

let deleteModal = null;
let confirmDeleteBtn = null;
let cancelDeleteBtn = null;

let generateModal = null;
let confirmGenerateBtn = null;
let cancelGenerateBtn = null;

let downloadPdfModal = null;
let confirmDownloadPdfBtn = null;
let cancelDownloadPdfBtn = null;

let deleteBatchModal = null;
let confirmDeleteBatchBtn = null;
let cancelDeleteBatchBtn = null;

let selectTemplateModal = null;
let confirmSelectTemplateBtn = null;
let cancelSelectTemplateBtn = null;

let currentUploadControllers = []; // Array to track multiple XHR requests
let globalIsUploading = false;
let lastUploadedFiles = []; // Track all uploaded files in current batch for cleanup
let pendingUploads = []; // Track uploads in progress (before completion)
let isCancelling = false; // Guard flag to prevent multiple simultaneous cancellations

// Emergency reset function - accessible from console
window.resetUploadStates = function () {
  console.log("🔧 EMERGENCY RESET: Resetting all upload states...");
  currentUploadControllers = [];
  globalIsUploading = false;
  lastUploadedFiles = [];
  pendingUploads = [];
  isCancelling = false;

  // Reset all file inputs
  document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.value = "";
  });

  // Hide overlay
  const uploadOverlay = document.getElementById("upload-overlay");
  if (uploadOverlay) {
    uploadOverlay.style.display = "none";
  }

  console.log(
    "✅ All upload states reset. Upload boxes should be clickable now."
  );
  console.log(
    "Note: Individual upload box flags (isUploading, isFileInputOpen) are scoped to each box."
  );
  console.log(
    "If still stuck, try clicking the upload box - it should auto-reset after 500ms."
  );
};

async function cancelUpload() {
  // Prevent multiple simultaneous cancel operations
  if (isCancelling) {
    console.log(
      "Cancel already in progress, ignoring duplicate cancel request"
    );
    return;
  }

  isCancelling = true;
  console.log("Cancel upload triggered in BatchTemplates");

  // Hide upload overlay IMMEDIATELY - don't wait for cleanup
  const uploadOverlay = document.getElementById("upload-overlay");
  if (uploadOverlay) {
    uploadOverlay.style.display = "none";
  }

  // Reset file inputs immediately
  document.querySelectorAll('input[type="file"]').forEach((input) => {
    if (input.files && input.files.length > 0) {
      input.value = "";
    }
  });

  // Show immediate feedback to user
  showNotification("Upload cancelled", "error");

  // Abort ALL active XHR requests
  if (currentUploadControllers.length > 0) {
    console.log(
      `Aborting ${currentUploadControllers.length} active upload(s)...`
    );
    currentUploadControllers.forEach((xhr, index) => {
      if (xhr) {
        console.log(
          `Aborting upload ${index + 1}/${currentUploadControllers.length}`
        );
        xhr.abort();
      }
    });
    currentUploadControllers = [];
  }

  // Combine both completed and pending uploads for cleanup
  // IMPORTANT: Copy arrays and clear originals immediately to prevent re-entry
  const allFilesToCleanup = [...lastUploadedFiles, ...pendingUploads];
  lastUploadedFiles = [];
  pendingUploads = [];

  globalIsUploading = false;

  // Perform cleanup in the background (don't block UI)
  if (allFilesToCleanup.length > 0) {
    console.log(
      `Background cleanup: ${allFilesToCleanup.length} file(s) from Bunny CDN and MongoDB...`
    );

    // Run cleanup without blocking
    Promise.all(
      allFilesToCleanup.map((fileInfo) => deleteRecentlyUploadedFile(fileInfo))
    )
      .then((results) => {
        const successCount = results.filter((r) => r === true).length;
        const failCount = results.filter((r) => r === false).length;

        console.log(
          `Background cleanup complete: ${successCount} deleted successfully, ${failCount} failed`
        );
        // No notification - user already saw "Upload cancelled" message
      })
      .catch((error) => {
        console.error("Error during background cleanup:", error);
      })
      .finally(() => {
        isCancelling = false;
        console.log("Background cleanup finished, flag reset");
      });
  } else {
    console.log(
      "No files to clean up (upload was cancelled before any uploads started)"
    );
    isCancelling = false;
    // No notification - user already saw "Upload cancelled" message
  }
}

async function deleteRecentlyUploadedFile(fileInfo) {
  const MAX_DELETE_RETRIES = 3;
  let attempt = 0;

  while (attempt < MAX_DELETE_RETRIES) {
    attempt++;

    try {
      const BASE_PATH = getBasePath();
      const DELETE_ENDPOINT = `${BASE_PATH}/Connection/Cover/DeleteCover.php`;

      const formData = new FormData();
      formData.append("slot", fileInfo.slot);
      formData.append("batch_year", fileInfo.batch_year);
      formData.append("side", fileInfo.side || "front");

      // Add template number for deletion
      const templateNumber =
        localStorage.getItem("selectedBatchTemplateNumber") || 1;
      formData.append("template", templateNumber);

      if (attempt === 1) {
        console.log(
          `🗑️ Deleting Slot ${fileInfo.slot} ${fileInfo.side} from Bunny CDN and MongoDB (Batch: ${fileInfo.batch_year})`
        );
      } else {
        console.log(
          `🔄 Retry ${attempt}/${MAX_DELETE_RETRIES} - Deleting Slot ${fileInfo.slot} ${fileInfo.side}`
        );
      }

      const response = await fetch(DELETE_ENDPOINT, {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        console.log(
          `✅ Confirmed deleted from Bunny CDN: Slot ${fileInfo.slot} ${fileInfo.side}`
        );
        return true;
      } else {
        // If file not found, it's already deleted (success)
        if (data.message && data.message.includes("not found")) {
          console.log(
            `ℹ️ Slot ${fileInfo.slot} ${fileInfo.side} already deleted or never saved`
          );
          return true;
        }

        console.warn(`⚠️ Delete attempt ${attempt} failed: ${data.message}`);

        // Retry after a short delay
        if (attempt < MAX_DELETE_RETRIES) {
          await new Promise((resolve) => setTimeout(resolve, 1000 * attempt));
        }
      }
    } catch (error) {
      console.error(`❌ Error on delete attempt ${attempt}:`, error);

      // Retry after a short delay
      if (attempt < MAX_DELETE_RETRIES) {
        await new Promise((resolve) => setTimeout(resolve, 1000 * attempt));
      }
    }
  }

  console.error(
    `❌ Failed to delete Slot ${fileInfo.slot} ${fileInfo.side} after ${MAX_DELETE_RETRIES} attempts`
  );
  return false;
}

let selectedStudentId = null;
let selectedCollection = null;
let selectedConfirmAction = null;

function openDeleteModal(studentId, collection) {
  selectedStudentId = studentId?.trim();
  selectedCollection = collection?.trim();
  if (deleteModal) deleteModal.style.display = "flex";
}

function closeDeleteModal() {
  selectedStudentId = null;
  selectedCollection = null;
  selectedConfirmAction = null;
  if (deleteModal) deleteModal.style.display = "none";
}

function openGenerateModal() {
  if (generateModal) generateModal.style.display = "flex";
}

function closeGenerateModal() {
  if (generateModal) generateModal.style.display = "none";
}

function openDownloadPdfModal(batchName) {
  console.log("openDownloadPdfModal called with:", batchName);
  const messageEl = document.getElementById("download-pdf-message");
  if (messageEl) {
    messageEl.textContent = `Are you sure you want to download the PDF for ${batchName}?`;
  }
  if (downloadPdfModal) {
    console.log("Showing download PDF modal");
    downloadPdfModal.style.display = "flex";
  } else {
    console.error("downloadPdfModal element not found!");
  }
}

function closeDownloadPdfModal() {
  if (downloadPdfModal) downloadPdfModal.style.display = "none";
}

function openDeleteBatchModal(batchName) {
  console.log("openDeleteBatchModal called with:", batchName);
  const messageEl = document.getElementById("delete-batch-message");
  if (messageEl) {
    messageEl.textContent = `Are you sure you want to delete ${batchName}? This action cannot be undone.`;
  }
  if (deleteBatchModal) {
    console.log("Showing delete batch modal");
    deleteBatchModal.style.display = "flex";
  } else {
    console.error("deleteBatchModal element not found!");
  }
}

function closeDeleteBatchModal() {
  if (deleteBatchModal) deleteBatchModal.style.display = "none";
}

function openSelectTemplateModal(batchName) {
  console.log("openSelectTemplateModal called with:", batchName);

  const sections = document.querySelectorAll(".section");
  let targetSection = null;

  sections.forEach((section) => {
    const sectionHeader = section.querySelector(".section-header");
    if (sectionHeader && sectionHeader.textContent.trim() === batchName) {
      targetSection = section;
    }
  });

  if (!targetSection) {
    console.error("Section not found for:", batchName);
    return;
  }

  window.pendingSelectSection = targetSection;
  window.pendingSelectBatchName = batchName;

  const messageEl = document.getElementById("select-template-message");
  if (messageEl) {
    messageEl.textContent = `Do you want to select ${batchName}?`;
  }
  if (selectTemplateModal) {
    console.log("Showing select template modal");
    selectTemplateModal.style.display = "flex";
  } else {
    console.error("selectTemplateModal element not found!");
  }
}

function closeSelectTemplateModal() {
  if (selectTemplateModal) selectTemplateModal.style.display = "none";
}

async function confirmDeleteStudent() {
  if (!selectedStudentId || !selectedCollection) return;

  if (confirmDeleteBtn) confirmDeleteBtn.disabled = true;

  try {
    const BASE_PATH = getBasePath();
    const CONNECTION_PATH = `${BASE_PATH}/Connection`;

    const res = await fetch(`${CONNECTION_PATH}/Student/DeleteStudent.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        student_id: selectedStudentId,
        collection: selectedCollection,
      }),
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }

    const data = await res.json();
    if (typeof showNotification === "function") {
      showNotification(data.message, data.success ? "success" : "error");
    }

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
    if (typeof showNotification === "function") {
      showNotification("Error deleting student.", "error");
    }
  } finally {
    if (confirmDeleteBtn) confirmDeleteBtn.disabled = false;
    setTimeout(() => {
      closeDeleteModal();
    }, 500);
  }
}

function initializeDeleteModal() {
  if (!confirmDeleteBtn || !cancelDeleteBtn || !deleteModal) return;

  cancelDeleteBtn.addEventListener("click", closeDeleteModal);
  deleteModal.addEventListener("click", (e) => {
    if (e.target === deleteModal) closeDeleteModal();
  });
  confirmDeleteBtn.addEventListener("click", async () => {
    if (selectedStudentId && selectedCollection) {
      await confirmDeleteStudent();
    } else if (typeof selectedConfirmAction === "function") {
      selectedConfirmAction();
      closeDeleteModal();
    } else {
      closeDeleteModal();
    }
  });
}

function generateNewBatchSection() {
  const formContent = document.querySelector(".form-content");
  if (!formContent) return;

  const allSections = document.querySelectorAll(".form-group .section");

  let nextYear = "2024-2025";
  if (allSections.length > 0) {
    const lastSection = allSections[allSections.length - 1];
    const lastHeader = lastSection
      .querySelector(".section-header")
      .textContent.trim();

    const yearMatch = lastHeader.match(/(\d{4})-(\d{4})/);
    if (yearMatch) {
      const startYear = parseInt(yearMatch[1]);
      const endYear = parseInt(yearMatch[2]);
      nextYear = `${startYear + 1}-${endYear + 1}`;
    }
  }

  const newSectionHTML = `
    <div class="section">
      <div class="section-header">Batch Year ${nextYear}</div>
      <div class="section-content">
        <div class="upload-grid">
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box action-box">
            <div class="action-buttons">
              <button class="action-btn select-batch-btn" title="Select Batch">
                <i class="fas fa-check-circle"></i>
                <span>Select Batch</span>
              </button>
              <button class="action-btn download-pdf-btn" title="Download PDF">
                <i class="fas fa-file-pdf"></i>
                <span>Download PDF</span>
              </button>
              <button class="action-btn delete-batch-btn" title="Delete Batch Template">
                <i class="fas fa-trash-alt"></i>
                <span>Delete Batch</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = newSectionHTML;
  const newSection = tempDiv.firstElementChild;

  const existingFormGroups = document.querySelectorAll(".form-group");
  let targetFormGroup = null;
  let insertPosition = 0;

  for (let i = existingFormGroups.length - 1; i >= 0; i--) {
    const sectionsInGroup = existingFormGroups[i].querySelectorAll(".section");
    if (sectionsInGroup.length < 3) {
      targetFormGroup = existingFormGroups[i];
      insertPosition = sectionsInGroup.length;
      break;
    }
  }

  if (targetFormGroup) {
    const sectionsInGroup = targetFormGroup.querySelectorAll(".section");
    if (insertPosition === 0) {
      targetFormGroup.insertBefore(newSection, sectionsInGroup[0] || null);
    } else {
      targetFormGroup.appendChild(newSection);
    }
  } else {
    const newFormGroup = document.createElement("div");
    newFormGroup.className = "form-group";
    newFormGroup.appendChild(newSection);

    const generateButtonContainer = document.querySelector(
      ".generate-button-container"
    );
    if (generateButtonContainer && generateButtonContainer.parentNode) {
      generateButtonContainer.parentNode.insertBefore(
        newFormGroup,
        generateButtonContainer
      );
    } else {
      formContent.appendChild(newFormGroup);
    }
  }

  initializeSection(newSection);

  const currentXhrs = window.currentXhrs || [];
  const isUploadCancelled = window.isUploadCancelled || false;

  initializeSectionUploadBoxes(newSection, currentXhrs, isUploadCancelled);

  saveGeneratedSectionsToLocalStorage();

  if (window.refreshSections) {
    window.refreshSections();
  }

  if (window.setAvailableSections) {
    window.setAvailableSections();
  }

  if (window.updateUploadBoxStates) {
    window.updateUploadBoxStates();
  }

  showNotification(`Batch Year ${nextYear} created successfully!`, "success");

  newSection.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

function saveGeneratedSectionsToLocalStorage() {
  const allSections = document.querySelectorAll(".form-group .section");
  const generatedSections = [];

  allSections.forEach((section, index) => {
    if (index >= 3) {
      const sectionHeader = section
        .querySelector(".section-header")
        .textContent.trim();
      generatedSections.push(sectionHeader);
    }
  });

  localStorage.setItem(
    "generatedBatchSections",
    JSON.stringify(generatedSections)
  );
  console.log("Saved generated sections to localStorage:", generatedSections);
}

function restoreGeneratedSectionsFromLocalStorage() {
  const savedSections = localStorage.getItem("generatedBatchSections");

  if (!savedSections) {
    console.log("No saved sections found in localStorage");
    return;
  }

  try {
    const sectionsArray = JSON.parse(savedSections);
    console.log("Restoring sections from localStorage:", sectionsArray);

    sectionsArray.forEach((sectionHeader) => {
      restoreSingleSection(sectionHeader);
    });
  } catch (e) {
    console.error("Error parsing saved sections:", e);
  }
}

function restoreSingleSection(sectionHeader) {
  const formContent = document.querySelector(".form-content");
  if (!formContent) return;

  const newSectionHTML = `
    <div class="section">
      <div class="section-header">${sectionHeader}</div>
      <div class="section-content">
        <div class="upload-grid">
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" multiple hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box">
            <span class="plus-icon">+</span>
            <input type="file" class="frontInput" accept="image/*" hidden>
            <input type="file" class="backInput" accept="image/*" hidden>
            <button class="delete-btn">&times;</button>
          </div>
          <div class="upload-box action-box">
            <div class="action-buttons">
              <button class="action-btn select-batch-btn" title="Select Batch">
                <i class="fas fa-check-circle"></i>
                <span>Select Batch</span>
              </button>
              <button class="action-btn download-pdf-btn" title="Download PDF">
                <i class="fas fa-file-pdf"></i>
                <span>Download PDF</span>
              </button>
              <button class="action-btn delete-batch-btn" title="Delete Batch Template">
                <i class="fas fa-trash-alt"></i>
                <span>Delete Batch</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;

  const tempDiv = document.createElement("div");
  tempDiv.innerHTML = newSectionHTML;
  const newSection = tempDiv.firstElementChild;

  const existingFormGroups = document.querySelectorAll(".form-group");
  let targetFormGroup = null;
  let insertPosition = 0;

  for (let i = existingFormGroups.length - 1; i >= 0; i--) {
    const sectionsInGroup = existingFormGroups[i].querySelectorAll(".section");
    if (sectionsInGroup.length < 3) {
      targetFormGroup = existingFormGroups[i];
      insertPosition = sectionsInGroup.length;
      break;
    }
  }

  if (targetFormGroup) {
    const sectionsInGroup = targetFormGroup.querySelectorAll(".section");
    if (insertPosition === 0) {
      targetFormGroup.insertBefore(newSection, sectionsInGroup[0] || null);
    } else {
      targetFormGroup.appendChild(newSection);
    }
  } else {
    const newFormGroup = document.createElement("div");
    newFormGroup.className = "form-group";
    newFormGroup.appendChild(newSection);

    const generateButtonContainer = document.querySelector(
      ".generate-button-container"
    );
    if (generateButtonContainer && generateButtonContainer.parentNode) {
      generateButtonContainer.parentNode.insertBefore(
        newFormGroup,
        generateButtonContainer
      );
    } else {
      formContent.appendChild(newFormGroup);
    }
  }

  if (!window.deferSectionInitialization) {
    initializeSection(newSection);

    const currentXhrs = window.currentXhrs || [];
    const isUploadCancelled = window.isUploadCancelled || false;

    initializeSectionUploadBoxes(newSection, currentXhrs, isUploadCancelled);

    if (window.updateUploadBoxStates) {
      window.updateUploadBoxStates();
    }
  }

  console.log(`Restored section: ${sectionHeader}`);
}

function initializeSection(section) {
  if (!section || !section.parentNode) {
    console.error("initializeSection: Invalid section");
    return;
  }

  const sectionHeader = section.querySelector(".section-header");
  if (!sectionHeader) {
    console.error("initializeSection: Section header not found");
    return;
  }

  if (section.dataset.initialized === "true") {
    console.log("initializeSection: Section already initialized, skipping");
    return;
  }

  section.dataset.initialized = "true";
}

function initializeSectionUploadBoxes(section, currentXhrs, isUploadCancelled) {
  const sectionUploadBoxes = section.querySelectorAll(".upload-box");
  const sectionHeader = section
    .querySelector(".section-header")
    .textContent.trim();

  sectionUploadBoxes.forEach((box, index) => {
    const slot = index + 1;
    const isBackgroundSlot = slot === 8;
    const isActionBox = slot === 9;

    if (box.dataset.initialized === "true") {
      console.log(
        `Upload box ${slot} for ${sectionHeader} already initialized, skipping`
      );
      return;
    }

    box.dataset.initialized = "true";

    if (isActionBox) {
      const selectBatchBtn = box.querySelector(".select-batch-btn");
      const downloadBtn = box.querySelector(".download-pdf-btn");
      const deleteBatchBtn = box.querySelector(".delete-batch-btn");

      console.log(
        "Action box found, selectBatchBtn:",
        selectBatchBtn,
        "downloadBtn:",
        downloadBtn,
        "deleteBatchBtn:",
        deleteBatchBtn
      );

      if (selectBatchBtn) {
        selectBatchBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          if (window.openSelectTemplateModal) {
            window.openSelectTemplateModal(section, sectionHeader);
          }
        });
      }

      if (downloadBtn) {
        downloadBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          console.log("Download button clicked for:", sectionHeader);
          downloadPDF(sectionHeader);
        });
      }

      if (deleteBatchBtn) {
        deleteBatchBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          if (deleteBatchBtn.disabled) {
            console.log("Delete batch button is disabled for:", sectionHeader);
            return;
          }
          console.log("Delete batch button clicked for:", sectionHeader);
          deleteBatchTemplate(section, sectionHeader);
        });
      }
      return;
    }

    const frontInput = box.querySelector(".frontInput");
    const backInput = box.querySelector(".backInput");
    const deleteBtn = box.querySelector(".delete-btn");
    const plusIcon = box.querySelector(".plus-icon");

    let frontImg = null;
    let backImg = null;
    let showingFront = true;
    let isUploading = false;
    let isFileInputOpen = false;

    const BASE_PATH = getBasePath();
    const CONNECTION_PATH = `${BASE_PATH}/Connection`;
    const UPLOAD_ENDPOINT = `${CONNECTION_PATH}/Cover/UploadCover.php`;
    const FETCH_ENDPOINT = `${CONNECTION_PATH}/Cover/FetchCovers.php`;
    const DELETE_ENDPOINT = `${CONNECTION_PATH}/Cover/DeleteCover.php`;

    const toggleImages = () => {
      if (isBackgroundSlot) return;
      showingFront = !showingFront;
      if (frontImg && backImg) {
        if (showingFront) {
          frontImg.style.opacity = 1;
          backImg.style.opacity = 0;
        } else {
          frontImg.style.opacity = 0;
          backImg.style.opacity = 1;
        }
      }
    };

    const ensureChildren = () => {
      console.log("ensureChildren called - appending elements to box");
      if (frontImg) {
        console.log("Appending frontImg:", frontImg.src);
        box.appendChild(frontImg);
      }
      if (backImg) {
        console.log("Appending backImg:", backImg.src);
        box.appendChild(backImg);
      }
      box.appendChild(deleteBtn);
      box.appendChild(frontInput);
      box.appendChild(backInput);
      console.log("Box children after appending:", box.children);
    };

    box.addEventListener("click", (event) => {
      if (event.target === deleteBtn) return;

      if (isUploading) {
        console.log(
          `⚠️ Upload box Slot ${slot} - Click blocked: upload in progress (isUploading=${isUploading})`
        );
        return;
      }

      if (isFileInputOpen) {
        console.log(
          `⚠️ Upload box Slot ${slot} - Click blocked: file dialog open`
        );
        return;
      }

      if (!frontImg) {
        console.log(
          `✓ Upload box Slot ${slot} - Opening file dialog for front image`
        );
        isFileInputOpen = true;

        const resetFlag = () => {
          setTimeout(() => {
            if (isFileInputOpen) {
              console.log(
                `✓ Upload box Slot ${slot} - File dialog closed, resetting flag`
              );
              isFileInputOpen = false;
            }
          }, 500);
          window.removeEventListener("focus", resetFlag);
        };
        window.addEventListener("focus", resetFlag, { once: true });

        frontInput.click();
      } else if (!backImg && !isBackgroundSlot) {
        console.log(
          `✓ Upload box Slot ${slot} - Opening file dialog for back image`
        );
        isFileInputOpen = true;

        const resetFlag = () => {
          setTimeout(() => {
            if (isFileInputOpen) {
              console.log(
                `✓ Upload box Slot ${slot} - File dialog closed, resetting flag`
              );
              isFileInputOpen = false;
            }
          }, 500);
          window.removeEventListener("focus", resetFlag);
        };
        window.addEventListener("focus", resetFlag, { once: true });

        backInput.click();
      } else {
        console.log(
          `✓ Upload box Slot ${slot} - Toggling between front/back images`
        );
        toggleImages();
      }
    });

    frontInput.addEventListener("change", async (event) => {
      const files = event.target.files;
      if (!files || files.length === 0) {
        isFileInputOpen = false;
        isUploading = false;
        return;
      }

      event.stopPropagation();

      isFileInputOpen = false;

      if (isUploading) {
        console.log(
          "Upload already in progress, ignoring duplicate change event"
        );
        event.target.value = "";
        return;
      }
      isUploading = true;

      isUploadCancelled = false;

      lastUploadedFiles = [];
      pendingUploads = [];
      currentUploadControllers = [];

      console.log("Files selected:", files.length, "files");
      console.log("File 0 name:", files[0]?.name);
      console.log("File 1 name:", files[1]?.name);

      const backImageUrl =
        files.length > 0 ? URL.createObjectURL(files[0]) : null;
      const frontImageUrl =
        files.length > 1 ? URL.createObjectURL(files[1]) : null;

      console.log("Front image URL:", frontImageUrl);
      console.log("Back image URL:", backImageUrl);

      try {
        const uploadOverlay = document.getElementById("upload-overlay");
        const uploadText = document.getElementById("uploadText");

        if (isBackgroundSlot) {
          if (files.length > 1) {
            showNotification(
              "Background slot can only accept 1 image. Please select only 1 image.",
              "error"
            );
            event.target.value = "";
            isUploading = false;
            isFileInputOpen = false;
            return;
          }

          if (uploadOverlay && uploadText) {
            uploadOverlay.style.display = "flex";
            uploadText.textContent = `Preparing upload... (5s to cancel)`;
          }

          const result = await uploadToBunny(
            files[0],
            slot,
            "front",
            true,
            false,
            0,
            true
          );

          if (result && result.success) {
            frontImg = document.createElement("img");
            frontImg.src = result.url || frontImageUrl;
            frontImg.classList.add("front-img");
            frontImg.style.zIndex = "10";
            frontImg.style.opacity = 1;

            box.innerHTML = "";
            ensureChildren();
            deleteBtn.style.display = "flex";
            box.classList.add("has-image");
            showingFront = true;

            showNotification(
              "Background cover uploaded successfully!",
              "success"
            );

            if (window.setAvailableSections) {
              await window.setAvailableSections();
            }
          }
        } else {
          if (files.length > 2) {
            showNotification(
              "You can only upload 2 images at the same time. Please select only 2 images.",
              "error"
            );
            event.target.value = "";
            isUploading = false;
            isFileInputOpen = false;
            return;
          }

          if (files.length === 2 && uploadOverlay && uploadText) {
            uploadOverlay.style.display = "flex";
            uploadText.textContent = `Preparing upload... (5s to cancel)`;
          }

          const suppressNotifications = files.length === 2;
          const isBatchUpload = files.length === 2;
          let uploadCancelled = false;

          if (files.length === 2) {
            const uploadPromises = [
              uploadToBunny(
                files[0],
                slot,
                "back",
                !suppressNotifications,
                isBatchUpload,
                0,
                false
              ),
              uploadToBunny(
                files[1],
                slot,
                "front",
                !suppressNotifications,
                isBatchUpload,
                0,
                false
              ),
            ];

            const results = await Promise.all(uploadPromises);
            uploadCancelled =
              results.some((result) => result && result.cancelled) ||
              isUploadCancelled ||
              isCancelling;

            if (!uploadCancelled && !isUploadCancelled && !isCancelling) {
              const frontUrl = results[1]?.url || frontImageUrl;
              const backUrl = results[0]?.url || backImageUrl;

              console.log("Creating frontImg with URL:", frontUrl);
              frontImg = document.createElement("img");
              frontImg.src = frontUrl;
              frontImg.classList.add("front-img");
              frontImg.style.zIndex = "10";
              frontImg.style.opacity = 1;
              frontImg.style.position = "absolute";
              frontImg.style.inset = "0";

              console.log("Creating backImg with URL:", backUrl);
              backImg = document.createElement("img");
              backImg.src = backUrl;
              backImg.classList.add("back-img");
              backImg.style.zIndex = "5";
              backImg.style.opacity = 0;
              backImg.style.position = "absolute";
              backImg.style.inset = "0";

              console.log("frontImg element:", frontImg);
              console.log("backImg element:", backImg);

              await new Promise((resolve) => {
                let loadedCount = 0;
                const checkLoaded = () => {
                  loadedCount++;
                  if (loadedCount === 2) resolve();
                };
                frontImg.onload = checkLoaded;
                backImg.onload = checkLoaded;
                setTimeout(resolve, 2000);
              });

              box.innerHTML = "";
              ensureChildren();
              deleteBtn.style.display = "flex";
              box.classList.add("has-image");
              showingFront = true;

              showNotification(
                `Uploaded successfully to Slot ${slot} front and back cover`,
                "success"
              );

              if (window.setAvailableSections) {
                await window.setAvailableSections();
              }
            }
          } else if (files.length === 1) {
            const result = await uploadToBunny(
              files[0],
              slot,
              "front",
              !suppressNotifications,
              false,
              0,
              true
            );

            if (result && result.success) {
              frontImg = document.createElement("img");
              frontImg.src = result.url || frontImageUrl;
              frontImg.classList.add("front-img");
              frontImg.style.zIndex = "10";
              frontImg.style.opacity = 1;

              box.innerHTML = "";
              ensureChildren();
              deleteBtn.style.display = "flex";
              box.classList.add("has-image");
              showingFront = true;

              if (window.setAvailableSections) {
                await window.setAvailableSections();
              }
            }
            uploadCancelled =
              (result && result.cancelled) || isUploadCancelled || isCancelling;
          }
        }

        if (uploadOverlay) {
          uploadOverlay.style.display = "none";
        }
      } catch (error) {
        console.error("Unexpected error during upload:", error);
        showNotification("An unexpected error occurred during upload", "error");
        event.target.value = "";
        isUploading = false;
        isFileInputOpen = false;
        if (uploadOverlay) {
          uploadOverlay.style.display = "none";
        }
      } finally {
        event.target.value = "";
        isUploading = false;
        isFileInputOpen = false;
        console.log(
          `✓ Upload box Slot ${slot} - isUploading reset to false, ready for next upload`
        );
      }
    });

    deleteBtn.addEventListener("click", async (event) => {
      event.stopPropagation();
      selectedConfirmAction = async () => {
        const sides = [];
        if (frontImg) sides.push("front");
        if (backImg && !isBackgroundSlot) sides.push("back");
        if (!sides.length) return;

        const sectionHeader = box
          .closest(".section")
          ?.querySelector(".section-header");
        const batchYear = sectionHeader ? sectionHeader.textContent.trim() : "";
        console.log(
          `Delete button clicked for slot ${slot}, batch_year: ${batchYear}, sides to delete:`,
          sides
        );

        const tempFrontImg = frontImg;
        const tempBackImg = backImg;
        frontImg = null;
        backImg = null;
        showingFront = true;
        box.innerHTML = "";
        const newPlus = document.createElement("span");
        newPlus.className = "plus-icon";
        newPlus.textContent = "+";
        box.appendChild(newPlus);
        ensureChildren();
        deleteBtn.style.display = "none";
        frontInput.value = "";
        backInput.value = "";
        box.classList.remove("has-image");

        showNotification("Image deleted successfully", "success");

        if (sides.length > 0) {
          console.log(
            `Starting parallel deletion of ${sides.length} side(s) for slot ${slot}`
          );

          const deletePromises = sides.map((side) => deleteCover(slot, side));
          const results = await Promise.all(deletePromises);

          const allSuccessful = results.every((result) => result === true);

          if (allSuccessful) {
            console.log(`All deletions successful for slot ${slot}`);
          } else {
            console.error(`Some deletions failed for slot ${slot}`);
            showNotification(
              "Server deletion failed, but UI was updated",
              "error"
            );
          }
        }
      };
      openDeleteModal();
    });

    async function uploadToBunny(
      file,
      slot,
      side,
      showNotif,
      isBatch,
      retryCount = 0,
      isSingleUpload = false
    ) {
      const MAX_RETRIES = 3;
      const UPLOAD_TIMEOUT = slot === 8 ? 120000 : 60000;

      try {
        const sectionHeader = box
          .closest(".section")
          .querySelector(".section-header");
        const batchYear = sectionHeader ? sectionHeader.textContent.trim() : "";

        const formData = new FormData();
        formData.append("file", file);
        formData.append("slot", String(slot));
        formData.append("side", side);
        formData.append("batch_year", batchYear);

        const templateNumber =
          localStorage.getItem("selectedBatchTemplateNumber") || 1;
        formData.append("template", templateNumber);

        const xhr = new XMLHttpRequest();

        const fileInfo = {
          slot: slot,
          batch_year: batchYear,
          side: side,
          timestamp: Date.now(),
        };
        pendingUploads.push(fileInfo);
        console.log(
          `Added to pending uploads: Slot ${slot} ${side} (${pendingUploads.length} pending)`
        );

        currentUploadControllers.push(xhr);
        globalIsUploading = true;
        console.log(`Active XHR requests: ${currentUploadControllers.length}`);

        const uploadPromise = new Promise(async (resolve, reject) => {
          const CANCEL_WINDOW_MS = 5000;
          const uploadText = document.getElementById("uploadText");

          console.log(
            `⏳ Starting 5-second cancellation window for Slot ${slot} ${side}`
          );

          for (let secondsLeft = 5; secondsLeft > 0; secondsLeft--) {
            if (isCancelling) {
              console.log(
                `✅ Upload cancelled during countdown for Slot ${slot} ${side} (${secondsLeft}s remaining - PREVENTED UPLOAD)`
              );

              const xhrIndex = currentUploadControllers.indexOf(xhr);
              if (xhrIndex > -1) {
                currentUploadControllers.splice(xhrIndex, 1);
              }

              const pendingIndex = pendingUploads.findIndex(
                (f) =>
                  f.slot === slot &&
                  f.side === side &&
                  f.batch_year === batchYear
              );
              if (pendingIndex > -1) {
                pendingUploads.splice(pendingIndex, 1);
                console.log(
                  `Removed from pending uploads (never sent to server)`
                );
              }

              reject(new Error("Upload cancelled"));
              return;
            }

            if (uploadText) {
              if (isBatch) {
                uploadText.textContent = `Preparing upload... (${secondsLeft}s to cancel)`;
              } else {
                uploadText.textContent = `Preparing Slot ${slot} ${side}... (${secondsLeft}s to cancel)`;
              }
            }

            await new Promise((resolve) => setTimeout(resolve, 1000));
          }

          if (isCancelling) {
            console.log(
              `✅ Upload cancelled just before upload start for Slot ${slot} ${side} - PREVENTED UPLOAD`
            );

            // Remove XHR from controllers since we never sent it
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }

            // Remove from pending uploads since upload never started
            const pendingIndex = pendingUploads.findIndex(
              (f) =>
                f.slot === slot && f.side === side && f.batch_year === batchYear
            );
            if (pendingIndex > -1) {
              pendingUploads.splice(pendingIndex, 1);
              console.log(
                `Removed from pending uploads (never sent to server)`
              );
            }

            reject(new Error("Upload cancelled"));
            return;
          }

          console.log(
            `✓ Cancellation window expired, starting actual upload for Slot ${slot} ${side}`
          );
          if (uploadText) {
            uploadText.textContent = `Uploading Slot ${slot} ${side}...`;
          }

          let uploadStartTime = Date.now();
          let uploadCompleted = false;
          xhr.upload.addEventListener("progress", (e) => {
            if (e.lengthComputable) {
              const percentComplete = (e.loaded / e.total) * 100;
              console.log(
                `Upload progress for slot ${slot} ${side}: ${percentComplete.toFixed(
                  2
                )}%`
              );

              // Update upload overlay text
              if (uploadText) {
                uploadText.textContent = `Uploading Slot ${slot} ${side}... ${percentComplete.toFixed(
                  0
                )}%`;
              }
            }
          });

          xhr.addEventListener("load", () => {
            uploadCompleted = true;

            // Remove XHR from active controllers
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
              console.log(
                `Removed XHR from active controllers. Remaining: ${currentUploadControllers.length}`
              );
            }

            // Check if cancellation is in progress - if so, treat this as cancelled
            if (isCancelling) {
              console.log(
                `⚠️ Upload completed but cancellation is active - treating as cancelled for Slot ${slot} ${side}`
              );
              // Keep in pending array - cancelUpload() will handle cleanup
              reject(new Error("Upload cancelled"));
              return;
            }

            if (xhr.status === 200) {
              try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                  // Move from pending to completed uploads
                  const pendingIndex = pendingUploads.findIndex(
                    (f) =>
                      f.slot === slot &&
                      f.side === side &&
                      f.batch_year === batchYear
                  );
                  if (pendingIndex > -1) {
                    const completedFile = pendingUploads.splice(
                      pendingIndex,
                      1
                    )[0];
                    lastUploadedFiles.push(completedFile);
                    console.log(
                      `✓ Upload completed for Slot ${slot} ${side} (Batch: ${batchYear})`
                    );
                    console.log(
                      `  Moved from pending (${pendingUploads.length} remaining) to completed (${lastUploadedFiles.length} total)`
                    );
                  } else {
                    // Fallback if not found in pending
                    lastUploadedFiles.push(fileInfo);
                    console.log(
                      `✓ Upload completed for Slot ${slot} ${side} - Added to completed (${lastUploadedFiles.length} total)`
                    );
                  }

                  if (showNotif && !isBatch) {
                    showNotification(
                      `Uploaded successfully to Slot ${slot} ${side}`,
                      "success"
                    );
                  }
                  resolve(data);
                } else {
                  // Remove from pending on failure
                  const pendingIndex = pendingUploads.findIndex(
                    (f) =>
                      f.slot === slot &&
                      f.side === side &&
                      f.batch_year === batchYear
                  );
                  if (pendingIndex > -1) {
                    pendingUploads.splice(pendingIndex, 1);
                  }
                  showNotification(data.message || "Upload failed", "error");
                  reject(new Error(data.message || "Upload failed"));
                }
              } catch (e) {
                // Remove from pending on parse error
                const pendingIndex = pendingUploads.findIndex(
                  (f) =>
                    f.slot === slot &&
                    f.side === side &&
                    f.batch_year === batchYear
                );
                if (pendingIndex > -1) {
                  pendingUploads.splice(pendingIndex, 1);
                }
                showNotification("Failed to parse response", "error");
                reject(e);
              }
            } else {
              // Remove from pending on HTTP error
              const pendingIndex = pendingUploads.findIndex(
                (f) =>
                  f.slot === slot &&
                  f.side === side &&
                  f.batch_year === batchYear
              );
              if (pendingIndex > -1) {
                pendingUploads.splice(pendingIndex, 1);
              }
              showNotification(`Upload failed: HTTP ${xhr.status}`, "error");
              reject(new Error(`HTTP ${xhr.status}`));
            }
          });

          xhr.addEventListener("error", () => {
            // Remove XHR from active controllers
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }
            // Remove from pending
            const pendingIndex = pendingUploads.findIndex(
              (f) =>
                f.slot === slot && f.side === side && f.batch_year === batchYear
            );
            if (pendingIndex > -1) {
              pendingUploads.splice(pendingIndex, 1);
            }
            showNotification("Upload failed", "error");
            reject(new Error("Upload failed"));
          });

          xhr.addEventListener("abort", () => {
            // Remove XHR from active controllers
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }
            // Keep in pending - will be cleaned up by cancelUpload()
            console.log("Upload aborted for slot", slot, side);
            reject(new Error("Upload cancelled"));
          });

          xhr.addEventListener("timeout", () => {
            // Remove XHR from active controllers
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }
            // Remove from pending
            const pendingIndex = pendingUploads.findIndex(
              (f) =>
                f.slot === slot && f.side === side && f.batch_year === batchYear
            );
            if (pendingIndex > -1) {
              pendingUploads.splice(pendingIndex, 1);
            }
            showNotification("Upload timeout - please try again", "error");
            reject(new Error("Upload timeout"));
          });

          xhr.open("POST", UPLOAD_ENDPOINT);
          xhr.timeout = UPLOAD_TIMEOUT;
          xhr.send(formData);
        });

        const result = await uploadPromise;

        // uploadPromise already cleaned up XHR from array in event handlers
        // Just update global upload state if no more active uploads
        if (currentUploadControllers.length === 0) {
          globalIsUploading = false;
        }

        return result;
      } catch (err) {
        // XHR already cleaned up in event handlers
        // Just update global upload state if no more active uploads
        if (currentUploadControllers.length === 0) {
          globalIsUploading = false;
        }

        // Check if this is a cancellation error
        if (err.message === "Upload cancelled") {
          console.log("Upload cancelled by user for slot", slot, side);
          return { success: false, cancelled: true };
        }

        console.error("Upload error:", err);

        if (
          (err.message.includes("timeout") || err.message.includes("failed")) &&
          retryCount < MAX_RETRIES
        ) {
          const newRetryCount = retryCount + 1;
          console.log(
            `Upload failed, retrying... (${newRetryCount}/${MAX_RETRIES})`
          );

          if (showNotif && !isBatch) {
            showNotification(
              `Upload failed, retrying... (${newRetryCount}/${MAX_RETRIES})`,
              "warning"
            );
          }

          await new Promise((resolve) =>
            setTimeout(resolve, 1000 * newRetryCount)
          );

          return await uploadToBunny(
            file,
            slot,
            side,
            showNotif,
            isBatch,
            newRetryCount,
            isSingleUpload
          );
        }

        showNotification(
          err.message || "Upload failed after multiple attempts",
          "error"
        );
        return { success: false, cancelled: true };
      }
    }

    async function deleteCover(slot, side) {
      try {
        const sectionHeader = box
          .closest(".section")
          ?.querySelector(".section-header");
        const batchYear = sectionHeader ? sectionHeader.textContent.trim() : "";

        console.log(
          `deleteCover: Attempting to delete slot ${slot}, side ${side}, batch_year: ${batchYear}`
        );

        const form = new FormData();
        form.append("slot", String(slot));
        form.append("side", side);
        form.append("batch_year", batchYear);

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 20000);

        const res = await fetch(DELETE_ENDPOINT, {
          method: "POST",
          body: form,
          signal: controller.signal,
        });

        clearTimeout(timeoutId);

        console.log(`deleteCover: Response status: ${res.status}`);

        if (!res.ok) {
          const errorText = await res.text();
          console.error(`deleteCover: HTTP ${res.status} error:`, errorText);
          throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }

        const data = await res.json();
        console.log(`deleteCover: Response data:`, data);

        if (!data?.success) {
          console.error(
            `deleteCover: Delete failed - ${data?.message || "Unknown error"}`
          );
          showNotification(data?.message || "Delete failed", "error");
          return false;
        } else {
          console.log(`deleteCover: Delete successful`);
          if (window.setAvailableSections) {
            await window.setAvailableSections();
          }
          return true;
        }
      } catch (err) {
        console.error("deleteCover: Exception caught:", err);
        console.error("deleteCover: Error details:", {
          message: err.message,
          stack: err.stack,
          name: err.name,
        });

        if (err.name === "AbortError") {
          showNotification(
            "Delete operation timed out after 20 seconds",
            "error"
          );
        } else {
          showNotification(err.message || "Delete failed", "error");
        }
        return false;
      }
    }

    (async function loadExisting() {
      try {
        await new Promise((resolve) => setTimeout(resolve, 50));

        const sectionHeader = box
          .closest(".section")
          ?.querySelector(".section-header");
        const batchYear = sectionHeader
          ? sectionHeader.textContent.trim()
          : null;

        // Get template number from localStorage
        const templateNumber =
          localStorage.getItem("selectedBatchTemplateNumber") || 1;

        // Build query parameters
        const params = new URLSearchParams({
          template: templateNumber,
        });
        if (batchYear) {
          params.append("batch_year", batchYear);
        }

        const res = await fetch(`${FETCH_ENDPOINT}?${params}`, {
          signal: AbortSignal.timeout(30000),
        });

        if (!res.ok) {
          throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }

        const data = await res.json();
        if (!data?.success) {
          console.warn("Failed to load covers:", data?.message);
          return;
        }

        const found = (data.items || []).find((i) => i.slot === slot);
        if (!found) return;

        if (found.back_url && !isBackgroundSlot) {
          backImg = document.createElement("img");
          backImg.src = found.back_url;
          backImg.classList.add("back-img");
          backImg.style.zIndex = "5";
          backImg.style.opacity = 0;
        }

        if (found.front_url) {
          frontImg = document.createElement("img");
          frontImg.src = found.front_url;
          frontImg.classList.add("front-img");
          frontImg.style.zIndex = "10";
          frontImg.style.opacity = 1;
        }

        if (frontImg || backImg) {
          box.innerHTML = "";
          const plusIcon = box.querySelector(".plus-icon");
          if (plusIcon) plusIcon.remove();
          ensureChildren();
          deleteBtn.style.display = "flex";
          box.classList.add("has-image");
          showingFront = true;
        }
      } catch (e) {
        if (e.name === "TimeoutError" || e.name === "AbortError") {
          console.warn("Fetch covers request timed out");
          showNotification(
            "Loading covers timed out. Please try again.",
            "error"
          );
        } else {
          console.error("Failed to load existing covers:", e);
          showNotification("Failed to load existing covers", "error");
        }
      }
    })();
  });
}

function downloadPDF(batchName) {
  openDownloadPdfModal(batchName);
}

function confirmDownloadPDF() {
  showNotification(
    "PDF download functionality will be implemented soon!",
    "info"
  );
  console.log("Downloading PDF...");
  closeDownloadPdfModal();
}

function deleteBatchTemplate(section, batchName) {
  openDeleteBatchModal(batchName);

  window.pendingDeleteSection = section;
  window.pendingDeleteBatchName = batchName;
}

function confirmDeleteBatch() {
  if (window.pendingDeleteSection) {
    window.pendingDeleteSection.remove();

    saveGeneratedSectionsToLocalStorage();

    if (window.refreshSections) {
      window.refreshSections();
    }

    showNotification(
      `${window.pendingDeleteBatchName} deleted successfully!`,
      "success"
    );

    window.pendingDeleteSection = null;
    window.pendingDeleteBatchName = null;
  }
  closeDeleteBatchModal();
}

function confirmSelectTemplate() {
  if (window.pendingSelectSection) {
    document.querySelectorAll(".section").forEach((s) => {
      s.classList.remove("selected");
    });

    window.pendingSelectSection.classList.add("selected");

    // Update the sections variable to include the newly selected section
    const sections = document.querySelectorAll(".section");

    // Extract and store the batch year from the section header
    const sectionHeader =
      window.pendingSelectSection.querySelector(".section-header");
    const batchYear = sectionHeader ? sectionHeader.textContent.trim() : "";

    console.log("=== BATCH TEMPLATE SELECTION DEBUG ===");
    console.log("Section header element:", sectionHeader);
    console.log("Section header text:", batchYear);
    console.log("Full section HTML:", window.pendingSelectSection.outerHTML);

    // Store the batch year in localStorage
    if (batchYear) {
      localStorage.setItem("selectedBatchYear", batchYear);
      console.log("Stored batch year:", batchYear);
      console.log(
        "Verification - localStorage now contains:",
        localStorage.getItem("selectedBatchYear")
      );
    } else {
      console.warn("No batch year found in section header!");
    }

    // Call the centralized updateUploadBoxStates function
    if (typeof window.updateUploadBoxStates === "function") {
      window.updateUploadBoxStates();
    }

    // Trigger a custom event to notify other parts of the app (like department iframes)
    window.dispatchEvent(
      new CustomEvent("batchTemplateSelected", {
        detail: {
          batchName: window.pendingSelectBatchName,
          batchYear: batchYear,
        },
      })
    );

    showNotification(
      `${window.pendingSelectBatchName} selected successfully!`,
      "success"
    );

    window.pendingSelectSection = null;
    window.pendingSelectBatchName = null;
  }
  closeSelectTemplateModal();
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  deleteModal = document.getElementById("delete-modal-overlay");
  confirmDeleteBtn = document.getElementById("confirm-delete-btn");
  cancelDeleteBtn = document.getElementById("cancel-delete-btn");

  generateModal = document.getElementById("generate-modal-overlay");
  confirmGenerateBtn = document.getElementById("confirm-generate-btn");
  cancelGenerateBtn = document.getElementById("cancel-generate-btn");

  downloadPdfModal = document.getElementById("download-pdf-modal-overlay");
  confirmDownloadPdfBtn = document.getElementById("confirm-download-pdf-btn");
  cancelDownloadPdfBtn = document.getElementById("cancel-download-pdf-btn");

  deleteBatchModal = document.getElementById("delete-batch-modal-overlay");
  confirmDeleteBatchBtn = document.getElementById("confirm-delete-batch-btn");
  cancelDeleteBatchBtn = document.getElementById("cancel-delete-batch-btn");

  selectTemplateModal = document.getElementById(
    "select-template-modal-overlay"
  );
  confirmSelectTemplateBtn = document.getElementById(
    "confirm-select-template-btn"
  );
  cancelSelectTemplateBtn = document.getElementById(
    "cancel-select-template-btn"
  );

  console.log("downloadPdfModal:", downloadPdfModal);
  console.log("deleteBatchModal:", deleteBatchModal);
  console.log("selectTemplateModal:", selectTemplateModal);
  console.log("confirmDownloadPdfBtn:", confirmDownloadPdfBtn);
  console.log("confirmDeleteBatchBtn:", confirmDeleteBatchBtn);
  console.log("confirmSelectTemplateBtn:", confirmSelectTemplateBtn);

  window.currentXhrs = [];
  window.isUploadCancelled = false;

  let sections = document.querySelectorAll(".form-group .section");
  let sectionHeaders = document.querySelectorAll(
    ".form-group .section .section-header"
  );

  function refreshSections() {
    sections = document.querySelectorAll(".form-group .section");
    sectionHeaders = document.querySelectorAll(
      ".form-group .section .section-header"
    );
  }

  window.refreshSections = refreshSections;

  window.deferSectionInitialization = true;

  restoreGeneratedSectionsFromLocalStorage();

  refreshSections();

  function selectSection(section) {
    console.log("=== selectSection called ===");
    console.log("Section to select:", section);

    sections.forEach((s) => s.classList.remove("selected"));

    if (section && section.parentNode) {
      section.classList.add("selected");

      const templateName = section
        .querySelector(".section-header")
        .textContent.trim();
      console.log("✓ Selected section:", templateName);
      localStorage.setItem("selectedBatchTemplate", templateName);

      const templateMatch = templateName.match(/Batch Template (\d+)/);
      if (templateMatch && templateMatch[1]) {
        const templateNumber = parseInt(templateMatch[1]);
        localStorage.setItem("selectedBatchTemplateNumber", templateNumber);
        console.log("Stored template number:", templateNumber);
      }
    } else {
      console.error("❌ selectSection: section is undefined or not in DOM");
    }

    updateUploadBoxStates();
  }

  function updateUploadBoxStates() {
    const selectedTemplate = document.querySelector(".section.selected");
    const allSections = document.querySelectorAll(".section");

    console.log("=== updateUploadBoxStates called ===");
    console.log("Selected template:", selectedTemplate);
    console.log("Total sections:", allSections.length);

    if (!selectedTemplate) {
      console.warn("⚠️ No section is selected! Upload boxes will be disabled.");
    }

    allSections.forEach((section, sectionIndex) => {
      const uploadBoxes = section.querySelectorAll(".upload-box");
      const isSelected = section === selectedTemplate;
      const sectionName = section
        .querySelector(".section-header")
        ?.textContent.trim();

      console.log(
        `Section ${sectionIndex} (${sectionName}): ${
          isSelected ? "SELECTED ✓" : "not selected"
        }`
      );

      uploadBoxes.forEach((box) => {
        const isActionBox = box.classList.contains("action-box");

        if (isSelected) {
          // Enable all upload boxes for the selected batch
          box.classList.remove("disabled");
          box.style.pointerEvents = "auto";
          box.style.opacity = "1";
        } else if (!isActionBox) {
          // Disable upload boxes for non-selected batches
          box.classList.add("disabled");
          box.style.pointerEvents = "none";
          box.style.opacity = "0.5";
        }
      });

      const selectBatchBtn = section.querySelector(".select-batch-btn");
      if (selectBatchBtn) {
        if (isSelected) {
          selectBatchBtn.disabled = true;
          selectBatchBtn.classList.add("selected");
        } else {
          selectBatchBtn.disabled = false;
          selectBatchBtn.classList.remove("selected");
        }
      }
    });

    console.log("=== updateUploadBoxStates complete ===");
  }

  // Make updateUploadBoxStates globally accessible
  window.updateUploadBoxStates = updateUploadBoxStates;

  function openSelectTemplateModal(targetSection, templateLabel) {
    if (!deleteModal) {
      console.error("openSelectTemplateModal: deleteModal is not available");
      return;
    }

    if (!targetSection) {
      console.error("openSelectTemplateModal: targetSection is undefined");
      return;
    }

    const titleEl = deleteModal.querySelector("h3");
    const iconEl = deleteModal.querySelector(".modal-header .modal-icon");
    const messageEl = deleteModal.querySelector(".modal-content p");
    const confirmBtnEl = confirmDeleteBtn;
    const cancelBtnEl = cancelDeleteBtn;

    const defaultTitle = titleEl ? titleEl.textContent : "";
    const defaultMsg = messageEl ? messageEl.textContent : "";
    const defaultIcon = iconEl ? iconEl.className : "";
    const defaultConfirmText = confirmBtnEl ? confirmBtnEl.textContent : "";
    const defaultCancelText = cancelBtnEl ? cancelBtnEl.textContent : "";

    if (titleEl) titleEl.textContent = "Select Batch Template";
    if (messageEl)
      messageEl.textContent = `Do you want to select ${templateLabel}?`;
    if (iconEl) iconEl.className = "fas fa-question-circle modal-icon";
    if (confirmBtnEl) confirmBtnEl.textContent = "Yes, Select";
    if (cancelBtnEl) cancelBtnEl.textContent = "Cancel";

    selectedConfirmAction = () => {
      if (!templateLabel) {
        console.error("selectedConfirmAction: templateLabel is undefined");
        if (typeof showNotification === "function") {
          showNotification(
            "Error: Could not select batch template - invalid label",
            "error"
          );
        }
        closeDeleteModal();
        return;
      }

      const allSections = document.querySelectorAll(".form-group .section");
      let actualSection = null;

      allSections.forEach((section) => {
        const header = section.querySelector(".section-header");
        if (header && header.textContent.trim() === templateLabel) {
          actualSection = section;
        }
      });

      if (actualSection && actualSection.parentNode) {
        selectSection(actualSection);
        if (typeof showNotification === "function") {
          showNotification(`${templateLabel} selected`, "success");
        }
      } else {
        console.error(
          "selectedConfirmAction: Could not find section with label:",
          templateLabel
        );
        if (typeof showNotification === "function") {
          showNotification(
            "Error: Could not select batch template - section not found",
            "error"
          );
        }
      }

      if (titleEl) titleEl.textContent = defaultTitle;
      if (messageEl) messageEl.textContent = defaultMsg;
      if (iconEl) iconEl.className = defaultIcon;
      if (confirmBtnEl) confirmBtnEl.textContent = defaultConfirmText;
      if (cancelBtnEl) cancelBtnEl.textContent = defaultCancelText;
    };

    openDeleteModal();
  }

  window.openSelectTemplateModal = openSelectTemplateModal;

  if (window.deferSectionInitialization) {
    const allSections = document.querySelectorAll(".form-group .section");
    const currentXhrs = window.currentXhrs || [];
    const isUploadCancelled = window.isUploadCancelled || false;

    allSections.forEach((section) => {
      initializeSection(section);
      initializeSectionUploadBoxes(section, currentXhrs, isUploadCancelled);
    });

    window.deferSectionInitialization = false;
  }

  async function setAvailableSections() {
    const allSections = document.querySelectorAll(".form-group .section");
    const currentDate = new Date();

    try {
      const BASE_PATH = getBasePath();
      const CONNECTION_PATH = `${BASE_PATH}/Connection`;
      const FETCH_ENDPOINT = `${CONNECTION_PATH}/Cover/FetchCovers.php`;

      const res = await fetch(FETCH_ENDPOINT, {
        signal: AbortSignal.timeout(30000),
      });

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }

      const data = await res.json();

      if (data?.success && data?.items) {
        const batchYearDataMap = {};
        data.items.forEach((item) => {
          if (item.batch_year && !batchYearDataMap[item.batch_year]) {
            batchYearDataMap[item.batch_year] = {
              completion_date: item.completion_date,
              slots: [],
            };
          }
          if (item.batch_year) {
            batchYearDataMap[item.batch_year].slots.push(item.slot);
          }
        });

        allSections.forEach((section) => {
          const header = section.querySelector(".section-header");
          if (!header) return;

          const headerText = header.textContent.trim();
          const sectionData = batchYearDataMap[headerText];

          if (sectionData && sectionData.completion_date) {
            const completionDate = new Date(sectionData.completion_date);
            const yearsSinceCompletion =
              (currentDate - completionDate) / (1000 * 60 * 60 * 24 * 365);

            if (yearsSinceCompletion <= 3) {
              section.classList.add("available");
            } else {
              section.classList.remove("available");
            }
          } else {
            section.classList.remove("available");
          }
        });
      }
    } catch (error) {
      console.error("Error fetching completion dates:", error);
      allSections.forEach((section) => {
        section.classList.remove("available");
      });
    }
  }

  window.setAvailableSections = setAvailableSections;

  if (sections.length > 0) {
    const savedTemplate = localStorage.getItem("selectedBatchTemplate");
    const savedBatchYear = localStorage.getItem("selectedBatchYear");
    let selectedSection = null;

    // Try to restore saved batch year first (from confirmSelectTemplate)
    if (savedBatchYear) {
      sections.forEach((section) => {
        const headerText = section
          .querySelector(".section-header")
          .textContent.trim();
        if (headerText === savedBatchYear) {
          selectedSection = section;
          console.log(
            "Restored selected section from savedBatchYear:",
            savedBatchYear
          );
        }
      });
    }

    // Fallback to saved template name
    if (!selectedSection && savedTemplate) {
      sections.forEach((section) => {
        const headerText = section
          .querySelector(".section-header")
          .textContent.trim();
        if (headerText === savedTemplate) {
          selectedSection = section;
          console.log(
            "Restored selected section from savedTemplate:",
            savedTemplate
          );
        }
      });
    }

    // Default to first section if nothing saved
    if (!selectedSection) {
      selectedSection = sections[0];
      console.log("No saved selection, defaulting to first section");
    }

    selectSection(selectedSection);
    console.log(
      "Section selected on page load, upload boxes should be enabled"
    );
  } else {
    console.warn("No sections found on page load - this shouldn't happen");
    // Don't call updateUploadBoxStates() when there are no sections
    // It will disable all boxes when sections are added later
  }

  setAvailableSections();

  initializeDeleteModal();

  const generateBatchBtn = document.getElementById("generateBatchBtn");

  if (generateBatchBtn) {
    generateBatchBtn.addEventListener("click", openGenerateModal);
  }

  if (cancelGenerateBtn) {
    cancelGenerateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeGenerateModal();
    });
  }

  if (generateModal) {
    generateModal.addEventListener("click", (e) => {
      if (e.target === generateModal) closeGenerateModal();
    });
  }

  if (confirmGenerateBtn) {
    confirmGenerateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeGenerateModal();
      generateNewBatchSection();
    });
  }

  if (cancelDownloadPdfBtn) {
    cancelDownloadPdfBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeDownloadPdfModal();
    });
  }

  if (downloadPdfModal) {
    downloadPdfModal.addEventListener("click", (e) => {
      if (e.target === downloadPdfModal) closeDownloadPdfModal();
    });
  }

  if (confirmDownloadPdfBtn) {
    confirmDownloadPdfBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      confirmDownloadPDF();
    });
  }

  if (cancelDeleteBatchBtn) {
    cancelDeleteBatchBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeDeleteBatchModal();
    });
  }

  if (deleteBatchModal) {
    deleteBatchModal.addEventListener("click", (e) => {
      if (e.target === deleteBatchModal) closeDeleteBatchModal();
    });
  }

  if (confirmDeleteBatchBtn) {
    confirmDeleteBatchBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      confirmDeleteBatch();
    });
  }

  if (cancelSelectTemplateBtn) {
    cancelSelectTemplateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeSelectTemplateModal();
    });
  }

  if (selectTemplateModal) {
    selectTemplateModal.addEventListener("click", (e) => {
      if (e.target === selectTemplateModal) closeSelectTemplateModal();
    });
  }

  if (confirmSelectTemplateBtn) {
    confirmSelectTemplateBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      confirmSelectTemplate();
    });
  }
});
