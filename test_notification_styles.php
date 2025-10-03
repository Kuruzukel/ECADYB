<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Styles Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="Admin/assets/css/BatchUpload.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #112d4e;
            color: white;
            padding: 20px;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .test-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
        .test-button.error {
            background: #f44336;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>Notification Styles Test</h1>
        <p>This page tests the notification styles that are now consistent between BatchUpload.php and BatchTemplates.php</p>
        
        <button class="test-button" onclick="showSuccessNotification()">Show Success Notification</button>
        <button class="test-button error" onclick="showErrorNotification()">Show Error Notification</button>
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
            showNotification("This is a success notification!", "success");
        }
        
        function showErrorNotification() {
            showNotification("This is an error notification!", "error");
        }
    </script>
</body>
</html>