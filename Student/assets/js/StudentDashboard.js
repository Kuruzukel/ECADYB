(function () {
  let currentPage = "home";
  let isLoading = false;
  const basePath = "/ECADYB/Student/Components/";

  function getCurrentPageFromURL() {
    const path = window.location.pathname;
    if (path.includes("StudentDashboard.php")) return "home";
    if (path.includes("About.php")) return "about";
    if (path.includes("Yearbook.php")) return "yearbook";
    if (path.includes("Memories.php")) return "memories";
    return "home";
  }

  currentPage = getCurrentPageFromURL();

  function updateActiveNav(page) {
    document.querySelectorAll(".nav-link").forEach((link) => {
      const linkPage = link.getAttribute("data-page");
      if (linkPage === page) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    });
  }

  function loadPage(page, updateHistory = true) {
    if (isLoading || page === currentPage) return;

    isLoading = true;

    const mainContent =
      document.querySelector("main") || document.querySelector("body");
    if (mainContent) {
      mainContent.style.opacity = "0.6";
      mainContent.style.pointerEvents = "none";
    }

    fetch(basePath + `PageLoader.php?page=${page}`, {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          let contentContainer = document.querySelector("main");
          if (!contentContainer) {
            const header = document.querySelector("header");
            if (header && header.nextElementSibling) {
              contentContainer = header.nextElementSibling;
              let sections = [];
              let current = header.nextElementSibling;
              const footer = document.querySelector("footer");
              while (current && current !== footer) {
                if (
                  current.tagName === "SECTION" ||
                  current.tagName === "MAIN"
                ) {
                  sections.push(current);
                }
                current = current.nextElementSibling;
              }

              sections.forEach((section) => section.remove());

              const bottomNav = document.querySelector(".bottom-nav");
              const insertBefore =
                footer || bottomNav || document.body.lastChild;

              const tempDiv = document.createElement("div");
              tempDiv.innerHTML = data.content;

              while (tempDiv.firstChild) {
                document.body.insertBefore(tempDiv.firstChild, insertBefore);
              }
            }
          } else {
            contentContainer.innerHTML = data.content;
          }

          document.title = data.title;

          updateActiveNav(page);

          if (updateHistory) {
            const pageURLs = {
              home: basePath + "StudentDashboard.php",
              about: basePath + "About.php",
              yearbook: basePath + "Yearbook.php",
              memories: basePath + "Memories.php",
            };
            window.history.pushState(
              { page: page },
              data.title,
              pageURLs[page]
            );
          }

          currentPage = page;

          window.scrollTo({ top: 0, behavior: "smooth" });

          if (mainContent) {
            mainContent.style.opacity = "1";
            mainContent.style.pointerEvents = "auto";
          }
        } else {
          console.error("Failed to load page:", data.error);
          window.location.href = basePath + pageURLs[page];
        }
      })
      .catch((error) => {
        console.error("Error loading page:", error);
        const pageURLs = {
          home: "StudentDashboard.php",
          about: "About.php",
          yearbook: "Yearbook.php",
          memories: "Memories.php",
        };
        window.location.href = basePath + pageURLs[page];
      })
      .finally(() => {
        isLoading = false;
      });
  }

  window.addEventListener("popstate", (event) => {
    if (event.state && event.state.page) {
      loadPage(event.state.page, false);
    } else {
      const page = getCurrentPageFromURL();
      loadPage(page, false);
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    updateActiveNav(currentPage);

    document.querySelectorAll(".nav-link").forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const page = this.getAttribute("data-page");
        if (page) {
          loadPage(page);
        }

        const hamburgerMenu = document.getElementById("hamburgerMenu");
        const centerNav = document.querySelector(".center-nav");
        if (hamburgerMenu && centerNav) {
          hamburgerMenu.classList.remove("active");
          centerNav.classList.remove("mobile-active");
        }
      });
    });
  });

  window.navigateToPage = loadPage;
})();

document.addEventListener("DOMContentLoaded", function () {
  if (window.location.hash) {
    setTimeout(() => {
      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
      }
    }, 100);
  }

  const hamburgerMenu = document.getElementById("hamburgerMenu");
  const centerNav = document.querySelector(".center-nav");

  if (!hamburgerMenu || !centerNav) return;

  hamburgerMenu.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("active");
    centerNav.classList.toggle("mobile-active");
  });

  document.addEventListener("click", function (event) {
    if (
      !hamburgerMenu.contains(event.target) &&
      !centerNav.contains(event.target)
    ) {
      hamburgerMenu.classList.remove("active");
      centerNav.classList.remove("mobile-active");
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const notificationIcon = document.getElementById("notificationIcon");
  const notificationDropdown = document.getElementById("notificationDropdown");
  const notificationBadge = document.getElementById("notificationBadge");
  const profileIcon = document.getElementById("profileIcon");
  const dropdownMenu = document.getElementById("profileDropdownMenu");

  if (!notificationIcon || !notificationDropdown) return;

  notificationIcon.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("clicked");
    notificationDropdown.classList.toggle("show");

    if (profileIcon && dropdownMenu) {
      profileIcon.classList.remove("clicked");
      dropdownMenu.classList.remove("show");
    }
  });

  document.addEventListener("click", function (event) {
    if (
      !notificationIcon.contains(event.target) &&
      !notificationDropdown.contains(event.target)
    ) {
      notificationIcon.classList.remove("clicked");
      notificationDropdown.classList.remove("show");
    }
  });

  const notificationItems = document.querySelectorAll(".notification-item");
  notificationItems.forEach((item) => {
    item.addEventListener("click", function () {
      this.classList.remove("unread");
      updateNotificationBadge();
    });
  });

  function updateNotificationBadge() {
    const unreadCount = document.querySelectorAll(
      ".notification-item.unread"
    ).length;
    const notificationBadge = document.getElementById("notificationBadge");

    if (notificationBadge) {
      if (unreadCount > 0) {
        notificationBadge.textContent = unreadCount;
        notificationBadge.classList.remove("hidden");
      } else {
        notificationBadge.classList.add("hidden");
      }
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const profileIcon = document.getElementById("profileIcon");
  const dropdownMenu = document.getElementById("profileDropdownMenu");
  const notificationIcon = document.getElementById("notificationIcon");
  const notificationDropdown = document.getElementById("notificationDropdown");

  if (!profileIcon || !dropdownMenu) return;

  profileIcon.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("clicked");
    dropdownMenu.classList.toggle("show");

    if (notificationIcon && notificationDropdown) {
      notificationIcon.classList.remove("clicked");
      notificationDropdown.classList.remove("show");
    }
  });

  document.addEventListener("click", function (event) {
    if (
      !profileIcon.contains(event.target) &&
      !dropdownMenu.contains(event.target)
    ) {
      profileIcon.classList.remove("clicked");
      dropdownMenu.classList.remove("show");
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".center-nav a");
  const heroButtons = document.querySelectorAll(
    ".hero-btn, .hero-btn-secondary"
  );

  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      const targetHref = this.getAttribute("href");

      if (targetHref && targetHref.startsWith("#")) {
        e.preventDefault();

        navLinks.forEach((navLink) => navLink.classList.remove("clicked"));
        this.classList.add("clicked");

        const targetSection = document.querySelector(targetHref);

        if (targetSection) {
          targetSection.scrollIntoView({
            behavior: "smooth",
          });

          setTimeout(() => {
            if (window.history && window.history.replaceState) {
              window.history.replaceState(null, null, window.location.pathname);
            }
          }, 1000);
        }
      }
    });
  });

  heroButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const targetHref = this.getAttribute("href");

      if (targetHref && targetHref.startsWith("#")) {
        e.preventDefault();

        this.classList.add("clicked");

        setTimeout(() => {
          this.classList.remove("clicked");
        }, 300);

        const targetSection = document.querySelector(targetHref);

        if (targetSection) {
          targetSection.scrollIntoView({
            behavior: "smooth",
          });

          setTimeout(() => {
            if (window.history && window.history.replaceState) {
              window.history.replaceState(null, null, window.location.pathname);
            }
          }, 1000);
        }
      }
    });
  });
});

function handleLoginClick(e) {
  e.preventDefault();
  e.stopPropagation();

  const basePath = window.location.hostname === "localhost" ? "/ECADYB/" : "/";
  window.location.href = basePath + "Login";
}

const loginButtons = document.querySelectorAll(
  '.hero-btn[href="#login"], .hero-btn-secondary[href="#login"]'
);
loginButtons.forEach((button) => {
  button.addEventListener("click", handleLoginClick);
});

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    initializeYearbookItems();
  }, 100);
});

function initializeYearbookItems() {
  const itemsContainer = document.querySelector(".yearbook-items-container");
  const sliderMain = document.querySelector(".yearbook-slider-main");

  if (!itemsContainer || !sliderMain) {
    if (!document.querySelector(".yearbooks-section")) {
      return;
    }
    setTimeout(initializeYearbookItems, 200);
    return;
  }

  if (itemsContainer.getAttribute("data-listener") === "true") {
    return;
  }

  itemsContainer.setAttribute("data-listener", "true");

  const yearBookItems = document.querySelectorAll(".yearbook-item");
  yearBookItems.forEach((item) => {
    if (item) {
      item.style.transform = "";
      item.style.transition = "";
    }
  });

  console.log(
    "Yearbook items initialized successfully - hover should work smoothly now"
  );
}

function showYearbookBackground(clickedItem, imageUrl) {
  try {
    if (!clickedItem || !imageUrl) {
      console.error("Missing required parameters for showYearbookBackground");
      return;
    }

    const sliderMain = document.querySelector(".yearbook-slider-main");
    if (!sliderMain) {
      console.error("Yearbook slider main container not found");
      return;
    }

    const allItems = document.querySelectorAll(".yearbook-item");
    const introContent = document.querySelector(".yearbook-intro-content");
    const detailDisplay = document.querySelector(".yearbook-detail-display");
    const coverImage = document.querySelector(".yearbook-cover-image");
    const detailTitle = document.querySelector(".yearbook-detail-title");
    const detailDescription = document.querySelector(
      ".yearbook-detail-description"
    );

    const filename = imageUrl.split("/").pop();
    const departmentInfo = getDepartmentInfo(filename);

    const coverImageUrl = imageUrl.replace(
      "https://ECADYB.b-cdn.net/img/YB%20COVER/",
      "https://ECADYB.b-cdn.net/img/YB%20COVER/"
    );

    sliderMain.style.backgroundImage = `url('${imageUrl}')`;
    sliderMain.classList.add("show-yearbook-bg");

    if (introContent) {
      introContent.style.opacity = "0";
      introContent.style.pointerEvents = "none";
    }

    if (detailDisplay && coverImage && detailTitle && detailDescription) {
      coverImage.src = coverImageUrl;
      coverImage.alt = departmentInfo.title + " Yearbook Cover";

      detailTitle.textContent = departmentInfo.title;
      detailDescription.textContent = departmentInfo.description;

      detailDisplay.style.display = "flex";
      detailDisplay.style.opacity = "1";
      detailDisplay.style.pointerEvents = "auto";
    }

    console.log("Department info:", departmentInfo);
  } catch (error) {
    console.error("Error in showYearbookBackground:", error);
  }
}

const departmentMap = {
  "MaritimeEducation.png": {
    title: "College of Maritime Education",
    description:
      "Dedicated to the seafarers who embraced discipline, courage, and determination. This yearbook captures the proud tradition of alumni who are now prepared to navigate not only the seas but also the challenges of life with strength and honor.",
  },
  "TourismManagement.png": {
    title: "College of Tourism Management",
    description:
      "This yearbook celebrates the hospitality professionals who turned passion into purpose. Alumni from this department carry forward the spirit of service excellence, cultural appreciation, and global connectivity that defines the tourism industry.",
  },
  "CriminalJusticeEducation.png": {
    title: "College of Criminal Justice Education",
    description:
      "This yearbook honors the guardians of justice who chose to serve and protect. Alumni from this department embody integrity, courage, and unwavering commitment to upholding the law and ensuring community safety.",
  },
  "InformationSystem.png": {
    title: "College of Information System",
    description:
      "This yearbook celebrates the innovators and problem-solvers who turned codes into solutions and ideas into systems. Alumni of this department leave behind a legacy of creativity and technological advancement, ready to shape the digital future.",
  },
  "Education.png": {
    title: "College of Education",
    description:
      "This yearbook honors the future educators who chose to inspire and nurture minds. Alumni from this department carry forward the noble mission of shaping generations, fostering learning, and building a brighter tomorrow through education.",
  },
  "BusinessAdministration.png": {
    title: "College of Business Administration",
    description:
      "This yearbook celebrates the future leaders and entrepreneurs who turned vision into reality. Alumni from this department are equipped with strategic thinking, leadership skills, and business acumen to drive innovation and economic growth.",
  },
  "Nursing.png": {
    title: "College of Nursing",
    description:
      "This yearbook honors the compassionate hearts and steady hands of those who trained to serve. Alumni from this department will always be remembered for their dedication to care, their selflessness, and their unwavering commitment to saving lives.",
  },
};

function getDepartmentInfo(filename) {
  return (
    departmentMap[filename] || {
      title: "Department Yearbook",
      description:
        "Explore memories, achievements, and milestones from our academic programs. A collection of moments that showcase the dedication, growth, and success of our students and faculty members.",
    }
  );
}

function closeYearbookView() {
  try {
    const sliderMain = document.querySelector(".yearbook-slider-main");
    if (!sliderMain) {
      console.error("Yearbook slider main container not found");
      return;
    }

    const allItems = document.querySelectorAll(".yearbook-item");
    const introContent = document.querySelector(".yearbook-intro-content");
    const detailDisplay = document.querySelector(".yearbook-detail-display");

    sliderMain.style.backgroundImage = "";
    sliderMain.classList.remove("show-yearbook-bg", "background-loaded");

    allItems.forEach((item) => {
      item.classList.remove("active");
    });

    if (introContent) {
      introContent.style.opacity = "1";
      introContent.style.pointerEvents = "auto";
    }

    if (detailDisplay) {
      detailDisplay.style.display = "none";
      detailDisplay.style.opacity = "0";
      detailDisplay.style.pointerEvents = "none";
    }

    console.log("Yearbook view closed successfully");
  } catch (error) {
    console.error("Error in closeYearbookView:", error);
  }
}

document.addEventListener("keydown", function (e) {
  try {
    if (e.key === "Escape") {
      const sliderMain = document.querySelector(".yearbook-slider-main");
      if (
        sliderMain &&
        sliderMain.classList &&
        sliderMain.classList.contains("show-yearbook-bg")
      ) {
        closeYearbookView();
      }
    }
  } catch (error) {
    console.error("Error in escape key handler:", error);
  }
});

document.addEventListener("click", function (e) {
  try {
    const sliderMain = document.querySelector(".yearbook-slider-main");
    if (
      !sliderMain ||
      !sliderMain.classList ||
      !sliderMain.classList.contains("show-yearbook-bg")
    ) {
      return;
    }

    const itemsContainer = document.querySelector(".yearbook-items-container");
    const detailDisplay = document.querySelector(".yearbook-detail-display");

    const clickedInContainer =
      itemsContainer && itemsContainer.contains(e.target);
    const clickedInDetail = detailDisplay && detailDisplay.contains(e.target);
    const clickedIntroContent = e.target.closest(".yearbook-intro-content");

    if (
      !clickedInContainer &&
      !clickedInDetail &&
      !clickedIntroContent &&
      sliderMain.contains(e.target)
    ) {
      closeYearbookView();
    }
  } catch (error) {
    console.error("Error in click handler:", error);
  }
});

function markAllAsRead() {
  const notificationItems = document.querySelectorAll(".notification-item");
  const notificationBadge = document.getElementById("notificationBadge");

  notificationItems.forEach((item) => {
    item.classList.remove("unread");
  });

  if (notificationBadge) {
    notificationBadge.classList.add("hidden");
  }

  console.log("All notifications marked as read");
}

let originalFormValues = {};

function editProfile() {
  const modal = document.getElementById("editStudentModal");
  const form = document.getElementById("editStudentForm");

  if (!modal || !form) {
    console.error("Modal or form not found");
    return;
  }

  const formData = new FormData(form);
  originalFormValues = {};
  for (let [key, value] of formData.entries()) {
    originalFormValues[key] = value;
  }

  modal.classList.add("active");
}

function closeEditModal() {
  const modal = document.getElementById("editStudentModal");
  if (!modal) return;

  modal.classList.remove("active");
}

function cancelEdit() {
  const form = document.getElementById("editStudentForm");
  if (!form) return;

  for (let [key, value] of Object.entries(originalFormValues)) {
    const input = form.querySelector(`[name="${key}"]`);
    if (input) {
      input.value = value;
    }
  }

  closeEditModal();
}

async function saveStudentChanges() {
  const form = document.getElementById("editStudentForm");
  if (!form) {
    console.error("Form not found");
    return;
  }

  const formData = new FormData(form);

  const departmentCollectionMap = {
    "BS Marine Engineering": "bsme",
    "BS Marine Transportation": "bsmt",
    "BS Criminal Justice Education": "bscje",
    "BS Tourism Management": "bstm",
    "BS Technical-Vocational Teacher Education": "btvted",
    "BS Early Childhood Education": "beced",
    "BS Nursing": "bsn",
    "BS Information System": "bsis",
    "BS Management Accounting": "bsma",
    "BS Entrepreneurship": "bse",
  };

  const studentDepartment = window.studentData?.studentDepartment || "";
  const collectionName = departmentCollectionMap[studentDepartment] || "bsme";

  const data = {
    original_student_id: formData.get("student_id"),
    collection: collectionName,
    academic_year: window.studentData?.studentAcademicYear || "",
    "first name": formData.get("first_name"),
    "middle name": formData.get("middle_name"),
    "last name": formData.get("last_name"),
    email: formData.get("email"),
    motto: formData.get("motto"),
    honors: formData.get("honors"),
    milestone: formData.get("milestone"),
  };

  Object.keys(data).forEach((key) => {
    if (data[key] === null || data[key] === undefined || data[key] === "") {
      if (key !== "original_student_id" && key !== "collection") {
        delete data[key];
      }
    }
  });

  // Debug logging
  console.log("Form data being sent:", data);
  console.log("Student department:", studentDepartment);
  console.log("Collection name:", collectionName);
  console.log("Student data:", window.studentData);

  // Log individual form field values
  console.log("Individual form fields:");
  console.log("- student_id:", formData.get("student_id"));
  console.log("- email:", formData.get("email"));
  console.log("- motto:", formData.get("motto"));
  console.log("- milestone:", formData.get("milestone"));
  console.log("- first_name:", formData.get("first_name"));
  console.log("- middle_name:", formData.get("middle_name"));
  console.log("- last_name:", formData.get("last_name"));
  console.log("- honors:", formData.get("honors"));

  try {
    const response = await fetch(
      "/ECADYB/Connection/Student/UpdateStudent.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      }
    );

    const result = await response.json();

    // Debug logging
    console.log("Response status:", response.status);
    console.log("Response result:", result);

    if (result.success) {
      // Check if no changes were detected
      if (result.message && result.message.includes("No changes detected")) {
        showNotification(
          "No changes detected, student record remains the same.",
          "info"
        );

        setTimeout(() => {
          closeEditModal();
        }, 1500);
      } else {
        showNotification("Success! Student information updated.", "success");

        setTimeout(() => {
          closeEditModal();
        }, 1000);

        setTimeout(() => {
          location.reload();
        }, 2000);
      }
    } else {
      showNotification(
        "Error: " + (result.message || "Failed to update student information."),
        "error"
      );
    }
  } catch (error) {
    console.error("Error updating student:", error);
    showNotification("Error: Failed to update student information.", "error");
  }
}

function submitStudentInfo(event) {
  if (event) {
    event.preventDefault();
  }
  saveStudentChanges();
}

function removeSpaces(input) {
  input.value = input.value.replace(/\s/g, "");
}

function formatAcademicYear(input) {
  let value = input.value.replace(/\D/g, ""); // Remove non-digits
  if (value.length >= 4) {
    value = value.substring(0, 4) + "-" + value.substring(4, 8);
  }
  input.value = value;
}

// Debug function to check if student exists in database
async function checkStudentExists() {
  const studentId = window.studentData?.studentId || "";
  const department = window.studentData?.studentDepartment || "";

  console.log("Checking student existence:");
  console.log("- Student ID:", studentId);
  console.log("- Department:", department);

  const departmentCollectionMap = {
    "BS Marine Engineering": "bsme",
    "BS Marine Transportation": "bsmt",
    "BS Criminal Justice Education": "bscje",
    "BS Tourism Management": "bstm",
    "BS Technical-Vocational Teacher Education": "btvted",
    "BS Early Childhood Education": "beced",
    "BS Nursing": "bsn",
    "BS Information System": "bsis",
    "BS Management Accounting": "bsma",
    "BS Entrepreneurship": "bse",
  };

  const collectionName = departmentCollectionMap[department] || "bsme";
  console.log("- Collection name:", collectionName);

  try {
    const response = await fetch(
      "/ECADYB/Connection/Student/CheckStudent.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          student_id: studentId,
          collection: collectionName,
          academic_year: window.studentData?.studentAcademicYear || "",
        }),
      }
    );

    const result = await response.json();
    console.log("Student check result:", result);

    if (result.exists) {
      console.log("✅ Student found in database");
      console.log("Student data:", result.student);
    } else {
      console.log("❌ Student NOT found in database");
      console.log("Error:", result.message);
    }

    return result;
  } catch (error) {
    console.error("Error checking student:", error);
    return { exists: false, error: error.message };
  }
}

// Call this function to test
// checkStudentExists();

function showNotification(message, type = "info") {
  const existingNotification = document.querySelector(".notification");
  if (existingNotification) {
    existingNotification.remove();
  }

  const notification = document.createElement("div");
  notification.className = `notification ${type}`;

  let icon = "fa-info";
  if (type === "success") icon = "fa-check";
  if (type === "error") icon = "fa-times";
  if (type === "warning") icon = "fa-exclamation";

  notification.innerHTML = `
    <div class="notification-icon-wrapper">
      <i class="fas ${icon}"></i>
    </div>
    <div class="notification-message">${message}</div>
    <button class="notification-close" onclick="this.parentElement.remove()">
      <i class="fas fa-times"></i>
    </button>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.classList.add("show");
  }, 100);

  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 500);
  }, 3000);
}

function logout() {
  console.log("Logout clicked");
  const basePath = window.location.hostname === "localhost" ? "/ECADYB/" : "/";
  window.location.href = basePath + "Student/Components/Logout.php";
}

async function loadAnnouncements() {
  try {
    const notificationList = document.getElementById("notificationList");
    const notificationBadge = document.getElementById("notificationBadge");

    if (!notificationList || !notificationBadge) {
      return;
    }

    const response = await fetch(
      "/ECADYB/Connection/Announcement/FetchAnnouncements.php"
    );
    const result = await response.json();

    if (result.success && result.data.length > 0) {
      notificationBadge.textContent = result.count;
      notificationBadge.classList.remove("hidden");

      notificationList.innerHTML = "";

      result.data.forEach((announcement) => {
        const notificationItem = createNotificationItem(announcement);
        notificationList.appendChild(notificationItem);
      });
    } else {
      notificationList.innerHTML =
        '<div class="notification-item"><div class="notification-content"><p class="notification-text">No new announcements</p></div></div>';
      notificationBadge.classList.add("hidden");
    }
  } catch (error) {
    console.error("Error loading announcements:", error);
    const notificationList = document.getElementById("notificationList");
    if (notificationList) {
      notificationList.innerHTML =
        '<div class="notification-item"><div class="notification-content"><p class="notification-text">Error loading announcements</p></div></div>';
    }
  }
}

function createNotificationItem(announcement) {
  const item = document.createElement("div");
  item.className = "notification-item unread";

  let iconClass = "fa-solid fa-info-circle";
  if (announcement.type === "announcement") {
    iconClass = "fa-solid fa-bullhorn";
  } else if (announcement.type === "event") {
    iconClass = "fa-solid fa-calendar";
  }

  const formattedDate = formatAnnouncementDate(
    announcement.date,
    announcement.time
  );

  item.innerHTML = `
    <i class="${iconClass} notification-item-icon"></i>
    <div class="notification-content">
      <p class="notification-text"><strong>${announcement.title}</strong><br>${announcement.message}</p>
      <span class="notification-time">${formattedDate}</span>
    </div>
  `;

  return item;
}

function formatAnnouncementDate(date, time) {
  if (!date) return "Recently";

  const announcementDate = new Date(date + " " + time);

  const dateOptions = {
    year: "numeric",
    month: "short",
    day: "numeric",
  };

  const timeOptions = {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  };

  const formattedDate = announcementDate.toLocaleDateString(
    "en-US",
    dateOptions
  );
  const formattedTime = announcementDate.toLocaleTimeString(
    "en-US",
    timeOptions
  );

  return `${formattedDate} at ${formattedTime}`;
}

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    loadAnnouncements();
  }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
  const bottomProfileIcon = document.getElementById("bottomProfileIcon");
  const bottomDropdownMenu = document.getElementById(
    "bottomProfileDropdownMenu"
  );
  const notificationIcon = document.getElementById("notificationIcon");
  const notificationDropdown = document.getElementById("notificationDropdown");

  if (bottomProfileIcon && bottomDropdownMenu) {
    bottomProfileIcon.addEventListener("click", function (e) {
      e.stopPropagation();
      this.classList.toggle("clicked");
      bottomDropdownMenu.classList.toggle("show");

      if (notificationIcon && notificationDropdown) {
        notificationIcon.classList.remove("clicked");
        notificationDropdown.classList.remove("show");
      }
    });

    document.addEventListener("click", function (event) {
      if (
        !bottomProfileIcon.contains(event.target) &&
        !bottomDropdownMenu.contains(event.target)
      ) {
        bottomProfileIcon.classList.remove("clicked");
        bottomDropdownMenu.classList.remove("show");
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const bottomNavLinks = document.querySelectorAll(".bottom-nav a");
  const currentPath = window.location.pathname;

  bottomNavLinks.forEach((link) => {
    const linkPath = link.getAttribute("href");
    if (currentPath.includes(linkPath)) {
      link.classList.add("active");
    }
  });
});

// Yearbook navigation functionality (also available in Yearbook.php)
// Use window property to avoid redeclaration errors
window.currentYearbookIndex = window.currentYearbookIndex || 0;

function navigateYearbook(direction) {
  try {
    if (window.innerWidth > 768) {
      return; // Desktop navigation not needed for this implementation
    }

    const yearbooks = document.querySelectorAll(".yearbook-item");
    if (yearbooks.length === 0) return;

    // Remove current active classes
    yearbooks.forEach((item) => {
      item.classList.remove("mobile-active", "mobile-prev", "mobile-next");
    });

    // Update index
    if (direction === "next") {
      window.currentYearbookIndex =
        (window.currentYearbookIndex + 1) % yearbooks.length;
    } else {
      window.currentYearbookIndex =
        (window.currentYearbookIndex - 1 + yearbooks.length) % yearbooks.length;
    }

    // Apply new classes
    yearbooks.forEach((item, index) => {
      if (index === window.currentYearbookIndex) {
        item.classList.add("mobile-active");
      } else if (
        index ===
        (window.currentYearbookIndex - 1 + yearbooks.length) % yearbooks.length
      ) {
        item.classList.add("mobile-prev");
      } else if (
        index ===
        (window.currentYearbookIndex + 1) % yearbooks.length
      ) {
        item.classList.add("mobile-next");
      }
    });

    console.log("Navigated to yearbook item:", window.currentYearbookIndex);
  } catch (error) {
    console.error("Error in navigateYearbook:", error);
  }
}

// Initialize mobile yearbook classes when yearbooks are loaded
function initializeYearbookMobileView() {
  if (window.innerWidth <= 768) {
    const yearbooks = document.querySelectorAll(".yearbook-item");
    if (yearbooks.length > 0) {
      // Reset to first item
      window.currentYearbookIndex = 0;

      // Set first item as active
      yearbooks[0].classList.add("mobile-active");

      // Set adjacent items
      if (yearbooks.length > 1) {
        yearbooks[yearbooks.length - 1].classList.add("mobile-prev");
      }
      if (yearbooks.length > 2) {
        yearbooks[1].classList.add("mobile-next");
      }
    }
  }
}

// Make navigateYearbook available globally
window.navigateYearbook = navigateYearbook;
window.initializeYearbookMobileView = initializeYearbookMobileView;

// Carousel functionality for Memories page
function initMemoriesCarousel() {
  // Lightbox functionality for carousel images
  function showImageLightbox(imageSrc) {
    // Check if lightbox already exists
    let lightbox = document.getElementById("carousel-lightbox");

    if (!lightbox) {
      // Create lightbox
      lightbox = document.createElement("div");
      lightbox.id = "carousel-lightbox";
      lightbox.className = "carousel-lightbox";
      lightbox.innerHTML = `
        <div class="lightbox-overlay"></div>
        <div class="lightbox-content">
          <button class="lightbox-close" aria-label="Close">&times;</button>
          <img src="" alt="Full size image" class="lightbox-image">
        </div>
      `;
      document.body.appendChild(lightbox);

      // Add close handlers
      const closeBtn = lightbox.querySelector(".lightbox-close");
      const overlay = lightbox.querySelector(".lightbox-overlay");

      const closeLightbox = () => {
        lightbox.classList.remove("active");
        setTimeout(() => {
          lightbox.style.display = "none";
        }, 300);

        // Resume auto-slide after closing
        setTimeout(() => {
          startAutoSlide();
        }, 500);
      };

      closeBtn.addEventListener("click", closeLightbox);
      overlay.addEventListener("click", closeLightbox);

      // Close on escape key
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && lightbox.classList.contains("active")) {
          closeLightbox();
        }
      });
    }

    // Show the lightbox with the image
    const lightboxImage = lightbox.querySelector(".lightbox-image");
    lightboxImage.src = imageSrc;
    lightbox.style.display = "flex";

    // Trigger animation
    setTimeout(() => {
      lightbox.classList.add("active");
    }, 10);
  }

  const track = document.getElementById("carousel-track");

  if (!track) return;

  let carouselImageElements = Array.from(
    track.querySelectorAll(".carousel-img")
  );
  let carouselImages = carouselImageElements.map((img) => img.src);

  carouselImages = [...new Set(carouselImages)];

  let currentIndex = 0;
  let isTransitioning = false;

  function renderImages() {
    const images = [
      carouselImages[carouselImages.length - 1],
      ...carouselImages,
      carouselImages[0],
    ];

    track.innerHTML = images
      .map(
        (src, i) =>
          `<img src="${src}" class="carousel-img" data-index="${
            i - 1
          }" draggable="false" />`
      )
      .join("");

    carouselImageElements = Array.from(track.querySelectorAll(".carousel-img"));

    track.style.transition = "none";
    track.style.transform = `translateX(-100%)`;
    currentIndex = 0;
  }

  function moveToIndex(index) {
    if (isTransitioning) return;

    currentIndex = index;
    isTransitioning = true;
    track.style.transition = "transform 0.6s ease-in-out";
    track.style.transform = `translateX(-${(index + 1) * 100}%)`;
  }

  function handleTransitionEnd() {
    isTransitioning = false;

    if (currentIndex >= carouselImages.length) {
      track.style.transition = "none";
      track.style.transform = `translateX(-100%)`;
      currentIndex = 0;
    } else if (currentIndex < 0) {
      track.style.transition = "none";
      track.style.transform = `translateX(-${carouselImages.length * 100}%)`;
      currentIndex = carouselImages.length - 1;
    }
  }

  function nextImage() {
    moveToIndex(currentIndex + 1);
  }

  function prevImage() {
    moveToIndex(currentIndex - 1);
  }

  track.addEventListener("transitionend", handleTransitionEnd);

  let startX = 0;
  let isDragging = false;

  track.addEventListener("touchstart", (e) => {
    startX = e.touches[0].clientX;
    isDragging = true;
  });

  track.addEventListener("touchmove", (e) => {
    if (!isDragging) return;
    const diff = e.touches[0].clientX - startX;
    track.style.transition = "none";
    track.style.transform = `translateX(calc(-${
      (currentIndex + 1) * 100
    }% + ${diff}px))`;
  });

  track.addEventListener("touchend", (e) => {
    isDragging = false;
    const diff = e.changedTouches[0].clientX - startX;
    if (diff > 50) {
      prevImage();
    } else if (diff < -50) {
      nextImage();
    } else {
      moveToIndex(currentIndex);
    }
  });

  track.addEventListener("click", (e) => {
    if (e.target.classList.contains("carousel-img")) {
      stopAutoSlide();

      showImageLightbox(e.target.src);
    }
  });

  renderImages();

  let autoSlideInterval = null;

  function startAutoSlide() {
    if (autoSlideInterval) {
      clearInterval(autoSlideInterval);
    }
    autoSlideInterval = setInterval(() => {
      nextImage();
    }, 3000);
  }

  function stopAutoSlide() {
    if (autoSlideInterval) {
      clearInterval(autoSlideInterval);
      autoSlideInterval = null;
    }
  }

  const isTouchDevice =
    "ontouchstart" in window || navigator.maxTouchPoints > 0;

  if (!isTouchDevice) {
    track.addEventListener("mouseenter", stopAutoSlide);
    track.addEventListener("mouseleave", startAutoSlide);
  }

  startAutoSlide();
}

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    initMemoriesCarousel();
  }, 100);
});
