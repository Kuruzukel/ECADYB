<?php
// Deployment Test Script for Railway
// This file helps verify that all key components are working correctly

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Deployment Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🚂 Railway Deployment Test</h1>
    <p>This page helps verify that your ECADYB application is working correctly on Railway.</p>

    <?php
    // Test 1: Basic PHP functionality
    echo '<div class="test-section success">';
    echo '<h3>✅ Basic PHP Test</h3>';
    echo '<p>PHP Version: ' . PHP_VERSION . '</p>';
    echo '<p>Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</p>';
    echo '<p>Current Time: ' . date('Y-m-d H:i:s T') . '</p>';
    echo '</div>';

    // Test 2: File permissions
    echo '<div class="test-section info">';
    echo '<h3>📁 File System Test</h3>';
    echo '<p>Current Directory: ' . getcwd() . '</p>';
    echo '<p>Writable: ' . (is_writable('.') ? 'Yes' : 'No') . '</p>';
    echo '<p>Connection Directory: ' . (is_dir('./Connection') ? 'Exists' : 'Missing') . '</p>';
    echo '</div>';

    // Test 3: Composer autoloader
    echo '<div class="test-section info">';
    echo '<h3>📦 Composer Test</h3>';
    if (file_exists('./vendor/autoload.php')) {
        echo '<p>✅ Vendor autoloader exists</p>';
        try {
            require_once './vendor/autoload.php';
            echo '<p>✅ Autoloader loaded successfully</p>';
        } catch (Exception $e) {
            echo '<p>❌ Autoloader failed: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p>❌ Vendor autoloader missing</p>';
    }
    echo '</div>';

    // Test 4: MongoDB connection
    echo '<div class="test-section info">';
    echo '<h3>🗄️ MongoDB Test</h3>';
    if (class_exists('MongoDB\Client')) {
        echo '<p>✅ MongoDB extension loaded</p>';
        try {
            $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
            $client = new MongoDB\Client($mongoUrl, [
                'serverSelectionTimeoutMS' => 5000,
                'connectTimeoutMS' => 5000,
                'socketTimeoutMS' => 5000
            ]);
            $client->listDatabases();
            echo '<p>✅ MongoDB connection successful</p>';
        } catch (Exception $e) {
            echo '<p>❌ MongoDB connection failed: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p>❌ MongoDB extension not available</p>';
    }
    echo '</div>';

    // Test 5: Environment variables
    echo '<div class="test-section info">';
    echo '<h3>🔑 Environment Variables Test</h3>';
    $envVars = ['MONGO_URL', 'BUNNY_STORAGE_ZONE', 'BUNNY_ACCESS_KEY', 'BUNNY_CDN_HOST'];
    foreach ($envVars as $var) {
        $value = getenv($var);
        if ($value) {
            echo '<p>✅ ' . $var . ': ' . substr($value, 0, 20) . '...</p>';
        } else {
            echo '<p>❌ ' . $var . ': Not set</p>';
        }
    }
    echo '</div>';

    // Test 6: PHP extensions
    echo '<div class="test-section info">';
    echo '<h3>🔌 PHP Extensions Test</h3>';
    $requiredExtensions = ['curl', 'json', 'mongodb', 'openssl'];
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo '<p>✅ ' . $ext . ' extension loaded</p>';
        } else {
            echo '<p>❌ ' . $ext . ' extension missing</p>';
        }
    }
    echo '</div>';

    // Test 7: File upload settings
    echo '<div class="test-section info">';
    echo '<h3>📤 File Upload Settings</h3>';
    echo '<p>upload_max_filesize: ' . ini_get('upload_max_filesize') . '</p>';
    echo '<p>post_max_size: ' . ini_get('post_max_size') . '</p>';
    echo '<p>max_file_uploads: ' . ini_get('max_file_uploads') . '</p>';
    echo '<p>max_execution_time: ' . ini_get('max_execution_time') . '</p>';
    echo '</div>';

    // Test 8: Test API endpoints
    echo '<div class="test-section info">';
    echo '<h3>🌐 API Endpoints Test</h3>';
    echo '<p><a href="./Connection/test.php" target="_blank">Test PHP Endpoint</a></p>';
    echo '<p><a href="./Connection/FetchLogos.php" target="_blank">Test Fetch Logos</a></p>';
    echo '<p><a href="./Connection/FetchCovers.php?template=1" target="_blank">Test Fetch Covers</a></p>';
    echo '</div>';
    ?>

    <div class="test-section warning">
        <h3>⚠️ Next Steps</h3>
        <p>If all tests pass, your application should be working correctly on Railway.</p>
        <p>If you see errors:</p>
        <ul>
            <li>Check your environment variables in Railway dashboard</li>
            <li>Verify your MongoDB connection string</li>
            <li>Check the Railway logs for any PHP errors</li>
            <li>Ensure your BunnyCDN credentials are correct</li>
        </ul>
    </div>

    <div class="test-section info">
        <h3>🔗 Useful Links</h3>
        <p><a href="./Admin/Components/Themes.php" target="_blank">Themes Page</a></p>
        <p><a href="./Admin/Components/BatchTemplates.php" target="_blank">Batch Templates Page</a></p>
        <p><a href="./Admin/Components/AdminDashboard.php" target="_blank">Admin Dashboard</a></p>
    </div>
</body>
</html>
