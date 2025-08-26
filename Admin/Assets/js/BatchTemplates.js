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

  // Initialize upload boxes (front/back/toggle/delete per box)
  const uploadBoxes = document.querySelectorAll(".upload-box");
  uploadBoxes.forEach((box) => {
    const frontInput = box.querySelector(".frontInput");
    const backInput = box.querySelector(".backInput");
    const deleteBtn = box.querySelector(".delete-btn");
    const plusIcon = box.querySelector(".plus-icon");

    let frontImg = null;
    let backImg = null;
    let showingFront = true;

    const toggleImages = () => {
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
      } else if (!backImg) {
        backInput.click();
      } else {
        toggleImages();
      }
    });

    frontInput.addEventListener("change", (event) => {
      const file = event.target.files && event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        frontImg = document.createElement("img");
        frontImg.src = e.target.result;
        frontImg.classList.add("front-img");

        box.innerHTML = "";
        if (plusIcon) plusIcon.remove();
        ensureChildren();
        deleteBtn.style.display = "flex";
        box.classList.add("has-image");
      };
      reader.readAsDataURL(file);
    });

    backInput.addEventListener("change", (event) => {
      const file = event.target.files && event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        backImg = document.createElement("img");
        backImg.src = e.target.result;
        backImg.classList.add("back-img");

        box.innerHTML = "";
        ensureChildren();
        deleteBtn.style.display = "flex";
        box.classList.add("has-image");
        // Always start by showing front if available
        showingFront = true;
        if (frontImg) {
          frontImg.style.opacity = 1;
          if (backImg) backImg.style.opacity = 0;
        }
      };
      reader.readAsDataURL(file);
    });

    deleteBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      frontImg = null;
      backImg = null;
      showingFront = true;
      box.innerHTML = "";
      // Recreate plus icon
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
  });
});
