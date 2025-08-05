<?php
// Deployment Check for Kel-Miyata
echo "<h1>🔍 Kel-Miyata Deployment Check</h1>";

// Basic server info
echo "<h2>📊 Server Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// Check if we're on Railway
$isRailway = isset($_SERVER['RAILWAY_STATIC_URL']) || strpos($_SERVER['HTTP_HOST'] ?? '', 'railway') !== false;
echo "<p><strong>On Railway:</strong> " . ($isRailway ? 'Yes' : 'No') . "</p>";

// Check environment variables
echo "<h2>🔧 Environment Variables</h2>";
$mongoUrl = getenv('MONGO_URL');
echo "<p><strong>MONGO_URL:</strong> " . ($mongoUrl ? 'Set' : 'Not set') . "</p>";

// Check file structure
echo "<h2>📁 File Structure Check</h2>";
$files = ['index.php', 'LandingPage/LandingPage.html', 'composer.json', 'vendor/autoload.php'];
foreach ($files as $file) {
    $exists = file_exists($file);
    echo "<p>" . ($exists ? "✅" : "❌") . " $file</p>";
}

// Test MongoDB connection
echo "<h2>🔍 MongoDB Test</h2>";
try {
    require __DIR__ . '/vendor/autoload.php';
    $client = new \MongoDB\Client($mongoUrl ?: 'mongodb://localhost:27017');
    $client->listDatabases();
    echo "<p style='color: green;'>✅ MongoDB connection successful!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ MongoDB Error: " . $e->getMessage() . "</p>";
}

echo "<h2>🎯 Next Steps</h2>";
echo "<p>If you can see this page, your PHP application is running!</p>";
echo "<p>Try visiting: <strong>" . $_SERVER['HTTP_HOST'] . "</strong></p>";
?> 