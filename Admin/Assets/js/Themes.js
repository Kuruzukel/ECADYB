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

let pendingTheme = null; // store selected theme until confirmed

function selectColor(el) {
  // highlight selected box
  document
    .querySelectorAll(".color-box")
    .forEach((box) => box.classList.remove("selected"));
  el.classList.add("selected");

  // save the theme to apply later
  pendingTheme = el.getAttribute("data-label");

  // show modal
  document.getElementById("modal-overlay").style.display = "flex";
}

function applyTheme(theme) {
  const root = document.documentElement;
  const selectedTheme = themes[theme] || themes["Default"];

  // Apply theme colors
  for (const [varName, color] of Object.entries(selectedTheme)) {
    root.style.setProperty(varName, color);
  }

  // Always lock modal background to Default theme's section-bg
  const defaultSectionBg = themes["Default"]["--section-bg"];
  const modal = document.querySelector(".modal");
  if (modal) {
    modal.style.background = defaultSectionBg;
  }

  localStorage.setItem("dashboard-theme", theme);
}

window.addEventListener("DOMContentLoaded", () => {
  // load saved theme
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

  confirmBtn.addEventListener("click", () => {
    if (pendingTheme) {
      applyTheme(pendingTheme);
      pendingTheme = null;
    }
    modalOverlay.style.display = "none";
  });

  cancelBtn.addEventListener("click", () => {
    pendingTheme = null;
    modalOverlay.style.display = "none";
    // remove accidental highlight if cancelled
    document
      .querySelectorAll(".color-box")
      .forEach((box) => box.classList.remove("selected"));
    const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
    const selectedBox = document.querySelector(
      `.color-box[data-label="${savedTheme}"]`
    );
    if (selectedBox) selectedBox.classList.add("selected");
  });

  // Logo circular upload behavior (reusing BatchTemplates logic simplified)
  const logoBoxes = document.querySelectorAll(".logo-upload-grid .upload-box.circle");
  const UPLOAD_ENDPOINT = `${window.location.origin}/ECADYB/Connection/UploadLogo.php`;
  const FETCH_ENDPOINT = `${window.location.origin}/ECADYB/Connection/FetchLogos.php`;
  const DELETE_ENDPOINT = `${window.location.origin}/ECADYB/Connection/DeleteLogo.php`;

  // Upload overlay controls
  const uploadOverlay = document.getElementById('upload-overlay');
  const uploadModal = document.getElementById('uploadModal');
  function showUploadOverlay() { if (uploadOverlay) uploadOverlay.style.display = 'flex'; }
  function hideUploadOverlay() { if (uploadOverlay) uploadOverlay.style.display = 'none'; }
  logoBoxes.forEach((box) => {
    const input = box.querySelector('.logoInput');
    const deleteBtn = box.querySelector('.delete-btn');
    const plusIcon = box.querySelector('.plus-icon');

    let imgEl = null;

    box.addEventListener('click', (e) => {
      if (e.target === deleteBtn) return;
      if (!imgEl) input.click();
    });

    input.addEventListener('change', async (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      const slot = Array.from(logoBoxes).indexOf(box) + 1; // 1..9
      const form = new FormData();
      form.append('file', file);
      form.append('slot', String(slot));
      try {
        showUploadOverlay();
        const res = await fetch(UPLOAD_ENDPOINT, { method: 'POST', body: form });
        const data = await res.json();
        if (!data?.success) throw new Error(data?.message || 'Upload failed');
        imgEl = document.createElement('img');
        imgEl.src = data.url;
        box.innerHTML = '';
        box.appendChild(imgEl);
        box.appendChild(deleteBtn);
        box.appendChild(input);
        deleteBtn.style.display = '';
        box.classList.add('has-image');
      } catch (err) {
        alert(err.message || 'Upload failed');
      } finally {
        hideUploadOverlay();
      }
    });

    deleteBtn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const slot = Array.from(logoBoxes).indexOf(box) + 1;
      try {
        showUploadOverlay();
        const form = new FormData();
        form.append('slot', String(slot));
        const res = await fetch(DELETE_ENDPOINT, { method: 'POST', body: form });
        const data = await res.json();
        if (!data?.success) throw new Error(data?.message || 'Delete failed');
      } catch (err) {
        alert(err.message || 'Delete failed');
        return;
      } finally {
        hideUploadOverlay();
      }
      imgEl = null;
      box.innerHTML = '';
      const newPlus = document.createElement('span');
      newPlus.className = 'plus-icon';
      newPlus.textContent = '+';
      box.appendChild(newPlus);
      box.appendChild(deleteBtn);
      box.appendChild(input);
      deleteBtn.style.display = 'none';
      input.value = '';
      box.classList.remove('has-image');
    });
  });

  // Load existing logos
  (async function loadLogos() {
    try {
      const res = await fetch(FETCH_ENDPOINT);
      const data = await res.json();
      if (!data?.success) return;
      const bySlot = new Map((data.items || []).map(i => [i.slot, i.url]));
      logoBoxes.forEach((box, idx) => {
        const url = bySlot.get(idx + 1);
        if (!url) return;
        const input = box.querySelector('.logoInput');
        const deleteBtn = box.querySelector('.delete-btn');
        const img = document.createElement('img');
        img.src = url;
        box.innerHTML = '';
        box.appendChild(img);
        box.appendChild(deleteBtn);
        box.appendChild(input);
        deleteBtn.style.display = '';
        box.classList.add('has-image');
      });
    } catch {}
  })();
});
