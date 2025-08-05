<?php
// Kel-Miyata Service Test
echo "<h1>🎯 Kel-Miyata Service Test</h1>";
echo "<p>✅ Your PHP application is running!</p>";

// Show service information
echo "<h2>🔧 Service Information</h2>";
echo "<p><strong>Service Name:</strong> Kel-Miyata</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Current URL:</strong> " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";

// Test MongoDB connection
echo "<h2>🔍 MongoDB Connection Test</h2>";
try {
    require __DIR__ . '/vendor/autoload.php';
    
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
    echo "<p>📡 Connection String: " . (strpos($mongoUrl, 'mongodb://localhost') === 0 ? 'localhost' : 'Railway MongoDB') . "</p>";
    
    $client = new \MongoDB\Client($mongoUrl);
    $client->listDatabases();
    echo "<p style='color: green;'>✅ MongoDB connection successful!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ MongoDB Error: " . $e->getMessage() . "</p>";
}

// Show file structure
echo "<h2>📁 File Structure</h2>";
if (file_exists('LandingPage/LandingPage.html')) {
    echo "<p>✅ LandingPage.html exists</p>";
} else {
    echo "<p>❌ LandingPage.html not found</p>";
}

echo "<h2>🎉 Kel-Miyata is Ready!</h2>";
echo "<p>Your ECADYB application is successfully deployed on Railway!</p>";
?> 