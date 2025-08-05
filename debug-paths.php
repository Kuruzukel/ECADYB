<?php
// Debug paths and content
echo "<h1>🔍 Debug Paths and Content</h1>";

// Check if files exist
$files = [
    'LandingPage/LandingPage.html',
    'LandingPage/LandingPage.css',
    'LandingPage/LandingPage.js',
    'img/ECALOGO.png',
    'img/GRALLERYLOGO4.0.png'
];

echo "<h2>📁 File Existence Check</h2>";
foreach ($files as $file) {
    $exists = file_exists($file);
    echo "<p>" . ($exists ? "✅" : "❌") . " $file</p>";
    if ($exists) {
        echo "<p>📏 Size: " . number_format(filesize($file)) . " bytes</p>";
    }
}

// Show the actual HTML content being served
echo "<h2>📄 HTML Content Analysis</h2>";
if (file_exists('LandingPage/LandingPage.html')) {
    $htmlContent = file_get_contents('LandingPage/LandingPage.html');
    
    // Check for CSS link
    if (strpos($htmlContent, 'href="LandingPage.css"') !== false) {
        echo "<p>✅ Found: href=\"LandingPage.css\"</p>";
    } else {
        echo "<p>❌ Not found: href=\"LandingPage.css\"</p>";
    }
    
    // Check for JS script
    if (strpos($htmlContent, 'src="LandingPage.js"') !== false) {
        echo "<p>✅ Found: src=\"LandingPage.js\"</p>";
    } else {
        echo "<p>❌ Not found: src=\"LandingPage.js\"</p>";
    }
    
    // Show the first 500 characters
    echo "<h3>First 500 characters:</h3>";
    echo "<pre>" . htmlspecialchars(substr($htmlContent, 0, 500)) . "</pre>";
}

// Test the path replacements
echo "<h2>🔧 Path Replacement Test</h2>";
if (file_exists('LandingPage/LandingPage.html')) {
    $htmlContent = file_get_contents('LandingPage/LandingPage.html');
    
    // Apply the same replacements as index.php
    $htmlContent = str_replace('href="LandingPage.css"', 'href="LandingPage/LandingPage.css"', $htmlContent);
    $htmlContent = str_replace('src="LandingPage.js"', 'src="LandingPage/LandingPage.js"', $htmlContent);
    $htmlContent = str_replace('src="../img/', 'src="img/', $htmlContent);
    $htmlContent = str_replace('href="../', 'href="', $htmlContent);
    $htmlContent = str_replace('href="admin/', 'href="Admin/', $htmlContent);
    $htmlContent = str_replace('href="student/', 'href="student/', $htmlContent);
    
    echo "<h3>After replacements:</h3>";
    echo "<pre>" . htmlspecialchars(substr($htmlContent, 0, 500)) . "</pre>";
}

// Test direct access URLs
echo "<h2>🔗 Direct Access Test</h2>";
echo "<p><a href='LandingPage/LandingPage.css' target='_blank'>Test CSS: LandingPage/LandingPage.css</a></p>";
echo "<p><a href='LandingPage/LandingPage.js' target='_blank'>Test JS: LandingPage/LandingPage.js</a></p>";
echo "<p><a href='img/ECALOGO.png' target='_blank'>Test Image: img/ECALOGO.png</a></p>";

echo "<h2>🎯 Current URL Info</h2>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Base URL:</strong> https://" . $_SERVER['HTTP_HOST'] . "</p>";
?> 