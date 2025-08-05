<?php
// Test flipbook accessibility
echo "<h1>📚 Flipbook Test for ECADYB</h1>";

// Check if LandingPageYB folder exists
if (is_dir('LandingPage/LandingPageYB')) {
    echo "<p>✅ LandingPageYB folder exists</p>";
    
    // Check key files
    $files = [
        'LandingPage/LandingPageYB/index.html',
        'LandingPage/LandingPageYB/slider.html',
        'LandingPage/LandingPageYB/css/',
        'LandingPage/LandingPageYB/js/',
        'LandingPage/LandingPageYB/pages/'
    ];
    
    echo "<h2>📁 Key Files Check</h2>";
    foreach ($files as $file) {
        $exists = file_exists($file) || is_dir($file);
        echo "<p>" . ($exists ? "✅" : "❌") . " $file</p>";
    }
    
    // Check pages directory
    if (is_dir('LandingPage/LandingPageYB/pages')) {
        $pageFiles = scandir('LandingPage/LandingPageYB/pages');
        $imageFiles = array_filter($pageFiles, function($f) { 
            return $f != '.' && $f != '..' && (strpos($f, '.jpg') !== false || strpos($f, '.json') !== false); 
        });
        echo "<p>📸 Found " . count($imageFiles) . " image/JSON files in pages directory</p>";
    }
    
    // Test direct access
    echo "<h2>🔗 Direct Access Test</h2>";
    echo "<p><a href='LandingPage/LandingPageYB/index.html' target='_blank'>Test Flipbook: LandingPage/LandingPageYB/index.html</a></p>";
    echo "<p><a href='LandingPage/LandingPageYB/slider.html' target='_blank'>Test Slider: LandingPage/LandingPageYB/slider.html</a></p>";
    
    // Test iframe URL
    echo "<h2>🎯 Iframe Test</h2>";
    echo "<p>Iframe should load: <strong>LandingPage/LandingPageYB/index.html</strong></p>";
    
} else {
    echo "<p>❌ LandingPageYB folder not found</p>";
}

// Show current URL structure
echo "<h2>🌐 URL Information</h2>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Base URL:</strong> https://" . $_SERVER['HTTP_HOST'] . "</p>";

echo "<h2>🎯 Next Steps</h2>";
echo "<p>If the flipbook files are accessible via the links above, your iframe should work properly!</p>";
?> 