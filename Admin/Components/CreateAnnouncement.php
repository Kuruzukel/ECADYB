<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Announcement</title>
    <link rel="stylesheet" href="<?= $basePath ?>/Admin/assets/css/CreateAnnouncement.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <title>Create Announcement</title>
            <link rel="stylesheet" href="<?= $basePath ?>/Admin/assets/css/CreateAnnouncement.css">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
                rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        </head>

        <body>
        <?php endif; ?>
        <div class="container" style="font-family: Arial;">
            <div class="header-container" style="width: 100%;">
                <h1><i class="fas fa-bullhorn"></i> <span class="chevron"><i
                            class="fas fa-chevron-right"></i></span>Create
                    Announcement</h1>
            </div>

            <div class="form-content">
                <form id="announcementForm" action="../../../Connection/Announcement/SubmitAnnouncement.php"
                    method="post">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter announcement title" required />

                    <label for="message">Message</label>
                    <textarea id="message" name="message" placeholder="Write your announcement here..."
                        required></textarea>

                    <label for="date">Date (optional)</label>
                    <input type="date" id="date" name="date" value="<?php date_default_timezone_set('Asia/Manila');
                                                                    echo date('Y-m-d'); ?>" />
                    <div id="date-status" class="date-status"
                        style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;"></div>

                    <label for="time">Time (optional)</label>
                    <input type="time" id="time" name="time" />

                    <div class="form-actions">
                        <button type="submit" class="btn-primary" id="post-announcement-btn">
                            <i class="fas fa-paper-plane"></i>
                            Post Announcement
                        </button>
                    </div>
                </form>
            </div>

            <div class="modal-overlay" id="modal-overlay">
                <div class="modal" style="font-family: Arial, sans-serif;">
                    <div class="modal-header">
                        <i class="fas fa-question-circle modal-icon"></i>
                        <h3>Confirm Post</h3>
                    </div>
                    <div class="modal-content">
                        <p>Are you sure you want to post this announcement?</p>
                    </div>
                    <div class="modal-buttons">
                        <button class="modal-btn confirm" id="confirm-btn">
                            <i class="fas fa-check"></i>
                            Yes, Post
                        </button>
                        <button class="modal-btn cancel" id="cancel-btn">
                            <i class="fas fa-times"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div id="notification-container"></div>

        </div>
        <?php if ($outputFullHtml): ?>
            <script src="<?= $basePath ?>/Admin/assets/js/CreateAnnouncement.js"></script>
        </body>

        </html>
    <?php endif; ?>