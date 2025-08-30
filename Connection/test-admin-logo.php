<?php
// Test page for admin logo functionality
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logo Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .test-container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        button { padding: 10px 20px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .logo-display { text-align: center; margin: 20px 0; }
        .logo-display img { max-width: 200px; max-height: 200px; border: 2px solid #ddd; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔧 Admin Logo Functionality Test</h1>
        <p>This page tests the admin logo update functionality.</p>

        <div class="test-section info">
            <h3>📋 Test Instructions</h3>
            <ol>
                <li>Go to the Themes page in your admin dashboard</li>
                <li>Upload a logo or use an existing one</li>
                <li>Click on an existing logo (not the delete button)</li>
                <li>You should see a confirmation modal asking if you want to change the admin dashboard logo</li>
                <li>Click "Yes, Change it" to update the admin dashboard logo</li>
                <li>Return to the main admin dashboard to see the new logo</li>
            </ol>
        </div>

        <div class="test-section success">
            <h3>✅ Current Admin Logo</h3>
            <div class="logo-display">
                <img id="current-admin-logo" src="https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png" alt="Current Admin Logo">
            </div>
            <p><strong>Current Logo URL:</strong> <span id="current-logo-url">Loading...</span></p>
        </div>

        <div class="test-section info">
            <h3>🧪 Test Endpoints</h3>
            <p><strong>Fetch Admin Logo:</strong> <a href="./FetchAdminLogo.php" target="_blank">Test Fetch</a></p>
            <p><strong>Update Admin Logo:</strong> <button class="btn-primary" onclick="testUpdateLogo()">Test Update</button></p>
            <p><strong>Reset to Default:</strong> <button class="btn-danger" onclick="resetToDefault()">Reset Logo</button></p>
        </div>

        <div class="test-section warning">
            <h3>⚠️ Important Notes</h3>
            <ul>
                <li>The logo change only affects the admin dashboard sidebar</li>
                <li>Changes are stored in the MongoDB AdminSettings collection</li>
                <li>If no custom logo is set, it defaults to the original ADMINGRALLERYLOGO.png</li>
                <li>Logo changes are applied immediately to the current page</li>
            </ul>
        </div>

        <div class="test-section info">
            <h3>🔗 Quick Links</h3>
            <p><a href="../Admin/Components/Themes.php" target="_blank">Go to Themes Page</a></p>
            <p><a href="../Admin/Components/AdminDashboard.php" target="_blank">Go to Admin Dashboard</a></p>
        </div>
    </div>

    <script>
        // Load current admin logo on page load
        async function loadCurrentAdminLogo() {
            try {
                const response = await fetch('./FetchAdminLogo.php');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.logo_url) {
                        const currentLogo = document.getElementById('current-admin-logo');
                        const currentUrl = document.getElementById('current-logo-url');
                        if (currentLogo) currentLogo.src = data.logo_url;
                        if (currentUrl) currentUrl.textContent = data.logo_url;
                    }
                }
            } catch (error) {
                console.error('Failed to load current admin logo:', error);
            }
        }

        // Test update logo functionality
        async function testUpdateLogo() {
            const testLogoUrl = 'https://ECADYB.b-cdn.net/img/GRALLERYLOGO.png';
            try {
                const form = new FormData();
                form.append('logo_url', testLogoUrl);
                form.append('slot', '1');
                
                const response = await fetch('./UpdateAdminLogo.php', {
                    method: 'POST',
                    body: form
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        alert('Test logo update successful! Check the admin dashboard.');
                        loadCurrentAdminLogo(); // Refresh display
                    } else {
                        alert('Test failed: ' + data.message);
                    }
                } else {
                    alert('Test failed: HTTP ' + response.status);
                }
            } catch (error) {
                alert('Test failed: ' + error.message);
            }
        }

        // Reset to default logo
        async function resetToDefault() {
            const defaultLogoUrl = 'https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png';
            try {
                const form = new FormData();
                form.append('logo_url', defaultLogoUrl);
                form.append('slot', '1');
                
                const response = await fetch('./UpdateAdminLogo.php', {
                    method: 'POST',
                    body: form
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        alert('Logo reset to default successful!');
                        loadCurrentAdminLogo(); // Refresh display
                    } else {
                        alert('Reset failed: ' + data.message);
                    }
                } else {
                    alert('Reset failed: HTTP ' + response.status);
                }
            } catch (error) {
                alert('Reset failed: ' + error.message);
            }
        }

        // Load logo when page loads
        document.addEventListener('DOMContentLoaded', loadCurrentAdminLogo);
    </script>
</body>
</html>
