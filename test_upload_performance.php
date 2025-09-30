<?php
// Test script to verify upload performance improvements

echo "<h2>Upload Performance Test</h2>";

// Test 1: Check if BunnyCDN configuration is properly set
echo "<h3>1. BunnyCDN Configuration Check</h3>";
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: 'ecadyb';
$bunnyAccessKey = getenv('BUNNY_ACCESS_KEY');
$bunnyCdnHost = getenv('BUNNY_CDN_HOST') ?: 'https://ECADYB.b-cdn.net';

echo "Storage Zone: " . $bunnyStorageZone . "<br>";
echo "CDN Host: " . $bunnyCdnHost . "<br>";
echo "Access Key Set: " . ($bunnyAccessKey ? "Yes" : "No") . "<br><br>";

// Test 2: Test MongoDB connection
echo "<h3>2. MongoDB Connection Test</h3>";
require __DIR__ . '/vendor/autoload.php';
use MongoDB\Client;

try {
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    
    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 1000,
        'connectTimeoutMS' => 1000,
        'socketTimeoutMS' => 2000
    ]);
    
    // Test connection by listing databases
    $databases = $client->listDatabases();
    echo "MongoDB Connection: Successful<br>";
    echo "Available Databases: ";
    foreach ($databases as $database) {
        echo $database->getName() . " ";
    }
    echo "<br><br>";
} catch (Exception $e) {
    echo "MongoDB Connection: Failed - " . $e->getMessage() . "<br><br>";
}

// Test 3: Measure upload endpoint response time
echo "<h3>3. Upload Endpoint Response Time Test</h3>";
$uploadEndpoint = 'http://localhost/ECADYB/Connection/Cover/UploadCover.php';

// Using cURL to test response time
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uploadEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // We only want headers
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$start = microtime(true);
$response = curl_exec($ch);
$end = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseTime = ($end - $start) * 1000; // Convert to milliseconds

echo "Upload Endpoint: " . $uploadEndpoint . "<br>";
echo "HTTP Status: " . $httpCode . "<br>";
echo "Response Time: " . number_format($responseTime, 2) . " ms<br>";
echo "Performance Status: " . ($responseTime < 500 ? "Excellent" : ($responseTime < 1000 ? "Good" : ($responseTime < 3000 ? "Acceptable" : "Slow"))) . "<br><br>";

// Test 4: Measure fetch endpoint response time
echo "<h3>4. Fetch Endpoint Response Time Test</h3>";
$fetchEndpoint = 'http://localhost/ECADYB/Connection/Cover/FetchCovers.php?template=1';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fetchEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // We only want headers
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$start = microtime(true);
$response = curl_exec($ch);
$end = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$responseTime = ($end - $start) * 1000; // Convert to milliseconds

echo "Fetch Endpoint: " . $fetchEndpoint . "<br>";
echo "HTTP Status: " . $httpCode . "<br>";
echo "Response Time: " . number_format($responseTime, 2) . " ms<br>";
echo "Performance Status: " . ($responseTime < 200 ? "Excellent" : ($responseTime < 500 ? "Good" : ($responseTime < 1000 ? "Acceptable" : "Slow"))) . "<br><br>";

// Test 5: Simulate actual upload with small file
echo "<h3>5. Actual Upload Simulation Test</h3>";
echo "<p>Creating a small test file and uploading to measure real performance...</p>";

// Create a small test image (100x100 pixels)
$testImage = imagecreate(100, 100);
$background = imagecolorallocate($testImage, 255, 255, 255);
$textColor = imagecolorallocate($testImage, 0, 0, 0);
imagestring($testImage, 5, 10, 30, 'Test', $textColor);
ob_start();
imagejpeg($testImage, null, 50); // Low quality for small size
$imageData = ob_get_contents();
ob_end_clean();
imagedestroy($testImage);

$testFilePath = __DIR__ . '/test_upload.jpg';
file_put_contents($testFilePath, $imageData);

// Simulate upload
$uploadUrl = 'http://localhost/ECADYB/Connection/Cover/UploadCover.php';
$postFields = [
    'slot' => 1,
    'side' => 'front',
    'template' => 1,
    'file' => new CURLFile($testFilePath, 'image/jpeg', 'test.jpg')
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $uploadUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$start = microtime(true);
$response = curl_exec($ch);
$end = microtime(true);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Clean up test file
unlink($testFilePath);

$uploadTime = ($end - $start) * 1000; // Convert to milliseconds

echo "Test File Size: " . strlen($imageData) . " bytes<br>";
echo "HTTP Status: " . $httpCode . "<br>";
echo "Upload Time: " . number_format($uploadTime, 2) . " ms<br>";
echo "Performance Status: " . ($uploadTime < 1000 ? "Excellent (<1s)" : ($uploadTime < 3000 ? "Good (<3s)" : ($uploadTime < 5000 ? "Acceptable (<5s)" : "Slow (>5s)"))) . "<br>";

if ($response) {
    $result = json_decode($response, true);
    if ($result && isset($result['success']) && $result['success']) {
        echo "Upload Result: Success<br>";
        if (isset($result['url'])) {
            echo "CDN URL: " . htmlspecialchars($result['url']) . "<br>";
        }
    } else {
        echo "Upload Result: Failed<br>";
        if (isset($result['message'])) {
            echo "Error: " . htmlspecialchars($result['message']) . "<br>";
        }
    }
}

echo "<br>";

echo "<h3>Ultra-Fast Performance Optimization Summary</h3>";
echo "<ul>";
echo "<li>Reduced BunnyCDN upload timeouts to 8 seconds</li>";
echo "<li>Optimized MongoDB connection settings (1-2 second timeouts)</li>";
echo "<li>Removed unnecessary thumbnail creation</li>";
echo "<li>Added TCP optimization flags</li>";
echo "<li>Implemented connection reuse</li>";
echo "<li>Reduced frontend timeout to 8 seconds</li>";
echo "<li>Minimized data transfer with lean headers</li>";
echo "<li>Optimized database queries with projections</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> Actual upload time depends on file size, internet connection, and BunnyCDN performance. These optimizations minimize processing overhead to achieve the fastest possible upload times.</p>";

?>