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
let currentUploadController = null;

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

function showUploadOverlay(uploadType = "files") {
  const overlay = document.getElementById("upload-overlay");
  const uploadText = document.getElementById("uploadText");

  if (overlay && uploadText) {
    uploadText.textContent = `Please wait while we upload your ${uploadType}`;
    overlay.classList.add("show");
  }
}

function hideUploadOverlay() {
  const overlay = document.getElementById("upload-overlay");
  if (overlay) {
    overlay.classList.remove("show");
  }
}

function cancelUpload() {
  console.log("Cancel upload triggered");

  if (currentUploadController) {
    currentUploadController.abort();
    currentUploadController = null;
  }

  hideUploadOverlay();

  document.querySelectorAll(".upload-input").forEach((input) => {
    if (input.files && input.files.length > 0) {
      input.value = "";
      input.files = new DataTransfer().files;
      forceResetFileUI(input.id);
    }
  });

  currentOperation = null;

  showNotification("Upload cancelled", "error");
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  const cancelBtn = document.getElementById("cancel-upload-btn");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      cancelUpload();
    });
  }

  const selectedTemplateNumber = localStorage.getItem(
    "selectedBatchTemplateNumber"
  );
  const selectedTemplate = localStorage.getItem("selectedBatchTemplate");

  const hidden = document.getElementById("selected_template");
  if (hidden) {
    if (selectedTemplateNumber) {
      hidden.value = selectedTemplateNumber;
      console.log(
        "BatchUpload: Using template number from localStorage:",
        selectedTemplateNumber
      );
    } else if (selectedTemplate) {
      hidden.value = selectedTemplate;
      console.log(
        "BatchUpload: Using template from localStorage:",
        selectedTemplate
      );
    } else {
      hidden.value = "1";
      console.log("BatchUpload: No template selected, defaulting to 1");
    }
  }

  const flash = document.getElementById("flash-data");
  if (flash && typeof showNotification === "function") {
    const msg = flash.getAttribute("data-message");
    const type = flash.getAttribute("data-type") || "success";
    if (msg) showNotification(msg, type);
  }

  document.querySelectorAll(".file-card").forEach((card) => {
    const inputId = card.id.replace("card-", "");
    let fileInput;

    if (inputId === "top-management") {
      fileInput = document.getElementById("top_management_message");
    } else if (inputId === "student-info") {
      fileInput = document.getElementById("student-info");
    } else if (inputId === "student-photos") {
      fileInput = document.getElementById("student-photos");
    } else if (inputId === "management-photos") {
      fileInput = document.getElementById("management-photos");
    }

    if (!fileInput) return;

    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      card.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ["dragenter", "dragover"].forEach((eventName) => {
      card.addEventListener(
        eventName,
        () => {
          card.classList.add("drag-over");
        },
        false
      );
    });

    ["dragleave", "drop"].forEach((eventName) => {
      card.addEventListener(
        eventName,
        () => {
          card.classList.remove("drag-over");
        },
        false
      );
    });

    card.addEventListener(
      "drop",
      (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;

        const dataTransfer = new DataTransfer();
        Array.from(files).forEach((file) => {
          dataTransfer.items.add(file);
        });

        fileInput.files = dataTransfer.files;

        const event = new Event("change", { bubbles: true });
        fileInput.dispatchEvent(event);
      },
      false
    );
  });

  function updateFileUI(input) {
    const inputId = input.id;
    let cardId, infoId;

    if (inputId === "top_management_message") {
      cardId = "card-top-management";
      infoId = "info-top-management";
    } else if (inputId === "student-info") {
      cardId = "card-student-info";
      infoId = "info-student-info";
    } else if (inputId === "student-photos") {
      cardId = "card-student-photos";
      infoId = "info-student-photos";
    } else if (inputId === "management-photos") {
      cardId = "card-management-photos";
      infoId = "info-management-photos";
    }

    const card = document.getElementById(cardId);
    const info = document.getElementById(infoId);

    if (!card || !info) return;

    const hasFiles = input.files && input.files.length > 0;

    if (hasFiles) {
      card.classList.add("has-file");

      const fileNameSpan = info.querySelector(".file-name");
      if (input.files.length === 1) {
        fileNameSpan.textContent = input.files[0].name;
      } else {
        fileNameSpan.textContent = `${input.files.length} images selected`;
      }

      info.classList.add("show");
    } else {
      card.classList.remove("has-file");
      info.classList.remove("show");

      const fileNameSpan = info.querySelector(".file-name");
      if (fileNameSpan) {
        fileNameSpan.textContent = "";
      }

      setTimeout(() => {
        if (fileNameSpan) {
          fileNameSpan.textContent = "";
        }
      }, 100);
    }
  }

  function forceResetFileUI(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.value = "";
    input.files = new DataTransfer().files;

    updateFileUI(input);

    setTimeout(() => {
      updateFileUI(input);
    }, 200);
  }

  document.querySelectorAll(".upload-input").forEach((input) => {
    input.addEventListener("change", async (e) => {
      e.preventDefault();

      updateFileUI(input);

      if (input.files.length > 0) {
        const form = input.form;
        const formData = new FormData();
        const selectedTemplate = document.getElementById("selected_template");

        if (input.id === "student-photos" || input.id === "management-photos") {
          currentOperation = "uploading_photos";
          showUploadOverlay("photos");

          console.log("=== UPLOAD DEBUG ===");
          console.log("Input ID:", input.id);
          console.log("Number of files selected:", input.files.length);
          console.log(
            "File names:",
            Array.from(input.files).map((f) => f.name)
          );

          for (let i = 0; i < input.files.length; i++) {
            console.log(`Appending file ${i + 1}:`, input.files[i].name);
            formData.append(`files[${i}]`, input.files[i]);
          }

          // Debug: Verify FormData
          console.log("FormData entries:");
          for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
              console.log(`  ${key}: [File] ${value.name}`);
            } else {
              console.log(`  ${key}: ${value}`);
            }
          }

          let templateNumber = "1";
          if (selectedTemplate && selectedTemplate.value) {
            const extracted = selectedTemplate.value.replace(/[^0-9]/g, "");
            templateNumber = extracted || "1";
          }
          formData.append("template", templateNumber);
          console.log("Uploading with template number:", templateNumber);

          const basePath = window.location.pathname.includes("/ECADYB/")
            ? "/ECADYB"
            : "";
          const uploadEndpoint =
            input.id === "student-photos"
              ? window.location.origin +
                basePath +
                "/Connection/Photos/UploadStudentPhotos.php"
              : window.location.origin +
                basePath +
                "/Connection/Photos/UPloadTopManagementPhotos.php";

          try {
            currentUploadController = new AbortController();

            const response = await fetch(uploadEndpoint, {
              method: "POST",
              body: formData,
              signal: currentUploadController.signal,
            });

            const result = await response.json();

            if (result.success) {
              const uploadType =
                input.id === "student-photos" ? "Student" : "Top Management";
              const templateName = `Batch Template ${templateNumber}`;
              showNotification(
                `${uploadType} photos uploaded successfully to ${templateName}!`,
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

              forceResetFileUI(input.id);
            } else {
              showNotification(
                result.message || "Upload failed. Please try again.",
                "error"
              );
            }

            hideUploadOverlay();
          } catch (error) {
            if (error.name === "AbortError") {
              console.log("Upload cancelled");
              forceResetFileUI(input.id);
              showNotification("Upload cancelled", "error");
              return;
            }
            console.error("Upload error:", error);
            showNotification("Upload failed. Please try again.", "error");
            hideUploadOverlay();
          } finally {
            currentUploadController = null;
          }
        } else {
          showUploadOverlay("CSV file");
          formData.append(input.name, input.files[0]);
          if (selectedTemplate) {
            formData.append("selected_template", selectedTemplate.value);
          }

          try {
            currentUploadController = new AbortController();

            const response = await fetch(form.action, {
              method: "POST",
              body: formData,
              signal: currentUploadController.signal,
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

                localStorage.removeItem("selectAllState");
              }
            }

            forceResetFileUI(input.id);
          } catch (error) {
            if (error.name === "AbortError") {
              console.log("Upload cancelled");
              forceResetFileUI(input.id);
              showNotification("Upload cancelled", "error");
              return;
            }
            console.error("Upload error:", error);
            showNotification("Upload failed. Please try again.", "error");
          } finally {
            hideUploadOverlay();
            currentUploadController = null;
          }
        }
      }
    });
  });
});
