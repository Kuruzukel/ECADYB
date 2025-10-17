<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Theme Selector</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/Admin/assets/css/Themes.css">
</head>

<body>
    <?php
    // Prevent direct access to this file
    if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
        // If accessed directly, redirect to the proper route
        header('Location: ../');
        exit;
    }
    
    // Check if this is being included in AdminDashboard
    $isIncludedInDashboard = defined('ADMIN_DASHBOARD_INCLUDED');
    $outputFullHtml = !$isIncludedInDashboard;
    
    if ($outputFullHtml):
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Dashboard Theme Selector</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="/Admin/assets/css/Themes.css">
    </head>

    <body>
    <?php endif; ?>
    <div class="container">
        <div class="header-container">
            <h1><i class="fas fa-sliders-h"></i> <span class="chevron"><i
                        class="fas fa-chevron-right"></i></span>Appearance</h1>

        </div>
        <div class="form-content">
            <div class="form-group">
                <div class="section">
                    <div class="section-header">Themes</div>
                    <div class="section-content">
                        <div class="color-selector">
                            <div class="color-box color-theme-light" data-label="Light Mode"
                                onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
                            <div class="color-box color-theme-Dark" data-label="Dark Mode" onclick="selectColor(this)">
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                                <div class="color-bar"></div>
                            </div>
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
                            <div class="logo-upload-grid">
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box circle">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="logoInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-overlay">
        <div class="modal" style="font-family: Arial, sans-serif; background: #34495e;">
            <div class="modal-header">
                <i class="fas fa-question-circle modal-icon"></i>
                <h3>Confirm Change</h3>
            </div>
            <div class="modal-content">
                <p>Are you sure you want to change the theme?</p>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn confirm" id="confirm-btn">
                    <i class="fas fa-check"></i>
                    Yes, Change it
                </button>
                <button class="modal-btn cancel" id="cancel-btn">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="delete-modal-overlay">
        <div class="modal" style="background: #34495e;">
            <div class="modal-header">
                <i class="fas fa-question-circle modal-icon"></i>
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-content">
                <p>Are you sure you want to delete this logo?</p>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn confirm" id="confirm-delete-btn">
                    <i class="fas fa-check"></i> Yes, Delete
                </button>
                <button class="modal-btn cancel" id="cancel-delete-btn">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="change-admin-logo-modal">
        <div class="modal" style="background: #34495e;">
            <div class="modal-header">
                <i class="fas fa-image modal-icon"></i>
                <h3>Change Admin Dashboard Logo</h3>
            </div>
            <div class="modal-content">
                <p>Do you want to change the admin dashboard logo to this image?</p>
                <div class="logo-preview">
                    <img id="preview-logo" src="" alt="Logo Preview">
                </div>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn confirm" id="confirm-change-logo-btn">
                    <i class="fas fa-check"></i> Yes, Change it
                </button>
                <button class="modal-btn cancel" id="cancel-change-logo-btn">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <div id="notification-container"></div>

    <div class="upload-overlay" id="upload-overlay">
        <div class="upload-modal" id="uploadModal">
            <h2>Uploading...</h2>
            <p id="uploadText">Please wait while we upload your file</p>
            <div class="loader">
                <div class="loading-bar-background">
                    <div class="loading-bar">
                        <div class="white-bars-container">
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                            <div class="white-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-buttons">
                <button class="modal-btn cancel" onclick="cancelUpload()">Cancel</button>
            </div>
        </div>
    </div>

<?php if ($outputFullHtml): ?>
    <script src="/Admin/assets/js/Themes.js"></script>
</body>

</html>
<?php endif; ?>