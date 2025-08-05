<?php
// Test what the main page is actually serving
echo "<h1>🎯 Main Page Content Test</h1>";

// Simulate what index.php does
if (file_exists('LandingPage/LandingPage.html')) {
    echo "<h2>✅ LandingPage.html exists</h2>";
    
    // Get the original content
    $htmlContent = file_get_contents('LandingPage/LandingPage.html');
    echo "<h3>Original HTML (first 1000 chars):</h3>";
    echo "<pre>" . htmlspecialchars(substr($htmlContent, 0, 1000)) . "</pre>";
    
    // Apply the same replacements as index.php
    echo "<h3>After path replacements:</h3>";
    $htmlContent = str_replace('href="LandingPage.css"', 'href="LandingPage/LandingPage.css"', $htmlContent);
    $htmlContent = str_replace('src="LandingPage.js"', 'src="LandingPage/LandingPage.js"', $htmlContent);
    $htmlContent = str_replace('src="../img/', 'src="img/', $htmlContent);
    $htmlContent = str_replace('href="../', 'href="', $htmlContent);
    $htmlContent = str_replace('href="admin/', 'href="Admin/', $htmlContent);
    $htmlContent = str_replace('href="student/', 'href="student/', $htmlContent);
    
    echo "<pre>" . htmlspecialchars(substr($htmlContent, 0, 1000)) . "</pre>";
    
    // Check if replacements worked
    echo "<h3>Replacement Check:</h3>";
    if (strpos($htmlContent, 'href="LandingPage/LandingPage.css"') !== false) {
        echo "<p>✅ CSS path correctly replaced</p>";
    } else {
        echo "<p>❌ CSS path NOT replaced</p>";
    }
    
    if (strpos($htmlContent, 'src="LandingPage/LandingPage.js"') !== false) {
        echo "<p>✅ JS path correctly replaced</p>";
    } else {
        echo "<p>❌ JS path NOT replaced</p>";
    }
    
} else {
    echo "<h2>❌ LandingPage.html NOT found</h2>";
}

echo "<h2>🔗 Test Direct Access</h2>";
echo "<p><a href='LandingPage/LandingPage.css' target='_blank'>CSS File</a></p>";
echo "<p><a href='LandingPage/LandingPage.js' target='_blank'>JS File</a></p>";
echo "<p><a href='img/ECALOGO.png' target='_blank'>Logo Image</a></p>";
?> 