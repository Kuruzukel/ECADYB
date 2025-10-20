<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Templates</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $basePath ?>/Admin/assets/css/BatchTemplates.css">
</head>

<body>
    <?php
    if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
        header('Location: ../');
        exit;
    }

    $isIncludedInDashboard = defined('ADMIN_DASHBOARD_INCLUDED');
    $outputFullHtml = !$isIncludedInDashboard;

    if ($outputFullHtml):
    ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Batch Templates</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
            <link rel="stylesheet" href="<?= $basePath ?>/Admin/assets/css/BatchTemplates.css">
        </head>

        <body>
        <?php endif; ?>
        <div class="container" style="font-family: Arial, sans-serif;">
            <div class="header-container" style="width: 100%;">
                <h1><i class="fas fa-sliders-h"></i> <span class="chevron"><i
                            class="fas fa-chevron-right"></i></span>Choose
                    Template</h1>
            </div>
            <div class="form-content" style="width: 100%;">

                <div class="form-group">
                    <div class="section">
                        <div class="section-header">Batch Year 2024-2025</div>
                        <div class="section-content">
                            <div class="upload-grid">
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box action-box">
                                    <div class="action-buttons">
                                        <button class="action-btn select-batch-btn" title="Select Batch">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Select Batch</span>
                                        </button>
                                        <button class="action-btn download-pdf-btn" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                        </button>
                                        <button class="action-btn delete-batch-btn" title="Delete Batch Template" disabled>
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Delete Batch</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Batch Year 2025-2026</div>
                        <div class="section-content">
                            <div class="upload-grid">
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box action-box">
                                    <div class="action-buttons">
                                        <button class="action-btn select-batch-btn" title="Select Batch">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Select Batch</span>
                                        </button>
                                        <button class="action-btn download-pdf-btn" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                        </button>
                                        <button class="action-btn delete-batch-btn" title="Delete Batch Template" disabled>
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Delete Batch</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <div class="section-header">Batch Year 2026-2027</div>
                        <div class="section-content">
                            <div class="upload-grid">
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" multiple hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box">
                                    <span class="plus-icon">+</span>
                                    <input type="file" class="frontInput" accept="image/*" hidden>
                                    <input type="file" class="backInput" accept="image/*" hidden>
                                    <button class="delete-btn">&times;</button>
                                </div>
                                <div class="upload-box action-box">
                                    <div class="action-buttons">
                                        <button class="action-btn select-batch-btn" title="Select Batch">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Select Batch</span>
                                        </button>
                                        <button class="action-btn download-pdf-btn" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Download PDF</span>
                                        </button>
                                        <button class="action-btn delete-batch-btn" title="Delete Batch Template" disabled>
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Delete Batch</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="generate-button-container">
                    <button class="generate-batch-btn" id="generateBatchBtn">
                        <span>Generate Batch Template</span>
                    </button>
                </div>
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
                    <p>Are you sure you want to delete this image?</p>
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

        <div class="modal-overlay" id="generate-modal-overlay">
            <div class="modal" style="background: #34495e;">
                <div class="modal-header">
                    <h3>Generate Batch Template</h3>
                </div>
                <div class="modal-content">
                    <p>Are you sure you want to generate the batch template?</p>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn confirm" id="confirm-generate-btn">
                        <i class="fas fa-check"></i> Yes, Generate
                    </button>
                    <button class="modal-btn cancel" id="cancel-generate-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="download-pdf-modal-overlay">
            <div class="modal" style="background: #34495e;">
                <div class="modal-header">
                    <i class="fas fa-file-pdf modal-icon"></i>
                    <h3>Download PDF</h3>
                </div>
                <div class="modal-content">
                    <p id="download-pdf-message">Are you sure you want to download the PDF for this batch?</p>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn confirm" id="confirm-download-pdf-btn">
                        <i class="fas fa-download"></i> Yes, Download
                    </button>
                    <button class="modal-btn cancel" id="cancel-download-pdf-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="delete-batch-modal-overlay">
            <div class="modal" style="background: #34495e;">
                <div class="modal-header">
                    <i class="fas fa-trash-alt modal-icon"></i>
                    <h3>Delete Batch Template</h3>
                </div>
                <div class="modal-content">
                    <p id="delete-batch-message">Are you sure you want to delete this batch template? This action cannot be undone.</p>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn confirm" id="confirm-delete-batch-btn">
                        <i class="fas fa-trash"></i> Yes, Delete
                    </button>
                    <button class="modal-btn cancel" id="cancel-delete-batch-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="select-template-modal-overlay">
            <div class="modal" style="background: #34495e;">
                <div class="modal-header">
                    <i class="fas fa-check-circle modal-icon"></i>
                    <h3>Select Batch Template</h3>
                </div>
                <div class="modal-content">
                    <p id="select-template-message">Do you want to select this batch template?</p>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn confirm" id="confirm-select-template-btn">
                        <i class="fas fa-check"></i> Yes, Select
                    </button>
                    <button class="modal-btn cancel" id="cancel-select-template-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>

        <div id="notification-container"></div>

        <div class="upload-overlay" id="upload-overlay">
            <div class="upload-modal" id="uploadModal">
                <h2>Uploading...</h2>
                <p id="uploadText">Please wait while we upload your images</p>

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
            <script src="<?= $basePath ?>/Admin/assets/js/BatchTemplates.js"></script>
        </body>

        </html>
    <?php endif; ?>