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
      this.setupOrientationListener();
      this.setMaxTimeout();
      this.startChecking();
    },

    ensureLoaderNote: function () {
      if (!this.loaderElement) return;

      const loaderContent = this.loaderElement.querySelector(".loader-content");
      if (!loaderContent) return;

      let noteWrapper = loaderContent.querySelector(".loader-note-wrapper");
      if (noteWrapper) {
        const textPara = noteWrapper.querySelector(
          ".yearbook-loader-note-text"
        );
        if (textPara) {
          textPara.textContent = this.noteText;
        }
        return;
      }

      const spinner = loaderContent.querySelector(".spinner");
      const loaderText = loaderContent.querySelector(".loader-text");

      if (spinner && loaderText) {
        let textWrapper = loaderContent.querySelector(".loader-text-wrapper");
        if (!textWrapper) {
          textWrapper = document.createElement("div");
          textWrapper.className = "loader-text-wrapper";
          loaderContent.insertBefore(textWrapper, loaderContent.firstChild);
          textWrapper.appendChild(spinner);
          textWrapper.appendChild(loaderText);
        }
      }

      noteWrapper = document.createElement("div");
      noteWrapper.className = "loader-note-wrapper";

      const noteIcon = document.createElement("div");
      noteIcon.className = "yearbook-loader-note";
      noteIcon.setAttribute("aria-hidden", "true");

      const noteParagraph = document.createElement("p");
      noteParagraph.className = "yearbook-loader-note-text";
      noteParagraph.textContent = this.noteText;

      noteWrapper.appendChild(noteIcon);
      noteWrapper.appendChild(noteParagraph);

      loaderContent.appendChild(noteWrapper);
      this.noteElement = noteWrapper;

      let orientationMsg = loaderContent.querySelector(".orientation-message");
      if (!orientationMsg) {
        orientationMsg = document.createElement("div");
        orientationMsg.className = "orientation-message";
        orientationMsg.innerHTML =
          '<div class="icon-group"><i class="fas fa-mobile-screen-button"></i><i class="fas fa-arrow-right rotate-icon"></i><i class="fas fa-mobile-screen"></i></div>Please rotate your phone to landscape mode for the best viewing experience';
        loaderContent.appendChild(orientationMsg);
      }
    },

    setupOrientationListener: function () {
      const self = this;

      function checkOrientation() {
        const isMobile = window.innerWidth <= 768;
        const isPortrait = window.innerHeight > window.innerWidth;

        if (isMobile && isPortrait) {
          console.log(
            "[Loader] Mobile portrait mode detected - keeping loader visible"
          );
          if (self.loaderElement) {
            self.loaderElement.classList.remove("hidden");
            self.loaderElement.style.display = "flex";
            self.loaderElement.style.opacity = "1";
            self.loaderElement.style.visibility = "visible";
            self.loaderElement.style.pointerEvents = "auto";
          }
          // Reset isLoaded so we can show the loader again if needed
          self.isLoaded = false;
        } else if (isMobile && !isPortrait) {
          console.log(
            "[Loader] Mobile landscape mode detected - checking if ready to hide"
          );
          // In landscape, check readiness to potentially hide the loader
          self.checkReadiness();
        }
      }

      // Check on load
      checkOrientation();

      // Listen for orientation changes
      window.addEventListener("orientationchange", function () {
        setTimeout(checkOrientation, 100);
      });

      // Also listen for resize as a fallback
      window.addEventListener("resize", function () {
        checkOrientation();
      });
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

      // Check if in mobile portrait mode
      const isMobile = window.innerWidth <= 768;
      const isPortrait = window.innerHeight > window.innerWidth;

      console.log("[Loader] Checking readiness:", {
        magazineReady: this.magazineReady,
        coverVisible: this.coverVisible,
        navigationComplete: this.navigationComplete,
        hasStudent: hasStudent,
        isMobile: isMobile,
        isPortrait: isPortrait,
      });

      // Don't hide loader if mobile device is in portrait mode
      if (isMobile && isPortrait) {
        console.log("[Loader] Mobile portrait mode - keeping loader visible");
        return;
      }

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
          // Check if in mobile portrait mode before forcing hide
          const isMobile = window.innerWidth <= 768;
          const isPortrait = window.innerHeight > window.innerWidth;

          if (isMobile && isPortrait) {
            console.log(
              "[Loader] Max wait time reached but in portrait mode - keeping loader visible"
            );
            return;
          }

          console.log("[Loader] Max wait time reached, forcing hide");
          self.hideLoader();
        }
      }, this.maxWaitTime);
    },

    hideLoader: function () {
      if (this.isLoaded) return;

      // Final check: don't hide if in mobile portrait mode
      const isMobile = window.innerWidth <= 768;
      const isPortrait = window.innerHeight > window.innerWidth;

      if (isMobile && isPortrait) {
        console.log("[Loader] Cannot hide loader - device in portrait mode");
        return;
      }

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

        // Ensure orientation message exists when showing loader again
        const loaderContent = loader.querySelector(".loader-content");
        if (
          loaderContent &&
          !loaderContent.querySelector(".orientation-message")
        ) {
          const orientationMsg = document.createElement("div");
          orientationMsg.className = "orientation-message";
          orientationMsg.innerHTML =
            '<div class="icon-group"><i class="fas fa-mobile-screen-button"></i><i class="fas fa-arrow-right rotate-icon"></i><i class="fas fa-mobile-screen"></i></div>Please rotate your phone to landscape mode for the best viewing experience';
          loaderContent.appendChild(orientationMsg);
        }
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
