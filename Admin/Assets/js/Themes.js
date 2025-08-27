// ----------------------
// Theme definitions
// ----------------------
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

// ----------------------
// Theme selection
// ----------------------
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

  const defaultSectionBg = themes["Default"]["--section-bg"];
  const modal = document.querySelector(".modal");
  if (modal) modal.style.background = defaultSectionBg;

  localStorage.setItem("dashboard-theme", theme);
}

// ----------------------
// Upload overlay helpers
// ----------------------
const uploadOverlay = document.getElementById("upload-overlay");

function showUploadOverlay() {
  if (uploadOverlay) uploadOverlay.style.display = "flex";
}
function hideUploadOverlay() {
  if (uploadOverlay) uploadOverlay.style.display = "none";
}

// ----------------------
// Notifications
// ----------------------
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

// ----------------------
// Upload helper
// ----------------------
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
    const res = await fetch(UPLOAD_ENDPOINT, { method: "POST", body: form });
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
    showNotification(err.message || "Upload failed", "error");
  } finally {
    hideUploadOverlay();
  }
}

// ----------------------
// DOM Ready
// ----------------------
window.addEventListener("DOMContentLoaded", () => {
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

  // ----------------------
  // Endpoints (fixed paths)
  // ----------------------
  const BASE_PATH = `${window.location.origin}/ECADYB/Connection`;
  window.UPLOAD_ENDPOINT = `${BASE_PATH}/UploadLogo.php`;
  const FETCH_ENDPOINT = `${BASE_PATH}/FetchLogos.php`;
  const DELETE_ENDPOINT = `${BASE_PATH}/DeleteLogo.php`;

  // ----------------------
  // Logo uploads
  // ----------------------
  const logoBoxes = document.querySelectorAll(
    ".logo-upload-grid .upload-box.circle"
  );

  // delete modal
  const deleteModal = document.getElementById("delete-modal-overlay");
  const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
  const cancelDeleteBtn = document.getElementById("cancel-delete-btn");
  let deleteTarget = null;

  logoBoxes.forEach((box) => {
    const input = box.querySelector(".logoInput");
    const deleteBtn = box.querySelector(".delete-btn");

    box.addEventListener("click", (e) => {
      if (e.target === deleteBtn) return;
      if (!box.classList.contains("has-image")) input.click();
    });

    input.addEventListener("change", async (e) => {
      const file = e.target.files?.[0];
      if (!file) return;
      const slot = Array.from(logoBoxes).indexOf(box) + 1;

      await uploadLogoToBunny(file, slot, box, input, deleteBtn);
    });

    deleteBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      deleteTarget = { box, input, deleteBtn };
      deleteModal.style.display = "flex";
    });
  });

  confirmDeleteBtn.addEventListener("click", async () => {
    if (!deleteTarget) return;
    const { box, input, deleteBtn } = deleteTarget;
    const slot = Array.from(logoBoxes).indexOf(box) + 1;

    try {
      const form = new FormData();
      form.append("slot", String(slot));
      const res = await fetch(DELETE_ENDPOINT, { method: "POST", body: form });
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

  // ----------------------
  // Load existing logos
  // ----------------------
  (async function loadLogos() {
    try {
      const res = await fetch(FETCH_ENDPOINT);
      const data = await res.json();
      if (!data?.success) return;

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
