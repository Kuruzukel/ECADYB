<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College of Information System</title>
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
        const themes = {
            "Theme 1": {
                "--header-bg": "#b21c0e",
                "--body-bg": "#470a0a",
                "--sidebar-bg": "#b21c0e",
                "--content-bg": "#bc4f5e",
                "--menu-bg-active": "#cb5382",
                "--menu-border-active": "#fff176",
                "--menu-hover-bg": "#cb5382"
            },
            "Theme 2": {
                "--header-bg": "#2B35AF",
                "--body-bg": "#12086F",
                "--sidebar-bg": "#2B35AF",
                "--content-bg": "#4895EF",
                "--menu-bg-active": "#4CC9F0",
                "--menu-border-active": "#ffffff",
                "--menu-hover-bg": "#4361EE"
            },
            "Theme 3": {
                "--header-bg": "#164f2c",
                "--body-bg": "#0d381e",
                "--sidebar-bg": "#164f2c",
                "--content-bg": "#2a834d",
                "--menu-bg-active": "#349e5e",
                "--menu-border-active": "#ffffff",
                "--menu-hover-bg": "#1f693c"
            },
            "Theme 4": {
                "--header-bg": "#572D0C",
                "--body-bg": "#281E18",
                "--sidebar-bg": "#572D0C",
                "--content-bg": "#E3B76A",
                "--menu-bg-active": "#9D9C75",
                "--menu-border-active": "#ffffff",
                "--menu-hover-bg": "#C78E3A"
            },
            "Default": {
                "--header-bg": "#0c27be",
                "--body-bg": "#000042",
                "--sidebar-bg": "#0c27be",
                "--content-bg": "#112d4e",
                "--menu-bg-active": "#000042",
                "--menu-border-active": "#fcda15",
                "--menu-hover-bg": "#1c1c84"
            }
        };
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