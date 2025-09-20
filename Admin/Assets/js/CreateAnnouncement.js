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

  for (const [varName, color] of Object.entries(selectedTheme)) {
    root.style.setProperty(varName, color);
  }

  const currentSectionBg =
    selectedTheme["--section-bg"] || themes["Default"]["--section-bg"];
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

  document.body.style.display = "none";
  document.body.offsetHeight;
  document.body.style.display = "";
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);
});

let originalModalContent = null;
let isPreviewMode = false;
let modalState = "confirm";

document.addEventListener("DOMContentLoaded", function () {
  displayCurrentDate();

  initializeCharacterCounter();

  initializeFormValidation();

  initializeModal();

  initializePreview();

  initializeDateStatusCheck();
});

function displayCurrentDate() {
  const currentDateElement = document.getElementById("current-date");
  if (currentDateElement) {
    const now = new Date();
    const options = {
      weekday: "long",
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    currentDateElement.textContent = now.toLocaleDateString("en-US", options);
  }
}

function initializeCharacterCounter() {
  const messageTextarea = document.getElementById("message");
  const charCountElement = document.getElementById("char-count");

  if (messageTextarea && charCountElement) {
    messageTextarea.addEventListener("input", function () {
      const currentLength = this.value.length;
      charCountElement.textContent = currentLength;

      if (currentLength > 500) {
        charCountElement.style.color = "#ef4444";
      } else if (currentLength > 300) {
        charCountElement.style.color = "#f59e0b";
      } else {
        charCountElement.style.color = "#94a3b8";
      }
    });
  }
}

function initializeFormValidation() {
  const form = document.getElementById("announcementForm");
  const titleInput = document.getElementById("title");
  const messageTextarea = document.getElementById("message");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      if (!titleInput.value.trim()) {
        showNotification("Please enter a title", "error");
        titleInput.focus();
        return;
      }

      if (!messageTextarea.value.trim()) {
        showNotification("Please enter a message", "error");
        messageTextarea.focus();
        return;
      }

      showModal();
    });
  }

  if (titleInput) {
    titleInput.addEventListener("input", function () {
      validateField(this, "Title is required");
    });
  }

  if (messageTextarea) {
    messageTextarea.addEventListener("input", function () {
      validateField(this, "Message is required");
    });
  }
}

function validateField(field, errorMessage) {
  const isValid = field.value.trim().length > 0;

  if (isValid) {
    field.style.borderColor = "#0c27be";
    field.style.boxShadow = "0 0 0 3px rgba(12, 39, 190, 0.1)";
  } else {
    field.style.borderColor = "#ef4444";
    field.style.boxShadow = "0 0 0 3px rgba(239, 68, 68, 0.1)";
  }
}

function initializeModal() {
  const modalOverlay = document.getElementById("modal-overlay");
  const modal = modalOverlay.querySelector(".modal");
  const form = document.getElementById("announcementForm");

  if (modalOverlay && modal) {
    originalModalContent = modal.innerHTML;

    modalOverlay.addEventListener("click", function (e) {
      if (e.target === modalOverlay) {
        hideModal();
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && modalOverlay.style.display === "flex") {
        hideModal();
      }
    });

    modalOverlay.addEventListener("click", function (e) {
      if (e.target.id === "confirm-btn") {
        hideModal();
        submitForm();
      } else if (e.target.id === "cancel-btn") {
        hideModal();
      } else if (e.target.id === "close-preview-btn") {
        hideModal();
      }
    });
  }
}

function showModal() {
  const modalOverlay = document.getElementById("modal-overlay");
  if (modalOverlay) {
    modalState = "confirm";
    modalOverlay.style.display = "flex";
    modalOverlay.style.animation = "fadeIn 0.3s ease-out";
  }
}

function hideModal() {
  const modalOverlay = document.getElementById("modal-overlay");
  if (modalOverlay) {
    modalOverlay.style.animation = "fadeOut 0.3s ease-out";
    setTimeout(() => {
      modalOverlay.style.display = "none";
      if (modalState === "preview") {
        const modal = modalOverlay.querySelector(".modal");
        if (modal && originalModalContent) {
          modal.innerHTML = originalModalContent;
        }
        modalState = "confirm";
      }
    }, 300);
  }
}

function submitForm() {
  const form = document.getElementById("announcementForm");
  if (form) {
    const submitBtn = document.getElementById("post-announcement-btn");
    if (submitBtn) {
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Posting...';
      submitBtn.disabled = true;

      const formData = new FormData(form);

      const dateInput = document.getElementById("date");
      if (dateInput && !dateInput.value) {
        const today = new Date().toISOString().split("T")[0];
        dateInput.value = today;
        formData.set("date", today);
      }

      fetch("../../Connection/SubmitAnnouncement.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          console.log("Response status:", response.status);
          return response.json();
        })
        .then((data) => {
          console.log("Response data:", data);
          if (data.success) {
            showNotification(data.message, "success");
            form.reset();
            const charCountElement = document.getElementById("char-count");
            if (charCountElement) {
              charCountElement.textContent = "0";
            }

            const dateInput = document.getElementById("date");
            if (dateInput) {
              const today = new Date().toISOString().split("T")[0];
              dateInput.value = today;
            }

            setTimeout(() => {
              checkDateStatus();
            }, 500);
          } else {
            showNotification(data.message, "error");
          }
        })
        .catch((error) => {
          console.error("Network error:", error);
          showNotification(
            "An error occurred while posting the announcement. Please check your connection and try again.",
            "error"
          );
        })
        .finally(() => {
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
    }
  }
}

function initializePreview() {
  const previewBtn = document.getElementById("preview-btn");
  const titleInput = document.getElementById("title");
  const messageTextarea = document.getElementById("message");

  if (previewBtn) {
    previewBtn.addEventListener("click", function () {
      const title = titleInput.value.trim();
      const message = messageTextarea.value.trim();

      if (!title || !message) {
        showNotification(
          "Please fill in both title and message to preview",
          "warning"
        );
        return;
      }

      showPreviewModal(title, message);
    });
  }
}

function showPreviewModal(title, message) {
  const modalOverlay = document.getElementById("modal-overlay");
  const modal = modalOverlay.querySelector(".modal");

  modalState = "preview";

  const previewContent = `
        <div class="modal-header">
            <i class="fas fa-eye modal-icon"></i>
            <h3>Preview Announcement</h3>
        </div>
        <div class="modal-content">
            <div class="preview-announcement">
                <h4 class="preview-title">${title}</h4>
                <p class="preview-message">${message}</p>
                <div class="preview-meta">
                    <span class="preview-date">${new Date().toLocaleDateString()}</span>
                </div>
            </div>
        </div>
        <div class="modal-buttons">
            <button class="modal-btn secondary" id="close-preview-btn">
                <i class="fas fa-times"></i>
                Close Preview
            </button>
        </div>
    `;

  modal.innerHTML = previewContent;
  modalOverlay.style.display = "flex";
}

// Notification system
function showNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
        <i class="fas fa-${getNotificationIcon(type)}"></i>
        <span>${message}</span>
    `;

  // Add styles
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        animation: slideInRight 0.3s ease-out;
    `;

  document.body.appendChild(notification);

  // Remove notification after 3 seconds
  setTimeout(() => {
    notification.style.animation = "slideOutRight 0.3s ease-out";
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    }, 300);
  }, 3000);
}

// Helper functions for notifications
function getNotificationIcon(type) {
  const icons = {
    success: "check-circle",
    error: "exclamation-circle",
    warning: "exclamation-triangle",
    info: "info-circle",
  };
  return icons[type] || "info-circle";
}

function getNotificationColor(type) {
  const colors = {
    success: "#10b981",
    error: "#ef4444",
    warning: "#f59e0b",
    info: "#8b5cf6",
  };
  return colors[type] || "#8b5cf6";
}

// Date status checking functionality
function initializeDateStatusCheck() {
  const dateInput = document.getElementById("date");
  const dateStatus = document.getElementById("date-status");

  if (dateInput && dateStatus) {
    // Check status on page load
    checkDateStatus();

    // Check status when date changes
    dateInput.addEventListener("change", checkDateStatus);
  }
}

async function checkDateStatus() {
  const dateInput = document.getElementById("date");
  const dateStatus = document.getElementById("date-status");

  if (!dateInput || !dateStatus) return;

  const selectedDate = dateInput.value;
  if (!selectedDate) {
    dateStatus.innerHTML = "";
    return;
  }

  try {
    const response = await fetch("../../Connection/FetchAnnouncement.php");
    const data = await response.json();

    if (data.success) {
      const announcementsForDate = data.announcements.filter(
        (announcement) => announcement.date === selectedDate
      );

      const count = announcementsForDate.length;

      if (count >= 5) {
        dateStatus.innerHTML = `<span style="color: #dc2626;">⚠️ Maximum announcements reached (${count}/5). Cannot add more announcements for this date.</span>`;
        dateStatus.style.color = "#dc2626";
      } else if (count === 4) {
        dateStatus.innerHTML = `<span style="color: #f59e0b;">⚠️ ${count} announcement(s) for this date (1 remaining)</span>`;
        dateStatus.style.color = "#f59e0b";
      } else {
        dateStatus.innerHTML = `<span style="color: #059669;">✓ ${count} announcement(s) for this date (${
          5 - count
        } remaining)</span>`;
        dateStatus.style.color = "#059669";
      }
    } else {
      console.error("Failed to fetch announcements:", data.message);
      dateStatus.innerHTML = "";
    }
  } catch (error) {
    console.error("Error checking date status:", error);
    dateStatus.innerHTML = "";
  }
}

// Add CSS animations
const style = document.createElement("style");
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .preview-announcement {
        background: var(--input-bg);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
    }
    
    .preview-title {
        color: var(--text-primary);
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .preview-message {
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .preview-meta {
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    
    .modal-btn.secondary {
        background: var(--border-color);
        color: var(--text-secondary);
    }
    
    .modal-btn.secondary:hover {
        background: var(--input-border);
        color: var(--text-primary);
    }
`;
document.head.appendChild(style);

// Initialize theme on page load
document.addEventListener("DOMContentLoaded", () => {
  // Apply saved theme
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);
});
