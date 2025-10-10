const hamburgerIcon = document.querySelector(".hamburger-menu-ico");
const closeIcon = document.querySelector(".close-ico");
const sidebar = document.querySelector(".sidebar");

const searchIcon = document.querySelector(".search-icon");
const searchCloseIcon = document.querySelector(".search-close-ico");
const searchContainer = document.querySelector(".search-container");

if (hamburgerIcon && closeIcon && sidebar) {
  hamburgerIcon.addEventListener("click", () => {
    sidebar.classList.remove("closed");
    hamburgerIcon.classList.add("hidden");
    closeIcon.classList.remove("hidden");
  });

  closeIcon.addEventListener("click", () => {
    sidebar.classList.add("closed");
    hamburgerIcon.classList.remove("hidden");
    closeIcon.classList.add("hidden");
  });
}

if (searchIcon && searchCloseIcon && searchContainer) {
  searchIcon.addEventListener("click", () => {
    searchContainer.classList.remove("hidden");
    searchIcon.classList.add("hidden");
    searchCloseIcon.classList.remove("hidden");
  });

  searchCloseIcon.addEventListener("click", () => {
    searchContainer.classList.add("hidden");
    searchIcon.classList.remove("hidden");
    searchCloseIcon.classList.add("hidden");
  });
}

function toggleSubmenu(menuId) {
  const currentMenu = document.getElementById(menuId);
  if (!currentMenu) return;

  const isShown = currentMenu.classList.contains("show");

  document.querySelectorAll(".submenu").forEach((submenu) => {
    if (submenu.id !== menuId && submenu.classList.contains("show")) {
      submenu.classList.remove("show");
      const chevron = document.querySelector(
        `[onclick="toggleSubmenu('${submenu.id}')"] .chevron i`
      );
      if (chevron) chevron.classList.remove("rotate-180");
      const tab = document.querySelector(
        `[onclick="toggleSubmenu('${submenu.id}')"]`
      );
      if (tab) tab.setAttribute("aria-expanded", "false");
    }
  });

  currentMenu.classList.toggle("show", !isShown);

  const currentTab = document.querySelector(
    `[onclick="toggleSubmenu('${menuId}')"]`
  );
  if (currentTab) {
    currentTab.setAttribute("aria-expanded", !isShown);
  }

  const currentChevron = currentTab?.querySelector(".chevron i");
  if (currentChevron) {
    currentChevron.classList.toggle("rotate-180", !isShown);
  }
}

function setActiveTab(currentPage) {
  let activated = false;

  document.querySelectorAll(".tab, .sub-tab").forEach((tab) => {
    tab.classList.remove("active");

    if (!activated) {
      const href = tab.getAttribute("href");
      if (
        href &&
        (href.includes(currentPage) || href.includes(`page=${currentPage}`))
      ) {
        tab.classList.add("active");
        activated = true;
      }
    }
  });

  if (!activated && currentPage === "student-list") {
    const studentListTab = document.querySelector('a[href*="student-list"]');
    if (studentListTab) {
      studentListTab.classList.add("active");
    }
  }
}

function expandParentMenuIfActive() {
  let submenuOpened = false;

  document.querySelectorAll(".submenu").forEach((submenu) => {
    submenu.classList.remove("show");
    const chevron = document.querySelector(
      `[onclick="toggleSubmenu('${submenu.id}')"] .chevron i`
    );
    if (chevron) chevron.classList.remove("rotate-180");
  });

  document.querySelectorAll(".sub-tab.active").forEach((activeSubTab) => {
    if (submenuOpened) return;

    const submenu = activeSubTab.closest(".submenu");
    if (submenu) {
      submenu.classList.add("show");
      submenuOpened = true;

      const chevron = document.querySelector(
        `[onclick="toggleSubmenu('${submenu.id}')"] .chevron i`
      );
      if (chevron) {
        chevron.classList.add("rotate-180");
        const tab = document.querySelector(
          `[onclick="toggleSubmenu('${submenu.id}')"]`
        );
        if (tab) tab.setAttribute("aria-expanded", "true");
      }
    }
  });
}

let tabListenersAdded = false;

if (!tabListenersAdded) {
  document.querySelectorAll(".tab[onclick]").forEach((tab) => {
    tab.addEventListener("click", function (e) {
      if (this.getAttribute("href")) return;
      e.preventDefault();

      document
        .querySelectorAll(".tab")
        .forEach((t) => t.classList.remove("active"));
      this.classList.add("active");
    });
  });

  document.querySelectorAll(".sub-tab").forEach((tab) => {
    tab.addEventListener("click", function () {
      document
        .querySelectorAll(".tab, .sub-tab")
        .forEach((t) => t.classList.remove("active"));
      this.classList.add("active");
    });
  });

  tabListenersAdded = true;
}

const urlParams = new URLSearchParams(window.location.search);
const page = urlParams.get("page") || "dashboard";

function setTabActive(tabId) {
  document
    .querySelectorAll(".tab, .sub-tab")
    .forEach((t) => t.classList.remove("active"));
  const tab = document.getElementById(tabId);
  if (tab) tab.classList.add("active");
}

function scrollToBottom() {
  const container = document.getElementById("scrollContainer");
  if (container) {
    container.scrollTop = container.scrollHeight;
  }
}

// Logout Modal Functionality
/*document.addEventListener("DOMContentLoaded", function () {
  const logoutTab = document.getElementById("logout-tab");
  const modalOverlay = document.getElementById("modal-overlay");
  const confirmBtn = document.getElementById("confirm-btn");
  const cancelBtn = document.getElementById("cancel-btn");

  if (logoutTab && modalOverlay && confirmBtn && cancelBtn) {
    // Prevent any parent form submission
    const closestForm = logoutTab.closest("form");
    if (closestForm) {
      closestForm.onsubmit = function (e) {
        if (e.submitter && e.submitter.id === "logout-tab") {
          e.preventDefault();
          return false;
        }
        return true;
      };
    }

    logoutTab.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      modalOverlay.style.display = "flex";
    });

    cancelBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      modalOverlay.style.display = "none";
    });

    confirmBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      // Check if we're in production (Railway)
      const isProduction = window.location.hostname.includes("railway.app");

      // Set the appropriate logout path
      let logoutPath = isProduction
        ? "/Admin/Components/AdminLogout.php" // For Railway
        : "/ECADYB/Admin/Components/AdminLogout.php"; // For local

      // For Railway, we need to ensure we're using the correct base path
      if (isProduction) {
        const basePath = window.location.pathname
          .split("/")
          .slice(0, 2)
          .join("/");
        logoutPath = basePath + "/Admin/Components/AdminLogout.php";
      }

      window.location.href = logoutPath;
    });

    // Close modal when clicking outside
    modalOverlay.addEventListener("click", (e) => {
      if (e.target === modalOverlay) {
        modalOverlay.style.display = "none";
      }
    });
  }
});
*/

document.addEventListener("DOMContentLoaded", () => {
  const currentPage =
    urlParams.get("page") || window.location.pathname.split("/").pop();
  setActiveTab(currentPage);
  expandParentMenuIfActive();

  if (sidebar && hamburgerIcon && closeIcon) {
    const isSidebarClosed = sidebar.classList.contains("closed");
    hamburgerIcon.classList.toggle("hidden", !isSidebarClosed);
    closeIcon.classList.toggle("hidden", isSidebarClosed);
  }

  if (searchContainer && searchIcon && searchCloseIcon) {
    const isSearchHidden = searchContainer.classList.contains("hidden");
    searchIcon.classList.toggle("hidden", !isSearchHidden);
    searchCloseIcon.classList.toggle("hidden", isSearchHidden);
  }
});

async function loadAdminLogo() {
  try {
    const BASE_PATH = window.location.pathname.includes("/Admin/")
      ? window.location.pathname.substring(
          0,
          window.location.pathname.indexOf("/Admin/")
        )
      : window.location.origin;
    const CONNECTION_PATH = `${BASE_PATH}/Connection`;

    const response = await fetch(`${CONNECTION_PATH}/Logo/FetchAdminLogo.php`);
    if (response.ok) {
      const data = await response.json();
      if (data.success && data.logo_url) {
        // Save logo URL to localStorage for immediate application on next page load
        localStorage.setItem("admin-logo-url", data.logo_url);
        
        const adminLogo = document.getElementById("admin-logo");
        if (adminLogo) {
          adminLogo.src = data.logo_url;
        }
      }
    }
  } catch (error) {
    console.error("Failed to load admin logo:", error);
  }
}

// Theme application system - only define if not already defined by Themes.js
if (typeof window.themes === "undefined") {
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
}

if (typeof window.applyTheme === "undefined") {
  window.applyTheme = function (theme) {
    console.log("Applying theme:", theme);
    const root = document.documentElement;
    const selectedTheme = window.themes[theme] || window.themes["Default"];

    console.log("Selected theme data:", selectedTheme);

    // Apply CSS variables to the root element
    for (const [varName, color] of Object.entries(selectedTheme)) {
      root.style.setProperty(varName, color);
    }

    const body = document.body;
    body.classList.remove("theme-light-mode", "theme-dark-mode");

    if (theme === "Light Mode") {
      body.classList.add("theme-light-mode");
    } else if (theme === "Dark Mode") {
      body.classList.add("theme-dark-mode");
    }

    console.log("Theme applied:", theme);
  };
}

document.addEventListener("DOMContentLoaded", () => {
  loadAdminLogo();

  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  if (savedTheme) {
    window.applyTheme(savedTheme);
  }

  const logoutTab = document.getElementById("logout-tab");
  if (logoutTab) {
    logoutTab.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      const isLocalhost = window.location.hostname === "localhost";
      const logoutPath = isLocalhost
        ? "../../Public/Components/Login.php"
        : "/Public/Components/Login.php";

      window.location.replace(logoutPath);
    });
  }
});
