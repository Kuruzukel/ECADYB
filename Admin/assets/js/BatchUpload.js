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

let notificationTimeout = null;
let currentOperation = null;
let currentUploadController = null;
let isCancelling = false;

function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const existingNotifications = container.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  if (notificationTimeout) {
    clearTimeout(notificationTimeout);
  }

  let messageClass = type === "error" ? "error-message" : "success-message";

  const notif = document.createElement("div");
  notif.className = `notification ${messageClass}`;
  notif.id = `${type}-notification`;
  notif.innerHTML = `
    <span class="notification-message">${message}</span>
    <button class="notification-close" onclick="closeNotification('${type}-notification')">
      <i class="fas fa-times"></i>
    </button>
  `;
  container.appendChild(notif);

  // Trigger animation
  setTimeout(() => {
    notif.classList.add("show");
  }, 10);

  const duration = type === "info" ? 2000 : 5000;
  notificationTimeout = setTimeout(() => {
    closeNotification(`${type}-notification`);
  }, duration);
}

function closeNotification(id) {
  const notification = document.getElementById(id);
  if (notification) {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
      notificationTimeout = null;
      currentOperation = null;
    }, 500);
  }
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
      const fileType =
        inputId === "student-info" || inputId === "top_management_message"
          ? "CSV files"
          : "images";
      fileNameSpan.textContent = `${input.files.length} ${fileType} selected`;
    }

    console.log(`Files selected for ${inputId}:`, input.files.length);
    for (let i = 0; i < input.files.length; i++) {
      console.log(`  File ${i}: ${input.files[i].name}`);
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

function cancelUpload() {
  console.log("Cancel upload triggered");

  isCancelling = true;

  if (currentUploadController) {
    console.log("Aborting current upload...");
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

  setTimeout(() => {
    isCancelling = false;
  }, 500);
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  const batchYearSelect = document.getElementById("batch-year-select");
  if (batchYearSelect) {
    const savedBatchYear = localStorage.getItem("selectedBatchYear");
    if (savedBatchYear) {
      const cleanBatchYear = savedBatchYear.replace("Batch Year ", "");
      batchYearSelect.value = cleanBatchYear;
      console.log("Loaded saved batch year:", cleanBatchYear);
    }

    batchYearSelect.addEventListener("change", (e) => {
      const selectedYear = e.target.value;
      if (selectedYear) {
        const formattedYear = "Batch Year " + selectedYear;
        localStorage.setItem("selectedBatchYear", formattedYear);
        console.log("Batch year selected and saved:", formattedYear);
        showNotification(`Academic Year ${selectedYear} selected`, "success");
      } else {
        localStorage.removeItem("selectedBatchYear");
        console.log("Batch year selection cleared");
      }
    });
  }

  const cancelBtn = document.getElementById("cancel-upload-btn");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      cancelUpload();
    });
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

  document.querySelectorAll(".upload-input").forEach((input) => {
    input.addEventListener("change", async (e) => {
      e.preventDefault();

      updateFileUI(input);

      if (input.files.length > 0) {
        const form = input.form;
        const formData = new FormData();

        if (input.id === "student-photos" || input.id === "management-photos") {
          const selectedBatchYear = localStorage.getItem("selectedBatchYear");

          console.log("=== BATCH YEAR VALIDATION ===");
          console.log(
            "selectedBatchYear from localStorage:",
            selectedBatchYear
          );
          console.log(
            "Batch year select element value:",
            document.getElementById("batch-year-select")?.value
          );

          if (!selectedBatchYear) {
            console.log("❌ No batch year selected - showing warning");
            showNotification(
              "Please select an Academic Year before uploading photos.",
              "warning"
            );
            forceResetFileUI(input.id);
            return;
          }

          console.log("✅ Batch year validation passed:", selectedBatchYear);

          const MAX_FILES = 20;
          const fileCount = input.files.length;

          if (fileCount > MAX_FILES) {
            showNotification(
              `You can only upload a maximum of ${MAX_FILES} images at a time. You selected ${fileCount} images. Please reduce the number of files.`,
              "error"
            );
            forceResetFileUI(input.id);
            return;
          }

          let totalSize = 0;
          for (let i = 0; i < input.files.length; i++) {
            totalSize += input.files[i].size;
          }

          const totalSizeMB = totalSize / (1024 * 1024);
          const MAX_SIZE_MB = 500;

          console.log(
            `Total files: ${fileCount}, Total upload size: ${totalSizeMB.toFixed(
              2
            )} MB`
          );

          if (totalSizeMB > MAX_SIZE_MB) {
            showNotification(
              `Total file size (${totalSizeMB.toFixed(
                2
              )} MB) exceeds the maximum limit of ${MAX_SIZE_MB} MB. Please reduce the number of files or compress them.`,
              "error"
            );
            forceResetFileUI(input.id);
            return;
          }

          currentOperation = "uploading_photos";

          isCancelling = false;

          showUploadOverlay("photos");
          const uploadText = document.getElementById("uploadText");

          console.log("=== FILE UPLOAD DEBUG ===");
          console.log("Input ID:", input.id);
          console.log("Number of files selected:", input.files.length);
          console.log(
            "File names:",
            Array.from(input.files).map((f) => f.name)
          );
          console.log("FormData entries before sending:");
          for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
              console.log(`  ${key}: [File] ${value.name}`);
            } else {
              console.log(`  ${key}: ${value}`);
            }
          }

          console.log("🚀 Starting instant photo upload");
          let uploadCancelled = false;

          if (isCancelling) {
            console.log(`✅ Upload cancelled before start`);
            uploadCancelled = true;
            forceResetFileUI(input.id);
            return;
          }

          if (uploadText) {
            const uploadType =
              input.id === "student-photos"
                ? "Student photos"
                : "Management photos";
            uploadText.textContent = `Uploading ${uploadType}...`;
          }

          for (let i = 0; i < input.files.length; i++) {
            console.log(`Appending file ${i + 1}:`, input.files[i].name);
            formData.append(`files[]`, input.files[i]);
          }

          console.log("=== BATCH YEAR DEBUG ===");
          console.log(
            "selectedBatchYear from localStorage:",
            selectedBatchYear
          );
          formData.append("batch_year", selectedBatchYear);
          console.log("Added batch year to FormData:", selectedBatchYear);

          console.log("FormData entries:");
          for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
              console.log(`  ${key}: [File] ${value.name}`);
            } else {
              console.log(`  ${key}: ${value}`);
            }
          }

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
                "/Connection/Photos/UploadTopManagementPhotos.php";

          console.log("=== UPLOAD ENDPOINT DEBUG ===");
          console.log("Upload endpoint:", uploadEndpoint);
          console.log("Base path:", basePath);
          console.log("Window location origin:", window.location.origin);

          try {
            const xhr = new XMLHttpRequest();
            currentUploadController = { abort: () => xhr.abort() };

            xhr.upload.addEventListener("progress", (e) => {
              if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                console.log(`Upload progress: ${percentComplete}%`);
              }
            });

            const uploadPromise = new Promise((resolve, reject) => {
              xhr.onload = () => {
                console.log("=== UPLOAD RESPONSE DEBUG ===");
                console.log("Response status:", xhr.status);
                console.log("Response headers:", xhr.getAllResponseHeaders());
                console.log("Response text:", xhr.responseText);

                if (xhr.status >= 200 && xhr.status < 300) {
                  try {
                    const result = JSON.parse(xhr.responseText);
                    console.log("Parsed JSON result:", result);
                    resolve(result);
                  } catch (e) {
                    console.error("JSON parse error:", e);
                    console.error("Raw response:", xhr.responseText);
                    reject(new Error("Invalid JSON response"));
                  }
                } else {
                  console.error("HTTP error status:", xhr.status);
                  reject(new Error(`Upload failed with status ${xhr.status}`));
                }
              };

              xhr.onerror = () => {
                console.error("=== UPLOAD ERROR ===");
                console.error("Network error occurred");
                reject(new Error("Network error"));
              };
              xhr.onabort = () => {
                const abortError = new Error("Upload cancelled");
                abortError.name = "AbortError";
                reject(abortError);
              };

              console.log("=== SENDING REQUEST ===");
              console.log("Opening POST request to:", uploadEndpoint);
              xhr.open("POST", uploadEndpoint);
              console.log(
                "Sending FormData with",
                formData.getAll("files[]").length,
                "files"
              );
              xhr.send(formData);
            });

            const result = await uploadPromise;

            if (result.success) {
              const uploadType =
                input.id === "student-photos" ? "Student" : "Top Management";
              showNotification(
                `${uploadType} photos uploaded successfully!`,
                "success"
              );

              if (result.uploaded > 0) {
                const imageText = result.uploaded === 1 ? "image" : "images";
                showNotification(
                  `Successfully uploaded ${result.uploaded} ${imageText}.`,
                  "success"
                );
              }

              if (result.failed > 0) {
                const imageText = result.failed === 1 ? "image" : "images";
                showNotification(
                  `Failed to upload ${result.failed} ${imageText}. Check file names and try again.`,
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

          if (input.files.length > 1) {
            console.log(`Sending ${input.files.length} CSV files to server`);
            for (let i = 0; i < input.files.length; i++) {
              console.log(`  Appending file ${i}: ${input.files[i].name}`);
              formData.append(input.name, input.files[i]);
            }
          } else {
            console.log(`Sending 1 CSV file to server: ${input.files[0].name}`);
            formData.append(input.name, input.files[0]);
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
