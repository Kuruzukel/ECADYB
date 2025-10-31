(function () {
  "use strict";

  const LoaderManager = {
    loaderElement: null,
    iframe: null,
    isLoaded: false,
    timeout: null,
    maxWaitTime: 15000,
    checkInterval: null,
    magazineReady: false,
    navigationComplete: false,
    coverVisible: false,
    noteElement: null,
    noteText:
      "If you notice any blurred photos in the yearbook, please proceed to the Accounting Office for assistance.",

    init: function () {
      console.log("[Loader] Initializing yearbook loader...");
      this.loaderElement = document.querySelector(".yearbook-loader-overlay");
      this.iframe = document.getElementById("yearbookIframe");

      if (!this.loaderElement || !this.iframe) {
        console.warn("[Loader] Loader or iframe not found");
        return;
      }

      this.ensureLoaderNote();
      this.setupEventListeners();
      this.setMaxTimeout();
      this.startChecking();
    },

    ensureLoaderNote: function () {
      if (!this.loaderElement) return;

      const loaderText = this.loaderElement.querySelector(".loader-text");
      let textWrapper = loaderText ? loaderText.parentElement : null;

      if (loaderText) {
        if (!textWrapper.classList.contains("loader-text-wrapper")) {
          const wrapper = document.createElement("div");
          wrapper.className = "loader-text-wrapper";
          loaderText.parentNode.insertBefore(wrapper, loaderText);
          wrapper.appendChild(loaderText);
          textWrapper = wrapper;
        }

        let textGroup = textWrapper.querySelector(".loader-text-group");
        if (!textGroup) {
          textGroup = document.createElement("div");
          textGroup.className = "loader-text-group";
          textWrapper.appendChild(textGroup);
        }

        if (!textGroup.contains(loaderText)) {
          textGroup.insertBefore(loaderText, textGroup.firstChild || null);
        }
      }

      let note = this.loaderElement.querySelector(".yearbook-loader-note");

      if (!note) {
        note = document.createElement("div");
        note.className = "yearbook-loader-note";
        note.style.marginTop = "8px";
        note.style.textAlign = "left";
        note.style.lineHeight = "1.4";
        note.style.maxWidth = "360px";

        const icon = document.createElement("span");
        icon.className = "yearbook-loader-note-icon";
        icon.setAttribute("aria-hidden", "true");

        const noteBody = document.createElement("div");
        noteBody.className = "yearbook-loader-note-body";

        const paragraph = document.createElement("p");
        paragraph.className = "yearbook-loader-note-text";
        paragraph.textContent = this.noteText;

        noteBody.appendChild(paragraph);
        note.appendChild(icon);
        note.appendChild(noteBody);
      } else {
        note.style.marginTop = "8px";
        note.style.textAlign = "left";
        let noteBody = note.querySelector(".yearbook-loader-note-body");
        if (!noteBody) {
          noteBody = document.createElement("div");
          noteBody.className = "yearbook-loader-note-body";
          const existingText = note.querySelector(".yearbook-loader-note-text");
          if (existingText) {
            noteBody.appendChild(existingText);
          }
          note.appendChild(noteBody);
        }

        let icon = note.querySelector(".yearbook-loader-note-icon");
        if (!icon) {
          icon = document.createElement("span");
          icon.className = "yearbook-loader-note-icon";
          icon.setAttribute("aria-hidden", "true");
          note.insertBefore(icon, noteBody);
        }

        let textElement = noteBody.querySelector(".yearbook-loader-note-text");
        if (!textElement) {
          textElement = document.createElement("p");
          textElement.className = "yearbook-loader-note-text";
          noteBody.appendChild(textElement);
        } else if (textElement.tagName.toLowerCase() !== "p") {
          const paragraph = document.createElement("p");
          paragraph.className = "yearbook-loader-note-text";
          paragraph.textContent = this.noteText;
          noteBody.replaceChild(paragraph, textElement);
          textElement = paragraph;
        }

        textElement.textContent = this.noteText;
      }

      const textElement = note.querySelector(".yearbook-loader-note-text");
      if (loaderText) {
        const computedStyles = window.getComputedStyle(loaderText);
        note.style.color = computedStyles.color;
        if (textElement) {
          textElement.style.fontSize = computedStyles.fontSize;
        }
      } else if (this.loaderElement) {
        const computedStyles = window.getComputedStyle(this.loaderElement);
        note.style.color = computedStyles.color;
      }

      if (loaderText && textWrapper) {
        const textGroup = textWrapper.querySelector(".loader-text-group");
        if (textGroup) {
          if (note.parentElement !== textGroup) {
            textGroup.appendChild(note);
          } else if (note.nextSibling) {
            textGroup.appendChild(note);
          }
        } else if (note.parentElement !== textWrapper) {
          textWrapper.appendChild(note);
        }
      } else if (note.parentElement !== this.loaderElement) {
        this.loaderElement.appendChild(note);
      }

      this.noteElement = note;
    },

    setupEventListeners: function () {
      const self = this;

      window.addEventListener("message", function (event) {
        if (!event.data || !event.data.type) return;

        console.log("[Loader] Received message:", event.data.type);

        switch (event.data.type) {
          case "yearbook-magazine-initialized":
            console.log("[Loader] Magazine initialized");
            self.magazineReady = true;
            self.checkReadiness();
            break;

          case "yearbook-cover-visible":
            console.log("[Loader] Cover is visible");
            self.coverVisible = true;
            self.checkReadiness();
            break;

          case "yearbook-navigation-complete":
            console.log("[Loader] Navigation to student complete");
            self.navigationComplete = true;
            self.checkReadiness();
            break;

          case "yearbook-ready":
            console.log("[Loader] Yearbook fully ready signal received");
            self.magazineReady = true;
            self.coverVisible = true;
            self.navigationComplete = true;
            self.checkReadiness();
            break;
        }
      });

      this.iframe.addEventListener("load", function () {
        console.log("[Loader] Iframe loaded");
      });
    },

    startChecking: function () {
      const self = this;
      let checkCount = 0;
      const maxChecks = 50;

      this.checkInterval = setInterval(function () {
        checkCount++;

        if (checkCount > maxChecks) {
          console.log("[Loader] Max checks reached, forcing hide");
          clearInterval(self.checkInterval);
          self.hideLoader();
          return;
        }

        try {
          const iframeDoc =
            self.iframe.contentDocument || self.iframe.contentWindow.document;

          if (iframeDoc && iframeDoc.readyState === "complete") {
            const canvas = iframeDoc.getElementById("canvas");
            if (canvas) {
              const canvasVisible =
                window.getComputedStyle(canvas).display !== "none";
              if (canvasVisible && !self.coverVisible) {
                console.log("[Loader] Canvas is visible");
                self.coverVisible = true;
              }
            }

            const magazine = iframeDoc.querySelector(".magazine");
            if (magazine && typeof iframeDoc.defaultView.$ !== "undefined") {
              const $magazine = iframeDoc.defaultView.$(".magazine");
              if (
                $magazine.length > 0 &&
                typeof $magazine.turn === "function" &&
                $magazine.turn("is")
              ) {
                if (!self.magazineReady) {
                  console.log("[Loader] Magazine detected and ready");
                  self.magazineReady = true;
                }
              }
            }

            self.checkReadiness();
          }
        } catch (e) {
          console.log("[Loader] Waiting for postMessage signals...");
        }
      }, 200);
    },

    checkReadiness: function () {
      const urlParams = new URLSearchParams(window.location.search);
      const hasStudent =
        urlParams.has("student_id") && urlParams.has("student_name");

      console.log("[Loader] Checking readiness:", {
        magazineReady: this.magazineReady,
        coverVisible: this.coverVisible,
        navigationComplete: this.navigationComplete,
        hasStudent: hasStudent,
      });

      if (hasStudent) {
        if (
          this.magazineReady &&
          this.coverVisible &&
          this.navigationComplete
        ) {
          console.log("[Loader] All conditions met (with student navigation)");
          this.hideLoader();
        }
      } else {
        if (this.magazineReady && this.coverVisible) {
          console.log(
            "[Loader] All conditions met (without student navigation)"
          );
          this.hideLoader();
        }
      }
    },

    setMaxTimeout: function () {
      const self = this;
      this.timeout = setTimeout(function () {
        if (!self.isLoaded) {
          console.log("[Loader] Max wait time reached, forcing hide");
          self.hideLoader();
        }
      }, this.maxWaitTime);
    },

    hideLoader: function () {
      if (this.isLoaded) return;

      console.log("[Loader] Hiding loader...");
      this.isLoaded = true;

      if (this.timeout) {
        clearTimeout(this.timeout);
        this.timeout = null;
      }

      if (this.checkInterval) {
        clearInterval(this.checkInterval);
        this.checkInterval = null;
      }

      if (this.loaderElement) {
        this.loaderElement.classList.add("hidden");

        setTimeout(
          function () {
            if (this.loaderElement) {
              this.loaderElement.style.display = "none";
              console.log("[Loader] Loader hidden (kept in DOM for reuse)");
            }
          }.bind(this),
          400
        );
      }

      setTimeout(
        function () {
          this.clearURLParameters();
        }.bind(this),
        2000
      );
    },

    clearURLParameters: function () {
      const urlParams = new URLSearchParams(window.location.search);
      const hasStudentParams =
        urlParams.has("student_id") || urlParams.has("student_name");

      if (hasStudentParams) {
        console.log("[Loader] Clearing student URL parameters...");

        urlParams.delete("student_id");
        urlParams.delete("student_name");

        const newUrl = window.location.pathname + "?" + urlParams.toString();

        window.history.replaceState({}, "", newUrl);
        console.log("[Loader] URL cleaned:", newUrl);
      }
    },
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      LoaderManager.init();
    });
  } else {
    LoaderManager.init();
  }

  let lastStudentId = null;
  let isFirstLoad = true;

  function checkForStudentURLChange() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStudentId = urlParams.get("student_id");
    const currentStudentName = urlParams.get("student_name");

    if (isFirstLoad && currentStudentId) {
      lastStudentId = currentStudentId;
      isFirstLoad = false;
      return;
    }

    if (currentStudentId && currentStudentId !== lastStudentId) {
      console.log(
        "[Loader] New student search detected in URL:",
        currentStudentId
      );
      lastStudentId = currentStudentId;

      const loader = document.querySelector(".yearbook-loader-overlay");
      if (loader) {
        loader.classList.remove("hidden");
        loader.style.display = "flex";
        loader.style.opacity = "1";
        loader.style.visibility = "visible";
        loader.style.pointerEvents = "auto";
        console.log("[Loader] Loader shown for student navigation");
      }

      const iframe = document.getElementById("yearbookIframe");
      if (iframe && iframe.contentWindow) {
        console.log(
          "[Loader] Sending navigate message to iframe for student:",
          currentStudentId
        );

        try {
          const iframeSrc = new URL(iframe.src);
          iframeSrc.searchParams.set("student_id", currentStudentId);
          iframeSrc.searchParams.set("student_name", currentStudentName);

          iframe.contentWindow.postMessage(
            {
              type: "navigate-to-student",
              studentId: currentStudentId,
              studentName: currentStudentName,
            },
            "*"
          );

          console.log("[Loader] Posted navigate message to iframe");

          if (LoaderManager.timeout) {
            clearTimeout(LoaderManager.timeout);
            LoaderManager.timeout = null;
          }
          if (LoaderManager.checkInterval) {
            clearInterval(LoaderManager.checkInterval);
            LoaderManager.checkInterval = null;
          }

          LoaderManager.isLoaded = false;
          LoaderManager.magazineReady = false;
          LoaderManager.coverVisible = false;
          LoaderManager.navigationComplete = false;

          LoaderManager.loaderElement = loader;
          LoaderManager.ensureLoaderNote();
          LoaderManager.iframe = iframe;

          LoaderManager.setMaxTimeout();
          LoaderManager.startChecking();
        } catch (e) {
          console.error("[Loader] Error communicating with iframe:", e);

          const iframeSrc = new URL(iframe.src);
          iframeSrc.searchParams.set("student_id", currentStudentId);
          iframeSrc.searchParams.set("student_name", currentStudentName);
          iframe.src = iframeSrc.toString();
        }
      }
    }
  }

  setInterval(checkForStudentURLChange, 300);

  window.YearbookLoader = LoaderManager;
})();
