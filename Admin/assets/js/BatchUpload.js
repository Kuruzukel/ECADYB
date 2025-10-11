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

let notificationTimeout = null;
let currentOperation = null;

function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const existingNotifications = container.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }

  let icon = "fa-check-circle";
  if (type === "error") {
    icon = "fa-exclamation-circle";
  } else if (type === "info") {
    icon = "fa-info-circle";
  } else if (type === "warning") {
    icon = "fa-exclamation-triangle";
  }

  const notif = document.createElement("div");
  notif.className = `notification ${type} show`;
  notif.innerHTML = `
      <i class="fas ${icon}"></i>
      <span>${message}</span>
    `;
  container.appendChild(notif);

  const duration = type === "info" ? 2000 : 5000;
  notificationTimeout = setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => {
      notif.remove();
      notificationTimeout = null;
      currentOperation = null;
    }, 500);
  }, duration);
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  const selectedTemplate = localStorage.getItem("selectedBatchTemplate");
  if (selectedTemplate) {
    const hidden = document.getElementById("selected_template");
    if (hidden) hidden.value = selectedTemplate;
  }

  const flash = document.getElementById("flash-data");
  if (flash && typeof showNotification === "function") {
    const msg = flash.getAttribute("data-message");
    const type = flash.getAttribute("data-type") || "success";
    if (msg) showNotification(msg, type);
  }

  document.querySelectorAll(".upload-input").forEach((input) => {
    input.addEventListener("change", async (e) => {
      e.preventDefault();
      if (input.files.length > 0) {
        const form = input.form;
        const formData = new FormData();
        const selectedTemplate = document.getElementById("selected_template");

        if (input.id === "student-photos" || input.id === "management-photos") {
          currentOperation = "uploading_photos";
          showNotification("Uploading photos...", "info");

          Array.from(input.files).forEach((file) => {
            formData.append("files[]", file);
          });

          if (selectedTemplate) {
            formData.append(
              "template",
              selectedTemplate.value.replace(/[^0-9]/g, "")
            );
          }

          const uploadEndpoint =
            input.id === "student-photos"
              ? "../../Connection/Photos/UploadStudentPhotos.php"
              : "../../Connection/Photos/UPloadTopManagementPhotos.php";

          try {
            const response = await fetch(uploadEndpoint, {
              method: "POST",
              body: formData,
            });

            const result = await response.json();

            if (result.success) {
              const uploadType =
                input.id === "student-photos" ? "Student" : "Top Management";
              const template = selectedTemplate
                ? selectedTemplate.value
                : "Batch Template 1";
              showNotification(
                `${uploadType} photos uploaded successfully to ${template}!`,
                "success"
              );

              if (result.uploaded > 0) {
                showNotification(
                  `Successfully uploaded ${result.uploaded} of ${result.total} images.`,
                  "success"
                );
              }

              if (result.failed > 0) {
                showNotification(
                  `Failed to upload ${result.failed} images. Check file names and try again.`,
                  "error"
                );
              }
            } else {
              showNotification(
                result.message || "Upload failed. Please try again.",
                "error"
              );
            }
          } catch (error) {
            console.error("Upload error:", error);
            showNotification("Upload failed. Please try again.", "error");
          }
        } else {
          formData.append(input.name, input.files[0]);
          if (selectedTemplate) {
            formData.append("selected_template", selectedTemplate.value);
          }

          try {
            const response = await fetch(form.action, {
              method: "POST",
              body: formData,
            });

            const result = await response.text();

            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = result;

            if (tempDiv.querySelector("#notification-container")) {
              const notificationScript = tempDiv.querySelector(
                "script[data-notification]"
              );
              if (notificationScript) {
                eval(notificationScript.textContent);

                // This ensures the select all checkbox is unchecked on the StudentList page
                // since all newly uploaded students have "Pending" status
                localStorage.removeItem("selectAllState");
              }
            }

            // Reset only the current file input
            input.value = "";
          } catch (error) {
            console.error("Upload error:", error);
            showNotification("Upload failed. Please try again.", "error");
          }
        }
      }
    });
  });
});
