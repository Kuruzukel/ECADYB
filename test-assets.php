<?php
// Test CSS and JS assets accessibility
echo "<h1>🎨 Asset Test for ECADYB</h1>";

// Check if CSS file exists
$cssFile = 'LandingPage/LandingPage.css';
if (file_exists($cssFile)) {
    echo "<p>✅ CSS file exists: $cssFile</p>";
    echo "<p>📏 CSS file size: " . number_format(filesize($cssFile)) . " bytes</p>";
} else {
    echo "<p>❌ CSS file not found: $cssFile</p>";
}

// Check if JS file exists
$jsFile = 'LandingPage/LandingPage.js';
if (file_exists($jsFile)) {
    echo "<p>✅ JS file exists: $jsFile</p>";
    echo "<p>📏 JS file size: " . number_format(filesize($jsFile)) . " bytes</p>";
} else {
    echo "<p>❌ JS file not found: $jsFile</p>";
}

// Check if img directory exists
$imgDir = 'img';
if (is_dir($imgDir)) {
    echo "<p>✅ img directory exists</p>";
    $imgFiles = scandir($imgDir);
    echo "<p>📁 Images found: " . count(array_filter($imgFiles, function($f) { return $f != '.' && $f != '..'; })) . "</p>";
} else {
    echo "<p>❌ img directory not found</p>";
}

// Test direct access to CSS
echo "<h2>🔗 CSS Test</h2>";
echo "<p>Try accessing: <a href='LandingPage/LandingPage.css' target='_blank'>LandingPage/LandingPage.css</a></p>";

// Test direct access to JS
echo "<h2>🔗 JS Test</h2>";
echo "<p>Try accessing: <a href='LandingPage/LandingPage.js' target='_blank'>LandingPage/LandingPage.js</a></p>";

// Show current URL structure
echo "<h2>🌐 URL Information</h2>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Base URL:</strong> https://" . $_SERVER['HTTP_HOST'] . "</p>";

echo "<h2>🎯 Next Steps</h2>";
echo "<p>If the CSS and JS files are accessible via the links above, your LandingPage should work properly!</p>";
?> 