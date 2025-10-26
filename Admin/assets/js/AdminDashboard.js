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
      const basePath = window.location.pathname.includes("/ECADYB/")
        ? "/ECADYB"
        : "";
      window.location.href = basePath + "/Admin/Components/AdminLogout.php";
    });
  }

  // Initialize search functionality
  initializeSearchAutocomplete();
});

// Search autocomplete functionality
let searchTimeout = null;

function initializeSearchAutocomplete() {
  const searchInput = document.getElementById("search-input");
  const searchSuggestions = document.getElementById("search-suggestions");
  const searchButton = document.querySelector(".search-button");

  if (!searchInput || !searchSuggestions) return;

  // Handle input changes
  searchInput.addEventListener("input", function () {
    const query = this.value.trim();

    // Clear previous timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    if (query.length < 2) {
      hideSuggestions();
      return;
    }

    // Debounce search
    searchTimeout = setTimeout(() => {
      searchStudents(query);
    }, 300);
  });

  // Handle search button click
  if (searchButton) {
    searchButton.addEventListener("click", function (e) {
      e.preventDefault();
      const query = searchInput.value.trim();
      if (query.length >= 2) {
        searchStudents(query);
      }
    });
  }

  // Handle Enter key
  searchInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      const query = this.value.trim();
      if (query.length >= 2) {
        searchStudents(query);
      }
    }
  });

  // Hide suggestions when clicking outside
  document.addEventListener("click", function (e) {
    if (!e.target.closest(".search-wrapper")) {
      hideSuggestions();
    }
  });

  // Handle focus
  searchInput.addEventListener("focus", function () {
    if (
      this.value.trim().length >= 2 &&
      searchSuggestions.children.length > 0
    ) {
      showSuggestions();
    }
  });
}

function searchStudents(query) {
  const searchSuggestions = document.getElementById("search-suggestions");
  if (!searchSuggestions) return;

  const basePath = window.location.pathname.includes("/ECADYB/")
    ? "/ECADYB"
    : "";
  const searchUrl = `${
    window.location.origin
  }${basePath}/Connection/Student/SearchStudents.php?query=${encodeURIComponent(
    query
  )}&limit=10`;

  searchSuggestions.innerHTML = `
    <div class="search-suggestion-empty">
      <i class="fas fa-spinner fa-spin"></i> Searching...
    </div>
  `;
  showSuggestions();

  fetch(searchUrl)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          console.error("Non-JSON response received:", text.substring(0, 200));
          throw new Error("Server returned non-JSON response");
        });
      }
      return response.json();
    })
    .then((data) => {
      if (data.success && data.results && data.results.length > 0) {
        displaySuggestions(data.results);
      } else {
        searchSuggestions.innerHTML = `
          <div class="search-suggestion-empty">
            <i class="fas fa-search"></i> No students found
          </div>
        `;
      }
    })
    .catch((error) => {
      console.error("Search error:", error);
      searchSuggestions.innerHTML = `
        <div class="search-suggestion-empty">
          <i class="fas fa-exclamation-circle"></i> Error searching students
        </div>
      `;
    });
}

function displaySuggestions(results) {
  const searchSuggestions = document.getElementById("search-suggestions");
  if (!searchSuggestions) return;

  searchSuggestions.innerHTML = "";

  results.forEach((student) => {
    const item = document.createElement("div");
    item.className = "search-suggestion-item";

    let departmentYear = "";
    if (student.department_section) {
      departmentYear = escapeHtml(student.department_section);
      if (student.academic_year) {
        departmentYear += " - " + escapeHtml(student.academic_year);
      }
    } else if (student.academic_year) {
      departmentYear = escapeHtml(student.academic_year);
    }

    item.innerHTML = `
      <div class="search-suggestion-name">${escapeHtml(student.name)}</div>
      <div class="search-suggestion-id">Student ID: ${escapeHtml(
        student.student_id
      )}</div>
      <div class="search-suggestion-program">${departmentYear}</div>
    `;

    item.addEventListener("click", function () {
      handleStudentSelection(student);
    });

    searchSuggestions.appendChild(item);
  });

  showSuggestions();
}

function handleStudentSelection(student) {
  console.log("Selected student:", student);

  const searchInput = document.getElementById("search-input");
  if (searchInput) {
    searchInput.value = student.name;
  }

  // Hide suggestions
  hideSuggestions();

  const basePath = window.location.pathname.includes("/ECADYB/")
    ? "/ECADYB"
    : "";

  // Map collection to yearbook page
  const collectionToYearbook = {
    bsme: "maritime",
    bsmt: "maritime",
    bscje: "criminology",
    bstm: "tourism",
    btvted: "education",
    beced: "education",
    bsn: "nursing",
    bsis: "informationsys",
    bsma: "businessad",
    bse: "businessad",
  };

  const yearbookPage = collectionToYearbook[student.collection] || "maritime";

  // Store selected student information for the yearbook to navigate to
  sessionStorage.setItem(
    "searchSelectedStudent",
    JSON.stringify({
      id: student.id,
      student_id: student.student_id,
      name: student.name,
      department_section: student.department_section,
      academic_year: student.academic_year,
      collection: student.collection,
    })
  );

  // Redirect to yearbook page with student info
  window.location.href = `${basePath}/Admin?page=${yearbookPage}&student_id=${encodeURIComponent(
    student.student_id
  )}&student_name=${encodeURIComponent(student.name)}`;
}

function showSuggestions() {
  const searchSuggestions = document.getElementById("search-suggestions");
  if (searchSuggestions) {
    searchSuggestions.classList.add("show");
  }
}

function hideSuggestions() {
  const searchSuggestions = document.getElementById("search-suggestions");
  if (searchSuggestions) {
    searchSuggestions.classList.remove("show");
  }
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}
