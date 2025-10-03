<?php
// Test script for photo uploads
echo "<h1>Photo Upload Test</h1>";

// Check if required directories exist
$requiredDirs = [
    'Connection/Photos',
    'Connection/Configuration'
];

foreach ($requiredDirs as $dir) {
    if (!file_exists($dir)) {
        echo "<p style='color: red;'>Missing directory: $dir</p>";
    } else {
        echo "<p style='color: green;'>Found directory: $dir</p>";
    }
}

// Check if required files exist
$requiredFiles = [
    'Connection/Photos/UploadStudentPhotos.php',
    'Connection/Photos/UploadTopManagementPhotos.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        echo "<p style='color: red;'>Missing file: $file</p>";
    } else {
        echo "<p style='color: green;'>Found file: $file</p>";
    }
}

// Check if BunnyCDN configuration exists
$bunnyConfigFile = 'Connection/Configuration/BunnyConfig.php';
if (file_exists($bunnyConfigFile)) {
    echo "<p style='color: green;'>Found BunnyCDN configuration file</p>";
} else {
    echo "<p style='color: orange;'>BunnyCDN configuration file not found (this is OK if using environment variables)</p>";
}

echo "<p>Test completed. Check the results above.</p>";
?>