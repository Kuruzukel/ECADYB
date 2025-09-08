// Sidebar toggle buttons
const hamburgerIcon = document.querySelector(".hamburger-menu-ico");
const closeIcon = document.querySelector(".close-ico");
const sidebar = document.querySelector(".sidebar");

// Search container toggle buttons
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

// Toggle submenu and chevron rotation
// ==========================
function toggleSubmenu(menuId) {
  const currentMenu = document.getElementById(menuId);
  if (!currentMenu) return;

  const isShown = currentMenu.classList.contains("show");
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

  // Close other submenus
  document.querySelectorAll(".submenu").forEach((submenu) => {
    if (submenu.id !== menuId) {
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
}

// ==========================
// Activate matching tab/sub-tab based on current page
// ==========================
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
}

// ==========================
// Expand parent menu if a sub-tab is active
// ==========================
function expandParentMenuIfActive() {
  let submenuOpened = false;

  // Close all submenus first
  document.querySelectorAll(".submenu").forEach((submenu) => {
    submenu.classList.remove("show");
    const chevron = document.querySelector(
      `[onclick="toggleSubmenu('${submenu.id}')"] .chevron i`
    );
    if (chevron) chevron.classList.remove("rotate-180");
  });

  // Open submenu of the active sub-tab
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

// ==========================
// Handle main tab clicks that have no href (toggle active)
// ==========================
document.querySelectorAll(".tab[onclick]").forEach((tab) => {
  tab.addEventListener("click", function (e) {
    if (this.getAttribute("href")) return; // If href exists, let link work
    e.preventDefault();

    document
      .querySelectorAll(".tab")
      .forEach((t) => t.classList.remove("active"));
    this.classList.add("active");
  });
});

// ==========================
// Handle sub-tab clicks
// ==========================
document.querySelectorAll(".sub-tab").forEach((tab) => {
  tab.addEventListener("click", function () {
    document
      .querySelectorAll(".tab, .sub-tab")
      .forEach((t) => t.classList.remove("active"));
    this.classList.add("active");
  });
});

// ==========================
// Get current page from URL params
// ==========================
const urlParams = new URLSearchParams(window.location.search);
const page = urlParams.get("page") || "dashboard";

// ==========================
// Manual activation helper
// ==========================
function setTabActive(tabId) {
  document
    .querySelectorAll(".tab, .sub-tab")
    .forEach((t) => t.classList.remove("active"));
  const tab = document.getElementById(tabId);
  if (tab) tab.classList.add("active");
}

// ==========================
// Scroll to bottom helper function
// ==========================
function scrollToBottom() {
  const container = document.getElementById("scrollContainer");
  if (container) {
    container.scrollTop = container.scrollHeight;
  }
}

// ==========================
// Logout Modal Functionality
// ==========================
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
// ==========================
// On DOM ready, setup active tab and menu
// ==========================
document.addEventListener("DOMContentLoaded", () => {
  const currentPage =
    urlParams.get("page") || window.location.pathname.split("/").pop();
  setActiveTab(currentPage);
  expandParentMenuIfActive();

  // Set icon visibility based on sidebar state
  const isSidebarClosed = sidebar.classList.contains("closed");
  hamburgerIcon.classList.toggle("hidden", !isSidebarClosed);
  closeIcon.classList.toggle("hidden", isSidebarClosed);

  // Set search icons initial visibility
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

    const response = await fetch(`${CONNECTION_PATH}/FetchAdminLogo.php`);
    if (response.ok) {
      const data = await response.json();
      if (data.success && data.logo_url) {
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

// Load logo when page loads
document.addEventListener("DOMContentLoaded", loadAdminLogo);
