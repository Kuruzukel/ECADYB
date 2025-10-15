window.themes = {
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

window.applyTheme = function (theme) {
  console.log("Applying theme:", theme);

  const root = document.documentElement;
  const selectedTheme = window.themes[theme] || window.themes["Default"];

  console.log("Selected theme data:", selectedTheme);

  // Apply CSS variables to the root element
  for (const [varName, color] of Object.entries(selectedTheme)) {
    root.style.setProperty(varName, color);
  }

  const currentSectionBg =
    selectedTheme["--section-bg"] || window.themes["Default"]["--section-bg"];
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

  // Force a repaint
  body.style.display = "none";
  body.offsetHeight;
  body.style.display = "";
};

const uploadOverlay = document.getElementById("upload-overlay");

let currentUploadRequest = null;
let uploadCompleted = false;
let currentUploadSlot = null;
let pendingUploadData = null;
let cancelPendingUpload = false;

function showUploadOverlay() {
  if (uploadOverlay) uploadOverlay.style.display = "flex";
}

function hideUploadOverlay() {
  if (uploadOverlay) uploadOverlay.style.display = "none";
}

async function cancelUpload() {
  cancelPendingUpload = true;

  if (currentUploadRequest) {
    currentUploadRequest.abort();
    currentUploadRequest = null;
  }

  if (pendingUploadData) {
    const { box, input, deleteBtn } = pendingUploadData;
    box.innerHTML = "";
    const newPlus = document.createElement("span");
    newPlus.className = "plus-icon";
    newPlus.textContent = "+";
    box.appendChild(newPlus);
    box.appendChild(deleteBtn);
    box.appendChild(input);
    deleteBtn.style.display = "none";
    input.value = "";
    box.classList.remove("has-image");

    pendingUploadData = null;
  }

  if (currentUploadSlot) {
    try {
      const form = new FormData();
      form.append("slot", String(currentUploadSlot));

      fetch(window.DELETE_ENDPOINT, {
        method: "POST",
        body: form,
      }).catch((err) => {
        console.warn("Failed to delete uploaded file:", err);
      });
    } catch (err) {
      console.warn("Error preparing deletion request:", err);
    }
  }

  await new Promise((resolve) => setTimeout(resolve, 100));

  showNotification("Upload cancelled", "error");

  uploadCompleted = false;
  currentUploadSlot = null;
  cancelPendingUpload = false;
  hideUploadOverlay();
}

// Smart notification system variables
let notificationTimeout = null;
let currentOperation = null;

function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  // Remove any existing notifications to prevent duplicates
  const existingNotifications = container.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  // Clear any existing notification timeout
  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }

  // Select icon based on notification type
  let icon = "fa-check-circle"; // default for success
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

  // Make notification visible for different durations based on type
  const duration = type === "info" ? 2000 : 5000; // Info notifications disappear faster
  notificationTimeout = setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => {
      notif.remove();
      notificationTimeout = null;
      currentOperation = null; // Clear current operation
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

async function uploadLogoToBunny(file, slot, box, input, deleteBtn) {
  if (cancelPendingUpload) {
    box.innerHTML = "";
    const newPlus = document.createElement("span");
    newPlus.className = "plus-icon";
    newPlus.textContent = "+";
    box.appendChild(newPlus);
    box.appendChild(deleteBtn);
    box.appendChild(input);
    deleteBtn.style.display = "none";
    input.value = "";
    box.classList.remove("has-image");

    pendingUploadData = null;
    cancelPendingUpload = false;
    showNotification("Upload cancelled", "error");
    return;
  }

  const form = new FormData();
  form.append("file", file);
  form.append("slot", String(slot));
  form.append("type", "logo_container");

  const uploadText = document.getElementById("uploadText");
  if (uploadOverlay && uploadText) {
    uploadOverlay.style.display = "flex";
    uploadText.textContent = "Please wait while we upload your logo";
  }

  uploadCompleted = false;
  currentUploadSlot = slot;

  const controller = new AbortController();
  currentUploadRequest = controller;

  try {
    const res = await fetch(window.UPLOAD_ENDPOINT, {
      method: "POST",
      body: form,
      signal: controller.signal,
    });

    currentUploadRequest = null;

    if (controller.signal.aborted) {
      throw new Error("Upload aborted");
    }

    if (cancelPendingUpload) {
      throw new Error("Upload cancelled");
    }

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }

    const data = await res.json();

    if (!data?.success) {
      showNotification(data?.message || "Upload failed", "error");
      return;
    }

    uploadCompleted = true;

    box.innerHTML = "";
    const img = document.createElement("img");
    img.src = data.url;
    img.alt = "Logo";
    box.appendChild(img);
    box.appendChild(deleteBtn);
    box.appendChild(input);

    box.classList.add("has-image");
    deleteBtn.style.display = "flex";

    showNotification("Logo uploaded successfully", "success");
  } catch (err) {
    currentUploadRequest = null;

    if (
      err.name === "AbortError" ||
      err.message === "Upload aborted" ||
      err.message === "Upload cancelled" ||
      controller.signal.aborted ||
      cancelPendingUpload
    ) {
      showNotification("Upload cancelled", "error");
      box.innerHTML = "";
      const newPlus = document.createElement("span");
      newPlus.className = "plus-icon";
      newPlus.textContent = "+";
      box.appendChild(newPlus);
      box.appendChild(deleteBtn);
      box.appendChild(input);
      deleteBtn.style.display = "none";
      input.value = "";
      box.classList.remove("has-image");

      uploadCompleted = false;
      currentUploadSlot = null;
      cancelPendingUpload = false;
    } else {
      console.error("Upload error:", err);
      showNotification(err.message || "Upload failed", "error");

      box.innerHTML = "";
      const newPlus = document.createElement("span");
      newPlus.className = "plus-icon";
      newPlus.textContent = "+";
      box.appendChild(newPlus);
      box.appendChild(deleteBtn);
      box.appendChild(input);
      deleteBtn.style.display = "none";
      input.value = "";
      box.classList.remove("has-image");

      uploadCompleted = false;
      currentUploadSlot = null;
      cancelPendingUpload = false;
    }
  } finally {
    currentUploadRequest = null;
    hideUploadOverlay();
    pendingUploadData = null;
  }
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  window.applyTheme(savedTheme);

  const selectedBox = document.querySelector(
    `.color-box[data-label="${savedTheme}"]`
  );
  if (selectedBox) selectedBox.classList.add("selected");

  const confirmBtn = document.getElementById("confirm-btn");
  const cancelBtn = document.getElementById("cancel-btn");
  const modalOverlay = document.getElementById("modal-overlay");

  confirmBtn?.addEventListener("click", () => {
    if (pendingTheme) {
      window.applyTheme(pendingTheme);
      showNotification("Theme applied successfully");
      pendingTheme = null;
    }
    modalOverlay.style.display = "none";
  });

  cancelBtn?.addEventListener("click", () => {
    pendingTheme = null;
    modalOverlay.style.display = "none";

    document
      .querySelectorAll(".color-box")
      .forEach((box) => box.classList.remove("selected"));
    const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
    const selectedBox = document.querySelector(
      `.color-box[data-label="${savedTheme}"]`
    );
    if (selectedBox) selectedBox.classList.add("selected");
  });

  const BASE_PATH = getBasePath();
  const CONNECTION_PATH = `${BASE_PATH}/Connection`;

  window.UPLOAD_ENDPOINT = `${CONNECTION_PATH}/Logo/UploadLogo.php`;
  window.FETCH_ENDPOINT = `${CONNECTION_PATH}/Logo/FetchLogos.php`;
  window.DELETE_ENDPOINT = `${CONNECTION_PATH}/Logo/DeleteLogo.php`;
  window.UPDATE_ADMIN_LOGO_ENDPOINT = `${CONNECTION_PATH}/Logo/UpdateAdminLogo.php`;

  console.log("Endpoints configured:", {
    UPLOAD_ENDPOINT: window.UPLOAD_ENDPOINT,
    FETCH_ENDPOINT: window.FETCH_ENDPOINT,
    DELETE_ENDPOINT: window.DELETE_ENDPOINT,
    UPDATE_ADMIN_LOGO_ENDPOINT: window.UPDATE_ADMIN_LOGO_ENDPOINT,
  });

  const logoBoxes = document.querySelectorAll(
    ".logo-upload-grid .upload-box.circle"
  );

  const deleteModal = document.getElementById("delete-modal-overlay");
  const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
  const cancelDeleteBtn = document.getElementById("cancel-delete-btn");
  let deleteTarget = null;

  const changeAdminLogoModal = document.getElementById(
    "change-admin-logo-modal"
  );
  const confirmChangeLogoBtn = document.getElementById(
    "confirm-change-logo-btn"
  );
  const cancelChangeLogoBtn = document.getElementById("cancel-change-logo-btn");
  const previewLogo = document.getElementById("preview-logo");
  let changeLogoTarget = null;

  const handleFileSelect = (input, box) => {
    const file = input.files[0];
    if (!file) return;

    showUploadOverlay();

    const deleteBtn = document.createElement("button");
    deleteBtn.className = "delete-btn";
    deleteBtn.innerHTML = "&times;";

    deleteBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      deleteTarget = {
        box: box,
        slot: box.dataset.slot,
        input: input,
        deleteBtn: deleteBtn,
      };
      deleteModal.style.display = "flex";
    });

    pendingUploadData = {
      file: file,
      slot: box.dataset.slot || "1",
      box: box,
      input: input,
      deleteBtn: deleteBtn,
    };

    if (cancelPendingUpload) {
      box.innerHTML = "";
      const newPlus = document.createElement("span");
      newPlus.className = "plus-icon";
      newPlus.textContent = "+";
      box.appendChild(newPlus);
      box.appendChild(deleteBtn);
      box.appendChild(input);
      deleteBtn.style.display = "none";
      input.value = "";
      box.classList.remove("has-image");

      pendingUploadData = null;
      cancelPendingUpload = false;
      return;
    }

    uploadLogoToBunny(file, box.dataset.slot || "1", box, input, deleteBtn);
  };

  logoBoxes.forEach((box, index) => {
    const input = box.querySelector(".logoInput");
    const deleteBtn = box.querySelector(".delete-btn");

    if (!box.dataset.slot) {
      box.dataset.slot = (index + 1).toString();
    }

    box.addEventListener("click", (e) => {
      if (e.target === deleteBtn || e.target.classList.contains("delete-btn"))
        return;

      if (box.classList.contains("has-image")) {
        const logoImg = box.querySelector("img");
        if (logoImg && logoImg.src) {
          previewLogo.src = logoImg.src;
          changeAdminLogoModal.style.display = "flex";
          changeLogoTarget = {
            box: box,
            logoUrl: logoImg.src,
            slot: box.dataset.slot,
          };
          return;
        }
      }

      input.click();
    });

    input.addEventListener("change", (e) => {
      const file = e.target.files?.[0];
      if (file) {
        handleFileSelect(input, box);
      }
    });

    if (deleteBtn) {
      deleteBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        deleteTarget = {
          box: box,
          slot: box.dataset.slot,
          input: input,
          deleteBtn: deleteBtn,
        };
        deleteModal.style.display = "flex";
      });
    }
  });

  if (confirmChangeLogoBtn) {
    confirmChangeLogoBtn.addEventListener("click", async () => {
      if (!changeLogoTarget) return;

      try {
        const form = new FormData();
        form.append("logo_url", changeLogoTarget.logoUrl);
        form.append("slot", String(changeLogoTarget.slot));

        const res = await fetch(UPDATE_ADMIN_LOGO_ENDPOINT, {
          method: "POST",
          body: form,
        });

        if (!res.ok) {
          throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }

        const data = await res.json();

        if (data?.success) {
          showNotification(
            "Admin dashboard logo changed successfully!",
            "success"
          );

          localStorage.setItem("admin-logo-url", changeLogoTarget.logoUrl);

          const adminLogo = document.querySelector(".sidebar .logoadmin");
          if (adminLogo) {
            adminLogo.src = changeLogoTarget.logoUrl;
          }
        } else {
          throw new Error(data?.message || "Failed to change admin logo");
        }
      } catch (err) {
        console.error("Change admin logo error:", err);
        showNotification(err.message || "Failed to change admin logo", "error");
      } finally {
        if (changeAdminLogoModal) {
          changeAdminLogoModal.style.display = "none";
        }
        changeLogoTarget = null;
      }
    });
  }

  if (cancelChangeLogoBtn) {
    cancelChangeLogoBtn.addEventListener("click", () => {
      changeLogoTarget = null;
      if (changeAdminLogoModal) {
        changeAdminLogoModal.style.display = "none";
      }
    });
  }

  changeAdminLogoModal.addEventListener("click", (e) => {
    if (e.target === changeAdminLogoModal) {
      changeLogoTarget = null;
      changeAdminLogoModal.style.display = "none";
    }
  });

  confirmDeleteBtn.addEventListener("click", async () => {
    if (!deleteTarget) return;
    const { box, input, deleteBtn } = deleteTarget;
    const slot = box.dataset.slot;

    deleteModal.style.display = "none";
    hideUploadOverlay();

    box.innerHTML = "";
    const newPlus = document.createElement("span");
    newPlus.className = "plus-icon";
    newPlus.textContent = "+";
    box.appendChild(newPlus);
    box.appendChild(deleteBtn);
    box.appendChild(input);
    deleteBtn.style.display = "none";
    input.value = "";
    box.classList.remove("has-image");

    showNotification("Logo deleted successfully", "success");

    try {
      const form = new FormData();
      form.append("slot", String(slot));
      const res = await fetch(window.DELETE_ENDPOINT, {
        method: "POST",
        body: form,
      });

      if (!res.ok) {
        console.error(`HTTP ${res.status}: ${res.statusText}`);
      }

      const data = await res.json();

      if (!data?.success) {
        console.error(data?.message || "Delete failed");
      }
    } catch (err) {
      console.error("Delete error:", err);
    } finally {
      deleteTarget = null;
    }
  });

  cancelDeleteBtn.addEventListener("click", () => {
    deleteTarget = null;
    deleteModal.style.display = "none";
  });

  (async function loadLogos() {
    try {
      const res = await fetch(window.FETCH_ENDPOINT);

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }

      const data = await res.json();
      if (!data?.success) {
        console.warn("Failed to load logos:", data?.message);
        return;
      }

      const bySlot = new Map((data.items || []).map((i) => [i.slot, i.url]));
      logoBoxes.forEach((box, idx) => {
        const url = bySlot.get(idx + 1);
        if (!url) return;

        const input = box.querySelector(".logoInput");
        const deleteBtn = box.querySelector(".delete-btn");

        const img = document.createElement("img");
        img.src = url;
        box.innerHTML = "";
        box.appendChild(img);
        box.appendChild(deleteBtn);
        box.appendChild(input);
        deleteBtn.style.display = "flex";
        box.classList.add("has-image");

        if (deleteBtn) {
          deleteBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            deleteTarget = {
              box: box,
              slot: box.dataset.slot,
              input: input,
              deleteBtn: deleteBtn,
            };
            deleteModal.style.display = "flex";
          });
        }
      });
    } catch (err) {
      console.error("Failed to load logos:", err);
    }
  })();
});
