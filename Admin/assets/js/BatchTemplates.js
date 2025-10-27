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
  notificationTimeout = setTimeout(() => {
    closeNotification(`${type}-notification`);
  }, duration);
}

function closeNotification(id) {
  const notification = document.getElementById(id);
  if (notification) {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
      notificationTimeout = null;
      currentOperation = null;
    }, 500);
  }
}

function showPersistentNotification(
  message,
  type = "info",
  allowCancel = false
) {
  const container = document.getElementById("notification-container");
  if (!container) return null;

  const existingNotifications = container.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
    notificationTimeout = null;
  }

  let messageClass =
    type === "error"
      ? "error-message"
      : type === "info"
      ? "info-message"
      : "success-message";

  const notif = document.createElement("div");
  notif.className = `notification ${messageClass} persistent`;
  notif.innerHTML = `
    <span class="notification-message">${message}</span>
    ${
      allowCancel
        ? '<button class="notification-close-btn" title="Cancel PDF Generation">✕</button>'
        : ""
    }
  `;
  container.appendChild(notif);

  if (allowCancel) {
    const closeBtn = notif.querySelector(".notification-close-btn");
    if (closeBtn) {
      closeBtn.addEventListener("click", async () => {
        const confirmed = await customConfirm(
          "Are you sure you want to cancel the PDF generation?",
          "Cancel PDF Generation",
          "fas fa-exclamation-triangle"
        );
        if (confirmed) {
          window.pdfGenerationCancelled = true;
          closePersistentNotification(notif);
          showNotification("PDF generation cancelled", "error");
        }
      });
    }
  }

  setTimeout(() => {
    notif.classList.add("show");
  }, 10);

  return notif;
}

function closePersistentNotification(notification) {
  if (notification) {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 300);
  }
}

function updatePersistentNotification(notification, message) {
  if (notification) {
    const messageElement = notification.querySelector(".notification-message");
    if (messageElement) {
      messageElement.innerHTML = message;
    }
  }
}

function customConfirm(
  message,
  title = "Confirmation",
  icon = "fas fa-exclamation-circle"
) {
  return new Promise((resolve) => {
    const overlay = document.createElement("div");
    overlay.className = "confirm-modal-overlay";

    overlay.innerHTML = `
      <div class="confirm-modal-container">
        <div class="confirm-modal-header">
          <i class="${icon} modal-icon"></i>
          <h3>${title}</h3>
        </div>
        <div class="confirm-modal-body">
          <p>${message}</p>
        </div>
        <div class="confirm-modal-buttons">
          <button class="confirm-modal-btn confirm-ok">
            <i class="fas fa-check"></i>
            OK
          </button>
          <button class="confirm-modal-btn confirm-cancel">
            <i class="fas fa-times"></i>
            Cancel
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(overlay);

    setTimeout(() => {
      overlay.classList.add("show");
    }, 10);

    const okBtn = overlay.querySelector(".confirm-ok");
    const cancelBtn = overlay.querySelector(".confirm-cancel");

    const closeModal = (result) => {
      overlay.classList.remove("show");
      setTimeout(() => {
        overlay.remove();
        resolve(result);
      }, 300);
    };

    okBtn.addEventListener("click", () => closeModal(true));
    cancelBtn.addEventListener("click", () => closeModal(false));

    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) {
        closeModal(false);
      }
    });

    const escapeHandler = (e) => {
      if (e.key === "Escape") {
        document.removeEventListener("keydown", escapeHandler);
        closeModal(false);
      }
    };
    document.addEventListener("keydown", escapeHandler);
  });
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

let currentUploadControllers = [];
let globalIsUploading = false;
let lastUploadedFiles = [];
let pendingUploads = [];
let isCancelling = false;

window.resetUploadStates = function () {
  console.log("🔧 EMERGENCY RESET: Resetting all upload states...");
  currentUploadControllers = [];
  globalIsUploading = false;
  lastUploadedFiles = [];
  pendingUploads = [];
  isCancelling = false;

  document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.value = "";
  });

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
  if (isCancelling) {
    console.log(
      "Cancel already in progress, ignoring duplicate cancel request"
    );
    return;
  }

  isCancelling = true;
  console.log("Cancel upload triggered in BatchTemplates");

  const uploadOverlay = document.getElementById("upload-overlay");
  if (uploadOverlay) {
    uploadOverlay.style.display = "none";
  }

  document.querySelectorAll('input[type="file"]').forEach((input) => {
    if (input.files && input.files.length > 0) {
      input.value = "";
    }
  });

  showNotification("Upload cancelled", "error");

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

  const allFilesToCleanup = [...lastUploadedFiles, ...pendingUploads];
  lastUploadedFiles = [];
  pendingUploads = [];

  globalIsUploading = false;

  if (allFilesToCleanup.length > 0) {
    console.log(
      `Background cleanup: ${allFilesToCleanup.length} file(s) from Bunny CDN and MongoDB...`
    );

    Promise.all(
      allFilesToCleanup.map((fileInfo) => deleteRecentlyUploadedFile(fileInfo))
    )
      .then((results) => {
        const successCount = results.filter((r) => r === true).length;
        const failCount = results.filter((r) => r === false).length;

        console.log(
          `Background cleanup complete: ${successCount} deleted successfully, ${failCount} failed`
        );
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
        if (data.message && data.message.includes("not found")) {
          console.log(
            `ℹ️ Slot ${fileInfo.slot} ${fileInfo.side} already deleted or never saved`
          );
          return true;
        }

        console.warn(`⚠️ Delete attempt ${attempt} failed: ${data.message}`);

        if (attempt < MAX_DELETE_RETRIES) {
          await new Promise((resolve) => setTimeout(resolve, 1000 * attempt));
        }
      }
    } catch (error) {
      console.error(`❌ Error on delete attempt ${attempt}:`, error);

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

  window.currentBatchForDownload = batchName;

  if (downloadPdfModal) {
    console.log("Showing download PDF modal for:", batchName);
    downloadPdfModal.style.display = "flex";
    setTimeout(() => {
      downloadPdfModal.classList.add("show");
    }, 10);
  } else {
    console.error("downloadPdfModal element not found!");
  }
}

function closeDownloadPdfModal() {
  if (downloadPdfModal) {
    downloadPdfModal.classList.remove("show");
    setTimeout(() => {
      downloadPdfModal.style.display = "none";
    }, 200);
  }
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

          if (downloadBtn.disabled) {
            const isAvailable = section.classList.contains("available");
            const isSelected = section.classList.contains("selected");

            if (!isAvailable) {
              showNotification(
                "This batch is not available for download. Only batches with green headers can be downloaded.",
                "error"
              );
            } else if (!isSelected) {
              showNotification(
                "Please select this batch first before downloading PDF",
                "error"
              );
            }
            return;
          }

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
                uploadText.textContent = `Preparing upload covers (${secondsLeft}s to cancel)`;
              } else {
                if (slot === 8) {
                  uploadText.textContent = `Preparing Background Cover (${secondsLeft}s to cancel)`;
                } else {
                  uploadText.textContent = `Preparing Covers Slot ${slot} (${secondsLeft}s to cancel)`;
                }
              }
            }

            await new Promise((resolve) => setTimeout(resolve, 1000));
          }

          if (isCancelling) {
            console.log(
              `✅ Upload cancelled just before upload start for Slot ${slot} ${side} - PREVENTED UPLOAD`
            );

            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }

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
            if (slot === 8) {
              uploadText.textContent = `Uploading Background Cover`;
            } else {
              uploadText.textContent = `Uploading Covers Slot ${slot}`;
            }
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

              if (uploadText) {
                if (slot === 8) {
                  uploadText.textContent = `Uploading Background Cover`;
                } else {
                  uploadText.textContent = `Uploading Covers Slot ${slot}`;
                }
              }
            }
          });

          xhr.addEventListener("load", () => {
            uploadCompleted = true;

            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
              console.log(
                `Removed XHR from active controllers. Remaining: ${currentUploadControllers.length}`
              );
            }

            if (isCancelling) {
              console.log(
                `⚠️ Upload completed but cancellation is active - treating as cancelled for Slot ${slot} ${side}`
              );
              reject(new Error("Upload cancelled"));
              return;
            }

            if (xhr.status === 200) {
              try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
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
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }
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
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }
            console.log("Upload aborted for slot", slot, side);
            reject(new Error("Upload cancelled"));
          });

          xhr.addEventListener("timeout", () => {
            const xhrIndex = currentUploadControllers.indexOf(xhr);
            if (xhrIndex > -1) {
              currentUploadControllers.splice(xhrIndex, 1);
            }
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

        if (currentUploadControllers.length === 0) {
          globalIsUploading = false;
        }

        return result;
      } catch (err) {
        if (currentUploadControllers.length === 0) {
          globalIsUploading = false;
        }

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

        const templateNumber =
          localStorage.getItem("selectedBatchTemplateNumber") || 1;

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

async function confirmDownloadPDF() {
  const selectedDepartments = getSelectedDepartments();

  if (selectedDepartments.length === 0) {
    showNotification("Please select at least one department", "error");
    return;
  }

  console.log("=== PDF DOWNLOAD STARTED ===");
  console.log("Selected departments:", selectedDepartments);
  console.log("Batch year:", window.currentBatchForDownload);

  closeDownloadPdfModal();

  const deptNames = selectedDepartments.join(", ");

  window.pdfGenerationCancelled = false;

  const preparingNotification = showPersistentNotification(
    `Preparing PDF... 0%<br><small>Initializing...</small>`,
    "info",
    true
  );

  try {
    console.log("Calling generateYearbookPDF...");
    await generateYearbookPDF(selectedDepartments, preparingNotification);
    console.log("PDF generation completed successfully");

    if (preparingNotification) {
      closePersistentNotification(preparingNotification);
    }

    await new Promise((resolve) => setTimeout(resolve, 100));

    const pdfCount = selectedDepartments.length;
    showNotification(
      `${pdfCount} PDF${
        pdfCount > 1 ? "s" : ""
      } downloaded successfully! (${deptNames})`,
      "success"
    );
  } catch (error) {
    console.error("=== PDF GENERATION ERROR ===");
    console.error("Error:", error);
    console.error("Error message:", error.message);
    console.error("Error stack:", error.stack);

    if (preparingNotification) {
      closePersistentNotification(preparingNotification);
    }

    await new Promise((resolve) => setTimeout(resolve, 100));

    showNotification(`Failed to generate PDF: ${error.message}`, "error");
  }

  setTimeout(() => {
    const checkboxes = document.querySelectorAll(".dept-checkbox");
    checkboxes.forEach((cb) => {
      cb.checked = false;
      cb.closest(".dept-label").classList.remove("selected");
    });
    updateDepartmentCount();
  }, 300);
}

async function generateYearbookPDF(departments, progressNotification) {
  console.log("=== generateYearbookPDF STARTED ===");
  const batchYear = window.currentBatchForDownload || "Yearbook";
  console.log("Batch year:", batchYear);
  console.log("Departments to process:", departments);

  console.log("Checking html2canvas:", typeof html2canvas);
  if (typeof html2canvas === "undefined") {
    console.error("html2canvas library not loaded!");
    throw new Error("html2canvas library not available");
  }

  console.log("Checking jsPDF:", typeof window.jspdf);
  if (typeof window.jspdf === "undefined") {
    console.error("jsPDF library not loaded!");
    throw new Error("jsPDF library not available");
  }

  const { jsPDF } = window.jspdf;
  console.log("jsPDF loaded successfully");

  const totalDepartments = departments.length;
  let currentDeptIndex = 0;

  const allDepartmentsText = departments.join(", ");

  let allPDFsSuccessful = true;

  for (const department of departments) {
    currentDeptIndex++;

    if (window.pdfGenerationCancelled) {
      console.log("PDF generation cancelled by user");
      throw new Error("PDF generation cancelled by user");
    }

    console.log(
      `\n=== Processing department ${currentDeptIndex}/${totalDepartments}: ${department} ===`
    );

    console.log(`Creating PDF document for ${department}...`);
    const pdf = new jsPDF({
      orientation: "landscape",
      unit: "px",
      format: [1920, 1080],
      compress: true,
    });
    console.log(`PDF document created for ${department}`);

    let isFirstPage = true;
    let totalPagesAdded = 0;

    const baseProgress = ((currentDeptIndex - 1) / totalDepartments) * 100;
    updatePersistentNotification(
      progressNotification,
      `Preparing PDF ${currentDeptIndex}/${totalDepartments}... ${Math.round(
        baseProgress
      )}%<br><small>Loading ${department}... [${allDepartmentsText}]</small>`
    );

    const yearbookUrl = getYearbookUrl(department, batchYear);
    console.log("Yearbook URL:", yearbookUrl);

    try {
      console.log(`Starting capture for ${department}...`);

      const onPageProgress = (currentPage, totalPages) => {
        const deptProgress =
          (currentPage / totalPages) * (80 / totalDepartments);
        const overallProgress = baseProgress + deptProgress;
        updatePersistentNotification(
          progressNotification,
          `Preparing PDF ${currentDeptIndex}/${totalDepartments}... ${Math.round(
            overallProgress
          )}%<br><small>Capturing ${department} pages (${currentPage}/${totalPages}) [${allDepartmentsText}]</small>`
        );
      };

      const pageCanvases = await captureAllYearbookPages(
        yearbookUrl,
        department,
        onPageProgress
      );
      console.log(`Captured ${pageCanvases.length} pages for ${department}`);

      if (window.pdfGenerationCancelled) {
        console.log("PDF generation cancelled by user");
        throw new Error("PDF generation cancelled by user");
      }

      if (pageCanvases.length === 0) {
        console.warn(`No pages captured for ${department}, skipping...`);
        allPDFsSuccessful = false;
        continue;
      }

      updatePersistentNotification(
        progressNotification,
        `Preparing PDF ${currentDeptIndex}/${totalDepartments}... ${Math.round(
          baseProgress + 80 / totalDepartments
        )}%<br><small>Adding ${department} to PDF... [${allDepartmentsText}]</small>`
      );

      for (let i = 0; i < pageCanvases.length; i++) {
        if (window.pdfGenerationCancelled) {
          console.log("PDF generation cancelled by user");
          throw new Error("PDF generation cancelled by user");
        }

        const pageCanvas = pageCanvases[i];
        console.log(
          `Adding page ${i + 1}/${pageCanvases.length} for ${department} to PDF`
        );

        if (!isFirstPage) {
          pdf.addPage([1920, 1080], "landscape");
          console.log("Added new page to PDF");
        }
        isFirstPage = false;

        const imgData = pageCanvas.toDataURL("image/jpeg", 0.95);
        console.log(`Image data generated (length: ${imgData.length} chars)`);

        pdf.addImage(imgData, "JPEG", 0, 0, 1920, 1080, undefined, "FAST");
        totalPagesAdded++;
        console.log(
          `Page added successfully. Total pages in PDF: ${totalPagesAdded}`
        );

        const addProgress =
          ((i + 1) / pageCanvases.length) * (100 / totalDepartments) -
          80 / totalDepartments;
        updatePersistentNotification(
          progressNotification,
          `Preparing PDF ${currentDeptIndex}/${totalDepartments}... ${Math.round(
            baseProgress + 80 / totalDepartments + addProgress
          )}%<br><small>Adding ${department} pages (${i + 1}/${
            pageCanvases.length
          }) [${allDepartmentsText}]</small>`
        );
      }

      if (totalPagesAdded > 0) {
        const deptProgress = (currentDeptIndex / totalDepartments) * 100;
        updatePersistentNotification(
          progressNotification,
          `Preparing PDF ${currentDeptIndex}/${totalDepartments}... ${Math.round(
            deptProgress
          )}%<br><small>Saving ${department} PDF... [${allDepartmentsText}]</small>`
        );

        const fileName = `${batchYear.replace(
          /\s+/g,
          "_"
        )}_Yearbook_${department}.pdf`;
        console.log(`Saving PDF for ${department} as:`, fileName);
        pdf.save(fileName);
        console.log(`PDF saved successfully for ${department}!`);

        await new Promise((resolve) => setTimeout(resolve, 500));
      } else {
        console.warn(`No pages added for ${department}, skipping save`);
        allPDFsSuccessful = false;
      }
    } catch (error) {
      console.error(`\n=== ERROR capturing pages for ${department} ===`);
      console.error("Error:", error);
      console.error("Error message:", error.message);
      console.error("Error stack:", error.stack);
      showNotification(
        `Error generating PDF for ${department}. Continuing with next...`,
        "error"
      );
      allPDFsSuccessful = false;
    }
  }

  console.log(`\n=== All PDF Generation Complete ===`);

  if (!allPDFsSuccessful && currentDeptIndex === 0) {
    throw new Error("No PDFs were generated. Please try again.");
  }

  updatePersistentNotification(
    progressNotification,
    `Complete! 100%<br><small>All PDFs downloaded [${allDepartmentsText}]</small>`
  );
}

function getYearbookUrl(department, batchYear) {
  const basePath = getBasePath();

  // Map department codes - Some departments use different codes in the yearbook
  const deptMapping = {
    COE: "BSE", // College of Education → BS Education
    CON: "BSN", // College of Nursing → BS Nursing
    BSCJE: "BSCJ", // BS Criminal Justice Education → BS Criminal Justice
  };

  const yearbookDept = deptMapping[department] || department;
  console.log(`Department mapping: ${department} -> ${yearbookDept}`);
  console.log(`Using batch year: ${batchYear}`);

  // Construct yearbook URL with department, batch year, and fullscreen flag
  return `${basePath}/Student/Yearbook/index.html?department=${yearbookDept}&batchYear=${encodeURIComponent(
    batchYear
  )}&fullscreen=true`;
}

async function captureAllYearbookPages(
  yearbookUrl,
  department,
  onPageProgress = null
) {
  return new Promise((resolve, reject) => {
    console.log(`\n>>> Opening yearbook for ${department}:`, yearbookUrl);

    // Create hidden iframe to load yearbook
    const iframe = document.createElement("iframe");
    iframe.style.position = "fixed";
    iframe.style.top = "-99999px";
    iframe.style.left = "-99999px";
    iframe.style.width = "1920px";
    iframe.style.height = "1080px";
    iframe.style.border = "none";
    iframe.style.zIndex = "-9999";

    console.log("Appending iframe to body...");
    document.body.appendChild(iframe);
    console.log("Iframe appended successfully");

    let timeoutHandle;
    let capturedPages = [];
    let iframeLoadFired = false;

    iframe.onload = async () => {
      iframeLoadFired = true;
      console.log(`✅ Iframe onload event fired for ${department}`);
      try {
        const iframeWindow = iframe.contentWindow;
        console.log("Got iframe window:", !!iframeWindow);

        const iframeDoc = iframe.contentDocument || iframeWindow.document;
        console.log("Got iframe document:", !!iframeDoc);

        if (!iframeDoc) {
          throw new Error(
            "Cannot access iframe document - possible CORS issue"
          );
        }

        console.log(
          `Yearbook loaded for ${department}, waiting for initialization...`
        );

        // Wait for the yearbook to fully initialize
        console.log("Waiting for yearbook initialization...");

        // Show initialization progress
        if (onPageProgress) {
          onPageProgress(0, 1);
        }

        await waitForYearbookInit(iframeWindow, iframeDoc);
        console.log("Yearbook initialized!");

        // Wait for images to load
        console.log("Waiting for images to load...");
        await waitForImages(iframeDoc);
        console.log("Images loaded!");

        // Get total pages from the yearbook
        console.log("Getting total pages...");
        const totalPages = await getTotalPagesFromYearbook(iframeWindow);
        console.log(`Total pages for ${department}:`, totalPages);

        if (totalPages === 0 || !totalPages) {
          throw new Error(`No pages found for ${department}`);
        }

        // Capture each page - Turn.js shows spreads (2 pages at a time)
        // We navigate to odd pages only to capture each unique spread
        // Page 1 = Front cover (single)
        // Page 2 = Shows spread of pages 2-3
        // Page 4 = Shows spread of pages 4-5
        // etc.

        let captureCount = 0;

        // Calculate approximate total captures (front + spreads + back if even)
        const approxTotalCaptures =
          Math.ceil(totalPages / 2) + (totalPages % 2 === 0 ? 1 : 0);

        // Capture front cover (page 1)
        console.log(`\n>>> Capturing front cover (page 1) for ${department}`);
        const nav1Success = await navigateToPage(iframeWindow, 1);
        if (nav1Success) {
          await new Promise((resolve) => setTimeout(resolve, 2000));
          let canvas = await capturePage(iframeDoc);
          if (canvas) {
            console.log(
              `Front cover captured (${canvas.width}x${canvas.height})`
            );
            capturedPages.push(canvas);
            captureCount++;
            // Report progress
            if (onPageProgress) {
              onPageProgress(captureCount, approxTotalCaptures);
            }
          }
        } else {
          console.error("Failed to navigate to front cover, skipping...");
        }

        // Capture spreads starting from page 2 (increment by 2 to avoid duplicates)
        for (let pageNum = 2; pageNum < totalPages; pageNum += 2) {
          console.log(
            `\n>>> Capturing spread at page ${pageNum} (pages ${pageNum}-${
              pageNum + 1
            }) for ${department}`
          );

          // Navigate to page
          console.log(`Navigating to page ${pageNum}...`);
          const navSuccess = await navigateToPage(iframeWindow, pageNum);

          if (!navSuccess) {
            console.warn(`Failed to navigate to page ${pageNum}, skipping...`);
            continue;
          }

          console.log(`Navigated to page ${pageNum}`);

          // Wait for page to render (increased wait time for better rendering)
          console.log("Waiting for page to render (2000ms)...");
          await new Promise((resolve) => setTimeout(resolve, 2000));
          console.log("Page should be rendered now");

          // Capture the current view
          console.log("Capturing spread...");
          let canvas = await capturePage(iframeDoc);
          if (canvas) {
            console.log(
              `Spread captured successfully (${canvas.width}x${canvas.height})`
            );
            capturedPages.push(canvas);
            captureCount++;
            // Report progress
            if (onPageProgress) {
              onPageProgress(captureCount, approxTotalCaptures);
            }
          } else {
            console.warn(`Failed to capture spread at page ${pageNum}`);
          }
        }

        // If total pages is even, capture the last page (back cover)
        if (totalPages > 1 && totalPages % 2 === 0) {
          console.log(
            `\n>>> Capturing back cover (page ${totalPages}) for ${department}`
          );
          const navLastSuccess = await navigateToPage(iframeWindow, totalPages);
          if (navLastSuccess) {
            await new Promise((resolve) => setTimeout(resolve, 2000));
            let canvas = await capturePage(iframeDoc);
            if (canvas) {
              console.log(
                `Back cover captured (${canvas.width}x${canvas.height})`
              );
              capturedPages.push(canvas);
              captureCount++;
              // Report progress
              if (onPageProgress) {
                onPageProgress(captureCount, approxTotalCaptures);
              }
            }
          } else {
            console.error("Failed to navigate to back cover, skipping...");
          }
        }

        console.log(`Total spreads captured: ${captureCount}`);

        // Clean up
        console.log("Cleaning up iframe...");
        clearTimeout(timeoutHandle);
        document.body.removeChild(iframe);
        console.log("Iframe removed");

        console.log(
          `\n>>> Successfully captured ${capturedPages.length} pages for ${department}`
        );

        if (capturedPages.length === 0) {
          reject(new Error(`No pages were captured for ${department}`));
        } else {
          resolve(capturedPages);
        }
      } catch (error) {
        console.error(`\n>>> ERROR in iframe.onload for ${department}`);
        console.error("Error:", error);
        console.error("Error message:", error.message);
        console.error("Error stack:", error.stack);

        clearTimeout(timeoutHandle);
        if (document.body.contains(iframe)) {
          document.body.removeChild(iframe);
          console.log("Iframe removed after error");
        }
        reject(error);
      }
    };

    iframe.onerror = (error) => {
      console.error(`\n❌ Iframe error event fired for ${department}`);
      console.error("Error:", error);
      console.error("Iframe URL was:", yearbookUrl);

      clearTimeout(timeoutHandle);
      if (document.body.contains(iframe)) {
        document.body.removeChild(iframe);
      }
      reject(new Error(`Failed to load yearbook for ${department}`));
    };

    // Set timeout (increased to 5 minutes for large departments)
    timeoutHandle = setTimeout(() => {
      console.error(
        `\n❌ Timeout loading yearbook for ${department} after 300s`
      );
      console.error("Iframe onload event fired:", iframeLoadFired);
      console.error("Iframe URL:", yearbookUrl);
      console.error(
        "Iframe readyState:",
        iframe.contentDocument ? iframe.contentDocument.readyState : "N/A"
      );

      // Check if iframe has any content
      try {
        const iframeDoc =
          iframe.contentDocument || iframe.contentWindow.document;
        if (iframeDoc) {
          console.error("Iframe title:", iframeDoc.title || "[no title]");
          console.error("Iframe body exists:", !!iframeDoc.body);
          console.error(
            "Iframe body innerHTML length:",
            iframeDoc.body ? iframeDoc.body.innerHTML.length : 0
          );
        } else {
          console.error("Cannot access iframe document - CORS issue likely");
        }
      } catch (e) {
        console.error("Error checking iframe content:", e.message);
      }

      if (document.body.contains(iframe)) {
        document.body.removeChild(iframe);
      }
      reject(new Error(`Timeout loading yearbook for ${department} (300s)`));
    }, 300000); // 5 minutes timeout

    console.log(`Setting iframe src for ${department}:`, yearbookUrl);
    iframe.src = yearbookUrl;
    console.log("Iframe src set, waiting for load event...");

    // Add a check to see if the page loads but onload doesn't fire
    setTimeout(() => {
      if (!iframeLoadFired) {
        console.warn(
          `⚠️ Iframe onload hasn't fired yet after 30s for ${department}`
        );
        console.warn("   Checking if content is loading...");
        try {
          const iframeDoc =
            iframe.contentDocument || iframe.contentWindow.document;
          if (iframeDoc && iframeDoc.body) {
            console.warn("   - Iframe document accessible: YES");
            console.warn("   - Document readyState:", iframeDoc.readyState);
            console.warn("   - Body exists:", !!iframeDoc.body);
            console.warn(
              "   - Body has content:",
              iframeDoc.body.innerHTML.length > 100
            );

            // If readyState is complete but onload didn't fire, manually trigger it
            if (
              iframeDoc.readyState === "complete" &&
              iframeDoc.body.innerHTML.length > 100
            ) {
              console.warn(
                "   📢 Document is complete but onload didn't fire. Manually triggering..."
              );
              iframe.onload();
            }
          } else {
            console.warn("   - Iframe document accessible: NO (possible CORS)");
          }
        } catch (e) {
          console.warn("   - Cannot access iframe:", e.message);
        }
      }
    }, 30000); // Check after 30 seconds
  });
}

async function waitForYearbookInit(iframeWindow, iframeDoc) {
  return new Promise((resolve, reject) => {
    let attempts = 0;
    const maxAttempts = 600; // Increased to 2 minutes (200ms * 600) for large departments
    const startTime = Date.now();

    // Check for errors in the iframe
    let hasLogged404 = false;
    let hasLoggedJSError = false;

    const checkInit = () => {
      attempts++;
      const elapsedTime = ((Date.now() - startTime) / 1000).toFixed(1);

      // Check for 404 or error pages (only if there's actual content)
      if (!hasLogged404 && attempts % 20 === 0) {
        const bodyText = iframeDoc.body
          ? iframeDoc.body.textContent.trim()
          : "";
        // Only log if there's meaningful content with error keywords
        if (
          bodyText.length > 20 &&
          (bodyText.includes("404") || bodyText.includes("Not Found"))
        ) {
          console.error(
            "⚠️ Error page detected in iframe:",
            bodyText.substring(0, 200)
          );
          hasLogged404 = true;
        } else if (attempts === 100 && bodyText.length < 100) {
          console.warn("⚠️ Iframe body appears mostly empty after 20s.");
          console.warn(
            "   This is normal if the page is still loading scripts and data."
          );
          console.warn(
            "   Body snippet:",
            bodyText.substring(0, 100) || "[empty]"
          );
        }
      }

      // Check for JavaScript errors
      if (
        !hasLoggedJSError &&
        iframeWindow.console &&
        iframeWindow.console.error
      ) {
        // Intercept console errors (if accessible)
        if (attempts === 1) {
          console.log("✓ Iframe window is accessible");
        }
      }

      const magazine = iframeDoc.querySelector(".magazine");
      const hasjQuery = iframeWindow.$ && typeof iframeWindow.$ === "function";

      if (!hasjQuery) {
        if (attempts % 25 === 0) {
          // Log every 5 seconds
          console.log(
            `⏳ Waiting for jQuery... (${elapsedTime}s elapsed, attempt ${attempts}/${maxAttempts})`
          );
          // Log what scripts are loaded
          const scripts = iframeDoc.querySelectorAll("script[src]");
          console.log(`   - Scripts found in iframe: ${scripts.length}`);
        }
        if (attempts < maxAttempts) {
          setTimeout(checkInit, 200);
        } else {
          console.error(
            "❌ jQuery not loaded after max attempts. Cannot proceed."
          );
          console.error("Page title:", iframeDoc.title);
          console.error(
            "Scripts loaded:",
            Array.from(iframeDoc.querySelectorAll("script[src]"))
              .map((s) => s.src)
              .join(", ")
          );
          reject(new Error("jQuery failed to load in yearbook"));
        }
        return;
      }

      const hasTurnJs = iframeWindow.$(".magazine").turn;

      if (magazine && hasTurnJs && typeof hasTurnJs === "function") {
        try {
          const isInitialized = iframeWindow.$(".magazine").turn("is");
          const totalPages = iframeWindow.$(".magazine").turn("pages");

          if (attempts % 25 === 0) {
            // Log every 5 seconds
            console.log(
              `⏳ Turn.js check - Initialized: ${isInitialized}, Pages: ${totalPages} (${elapsedTime}s elapsed)`
            );
          }

          if (isInitialized && totalPages > 0) {
            console.log(
              `✅ Yearbook initialized successfully! Total pages: ${totalPages} (took ${elapsedTime}s)`
            );
            // Extra wait to ensure all pages are ready
            setTimeout(() => resolve(), 2000); // 2 seconds
            return;
          } else {
            if (attempts % 50 === 0) {
              // Log every 10 seconds
              console.log(
                `   Turn.js found but not ready yet (initialized: ${isInitialized}, pages: ${totalPages})`
              );
            }
          }
        } catch (e) {
          if (attempts % 10 === 0) {
            console.log(
              `Turn.js check error (${elapsedTime}s elapsed, attempt ${attempts}/${maxAttempts}):`,
              e.message
            );
          }
        }
      } else {
        if (attempts % 25 === 0) {
          // Log every 5 seconds
          console.log(
            `⏳ Waiting for Turn.js initialization... (${elapsedTime}s elapsed)`
          );
        }
      }

      if (attempts < maxAttempts) {
        setTimeout(checkInit, 200);
      } else {
        console.error(`Max init attempts reached after ${elapsedTime}s`);
        console.error("Final state check:");
        console.error("  - Magazine element exists:", !!magazine);
        console.error("  - jQuery loaded:", hasjQuery);
        console.error("  - Turn.js available:", !!hasTurnJs);
        console.error("  - Page title:", iframeDoc.title);
        console.error(
          "  - Body classes:",
          iframeDoc.body ? iframeDoc.body.className : "N/A"
        );

        // Log any visible error messages in the page
        const errorElements = iframeDoc.querySelectorAll(
          ".error, [class*='error']"
        );
        if (errorElements.length > 0) {
          console.error("  - Error elements found:", errorElements.length);
          errorElements.forEach((el, i) => {
            console.error(
              `    Error ${i + 1}:`,
              el.textContent.substring(0, 100)
            );
          });
        }

        // Check if there's student data loaded
        const studentCards = iframeDoc.querySelectorAll(".student-card");
        console.error("  - Student cards found:", studentCards.length);

        reject(
          new Error(
            "Yearbook initialization timeout - Turn.js failed to initialize"
          )
        );
      }
    };

    checkInit();
  });
}

async function getTotalPagesFromYearbook(iframeWindow) {
  try {
    if (iframeWindow.$ && iframeWindow.$(".magazine").turn) {
      const totalPages = iframeWindow.$(".magazine").turn("pages");
      return totalPages || 1;
    }
  } catch (e) {
    console.error("Error getting total pages:", e);
  }
  return 1; // Default to 1 page if can't determine
}

async function navigateToPage(iframeWindow, pageNum) {
  try {
    if (!iframeWindow.$ || !iframeWindow.$(".magazine").turn) {
      console.error("Turn.js not available for navigation");
      return false;
    }

    // Check if page exists
    const totalPages = iframeWindow.$(".magazine").turn("pages");
    if (pageNum < 1 || pageNum > totalPages) {
      console.error(`Page ${pageNum} is out of range (1-${totalPages})`);
      return false;
    }

    // Check if Turn.js is initialized
    const isInitialized = iframeWindow.$(".magazine").turn("is");
    if (!isInitialized) {
      console.error("Turn.js not initialized yet");
      return false;
    }

    // Navigate to page
    iframeWindow.$(".magazine").turn("page", pageNum);
    console.log(`Successfully navigated to page ${pageNum}`);

    // Wait for page turn animation to complete (increased to 1500ms for slow animations)
    await new Promise((resolve) => setTimeout(resolve, 1500));

    return true;
  } catch (e) {
    console.error(`Error navigating to page ${pageNum}:`, e);
    console.error("Error name:", e.name);
    console.error("Error message:", e.message);
    return false;
  }
}

async function capturePage(iframeDoc) {
  console.log(">>> capturePage called");
  try {
    // Hide navigation controls before capturing
    console.log("Hiding navigation controls...");
    const navControls = iframeDoc.querySelector(".nav-controls");
    const originalNavDisplay = navControls ? navControls.style.display : null;
    if (navControls) {
      navControls.style.display = "none";
      console.log("Navigation controls hidden");
    }

    // Check if html2canvas is available
    if (typeof html2canvas === "undefined") {
      console.error("html2canvas is not defined!");
      return null;
    }

    // Create a canvas with exact fullscreen dimensions
    const finalCanvas = document.createElement("canvas");
    finalCanvas.width = 1920;
    finalCanvas.height = 1080;
    const ctx = finalCanvas.getContext("2d");

    console.log("Created canvas:", finalCanvas.width, "x", finalCanvas.height);

    // Step 1: Draw the background image
    console.log("Loading background image...");
    const bgImage = await loadImage(
      "https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png"
    );
    if (bgImage) {
      console.log("Drawing background image...");
      ctx.drawImage(bgImage, 0, 0, 1920, 1080);
    } else {
      // Fallback background color
      console.log("Using fallback background color");
      ctx.fillStyle = "#000042";
      ctx.fillRect(0, 0, 1920, 1080);
    }

    // Step 2: Capture the yearbook content at higher resolution
    console.log("Capturing yearbook content...");
    const canvas = iframeDoc.querySelector("#canvas");
    if (canvas) {
      const yearbookCanvas = await html2canvas(canvas, {
        backgroundColor: null,
        scale: 2, // Higher scale for better quality and larger size
        useCORS: true,
        allowTaint: true,
        logging: false,
        width: 1920,
        height: 1080,
        foreignObjectRendering: false, // Disable foreign object rendering to avoid some iframe issues
        removeContainer: true, // Clean up immediately
      });
      console.log("Drawing yearbook content at scale...");
      // Draw the captured content scaled to fit our canvas
      ctx.drawImage(yearbookCanvas, 0, 0, 1920, 1080);
    } else {
      console.warn("Canvas element not found, capturing body instead");
      const yearbookCanvas = await html2canvas(iframeDoc.body, {
        backgroundColor: null,
        scale: 2, // Higher scale for better quality and larger size
        useCORS: true,
        allowTaint: true,
        logging: false,
        width: 1920,
        height: 1080,
        foreignObjectRendering: false, // Disable foreign object rendering to avoid some iframe issues
        removeContainer: true, // Clean up immediately
      });
      ctx.drawImage(yearbookCanvas, 0, 0, 1920, 1080);
    }

    // Step 3: Draw the lower curl SVG
    console.log("Drawing lower curl...");
    await drawLowerCurl(ctx, 1920, 1080);

    // Restore navigation controls
    if (navControls && originalNavDisplay !== null) {
      navControls.style.display = originalNavDisplay;
      console.log("Navigation controls restored");
    }

    console.log("Final canvas composition completed!");
    console.log(
      "Final canvas dimensions:",
      finalCanvas.width,
      "x",
      finalCanvas.height
    );
    return finalCanvas;
  } catch (error) {
    console.error(">>> ERROR in capturePage:");
    console.error("Error:", error);
    console.error("Error message:", error.message);
    console.error("Error stack:", error.stack);

    // Restore navigation controls even on error
    const navControls = iframeDoc.querySelector(".nav-controls");
    if (navControls) {
      navControls.style.display = "";
    }

    return null;
  }
}

// Helper function to load an image
function loadImage(url) {
  return new Promise((resolve) => {
    const img = new Image();
    img.crossOrigin = "anonymous";
    img.onload = () => {
      console.log("Image loaded successfully:", url);
      resolve(img);
    };
    img.onerror = (error) => {
      console.error("Failed to load image:", url, error);
      resolve(null);
    };
    img.src = url;
  });
}

// Helper function to draw the lower curl SVG
async function drawLowerCurl(ctx, width, height) {
  try {
    // Create SVG for the lower curl
    const svgString = `
      <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" width="1920" height="80">
        <path d="M0,60 Q180,100 360,60 T720,60 T1080,60 T1440,60 L1440,120 L0,120 Z" fill="#1a237e" opacity="0.4" />
        <path d="M0,80 Q180,40 360,80 T720,80 T1080,80 T1440,80 L1440,120 L0,120 Z" fill="#112d4e" opacity="0.7" />
        <path d="M0,100 Q180,60 360,100 T720,100 T1080,100 T1440,100 L1440,120 L0,120 Z" fill="#021326" />
      </svg>
    `;

    // Convert SVG to image
    const svgBlob = new Blob([svgString], {
      type: "image/svg+xml;charset=utf-8",
    });
    const url = URL.createObjectURL(svgBlob);
    const img = await loadImage(url);

    if (img) {
      // Draw the curl at the bottom of the canvas
      const curlHeight = 80;
      const curlY = height - curlHeight;
      ctx.drawImage(img, 0, curlY, width, curlHeight);
      console.log("Lower curl drawn successfully");
    }

    URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Error drawing lower curl:", error);
  }
}

function waitForImages(doc) {
  return new Promise((resolve) => {
    const images = doc.querySelectorAll("img");
    const imagePromises = Array.from(images).map((img) => {
      if (img.complete) return Promise.resolve();
      return new Promise((resolve) => {
        img.onload = resolve;
        img.onerror = resolve; // Resolve even on error to not block
      });
    });

    Promise.all(imagePromises).then(resolve);

    // Timeout after 10 seconds
    setTimeout(resolve, 10000);
  });
}

function getSelectedDepartments() {
  const checkboxes = document.querySelectorAll(".dept-checkbox:checked");
  return Array.from(checkboxes).map((cb) => cb.value);
}

function updateDepartmentCount() {
  const count = document.querySelectorAll(".dept-checkbox:checked").length;
  const countElement = document.getElementById("selected-dept-count");
  const confirmBtn = document.getElementById("confirm-download-pdf-btn");

  if (countElement) {
    countElement.textContent = count;
  }

  // Enable/disable download button based on selection
  if (confirmBtn) {
    if (count === 0) {
      confirmBtn.disabled = true;
      confirmBtn.style.opacity = "0.5";
      confirmBtn.style.cursor = "not-allowed";
    } else {
      confirmBtn.disabled = false;
      confirmBtn.style.opacity = "1";
      confirmBtn.style.cursor = "pointer";
    }
  }
}

// Function to update Download PDF button states based on section availability
function updateDownloadPdfButtonStates() {
  const sections = document.querySelectorAll(".section");

  sections.forEach((section) => {
    const downloadPdfBtn = section.querySelector(".download-pdf-btn");

    if (downloadPdfBtn) {
      // Check if the section has the 'available' class (green header)
      // AND if it's selected (yellow header when selected)
      const isAvailable = section.classList.contains("available");
      const isSelected = section.classList.contains("selected");

      if (isAvailable && isSelected) {
        // Enable button ONLY for green AND selected batches
        downloadPdfBtn.disabled = false;
        downloadPdfBtn.style.opacity = "1";
        downloadPdfBtn.style.cursor = "pointer";
        downloadPdfBtn.title = "Download PDF";
      } else if (!isAvailable) {
        // Disable if not available (not green)
        downloadPdfBtn.disabled = true;
        downloadPdfBtn.style.opacity = "0.5";
        downloadPdfBtn.style.cursor = "not-allowed";
        downloadPdfBtn.title =
          "This batch is not available for download (must have green header)";
      } else if (!isSelected) {
        // Disable if not selected
        downloadPdfBtn.disabled = true;
        downloadPdfBtn.style.opacity = "0.5";
        downloadPdfBtn.style.cursor = "not-allowed";
        downloadPdfBtn.title =
          "Please select this batch first (click 'Select Batch' button)";
      }
    }
  });
}

function initializeDepartmentSelection() {
  const checkboxes = document.querySelectorAll(".dept-checkbox");
  const selectAllBtn = document.getElementById("select-all-dept-btn");

  // Handle individual checkbox changes
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      const label = this.closest(".dept-label");
      if (this.checked) {
        label.classList.add("selected");
      } else {
        label.classList.remove("selected");
      }
      updateDepartmentCount();
      updateSelectAllButton();
    });
  });

  // Handle select all button
  if (selectAllBtn) {
    selectAllBtn.addEventListener("click", function () {
      const allChecked = Array.from(checkboxes).every((cb) => cb.checked);

      checkboxes.forEach((checkbox) => {
        checkbox.checked = !allChecked;
        const label = checkbox.closest(".dept-label");
        if (checkbox.checked) {
          label.classList.add("selected");
        } else {
          label.classList.remove("selected");
        }
      });

      updateDepartmentCount();
      updateSelectAllButton();
    });
  }

  // Update button text based on selection state
  function updateSelectAllButton() {
    if (!selectAllBtn) return;

    const allChecked = Array.from(checkboxes).every((cb) => cb.checked);
    const icon = selectAllBtn.querySelector("i");
    const btnText = selectAllBtn.childNodes[selectAllBtn.childNodes.length - 1];

    if (allChecked) {
      if (icon) icon.className = "fas fa-times-circle";
      btnText.textContent = " Deselect All";
    } else {
      if (icon) icon.className = "fas fa-check-double";
      btnText.textContent = " Select All";
    }
  }

  updateDepartmentCount();
  updateSelectAllButton();
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

    // Update download PDF button states after batch deletion
    updateDownloadPdfButtonStates();
  }
  closeDeleteBatchModal();
}

function confirmSelectTemplate() {
  if (window.pendingSelectSection) {
    document.querySelectorAll(".section").forEach((s) => {
      s.classList.remove("selected");
    });

    window.pendingSelectSection.classList.add("selected");

    const sections = document.querySelectorAll(".section");

    const sectionHeader =
      window.pendingSelectSection.querySelector(".section-header");
    const batchYear = sectionHeader ? sectionHeader.textContent.trim() : "";

    console.log("=== BATCH TEMPLATE SELECTION DEBUG ===");
    console.log("Section header element:", sectionHeader);
    console.log("Section header text:", batchYear);
    console.log("Full section HTML:", window.pendingSelectSection.outerHTML);

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

    if (typeof window.updateUploadBoxStates === "function") {
      window.updateUploadBoxStates();
    }

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

    // Update download PDF button states after section selection changes
    updateDownloadPdfButtonStates();
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

      localStorage.setItem("selectedBatchYear", templateName);
      console.log("Stored batch year:", templateName);

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
    updateDownloadPdfButtonStates();
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
          box.classList.remove("disabled");
          box.style.pointerEvents = "auto";
          box.style.opacity = "1";
        } else if (!isActionBox) {
          box.classList.add("disabled");
          box.style.pointerEvents = "none";
          box.style.opacity = "0.5";
        }
      });

      const selectBatchBtn = section.querySelector(".select-batch-btn");
      if (selectBatchBtn) {
        const btnText = selectBatchBtn.querySelector("span");
        if (isSelected) {
          selectBatchBtn.disabled = true;
          selectBatchBtn.classList.add("selected");
          if (btnText) {
            btnText.textContent = "Batch Selected";
          }
        } else {
          selectBatchBtn.disabled = false;
          selectBatchBtn.classList.remove("selected");
          if (btnText) {
            btnText.textContent = "Select Batch";
          }
        }
      }
    });

    console.log("=== updateUploadBoxStates complete ===");
  }

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

    // Update download PDF button states after sections are updated
    updateDownloadPdfButtonStates();
  }

  window.setAvailableSections = setAvailableSections;

  if (sections.length > 0) {
    const savedTemplate = localStorage.getItem("selectedBatchTemplate");
    const savedBatchYear = localStorage.getItem("selectedBatchYear");
    let selectedSection = null;

    console.log("=== Restoring Selection on Page Load ===");
    console.log("Saved batch year:", savedBatchYear);
    console.log("Saved template:", savedTemplate);
    console.log("Total sections found:", sections.length);

    if (savedBatchYear) {
      sections.forEach((section) => {
        const headerText = section
          .querySelector(".section-header")
          .textContent.trim();
        if (headerText === savedBatchYear) {
          selectedSection = section;
          console.log(
            "✓ Restored selected section from savedBatchYear:",
            savedBatchYear
          );
        }
      });

      if (!selectedSection) {
        console.warn(
          "⚠️ Could not find section matching savedBatchYear:",
          savedBatchYear
        );
      }
    }

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

  initializeDepartmentSelection();

  updateDownloadPdfButtonStates();
});
