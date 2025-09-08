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

// Notifications
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

// Endpoint detection
function getBasePath() {
  const currentPath = window.location.pathname;

  // Check if we're on Railway (no /ECADYB in path)
  if (currentPath.includes("/Admin/")) {
    // Extract the base path up to /Admin/
    const adminIndex = currentPath.indexOf("/Admin/");
    return currentPath.substring(0, adminIndex);
  }

  // Fallback for localhost or other setups
  return window.location.origin;
}

// Delete student modal
const deleteModal = document.getElementById("delete-modal-overlay");
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const cancelDeleteBtn = document.getElementById("cancel-delete-btn");

let selectedStudentId = null;
let selectedCollection = null;
let selectedConfirmAction = null; // for image deletion in BatchTemplates

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
    //  Fixed path handling
    const BASE_PATH = getBasePath();
    const CONNECTION_PATH = `${BASE_PATH}/Connection`;

    const res = await fetch(`${CONNECTION_PATH}/DeleteStudent.php`, {
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

  const uploadBoxes = document.querySelectorAll(".upload-box");

  // Section selection via header click
  const sections = document.querySelectorAll(".form-group .section");
  const sectionHeaders = document.querySelectorAll(
    ".form-group .section .section-header"
  );

  function selectSection(section) {
    sections.forEach((s) => s.classList.remove("selected"));
    if (section) section.classList.add("selected");
  }

  // Open modal specifically for selecting a different batch template
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
      // restore defaults
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

  // Default select Batch Template 1 (first section)
  if (sections.length > 0) {
    selectSection(sections[0]);
  }
  uploadBoxes.forEach((box, index) => {
    const frontInput = box.querySelector(".frontInput");
    const backInput = box.querySelector(".backInput");
    const deleteBtn = box.querySelector(".delete-btn");
    const plusIcon = box.querySelector(".plus-icon");

    let frontImg = null;
    let backImg = null;
    let showingFront = true;
    const slot = index + 1;
    const template = 1;
    const isBackgroundSlot = slot === 8;

    // ✅ Fixed path handling
    const BASE_PATH = getBasePath();
    const CONNECTION_PATH = `${BASE_PATH}/Connection`;

    const UPLOAD_ENDPOINT = `${CONNECTION_PATH}/UploadCover.php`;
    const FETCH_ENDPOINT = `${CONNECTION_PATH}/FetchCovers.php?template=${template}`;
    const DELETE_ENDPOINT = `${CONNECTION_PATH}/DeleteCover.php`;

    console.log("BatchTemplates endpoints configured:", {
      UPLOAD_ENDPOINT,
      FETCH_ENDPOINT,
      DELETE_ENDPOINT,
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

    async function uploadToBunny(file, slot, side) {
      const form = new FormData();
      form.append("file", file);
      form.append("slot", String(slot));
      form.append("side", side);
      form.append("template", String(template));

      const uploadOverlay = document.getElementById("upload-overlay");
      const uploadText = document.getElementById("uploadText");

      if (uploadOverlay && uploadText) {
        uploadOverlay.style.display = "flex";
        uploadText.textContent = "Please wait while we upload your file";
      }

      try {
        const data = await xhrUpload(UPLOAD_ENDPOINT, form);
        if (data && data.aborted) {
          if (uploadOverlay) uploadOverlay.style.display = "none";
          return;
        }
        if (!data?.success) {
          showNotification(data?.message || "Upload failed", "error");
          return;
        }

        const img = document.createElement("img");
        img.src = data.url;
        img.classList.add(side === "front" ? "front-img" : "back-img");
        if (side === "front") frontImg = img;
        else backImg = img;

        box.innerHTML = "";
        if (plusIcon) plusIcon.remove();
        ensureChildren();
        deleteBtn.style.display = "flex";
        box.classList.add("has-image");

        showingFront = true;
        if (frontImg) {
          frontImg.style.opacity = 1;
          if (backImg && !isBackgroundSlot) backImg.style.opacity = 0;
        }

        showNotification("Image uploaded successfully", "success");
      } catch (err) {
        console.error("Upload error:", err);
        showNotification(err.message || "Upload failed", "error");
      } finally {
        if (!currentXhr && uploadOverlay) uploadOverlay.style.display = "none";
      }
    }

    function xhrUpload(url, formData) {
      return new Promise((resolve) => {
        const xhr = new XMLHttpRequest();
        currentXhr = xhr;
        xhr.open("POST", url, true);
        xhr.onabort = () => {
          resolve({ aborted: true });
        };
        xhr.onreadystatechange = () => {
          if (xhr.readyState === 4) {
            try {
              if (xhr.status >= 200 && xhr.status < 300) {
                resolve(JSON.parse(xhr.responseText));
              } else {
                resolve({
                  success: false,
                  message: `HTTP ${xhr.status}: ${xhr.statusText}`,
                });
              }
            } catch (e) {
              resolve({ success: false, message: "Invalid response format" });
            }
            currentXhr = null;
          }
        };
        xhr.onerror = () => {
          resolve({ success: false, message: "Network error" });
          currentXhr = null;
        };
        xhr.send(formData);
      });
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
        showNotification("Upload canceled", "error");
      }
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
        const res = await fetch(FETCH_ENDPOINT);

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
        console.error("Failed to load existing covers:", e);
      }
    })();
  });

  initializeDeleteModal();
});
