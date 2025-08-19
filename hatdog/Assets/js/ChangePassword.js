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

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);
});

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".passwordField").forEach((field) => {
    const input = field.querySelector("input");
    const iconOpen = field.querySelector(".eyeIcon.open");
    const iconClose = field.querySelector(".eyeIcon.close");

    field.dataset.isvisible = "false";

    function toggle() {
      const isVisible = field.dataset.isvisible === "true";
      field.dataset.isvisible = isVisible ? "false" : "true";
      input.type = isVisible ? "password" : "text";
    }

    iconOpen.addEventListener("click", toggle);
    iconClose.addEventListener("click", toggle);
  });

  const postBtn = document.getElementById("post-change-btn");
  const modalOverlay = document.getElementById("modal-overlay");
  const confirmBtn = document.getElementById("confirm-btn");
  const cancelBtn = document.getElementById("cancel-btn");
  const form = document.getElementById("changepassForm");

  postBtn.addEventListener("click", (e) => {
    e.preventDefault();
    modalOverlay.style.display = "flex";
  });

  cancelBtn.addEventListener("click", () => {
    modalOverlay.style.display = "none";
  });

  confirmBtn.addEventListener("click", () => {
    modalOverlay.style.display = "none";
    form.submit();
  });

  const idInput = document.getElementById("idInput");
  if (idInput) {
    idInput.addEventListener("input", () => {
      const maxLen = +idInput.maxLength;
      if (idInput.value.length > maxLen) {
        idInput.value = idInput.value.slice(0, maxLen);
      }
    });
  }
});
