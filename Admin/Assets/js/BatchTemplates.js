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

// ----------------------
// Notifications
// ----------------------
function showNotification(message, type = "success") {
  const container = document.getElementById("notification-container");
  if (!container) return;

  const notif = document.createElement("div");
  notif.className = `notification ${type} show`;
  notif.innerHTML = `
    <i class="fas ${
      type === "success" ? "fa-check-circle" : "fa-exclamation-circle"
    }"></i>
    <span>${message}</span>
  `;
  container.appendChild(notif);

  setTimeout(() => {
    notif.classList.remove("show");
    setTimeout(() => notif.remove(), 500);
  }, 3000);
}

// ----------------------
// Delete student modal
// ----------------------
const deleteModal = document.getElementById("delete-modal-overlay");
const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
const cancelDeleteBtn = document.getElementById("cancel-delete-btn");

let selectedStudentId = null;
let selectedCollection = null;
let selectedConfirmAction = null; // for image deletion in BatchTemplates

function openDeleteModal(studentId, collection) {
  selectedStudentId = studentId?.trim();
  selectedCollection = collection?.trim();
  if (deleteModal) deleteModal.style.display = "flex";
}

function closeDeleteModal() {
  selectedStudentId = null;
  selectedCollection = null;
  selectedConfirmAction = null;
  if (deleteModal) deleteModal.style.display = "none";
}

async function confirmDeleteStudent() {
  if (!selectedStudentId || !selectedCollection) return;

  if (confirmDeleteBtn) confirmDeleteBtn.disabled = true;

  try {
    const res = await fetch(
      `${window.location.origin}/ECADYB/Connection/DeleteStudent.php`,
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          student_id: selectedStudentId,
          collection: selectedCollection,
        }),
      }
    );

    const data = await res.json();
    if (typeof showNotification === "function") {
      showNotification(data.message, data.success ? "success" : "error");
    }

    if (data.success) {
      const row = document
        .querySelector(
          `.student-checkbox[data-student-id="${selectedStudentId}"]`
        )
        ?.closest("tr");
      if (row) row.remove();
    }
  } catch (err) {
    console.error("Error deleting student:", err);
    if (typeof showNotification === "function") {
      showNotification("Error deleting student.", "error");
    }
  } finally {
    if (confirmDeleteBtn) confirmDeleteBtn.disabled = false;
    closeDeleteModal();
  }
}

function initializeDeleteModal() {
  if (!confirmDeleteBtn || !cancelDeleteBtn || !deleteModal) return;

  cancelDeleteBtn.addEventListener("click", closeDeleteModal);
  deleteModal.addEventListener("click", (e) => {
    if (e.target === deleteModal) closeDeleteModal();
  });
  confirmDeleteBtn.addEventListener("click", async () => {
    // Prefer student deletion if ids are set, else run image deletion action
    if (selectedStudentId && selectedCollection) {
      await confirmDeleteStudent();
    } else if (typeof selectedConfirmAction === "function") {
      selectedConfirmAction();
      closeDeleteModal();
    } else {
      closeDeleteModal();
    }
  });
}

window.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
  applyTheme(savedTheme);

  // Track the currently active upload globally so Cancel always aborts it
  let currentXhr = null;

  // Initialize upload boxes (front/back/toggle/delete per box)
  const uploadBoxes = document.querySelectorAll(".upload-box");
  uploadBoxes.forEach((box, index) => {
    const frontInput = box.querySelector(".frontInput");
    const backInput = box.querySelector(".backInput");
    const deleteBtn = box.querySelector(".delete-btn");
    const plusIcon = box.querySelector(".plus-icon");

    let frontImg = null;
    let backImg = null;
    let showingFront = true;
    const slot = index + 1; // 1-based slot index
    const template = 1; // static for now; extend if multiple templates
    const isBackgroundSlot = slot === 8;

    const UPLOAD_ENDPOINT = `${window.location.origin}/ECADYB/Connection/UploadCover.php`;
    const FETCH_ENDPOINT = `${window.location.origin}/ECADYB/Connection/FetchCovers.php?template=${template}`;
    const DELETE_ENDPOINT = `${window.location.origin}/ECADYB/Connection/DeleteCover.php`;

    const toggleImages = () => {
      if (isBackgroundSlot) return; // no toggle for background slot
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
      } else if (!backImg && !isBackgroundSlot) {
        backInput.click();
      } else {
        toggleImages();
      }
    });

    frontInput.addEventListener("change", async (event) => {
      const file = event.target.files && event.target.files[0];
      if (!file) return;
      await uploadToBunny(file, slot, "front");
    });

    if (!isBackgroundSlot) backInput.addEventListener("change", async (event) => {
      const file = event.target.files && event.target.files[0];
      if (!file) return;
      await uploadToBunny(file, slot, "back");
    });

    deleteBtn.addEventListener("click", (event) => {
      event.stopPropagation();
      // Defer actual deletion until modal confirmation
      selectedConfirmAction = () => {
        // Background slot: delete only front
        const sides = [];
        if (frontImg) sides.push("front");
        if (backImg && !isBackgroundSlot) sides.push("back");
        if (!sides.length) return;
        Promise.all(
          sides.map((side) => deleteCover(slot, side))
        ).then(() => {
          frontImg = null;
          backImg = null;
          showingFront = true;
          box.innerHTML = "";
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
      };
      openDeleteModal();
    });

    async function uploadToBunny(file, slot, side) {
      const form = new FormData();
      form.append("file", file);
      form.append("slot", String(slot));
      form.append("side", side);
      form.append("template", String(template));

      const uploadOverlay = document.getElementById("upload-overlay");
      const uploadModal = document.getElementById("uploadModal");
      const progressBar = document.getElementById("progressBar");
      const uploadText = document.getElementById("uploadText");
      const progressPercent = document.getElementById("progressPercent");

      // Show overlay + modal
      if (uploadOverlay && progressBar && uploadText) {
        uploadOverlay.style.display = "flex";
        progressBar.style.width = "0%";
        uploadText.textContent = "Please wait while we upload your file";
        if (progressPercent) progressPercent.textContent = "0%";
      }

      try {
        const data = await xhrUpload(UPLOAD_ENDPOINT, form, (percent) => {
          if (progressBar) progressBar.style.width = `${percent}%`;
          if (progressPercent) progressPercent.textContent = `${percent}%`;
        });

        // If user canceled, do nothing further
        if (data && data.aborted) {
          // Reset UI and exit
          if (progressBar) progressBar.style.width = "0%";
          if (progressPercent) progressPercent.textContent = "0%";
          if (uploadOverlay) uploadOverlay.style.display = "none";
          return;
        }

        if (!data?.success) {
          showNotification(data?.message || "Upload failed", "error");
          return;
        }

        const img = document.createElement("img");
        img.src = data.url;
        img.classList.add(side === "front" ? "front-img" : "back-img");
        if (side === "front") frontImg = img; else backImg = img;

        box.innerHTML = "";
        if (plusIcon) plusIcon.remove();
        ensureChildren();
        deleteBtn.style.display = "flex";
        box.classList.add("has-image");

        // Show front by default
        showingFront = true;
        if (frontImg) {
          frontImg.style.opacity = 1;
          if (backImg && !isBackgroundSlot) backImg.style.opacity = 0;
        }

        showNotification("Image uploaded successfully", "success");
      } finally {
        // Only hide overlay if not actively uploading (i.e., not canceled and no other upload started)
        if (!currentXhr && uploadOverlay) uploadOverlay.style.display = "none";
      }
    }

    function xhrUpload(url, formData, onProgress) {
      return new Promise((resolve) => {
        const xhr = new XMLHttpRequest();
        currentXhr = xhr;
        xhr.open("POST", url, true);
        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable && typeof onProgress === "function") {
            const percent = Math.round((e.loaded / e.total) * 100);
            onProgress(percent);
          }
        };
        xhr.onabort = () => {
          resolve({ aborted: true });
        };
        xhr.onreadystatechange = () => {
          if (xhr.readyState === 4) {
            try {
              resolve(JSON.parse(xhr.responseText));
            } catch (_) {
              resolve(null);
            }
            currentXhr = null;
          }
        };
        xhr.send(formData);
      });
    }

    // Allow cancel button to abort next uploads: simple UI reset
    window.cancelUpload = function () {
      const uploadOverlay = document.getElementById("upload-overlay");
      const progressBar = document.getElementById("progressBar");
      const uploadText = document.getElementById("uploadText");
      const progressPercent = document.getElementById("progressPercent");
      if (currentXhr) {
        try { currentXhr.abort(); } catch (_) {}
        currentXhr = null;
        showNotification("Upload canceled", "error");
      }
      if (progressBar) progressBar.style.width = "0%";
      if (uploadOverlay) uploadOverlay.style.display = "none";
      if (uploadText) uploadText.textContent = "Please wait while we upload your file";
      if (progressPercent) progressPercent.textContent = "0%";
    };

    async function deleteCover(slot, side) {
      const form = new FormData();
      form.append("slot", String(slot));
      form.append("side", side);
      form.append("template", String(template));
      const res = await fetch(DELETE_ENDPOINT, { method: "POST", body: form });
      const data = await res.json().catch(() => null);
      if (!data?.success) {
        showNotification(data?.message || "Delete failed", "error");
      } else {
        showNotification("Image deleted", "success");
      }
    }

    // Load existing
    (async function loadExisting() {
      try {
        const res = await fetch(FETCH_ENDPOINT);
        const data = await res.json();
        if (!data?.success) return;
        const found = (data.items || []).find((i) => i.slot === slot);
        if (!found) return;
        if (found.front_url) {
          frontImg = document.createElement("img");
          frontImg.src = found.front_url;
          frontImg.classList.add("front-img");
        }
        if (found.back_url && !isBackgroundSlot) {
          backImg = document.createElement("img");
          backImg.src = found.back_url;
          backImg.classList.add("back-img");
        }
        if (frontImg || backImg) {
          box.innerHTML = "";
          if (plusIcon) plusIcon.remove();
          ensureChildren();
          deleteBtn.style.display = "flex";
          box.classList.add("has-image");
          showingFront = true;
          if (frontImg) {
            frontImg.style.opacity = 1;
            if (backImg && !isBackgroundSlot) backImg.style.opacity = 0;
          }
        }
      } catch (e) {
        // ignore
      }
    })();
  });
  
  initializeDeleteModal();
});
