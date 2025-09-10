<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College of Business Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../Flipbook/turn.js/dist/style.css" rel="stylesheet">
    <style>
    :root {
        --header-bg: #1d2db2;
        --body-bg: #000042;
        --sidebar-bg: #0c27be;
        --content-bg: #112d4e;
        --menu-bg-active: #000042;
        --menu-border-active: #fcda15;
        --menu-hover-bg: #1c1c84;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        width: 100%;
        background-color: var(--body-bg);
    }

    .container {
        height: 100%;
        background-color: var(--content-bg);
        border-radius: 10px 10px 0 0;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .catalog-root {
        width: 100%;
        height: calc(100vh - 65px);
        background-color: var(--content-bg);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .catalog-app {
        width: 100%;
        height: 100%;
        overflow: hidden;
        position: relative;
        border-radius: 8px;
        background-color: var(--content-bg);
    }

    html:fullscreen .catalog-app,
    html:fullscreen #viewer,
    html:fullscreen #flipbook,
    html:-webkit-full-screen .catalog-app,
    html:-webkit-full-screen #viewer,
    html:-webkit-full-screen #flipbook {
        width: 100%;
        height: 100%;
        max-width: 100vw;
        max-height: 100vh;
        min-width: 1300px;
        min-height: 780px;
    }
    </style>

</head>

<body>
    <div class="container">
        <div class="catalog-root">
            <div class="catalog-app">
                <iframe src="http://localhost/ECADYB/admin/flipbook/turn.js/dist/index.html#page/1" width="100%"
                    height="100%" style="border: none;"></iframe>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-2.0.3.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.4.0/backbone-min.js"></script>
        <script src="./script.js"></script>

        <script>
        // Theme definitions
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

        // Theme selection
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

          // Apply CSS custom properties
          for (const [varName, color] of Object.entries(selectedTheme)) {
            root.style.setProperty(varName, color);
          }

          // Update modal background to match current theme
          const currentSectionBg =
            selectedTheme["--section-bg"] || themes["Default"]["--section-bg"];
          const modal = document.querySelector(".modal");
          if (modal) modal.style.background = currentSectionBg;

          // Add/remove theme-specific CSS classes to body
          const body = document.body;
          // Remove all theme classes first
          body.classList.remove("theme-light-mode", "theme-dark-mode");

          // Add specific theme class
          if (theme === "Light Mode") {
            body.classList.add("theme-light-mode");
          } else if (theme === "Dark Mode") {
            body.classList.add("theme-dark-mode");
          }

          // Save theme to localStorage
          localStorage.setItem("dashboard-theme", theme);

          console.log("Theme applied and saved:", theme);

          // Force a style recalculation
          document.body.style.display = "none";
          document.body.offsetHeight; // Trigger reflow
          document.body.style.display = "";
        }
        document.querySelector('iframe').addEventListener('load', function() {
            const iframeDoc = this.contentDocument || this.contentWindow.document;
            const iframeRoot = iframeDoc.documentElement;

            const computedStyles = getComputedStyle(document.documentElement);
            [
                '--header-bg',
                '--body-bg',
                '--sidebar-bg',
                '--content-bg',
                '--menu-bg-active',
                '--menu-border-active',
                '--menu-hover-bg'
            ].forEach(varName => {
                const value = computedStyles.getPropertyValue(varName);
                iframeRoot.style.setProperty(varName, value);
            });


            const iframeBody = iframeDoc.querySelector('body');
            if (iframeBody) {
                iframeBody.style.backgroundColor = computedStyles.getPropertyValue('--content-bg');
            }
        });

        function applyTheme(themeName) {
            const theme = themes[themeName] || themes["Default"];
            const root = document.documentElement;
            for (const [key, value] of Object.entries(theme)) {
                root.style.setProperty(key, value);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('dashboard-theme') || 'Default';
            applyTheme(savedTheme);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.catalog-app, #viewer, #flipbook');

            elements.forEach(el => {
                el.addEventListener('mousedown', function(e) {
                    const startX = e.clientX;
                    const startY = e.clientY;
                    const startWidth = parseInt(document.defaultView.getComputedStyle(el).width,
                        10);
                    const startHeight = parseInt(document.defaultView.getComputedStyle(el)
                        .height,
                        10);

                    function doDrag(e) {
                        el.style.width = startWidth + e.clientX - startX + 'px';
                        el.style.height = startHeight + e.clientY - startY + 'px';
                    }

                    function stopDrag() {
                        window.removeEventListener('mousemove', doDrag);
                        window.removeEventListener('mouseup', stopDrag);
                    }

                    window.addEventListener('mousemove', doDrag);
                    window.addEventListener('mouseup', stopDrag);
                });
            });
        });
        </script>
    </div>
</body>

</html>