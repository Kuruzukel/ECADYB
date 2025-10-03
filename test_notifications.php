<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="Admin/assets/css/BatchUpload.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #112d4e;
            color: white;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .test-section {
            background: #34495e;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .test-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 10px 10px 0;
            font-size: 16px;
        }
        .test-button.error {
            background: #f44336;
        }
        .test-button.warning {
            background: #ff9800;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Notification System Test</h1>
        <p>This page tests the notification system that should match the style in BatchTemplates.php</p>
        
        <div class="test-section">
            <h2>Test Notifications</h2>
            <button class="test-button" onclick="showSuccessNotification()">Show Success Notification</button>
            <button class="test-button error" onclick="showErrorNotification()">Show Error Notification</button>
            <button class="test-button warning" onclick="showMultipleNotifications()">Show Multiple Notifications</button>
        </div>
        
        <div class="test-section">
            <h2>Notification Features</h2>
            <ul>
                <li>Notifications slide in from top-right corner</li>
                <li>Success notifications have green gradient background</li>
                <li>Error notifications have red gradient background</li>
                <li>Notifications automatically disappear after 5 seconds</li>
                <li>Icons displayed alongside notification text</li>
                <li>Smooth animations for showing and hiding</li>
            </ul>
        </div>
    </div>
    
    <div id="notification-container"></div>
    
    <script>
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
            }, 5000);
        }
        
        function showSuccessNotification() {
            showNotification("Upload successful!", "success");
        }
        
        function showErrorNotification() {
            showNotification("Failed to upload files. Please try again.", "error");
        }
        
        function showMultipleNotifications() {
            showNotification("Successfully uploaded 5 files", "success");
            setTimeout(() => {
                showNotification("Failed to upload 2 files", "error");
            }, 1000);
            setTimeout(() => {
                showNotification("BunnyCDN configuration missing.", "error");
            }, 2000);
        }
    </script>
</body>
</html>