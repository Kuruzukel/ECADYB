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

  // Apply CSS custom properties
  for (const [varName, color] of Object.entries(selectedTheme)) {
    root.style.setProperty(varName, color);
  }

  // Update modal background to match current theme
  const currentSectionBg =
    selectedTheme["--section-bg"] || themes["Default"]["--section-bg"];
  const modal = document.querySelector(".modal");
  if (modal) modal.style.background = currentSectionBg;

  // Add/remove theme-specific CSS classes to body
  const body = document.body;
  // Remove all theme classes first
  body.classList.remove("theme-light-mode", "theme-dark-mode");

  // Add specific theme class
  if (theme === "Light Mode") {
    body.classList.add("theme-light-mode");
  } else if (theme === "Dark Mode") {
    body.classList.add("theme-dark-mode");
  }

  // Save theme to localStorage
  localStorage.setItem("dashboard-theme", theme);

  console.log("Theme applied and saved:", theme);

  // Force a style recalculation
  document.body.style.display = "none";
  document.body.offsetHeight; // Trigger reflow
  document.body.style.display = "";
}

// Upload overlay helpers
const uploadOverlay = document.getElementById("upload-overlay");

function showUploadOverlay() {
  if (uploadOverlay) uploadOverlay.style.display = "flex";
}
function hideUploadOverlay() {
  if (uploadOverlay) uploadOverlay.style.display = "none";
}

// Notifications
function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const notif = document.createElement("div");
  notif.className = `notification ${type} show`;
  notif.innerHTML = `
    <i class="fas ${
      type === "success"
        ? "fa-check-circle"
        : type === "warning"
        ? "fa-exclamation-triangle"
        : "fa-exclamation-circle"
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

// Upload helper
async function uploadLogoToBunny(file, slot, box, input, deleteBtn) {
  const form = new FormData();
  form.append("file", file);
  form.append("slot", String(slot));
  form.append("type", "logo_container");

  const uploadText = document.getElementById("uploadText");
  if (uploadOverlay && uploadText) {
    uploadOverlay.style.display = "flex";
    uploadText.textContent = "Please wait while we upload your logo";
  }

  try {
    const res = await fetch(window.UPLOAD_ENDPOINT, {
      method: "POST",
      body: form,
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}: ${res.statusText}`);
    }

    const data = await res.json();

    if (!data?.success) {
      showNotification(data?.message || "Upload failed", "error");
      return;
    }

    // Insert logo
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
    console.error("Upload error:", err);
    showNotification(err.message || "Upload failed", "error");
  } finally {
    hideUploadOverlay();
  }
}

// DOM Ready
window.addEventListener("DOMContentLoaded", () => {
  // Apply saved theme
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  const selectedBox = document.querySelector(
    `.color-box[data-label="${savedTheme}"]`
  );
  if (selectedBox) selectedBox.classList.add("selected");

  // modal buttons
  const confirmBtn = document.getElementById("confirm-btn");
  const cancelBtn = document.getElementById("cancel-btn");
  const modalOverlay = document.getElementById("modal-overlay");

  confirmBtn?.addEventListener("click", () => {
    if (pendingTheme) {
      applyTheme(pendingTheme);
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

  // Endpoints (improved path detection)
  const BASE_PATH = getBasePath();
  const CONNECTION_PATH = `${BASE_PATH}/Connection`;

  window.UPLOAD_ENDPOINT = `${CONNECTION_PATH}/UploadLogo.php`;
  const FETCH_ENDPOINT = `${CONNECTION_PATH}/FetchLogos.php`;
  const DELETE_ENDPOINT = `${CONNECTION_PATH}/DeleteLogo.php`;
  const UPDATE_ADMIN_LOGO_ENDPOINT = `${CONNECTION_PATH}/UpdateAdminLogo.php`;

  console.log("Endpoints configured:", {
    UPLOAD_ENDPOINT: window.UPLOAD_ENDPOINT,
    FETCH_ENDPOINT: FETCH_ENDPOINT,
    DELETE_ENDPOINT: DELETE_ENDPOINT,
    UPDATE_ADMIN_LOGO_ENDPOINT: UPDATE_ADMIN_LOGO_ENDPOINT,
  });

  // Logo uploads
  const logoBoxes = document.querySelectorAll(
    ".logo-upload-grid .upload-box.circle"
  );

  // delete modal
  const deleteModal = document.getElementById("delete-modal-overlay");
  const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
  const cancelDeleteBtn = document.getElementById("cancel-delete-btn");
  let deleteTarget = null;

  // change admin logo modal
  const changeAdminLogoModal = document.getElementById(
    "change-admin-logo-modal"
  );
  const confirmChangeLogoBtn = document.getElementById(
    "confirm-change-logo-btn"
  );
  const cancelChangeLogoBtn = document.getElementById("cancel-change-logo-btn");
  const previewLogo = document.getElementById("preview-logo");
  let changeLogoTarget = null;

  // Function to handle file selection
  const handleFileSelect = (input, box) => {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = document.createElement("img");
      img.src = e.target.result;
      img.alt = "Logo Preview";

      // Clear previous content
      box.innerHTML = "";
      box.appendChild(img);
      box.classList.add("has-image");

      // Show the delete button
      const deleteBtn = document.createElement("button");
      deleteBtn.className = "delete-btn";
      deleteBtn.innerHTML = "&times;";
      box.appendChild(deleteBtn);

      // Upload the file
      uploadLogoToBunny(file, box.dataset.slot || "1", box, input, deleteBtn);
    };
    reader.readAsDataURL(file);
  };

  // Initialize logo boxes
  logoBoxes.forEach((box, index) => {
    const input = box.querySelector(".logoInput");
    const deleteBtn = box.querySelector(".delete-btn");

    // Set data-slot attribute if not already set
    if (!box.dataset.slot) {
      box.dataset.slot = (index + 1).toString();
    }

    // Handle click on the box
    box.addEventListener("click", (e) => {
      // Don't trigger if clicking on delete button
      if (e.target === deleteBtn || e.target.classList.contains("delete-btn"))
        return;

      // If logo exists, show change admin logo modal
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

      // Otherwise, trigger file input
      input.click();
    });

    // Handle file selection
    input.addEventListener("change", (e) => {
      const file = e.target.files?.[0];
      if (file) {
        handleFileSelect(input, box);
      }
    });

    // Handle delete button click
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

  // Handle change admin logo confirmation
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

          // Update the admin dashboard logo if we're on the same page
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

  // Handle change admin logo cancellation
  if (cancelChangeLogoBtn) {
    cancelChangeLogoBtn.addEventListener("click", () => {
      changeLogoTarget = null;
      if (changeAdminLogoModal) {
        changeAdminLogoModal.style.display = "none";
      }
    });
  }

  // Close change admin logo modal when clicking outside
  changeAdminLogoModal.addEventListener("click", (e) => {
    if (e.target === changeAdminLogoModal) {
      changeLogoTarget = null;
      changeAdminLogoModal.style.display = "none";
    }
  });

  confirmDeleteBtn.addEventListener("click", async () => {
    if (!deleteTarget) return;
    const { box, input, deleteBtn } = deleteTarget;
    const slot = Array.from(logoBoxes).indexOf(box) + 1;

    try {
      const form = new FormData();
      form.append("slot", String(slot));
      const res = await fetch(DELETE_ENDPOINT, { method: "POST", body: form });

      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }

      const data = await res.json();

      if (!data?.success) throw new Error(data?.message || "Delete failed");
      showNotification("Logo deleted successfully", "success");

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
    } catch (err) {
      console.error("Delete error:", err);
      showNotification(err.message || "Delete failed", "error");
    } finally {
      hideUploadOverlay();
      deleteModal.style.display = "none";
      deleteTarget = null;
    }
  });

  cancelDeleteBtn.addEventListener("click", () => {
    deleteTarget = null;
    deleteModal.style.display = "none";
  });

  // Load existing logos
  (async function loadLogos() {
    try {
      const res = await fetch(FETCH_ENDPOINT);

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
      });
    } catch (err) {
      console.error("Failed to load logos:", err);
    }
  })();
});
