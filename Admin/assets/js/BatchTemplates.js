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

const deleteModal = document.getElementById("delete-modal-overlay");
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const cancelDeleteBtn = document.getElementById("cancel-delete-btn");

const generateModal = document.getElementById("generate-modal-overlay");
const confirmGenerateBtn = document.getElementById("confirm-generate-btn");
const cancelGenerateBtn = document.getElementById("cancel-generate-btn");

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

  showNotification(`Batch Year ${nextYear} created successfully!`, "success");
  
  // Scroll to the new section
  newSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function initializeSection(section) {
  const sectionHeader = section.querySelector(".section-header");
  
  // Add click event to section header
  sectionHeader.addEventListener("click", (e) => {
    const alreadySelected = section.classList.contains("selected");
    if (alreadySelected) return;
    const label = sectionHeader.textContent?.trim() || "Batch Template";
    openSelectTemplateModal(section, label);
  });

  // Update upload box states
  updateUploadBoxStates();
}

function initializeSectionUploadBoxes(section, currentXhrs, isUploadCancelled) {
  const sectionUploadBoxes = section.querySelectorAll(".upload-box");
  const sectionHeader = section.querySelector(".section-header").textContent.trim();

  sectionUploadBoxes.forEach((box, index) => {
    const frontInput = box.querySelector(".frontInput");
    const backInput = box.querySelector(".backInput");
    const deleteBtn = box.querySelector(".delete-btn");
    const plusIcon = box.querySelector(".plus-icon");

    let frontImg = null;
    let backImg = null;
    let showingFront = true;
    const slot = index + 1;
    const isBackgroundSlot = slot === 8;

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
        await uploadToBunny(files[0], slot, "front", true, false);
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
          }
        } else if (files.length === 1) {
          const result = await uploadToBunny(files[0], slot, "front", !suppressNotifications, false);
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
          signal: AbortSignal.timeout(10000),
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

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  // Make these variables globally accessible for dynamic section generation
  window.currentXhrs = [];
  window.isUploadCancelled = false;

  const sections = document.querySelectorAll(".form-group .section");
  const sectionHeaders = document.querySelectorAll(
    ".form-group .section .section-header"
  );

  function selectSection(section) {
    sections.forEach((s) => s.classList.remove("selected"));
    if (section) section.classList.add("selected");

    if (section) {
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
    }

    updateUploadBoxStates();
  }

  function updateUploadBoxStates() {
    const selectedTemplate = document.querySelector(".section.selected");

    sections.forEach((section) => {
      const uploadBoxes = section.querySelectorAll(".upload-box");
      const isSelected = section === selectedTemplate;

      uploadBoxes.forEach((box) => {
        if (isSelected) {
          box.classList.remove("disabled");
          box.style.pointerEvents = "auto";
        } else {
          box.classList.add("disabled");
          box.style.pointerEvents = "none";
        }
      });
    });
  }

  function openSelectTemplateModal(targetSection, templateLabel) {
    if (!deleteModal) return;

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
      selectSection(targetSection);
      if (typeof showNotification === "function") {
        showNotification(`${templateLabel} selected`, "success");
      }
      if (titleEl) titleEl.textContent = defaultTitle;
      if (messageEl) messageEl.textContent = defaultMsg;
      if (iconEl) iconEl.className = defaultIcon;
      if (confirmBtnEl) confirmBtnEl.textContent = defaultConfirmText;
      if (cancelBtnEl) cancelBtnEl.textContent = defaultCancelText;
    };

    openDeleteModal();
  }

  sectionHeaders.forEach((header, idx) => {
    header.addEventListener("click", (e) => {
      const section = header.closest(".section");
      const alreadySelected = section.classList.contains("selected");
      if (alreadySelected) return;
      const label = header.textContent?.trim() || `Batch Template ${idx + 1}`;
      openSelectTemplateModal(section, label);
    });
  });

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
});
