<?php
// Test LandingPage file structure
echo "<h1>🔍 LandingPage File Test</h1>";

// Check if LandingPage directory exists
if (is_dir('LandingPage')) {
    echo "<p>✅ LandingPage directory exists</p>";
    
    // List files in LandingPage directory
    $files = scandir('LandingPage');
    echo "<h2>Files in LandingPage directory:</h2>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
    
    // Check if LandingPage.html exists
    if (file_exists('LandingPage/LandingPage.html')) {
        echo "<p>✅ LandingPage.html exists</p>";
        
        // Get file size
        $fileSize = filesize('LandingPage/LandingPage.html');
        echo "<p>📏 File size: " . number_format($fileSize) . " bytes</p>";
        
        // Show first few lines
        $content = file_get_contents('LandingPage/LandingPage.html');
        $firstLines = substr($content, 0, 500);
        echo "<h2>First 500 characters:</h2>";
        echo "<pre>" . htmlspecialchars($firstLines) . "</pre>";
        
    } else {
        echo "<p>❌ LandingPage.html not found</p>";
    }
    
} else {
    echo "<p>❌ LandingPage directory not found</p>";
}

// Show current directory structure
echo "<h2>Current Directory:</h2>";
echo "<p>" . getcwd() . "</p>";

// List root directory files
$rootFiles = scandir('.');
echo "<h2>Root directory files:</h2>";
echo "<ul>";
foreach ($rootFiles as $file) {
    if ($file != '.' && $file != '..' && !is_dir($file)) {
        echo "<li>$file</li>";
    }
}
echo "</ul>";
?> 