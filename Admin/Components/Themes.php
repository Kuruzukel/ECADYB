<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Theme Selector</title>
    <link rel="stylesheet" href="../Assets/css/Themes.css">
</head>

<body>
    <div class="container">
        <div class="header-container">
            <h1>Appearance</h1>
        </div>
        <div class="form-content">
            <div class="form-group">
                <div class="section">
                    <div class="section-header">Themes</div>
                    <div class="section-content">
                        <div class="color-selector">
                            <div class="color-box color-theme1" data-label="Theme 1" onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
                            <div class="color-box color-theme2" data-label="Theme 2" onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
                            <div class="color-box color-theme3" data-label="Theme 3" onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
                            <div class="color-box color-theme4" data-label="Theme 4" onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
                            <div class="color-box color-default" data-label="Default" onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="section">
                    <div class="section-header">Logo Container</div>
                    <div class="section-content">
                        <div class="file-card">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/js/Themes.js"></script>
</body>

</html>