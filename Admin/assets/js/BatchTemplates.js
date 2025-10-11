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

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  let currentXhr = null;

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
    const sectionUploadBoxes = section.querySelectorAll(".upload-box");
    const sectionHeader = section
      .querySelector(".section-header")
      .textContent.trim();
    let template = 1;
    const templateMatch = sectionHeader.match(/Batch Template (\d+)/);
    if (templateMatch && templateMatch[1]) {
      template = parseInt(templateMatch[1]);
    }

    console.log("Section header:", sectionHeader);
    console.log("Extracted template:", template);

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
      const FETCH_ENDPOINT = `${CONNECTION_PATH}/Cover/FetchCovers.php?template=${template}`;
      const DELETE_ENDPOINT = `${CONNECTION_PATH}/Cover/DeleteCover.php`;

      console.log("BatchTemplates endpoints configured:", {
        UPLOAD_ENDPOINT,
        FETCH_ENDPOINT,
        DELETE_ENDPOINT,
        template,
        slot,
      });

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
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        await uploadToBunny(file, slot, "front");
      });

      if (!isBackgroundSlot)
        backInput.addEventListener("change", async (event) => {
          const file = event.target.files && event.target.files[0];
          if (!file) return;
          await uploadToBunny(file, slot, "back");
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

      function xhrUpload(url, formData) {
        return new Promise((resolve) => {
          const xhr = new XMLHttpRequest();
          currentXhr = xhr;

          xhr.upload.addEventListener("progress", function (e) {
            if (e.lengthComputable) {
              const percentComplete = (e.loaded / e.total) * 100;
              const progressBar = document.getElementById("progressBar");
              const progressPercent =
                document.getElementById("progressPercent");
              if (progressBar) progressBar.style.width = percentComplete + "%";
              if (progressPercent)
                progressPercent.textContent = Math.round(percentComplete) + "%";
            }
          });

          xhr.open("POST", url, true);
          xhr.timeout = 60000;

          xhr.onabort = () => {
            resolve({ aborted: true });
          };

          xhr.ontimeout = () => {
            resolve({ success: false, message: "Upload timed out" });
            currentXhr = null;
          };

          xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
              try {
                if (xhr.status >= 200 && xhr.status < 300) {
                  resolve(JSON.parse(xhr.responseText));
                } else if (xhr.status === 0) {
                  resolve({ success: false, message: "Upload cancelled" });
                } else {
                  resolve({
                    success: false,
                    message: `Error ${xhr.status}`,
                  });
                }
              } catch (e) {
                resolve({ success: false, message: "Network error" });
              }
              currentXhr = null;
            }
          };

          xhr.onerror = () => {
            resolve({ success: false, message: "Connection failed" });
            currentXhr = null;
          };

          xhr.send(formData);
        });
      }

      async function uploadToBunny(file, slot, side) {
        const form = new FormData();
        form.append("file", file);
        form.append("slot", String(slot));
        form.append("side", side);
        form.append("template", String(template));

        console.log("Sending upload request with template:", template);

        currentOperation = "uploading_image";
        showNotification(`Uploading image to Slot ${slot} ${side}...`, "info");

        const uploadOverlay = document.getElementById("upload-overlay");
        const uploadText = document.getElementById("uploadText");

        if (uploadOverlay && uploadText) {
          uploadOverlay.style.display = "flex";
          uploadText.textContent = "Image upload in progress…";
        }

        try {
          const fileName = file.name.toUpperCase();
          let detectedSide = side;

          if (fileName.includes("FRONT")) {
            detectedSide = "front";
          } else if (fileName.includes("BACK")) {
            detectedSide = "back";
          }

          form.set("side", detectedSide);

          const slotMapping = {
            BSME: 1,
            BSCJ: 2,
            BSTM: 3,
            BSE: 4,
            BSN: 5,
            BSIS: 6,
            BSBA: 7,
          };

          let detectedSlot = slot;

          for (const [prefix, slotNum] of Object.entries(slotMapping)) {
            if (fileName.startsWith(prefix)) {
              detectedSlot = slotNum;
              break;
            }
          }

          if (detectedSlot != slot) {
            showNotification(
              `Filename doesn't match slot. Expected slot ${detectedSlot} for this filename.`,
              "error"
            );
            if (uploadOverlay) uploadOverlay.style.display = "none";
            return;
          }

          if (
            slot >= 1 &&
            slot <= 7 &&
            detectedSlot == slot &&
            !fileName.startsWith("BS")
          ) {
            showNotification(
              "Filename must start with a valid prefix (BSME, BSCJ, BSTM, BSE, BSN, BSIS, BSBA).",
              "error"
            );
            if (uploadOverlay) uploadOverlay.style.display = "none";
            return;
          }

          form.set("slot", String(detectedSlot));

          console.log(
            `Auto-detected - Slot: ${detectedSlot}, Side: ${detectedSide} from filename: ${fileName}`
          );

          await new Promise((resolve) => setTimeout(resolve, 10));

          const data = await xhrUpload(UPLOAD_ENDPOINT, form);
          if (data && data.aborted) {
            if (uploadOverlay) uploadOverlay.style.display = "none";
            showNotification("Cancelled upload", "error");
            return;
          }

          if (data && data.message === "Cancelled upload") {
            if (uploadOverlay) uploadOverlay.style.display = "none";
            showNotification("Cancelled upload", "error");
            return;
          }

          if (!data?.success) {
            showNotification(data?.message || "Upload failed", "error");
            return;
          }

          const img = document.createElement("img");
          img.src = data.url || data.public_url;
          img.classList.add(
            detectedSide === "front" ? "front-img" : "back-img"
          );
          if (detectedSide === "front") frontImg = img;
          else backImg = img;

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

          if (detectedSlot === 8) {
            showNotification(
              "Yearbook Backgrounds have been uploaded successfully!",
              "success"
            );
          } else {
            showNotification(
              `Uploaded to Slot ${detectedSlot} ${detectedSide} successfully!`,
              "success"
            );
          }
        } catch (err) {
          console.error("Upload error:", err);
          showNotification(err.message || "Upload failed", "error");
        } finally {
          if (!currentXhr && uploadOverlay)
            uploadOverlay.style.display = "none";
        }
      }

      window.cancelUpload = function () {
        const uploadOverlay = document.getElementById("upload-overlay");
        const progressBar = document.getElementById("progressBar");
        const uploadText = document.getElementById("uploadText");
        const progressPercent = document.getElementById("progressPercent");

        if (currentXhr) {
          try {
            currentXhr.abort();
          } catch (_) {}
          currentXhr = null;
        }

        showNotification("Cancelled upload", "error");

        if (progressBar) progressBar.style.width = "0%";
        if (uploadOverlay) uploadOverlay.style.display = "none";
        if (uploadText)
          uploadText.textContent = "Please wait while we upload your file";
        if (progressPercent) progressPercent.textContent = "0%";
      };

      async function deleteCover(slot, side) {
        try {
          const form = new FormData();
          form.append("slot", String(slot));
          form.append("side", side);
          form.append("template", String(template));

          console.log("Sending delete request with template:", template);
          for (let [key, value] of form.entries()) {
            console.log(key, value);
          }

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

      (async function loadExisting() {
        try {
          // Add a small delay to prevent UI blocking
          await new Promise((resolve) => setTimeout(resolve, 50));

          const res = await fetch(
            `${CONNECTION_PATH}/Cover/FetchCovers.php?template=${template}`,
            {
              // Add timeout to fetch request
              signal: AbortSignal.timeout(10000), // 10 second timeout
            }
          );

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
          // Handle timeout errors specifically
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
  });

  initializeDeleteModal();
});
