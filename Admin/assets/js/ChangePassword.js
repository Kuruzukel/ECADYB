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
    "--text-color": "#000000",
    "--text-color-secondary": "#212529",
    "--text-color-muted": "#495057",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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
    "--text-color": "#ffffff",
    "--text-color-secondary": "#e9ecef",
    "--text-color-muted": "#ced4da",
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

  if (modalOverlay && modalOverlay.parentElement !== document.body) {
    document.body.appendChild(modalOverlay);
  }

  postBtn.addEventListener("click", (e) => {
    e.preventDefault();

    const currentPassword = document.getElementById("currentPassword").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    if (!currentPassword || !newPassword || !confirmPassword) {
      showNotification("All fields are required.", "error");
      return;
    }

    if (newPassword !== confirmPassword) {
      showNotification(
        "New password and confirm password do not match.",
        "error"
      );
      clearAllFields();
      return;
    }

    if (newPassword.length !== 8) {
      showNotification("Password must be exactly 8 characters.", "error");
      return;
    }

    if (currentPassword === newPassword) {
      showNotification(
        "New password must be different from current password.",
        "error"
      );
      return;
    }

    modalOverlay.style.display = "flex";
  });

  cancelBtn.addEventListener("click", () => {
    modalOverlay.style.display = "none";
  });

  confirmBtn.addEventListener("click", () => {
    modalOverlay.style.display = "none";
    submitPasswordChange();
  });

  modalOverlay.addEventListener("click", (e) => {
    if (e.target === modalOverlay) {
      modalOverlay.style.display = "none";
    }
  });

  // Function to submit password change via AJAX
  function submitPasswordChange() {
    const formData = new FormData(form);
    const basePath = window.location.pathname.includes("/ECADYB/")
      ? "/ECADYB"
      : "";

    // Show loading state
    confirmBtn.disabled = true;
    confirmBtn.textContent = "Changing...";

    fetch(basePath + "/Connection/Admin/ChangePassword.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = "Yes, Change";

        if (data.success) {
          showNotification(data.message, "success");
          // Clear form
          form.reset();
          // Redirect to login after 2 seconds
          setTimeout(() => {
            window.location.href = basePath + "/Public/Components/Login.php";
          }, 2000);
        } else {
          showNotification(data.message, "error");
          // Clear fields if password mismatch error
          if (data.message.includes("do not match")) {
            clearAllFields();
          }
        }
      })
      .catch((error) => {
        confirmBtn.disabled = false;
        confirmBtn.textContent = "Yes, Change";
        showNotification("An error occurred. Please try again.", "error");
        console.error("Error:", error);
      });
  }

  // Function to clear all password fields
  function clearAllFields() {
    document.getElementById("currentPassword").value = "";
    document.getElementById("newPassword").value = "";
    document.getElementById("confirmPassword").value = "";
  }

  // Function to show notifications
  function showNotification(message, type) {
    // Remove existing notifications
    const existingNotification = document.querySelector(".notification");
    if (existingNotification) {
      existingNotification.remove();
    }

    // Create notification element
    const notification = document.createElement("div");
    notification.className = `notification ${type}-message`;

    // Create message container
    const messageDiv = document.createElement("div");
    messageDiv.className = "notification-message";
    messageDiv.textContent = message;

    // Create close button
    const closeBtn = document.createElement("button");
    closeBtn.className = "notification-close";
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.onclick = () => {
      notification.classList.remove("show");
      setTimeout(() => {
        notification.remove();
      }, 300);
    };

    // Append elements
    notification.appendChild(messageDiv);
    notification.appendChild(closeBtn);

    // Add to body
    document.body.appendChild(notification);

    // Show notification
    setTimeout(() => {
      notification.classList.add("show");
    }, 10);

    // Hide and remove after 5 seconds
    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 5000);
  }

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
