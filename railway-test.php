<?php
// Simple Railway deployment test
echo "<h1>🚂 Railway PHP Deployment Test</h1>";
echo "<p>✅ PHP is working on Railway!</p>";

// Test MongoDB connection
echo "<h2>🔍 Testing MongoDB Connection</h2>";

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

// Show environment info
echo "<h2>🔧 Environment Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' . "</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";

echo "<h2>🎉 Deployment Successful!</h2>";
echo "<p>Your PHP application is now running on Railway!</p>";
?>