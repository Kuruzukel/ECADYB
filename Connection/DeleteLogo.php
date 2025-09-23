<?php
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Error handling function
function respond($success, $message = '', $data = [])
{
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Set JSON header
    header('Content-Type: application/json');

    // Return JSON response
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method');
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}

use MongoDB\Client;

try {
    $slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    if (!$slot || $slot < 1 || $slot > 9) {
        respond(false, 'Invalid slot. Must be between 1 and 9.');
    }

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : ($GLOBALS['BUNNY_STORAGE_ZONE'] ?? 'ecadyb'));
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : ($GLOBALS['BUNNY_ACCESS_KEY'] ?? null));

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);
    $db = $client->Departments;
    $collection = $db->DashboardAssets;

    $doc = $collection->findOne(['type' => 'logo_container', 'slot' => $slot]);
    if (!$doc) {
        respond(false, 'Logo not found');
    }

    $url = (string)($doc['url'] ?? '');
    if ($url && $bunnyStorageZone && $bunnyAccessKey) {
        $pathStart = strpos($url, '/Logo%20Container/');
        if ($pathStart !== false) {
            $relative = substr($url, $pathStart + 1);
            $storageUrl = 'https://storage.bunnycdn.com/' . $bunnyStorageZone . '/' . $relative;

            $ch = curl_init($storageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['AccessKey: ' . $bunnyAccessKey]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Don't fail if BunnyCDN deletion fails - just log it
            if ($httpCode !== 200 && $httpCode !== 404) {
                error_log("Warning: Failed to delete logo from BunnyCDN. HTTP $httpCode");
            }
        }
    }

    $collection->deleteOne(['type' => 'logo_container', 'slot' => $slot]);
    respond(true, 'Logo deleted successfully');
} catch (Exception $e) {
    respond(false, 'Failed to delete logo: ' . $e->getMessage());
}
