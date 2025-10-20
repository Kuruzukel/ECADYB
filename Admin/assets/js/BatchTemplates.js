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
  
  // Find the section by batch name
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
  
  // Store the section for confirmation
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
    closeDeleteModal();
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

  // Get all existing sections across all form-groups
  const allSections = document.querySelectorAll(".form-group .section");
  
  // Get the last section's header text
  let nextYear = "2024-2025"; // Default year
  if (allSections.length > 0) {
    const lastSection = allSections[allSections.length - 1];
    const lastHeader = lastSection.querySelector(".section-header").textContent.trim();
    
    // Extract year from "Batch Year 2026-2027" format
    const yearMatch = lastHeader.match(/(\d{4})-(\d{4})/);
    if (yearMatch) {
      const startYear = parseInt(yearMatch[1]);
      const endYear = parseInt(yearMatch[2]);
      nextYear = `${startYear + 1}-${endYear + 1}`;
    }
  }

  // Create new section HTML - identical structure to hard-coded sections
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

  // Create a temporary container and parse the HTML
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = newSectionHTML;
  const newSection = tempDiv.firstElementChild;

  // Check if there's an existing form-group with less than 3 sections
  const existingFormGroups = document.querySelectorAll(".form-group");
  let targetFormGroup = null;
  let insertPosition = 0;
  
  // Find the last form-group that has less than 3 sections
  for (let i = existingFormGroups.length - 1; i >= 0; i--) {
    const sectionsInGroup = existingFormGroups[i].querySelectorAll(".section");
    if (sectionsInGroup.length < 3) {
      targetFormGroup = existingFormGroups[i];
      insertPosition = sectionsInGroup.length; // Position to insert (0=left, 1=middle, 2=right)
      break;
    }
  }

  if (targetFormGroup) {
    // Add to existing form-group at the correct position (left to right)
    const sectionsInGroup = targetFormGroup.querySelectorAll(".section");
    if (insertPosition === 0) {
      // Insert at the beginning (left position)
      targetFormGroup.insertBefore(newSection, sectionsInGroup[0] || null);
    } else {
      // Insert at the end (right position) - for both middle and right positions
      targetFormGroup.appendChild(newSection);
    }
  } else {
    // Create a new form-group for the new section
    const newFormGroup = document.createElement('div');
    newFormGroup.className = 'form-group';
    newFormGroup.appendChild(newSection);

    // Find the generate button container and insert the new form-group before it
    const generateButtonContainer = document.querySelector(".generate-button-container");
    if (generateButtonContainer && generateButtonContainer.parentNode) {
      generateButtonContainer.parentNode.insertBefore(newFormGroup, generateButtonContainer);
    } else {
      // Fallback: append to form-content
      formContent.appendChild(newFormGroup);
    }
  }

  // Initialize the new section with upload functionality
  initializeSection(newSection);
  
  // Get the currentXhrs and isUploadCancelled from the parent scope
  // These are defined in the DOMContentLoaded event listener
  const currentXhrs = window.currentXhrs || [];
  const isUploadCancelled = window.isUploadCancelled || false;
  
  // Initialize upload boxes for the new section
  initializeSectionUploadBoxes(newSection, currentXhrs, isUploadCancelled);

  // Save generated sections to localStorage
  saveGeneratedSectionsToLocalStorage();
  
  // Refresh sections NodeList to include the new section
  if (window.refreshSections) {
    window.refreshSections();
  }
  
  // Update available sections to include the new section if it's within the 3-year window
  if (window.setAvailableSections) {
    window.setAvailableSections();
  }

  showNotification(`Batch Year ${nextYear} created successfully!`, "success");
  
  // Scroll to the new section
  newSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Save all dynamically generated sections to localStorage
function saveGeneratedSectionsToLocalStorage() {
  const allSections = document.querySelectorAll(".form-group .section");
  const generatedSections = [];
  
  // We know the first 3 sections are hardcoded (2024-2025, 2025-2026, 2026-2027)
  // So we only save sections after the first 3
  allSections.forEach((section, index) => {
    if (index >= 3) { // Skip the first 3 hardcoded sections
      const sectionHeader = section.querySelector(".section-header").textContent.trim();
      generatedSections.push(sectionHeader);
    }
  });
  
  localStorage.setItem('generatedBatchSections', JSON.stringify(generatedSections));
  console.log('Saved generated sections to localStorage:', generatedSections);
}

// Restore generated sections from localStorage on page load
function restoreGeneratedSectionsFromLocalStorage() {
  const savedSections = localStorage.getItem('generatedBatchSections');
  
  if (!savedSections) {
    console.log('No saved sections found in localStorage');
    return;
  }
  
  try {
    const sectionsArray = JSON.parse(savedSections);
    console.log('Restoring sections from localStorage:', sectionsArray);
    
    // Restore each section
    sectionsArray.forEach((sectionHeader) => {
      restoreSingleSection(sectionHeader);
    });
  } catch (e) {
    console.error('Error parsing saved sections:', e);
  }
}

// Restore a single section based on its header text
function restoreSingleSection(sectionHeader) {
  const formContent = document.querySelector(".form-content");
  if (!formContent) return;

  // Create new section HTML
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

  // Create a temporary container and parse the HTML
  const tempDiv = document.createElement('div');
  tempDiv.innerHTML = newSectionHTML;
  const newSection = tempDiv.firstElementChild;

  // Check if there's an existing form-group with less than 3 sections
  const existingFormGroups = document.querySelectorAll(".form-group");
  let targetFormGroup = null;
  let insertPosition = 0;
  
  // Find the last form-group that has less than 3 sections
  for (let i = existingFormGroups.length - 1; i >= 0; i--) {
    const sectionsInGroup = existingFormGroups[i].querySelectorAll(".section");
    if (sectionsInGroup.length < 3) {
      targetFormGroup = existingFormGroups[i];
      insertPosition = sectionsInGroup.length;
      break;
    }
  }

  if (targetFormGroup) {
    // Add to existing form-group at the correct position
    const sectionsInGroup = targetFormGroup.querySelectorAll(".section");
    if (insertPosition === 0) {
      targetFormGroup.insertBefore(newSection, sectionsInGroup[0] || null);
    } else {
      targetFormGroup.appendChild(newSection);
    }
  } else {
    // Create a new form-group for the new section
    const newFormGroup = document.createElement('div');
    newFormGroup.className = 'form-group';
    newFormGroup.appendChild(newSection);

    // Find the generate button container and insert the new form-group before it
    const generateButtonContainer = document.querySelector(".generate-button-container");
    if (generateButtonContainer && generateButtonContainer.parentNode) {
      generateButtonContainer.parentNode.insertBefore(newFormGroup, generateButtonContainer);
    } else {
      formContent.appendChild(newFormGroup);
    }
  }

  // Initialize the new section only if not deferring
  if (!window.deferSectionInitialization) {
    initializeSection(newSection);
    
    // Get the currentXhrs and isUploadCancelled from the parent scope
    const currentXhrs = window.currentXhrs || [];
    const isUploadCancelled = window.isUploadCancelled || false;
    
    // Initialize upload boxes for the new section
    initializeSectionUploadBoxes(newSection, currentXhrs, isUploadCancelled);
  }
  
  console.log(`Restored section: ${sectionHeader}`);
}

function initializeSection(section) {
  // Validate section
  if (!section || !section.parentNode) {
    console.error("initializeSection: Invalid section");
    return;
  }
  
  const sectionHeader = section.querySelector(".section-header");
  if (!sectionHeader) {
    console.error("initializeSection: Section header not found");
    return;
  }
  
  // Check if already initialized by checking for data attribute
  if (section.dataset.initialized === "true") {
    console.log("initializeSection: Section already initialized, skipping");
    return;
  }
  
  // Section header click functionality removed - now using Select Batch button only
  
  // Mark as initialized
  section.dataset.initialized = "true";
}

function initializeSectionUploadBoxes(section, currentXhrs, isUploadCancelled) {
  const sectionUploadBoxes = section.querySelectorAll(".upload-box");
  const sectionHeader = section.querySelector(".section-header").textContent.trim();

  sectionUploadBoxes.forEach((box, index) => {
    const slot = index + 1;
    const isBackgroundSlot = slot === 8;
    const isActionBox = slot === 9;

    // Handle action box separately
    if (isActionBox) {
      const selectBatchBtn = box.querySelector(".select-batch-btn");
      const downloadBtn = box.querySelector(".download-pdf-btn");
      const deleteBatchBtn = box.querySelector(".delete-batch-btn");
      
      console.log("Action box found, selectBatchBtn:", selectBatchBtn, "downloadBtn:", downloadBtn, "deleteBatchBtn:", deleteBatchBtn);
      
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
          // Prevent action if button is disabled
          if (deleteBatchBtn.disabled) {
            console.log("Delete batch button is disabled for:", sectionHeader);
            return;
          }
          console.log("Delete batch button clicked for:", sectionHeader);
          deleteBatchTemplate(section, sectionHeader);
        });
      }
      return; // Skip the rest of the initialization for action box
    }

    const frontInput = box.querySelector(".frontInput");
    const backInput = box.querySelector(".backInput");
    const deleteBtn = box.querySelector(".delete-btn");
    const plusIcon = box.querySelector(".plus-icon");

    let frontImg = null;
    let backImg = null;
    let showingFront = true;

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
      if (frontImg) box.appendChild(frontImg);
      if (backImg) box.appendChild(backImg);
      box.appendChild(deleteBtn);
      box.appendChild(frontInput);
      box.appendChild(backInput);
    };

    box.addEventListener("click", (event) => {
      if (event.target === deleteBtn) return;
      if (!frontImg) {
        frontInput.click();
      } else if (!backImg && !isBackgroundSlot) {
        backInput.click();
      } else {
        toggleImages();
      }
    });

    frontInput.addEventListener("change", async (event) => {
      const files = event.target.files;
      if (!files || files.length === 0) return;
      
      // Reset cancellation flag
      isUploadCancelled = false;
      
      const uploadOverlay = document.getElementById("upload-overlay");
      const uploadText = document.getElementById("uploadText");
      
      if (isBackgroundSlot) {
        if (files.length > 1) {
          showNotification("Background slot can only accept 1 image. Please select only 1 image.", "error");
          event.target.value = "";
          return;
        }
        const result = await uploadToBunny(files[0], slot, "front", true, false);
        // Update available sections after upload
        if (result && result.success && window.setAvailableSections) {
          await window.setAvailableSections();
        }
      } else {
        if (files.length > 2) {
          showNotification("You can only upload 2 images at the same time. Please select only 2 images.", "error");
          event.target.value = "";
          return;
        }
        
        if (files.length === 2 && uploadOverlay && uploadText) {
          uploadOverlay.style.display = "flex";
          uploadText.textContent = `Uploading Slot ${slot} front and back cover...`;
        }
        
        const suppressNotifications = files.length === 2;
        const isBatchUpload = files.length === 2;
        let uploadCancelled = false;
        
        if (files.length === 2) {
          const uploadPromises = [
            uploadToBunny(files[0], slot, "front", !suppressNotifications, isBatchUpload),
            uploadToBunny(files[1], slot, "back", !suppressNotifications, isBatchUpload)
          ];
          
          const results = await Promise.all(uploadPromises);
          uploadCancelled = results.some(result => result && result.cancelled) || isUploadCancelled;
          
          if (!uploadCancelled && !isUploadCancelled) {
            showNotification(`Uploaded successfully to Slot ${slot} front and back cover`, "success");
            // Update available sections after upload
            if (window.setAvailableSections) {
              await window.setAvailableSections();
            }
          }
        } else if (files.length === 1) {
          const result = await uploadToBunny(files[0], slot, "front", !suppressNotifications, false);
          // Update available sections after upload
          if (result && result.success && window.setAvailableSections) {
            await window.setAvailableSections();
          }
          uploadCancelled = (result && result.cancelled) || isUploadCancelled;
        }
        
        if (!uploadCancelled && !isUploadCancelled && uploadOverlay) {
          uploadOverlay.style.display = "none";
        }
      }
      
      event.target.value = "";
    });

    deleteBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      selectedConfirmAction = () => {
        const sides = [];
        if (frontImg) sides.push("front");
        if (backImg && !isBackgroundSlot) sides.push("back");
        if (!sides.length) return;
        Promise.all(sides.map((side) => deleteCover(slot, side))).then(() => {
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
        });
      };
      openDeleteModal();
    });

    async function uploadToBunny(file, slot, side, showNotif, isBatch) {
      try {
        // Get the batch year from the section header
        const sectionHeader = box.closest('.section').querySelector('.section-header');
        const batchYear = sectionHeader ? sectionHeader.textContent.trim() : '';
        
        const formData = new FormData();
        formData.append("file", file);
        formData.append("slot", String(slot));
        formData.append("side", side);
        formData.append("batch_year", batchYear);

        const xhr = new XMLHttpRequest();
        const uploadPromise = new Promise((resolve, reject) => {
          xhr.upload.addEventListener("progress", (e) => {
            if (e.lengthComputable) {
              const percentComplete = (e.loaded / e.total) * 100;
              console.log(`Upload progress for slot ${slot} ${side}: ${percentComplete.toFixed(2)}%`);
            }
          });

          xhr.addEventListener("load", () => {
            if (xhr.status === 200) {
              try {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                  if (showNotif && !isBatch) {
                    showNotification(`Uploaded successfully to Slot ${slot} ${side}`, "success");
                  }
                  resolve(data);
                } else {
                  if (showNotif) {
                    showNotification(data.message || "Upload failed", "error");
                  }
                  reject(new Error(data.message || "Upload failed"));
                }
              } catch (e) {
                if (showNotif) {
                  showNotification("Failed to parse response", "error");
                }
                reject(e);
              }
            } else {
              if (showNotif) {
                showNotification(`Upload failed: HTTP ${xhr.status}`, "error");
              }
              reject(new Error(`HTTP ${xhr.status}`));
            }
          });

          xhr.addEventListener("error", () => {
            if (showNotif) {
              showNotification("Upload failed", "error");
            }
            reject(new Error("Upload failed"));
          });

          xhr.addEventListener("abort", () => {
            if (showNotif) {
              showNotification("Upload cancelled", "error");
            }
            reject(new Error("Upload cancelled"));
          });

          xhr.open("POST", UPLOAD_ENDPOINT);
          xhr.send(formData);
        });

        return await uploadPromise;
      } catch (err) {
        console.error("Upload error:", err);
        if (showNotif) {
          showNotification(err.message || "Upload failed", "error");
        }
        return { success: false, cancelled: true };
      }
    }

    async function deleteCover(slot, side) {
      try {
        const form = new FormData();
        form.append("slot", String(slot));
        form.append("side", side);

        const res = await fetch(DELETE_ENDPOINT, {
          method: "POST",
          body: form,
        });

        if (!res.ok) {
          throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }

        const data = await res.json().catch(() => null);
        if (!data?.success) {
          showNotification(data?.message || "Delete failed", "error");
        } else {
          showNotification("Image deleted", "success");
          // Update available sections after deletion
          if (window.setAvailableSections) {
            await window.setAvailableSections();
          }
        }
      } catch (err) {
        console.error("Delete error:", err);
        showNotification(err.message || "Delete failed", "error");
      }
    }

    // Load existing images
    (async function loadExisting() {
      try {
        await new Promise((resolve) => setTimeout(resolve, 50));

        const res = await fetch(FETCH_ENDPOINT, {
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
        if (found.front_url) {
          frontImg = document.createElement("img");
          frontImg.src = found.front_url;
          frontImg.classList.add("front-img");
        }
        if (found.back_url && !isBackgroundSlot) {
          backImg = document.createElement("img");
          backImg.src = found.back_url;
          backImg.classList.add("back-img");
        }
        if (frontImg || backImg) {
          box.innerHTML = "";
          const plusIcon = box.querySelector(".plus-icon");
          if (plusIcon) plusIcon.remove();
          ensureChildren();
          deleteBtn.style.display = "flex";
          box.classList.add("has-image");
          showingFront = true;
          if (frontImg) {
            frontImg.style.opacity = 1;
            if (backImg && !isBackgroundSlot) backImg.style.opacity = 0;
          }
        }
      } catch (e) {
        if (e.name === "TimeoutError" || e.name === "AbortError") {
          console.warn("Fetch covers request timed out");
          showNotification("Loading covers timed out. Please try again.", "error");
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
  showNotification("PDF download functionality will be implemented soon!", "info");
  console.log("Downloading PDF...");
  closeDownloadPdfModal();
  // TODO: Implement PDF download logic
}

function deleteBatchTemplate(section, batchName) {
  openDeleteBatchModal(batchName);
  
  // Store the section and batch name for deletion
  window.pendingDeleteSection = section;
  window.pendingDeleteBatchName = batchName;
}

function confirmDeleteBatch() {
  if (window.pendingDeleteSection) {
    // Remove only the specific section, not the entire form-group
    window.pendingDeleteSection.remove();
    
    // Update localStorage after deletion
    saveGeneratedSectionsToLocalStorage();
    
    // Refresh sections NodeList after deletion
    if (window.refreshSections) {
      window.refreshSections();
    }
    
    showNotification(`${window.pendingDeleteBatchName} deleted successfully!`, "success");
    
    window.pendingDeleteSection = null;
    window.pendingDeleteBatchName = null;
  }
  closeDeleteBatchModal();
}

function confirmSelectTemplate() {
  if (window.pendingSelectSection) {
    // Remove selected class from all sections
    document.querySelectorAll(".section").forEach((s) => {
      s.classList.remove("selected");
    });
    
    // Add selected class to the pending section
    window.pendingSelectSection.classList.add("selected");
    
    // Update upload box states
    const sections = document.querySelectorAll(".section");
    sections.forEach((section) => {
      const uploadBoxes = section.querySelectorAll(".upload-box");
      const isSelected = section === window.pendingSelectSection;
      
      uploadBoxes.forEach((box) => {
        // Don't disable action-box, it should always be clickable
        const isActionBox = box.classList.contains("action-box");
        
        if (isSelected) {
          box.classList.remove("disabled");
          box.style.pointerEvents = "auto";
        } else if (!isActionBox) {
          box.classList.add("disabled");
          box.style.pointerEvents = "none";
        }
      });
      
      // Disable Select Batch button for the selected section only
      const selectBatchBtn = section.querySelector(".select-batch-btn");
      if (selectBatchBtn) {
        if (isSelected) {
          selectBatchBtn.disabled = true;
        } else {
          selectBatchBtn.disabled = false;
        }
      }
    });
    
    showNotification(`${window.pendingSelectBatchName} selected successfully!`, "success");
    
    window.pendingSelectSection = null;
    window.pendingSelectBatchName = null;
  }
  closeSelectTemplateModal();
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  // Initialize modal elements
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

  selectTemplateModal = document.getElementById("select-template-modal-overlay");
  confirmSelectTemplateBtn = document.getElementById("confirm-select-template-btn");
  cancelSelectTemplateBtn = document.getElementById("cancel-select-template-btn");

  // Debug: Check if modal elements exist
  console.log("downloadPdfModal:", downloadPdfModal);
  console.log("deleteBatchModal:", deleteBatchModal);
  console.log("selectTemplateModal:", selectTemplateModal);
  console.log("confirmDownloadPdfBtn:", confirmDownloadPdfBtn);
  console.log("confirmDeleteBatchBtn:", confirmDeleteBatchBtn);
  console.log("confirmSelectTemplateBtn:", confirmSelectTemplateBtn);

  // Make these variables globally accessible for dynamic section generation
  window.currentXhrs = [];
  window.isUploadCancelled = false;

  // Query sections before restoration
  let sections = document.querySelectorAll(".form-group .section");
  let sectionHeaders = document.querySelectorAll(
    ".form-group .section .section-header"
  );

  // Function to refresh sections NodeList
  function refreshSections() {
    sections = document.querySelectorAll(".form-group .section");
    sectionHeaders = document.querySelectorAll(".form-group .section .section-header");
  }
  
  // Make refreshSections globally accessible
  window.refreshSections = refreshSections;

  // Store a flag to defer initialization of restored sections
  window.deferSectionInitialization = true;

  // Restore any previously generated sections from localStorage
  restoreGeneratedSectionsFromLocalStorage();
  
  // Refresh sections after restoration
  refreshSections();

  function selectSection(section) {
    sections.forEach((s) => s.classList.remove("selected"));
    
    // Check if section exists and is still in the DOM
    if (section && section.parentNode) {
      section.classList.add("selected");

      const templateName = section
        .querySelector(".section-header")
        .textContent.trim();
      localStorage.setItem("selectedBatchTemplate", templateName);

      const templateMatch = templateName.match(/Batch Template (\d+)/);
      if (templateMatch && templateMatch[1]) {
        const templateNumber = parseInt(templateMatch[1]);
        localStorage.setItem("selectedBatchTemplateNumber", templateNumber);
        console.log("Stored template number:", templateNumber);
      }
    } else {
      console.error("selectSection: section is undefined or not in DOM");
    }

    updateUploadBoxStates();
  }

  function updateUploadBoxStates() {
    const selectedTemplate = document.querySelector(".section.selected");

    sections.forEach((section) => {
      const uploadBoxes = section.querySelectorAll(".upload-box");
      const isSelected = section === selectedTemplate;

      uploadBoxes.forEach((box) => {
        // Don't disable action-box, it should always be clickable
        const isActionBox = box.classList.contains("action-box");
        
        if (isSelected) {
          box.classList.remove("disabled");
          box.style.pointerEvents = "auto";
        } else if (!isActionBox) {
          box.classList.add("disabled");
          box.style.pointerEvents = "none";
        }
      });
      
      // Disable Select Batch button for the selected section only
      const selectBatchBtn = section.querySelector(".select-batch-btn");
      if (selectBatchBtn) {
        if (isSelected) {
          selectBatchBtn.disabled = true;
        } else {
          selectBatchBtn.disabled = false;
        }
      }
    });
  }

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
      // Validate templateLabel is defined
      if (!templateLabel) {
        console.error("selectedConfirmAction: templateLabel is undefined");
        if (typeof showNotification === "function") {
          showNotification("Error: Could not select batch template - invalid label", "error");
        }
        closeDeleteModal();
        return;
      }
      
      // Re-query the DOM to find the section by its header text instead of using captured reference
      // This prevents issues if the section was removed and recreated
      const allSections = document.querySelectorAll(".form-group .section");
      let actualSection = null;
      
      allSections.forEach((section) => {
        const header = section.querySelector(".section-header");
        if (header && header.textContent.trim() === templateLabel) {
          actualSection = section;
        }
      });
      
      if (actualSection && actualSection.parentNode) {
        // Section found and is in the DOM, proceed with selection
        selectSection(actualSection);
        if (typeof showNotification === "function") {
          showNotification(`${templateLabel} selected`, "success");
        }
      } else {
        console.error("selectedConfirmAction: Could not find section with label:", templateLabel);
        if (typeof showNotification === "function") {
          showNotification("Error: Could not select batch template - section not found", "error");
        }
      }
      
      // Always restore modal defaults
      if (titleEl) titleEl.textContent = defaultTitle;
      if (messageEl) messageEl.textContent = defaultMsg;
      if (iconEl) iconEl.className = defaultIcon;
      if (confirmBtnEl) confirmBtnEl.textContent = defaultConfirmText;
      if (cancelBtnEl) cancelBtnEl.textContent = defaultCancelText;
    };

    openDeleteModal();
  }

  // Make openSelectTemplateModal globally accessible for initializeSection
  window.openSelectTemplateModal = openSelectTemplateModal;

  // Initialize all restored sections now that openSelectTemplateModal is available
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

  // Function to set available status based on completion date
  // Only batch years that are complete AND within 3 years of completion are available (green) to students
  async function setAvailableSections() {
    const allSections = document.querySelectorAll(".form-group .section");
    const currentDate = new Date();
    
    // Fetch covers to get completion dates
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
        // Group items by batch_year
        const batchYearDataMap = {};
        data.items.forEach((item) => {
          if (item.batch_year && !batchYearDataMap[item.batch_year]) {
            batchYearDataMap[item.batch_year] = {
              completion_date: item.completion_date,
              slots: []
            };
          }
          if (item.batch_year) {
            batchYearDataMap[item.batch_year].slots.push(item.slot);
          }
        });
        
        // Update sections based on completion date
        allSections.forEach((section) => {
          const header = section.querySelector(".section-header");
          if (!header) return;
          
          const headerText = header.textContent.trim();
          const sectionData = batchYearDataMap[headerText];
          
          if (sectionData && sectionData.completion_date) {
            // Calculate years since completion
            const completionDate = new Date(sectionData.completion_date);
            const yearsSinceCompletion = (currentDate - completionDate) / (1000 * 60 * 60 * 24 * 365);
            
            if (yearsSinceCompletion <= 3) {
              // Within 3 years of completion - mark as available (green)
              section.classList.add("available");
            } else {
              // Older than 3 years - remove available class (will be blue/default)
              section.classList.remove("available");
            }
          } else {
            // No completion date or incomplete - not available
            section.classList.remove("available");
          }
        });
      }
    } catch (error) {
      console.error("Error fetching completion dates:", error);
      // On error, remove available class from all sections
      allSections.forEach((section) => {
        section.classList.remove("available");
      });
    }
  }
  
  // Make setAvailableSections globally accessible
  window.setAvailableSections = setAvailableSections;

  // Set available sections on page load (after all sections are restored)
  setAvailableSections();

  // Section header click event removed - now using Select Batch button

  if (sections.length > 0) {
    const savedTemplate = localStorage.getItem("selectedBatchTemplate");
    let selectedSection = null;

    if (savedTemplate) {
      sections.forEach((section) => {
        const headerText = section
          .querySelector(".section-header")
          .textContent.trim();
        if (headerText === savedTemplate) {
          selectedSection = section;
        }
      });
    }

    if (!selectedSection) {
      selectedSection = sections[0];
    }

    selectSection(selectedSection);
  } else {
    updateUploadBoxStates();
  }

  sections.forEach((section) => {
    initializeSectionUploadBoxes(section, window.currentXhrs, window.isUploadCancelled);
  });

  initializeDeleteModal();

  // Initialize Generate Modal
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

  // Initialize Download PDF Modal
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

  // Initialize Delete Batch Modal
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

  // Initialize Select Template Modal
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
