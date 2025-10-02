<?php
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

function respond($success, $message = '', $data = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');

    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method');
}

require __DIR__ . '/../../vendor/autoload.php';

if (file_exists(__DIR__ . '/../Configuration/BunnyConfig.php')) {
    require __DIR__ . '/../Configuration/BunnyConfig.php';
}

use MongoDB\Client;

try {
    $slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
    if (!$slot || $slot < 1 || $slot > 9) {
        respond(false, 'Invalid slot. Must be between 1 and 9.');
    }

    // Get MongoDB connection details from environment or config
    $mongoUrl = getenv('MONGO_URL');
    if (!$mongoUrl) {
        // Fallback to config file if exists
        if (file_exists(__DIR__ . '/../Configuration/MongoConfig.php')) {
            require __DIR__ . '/../Configuration/MongoConfig.php';
            $mongoUrl = defined('MONGO_URL') ? MONGO_URL : null;
        }
        if (!$mongoUrl) {
            respond(false, 'MongoDB connection URL not configured');
        }
    }

    // Get BunnyCDN details
    $bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : null);
    $bunnyAccessKey = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : null);

    if (!$bunnyStorageZone || !$bunnyAccessKey) {
        respond(false, 'BunnyCDN configuration is missing');
    }

    try {
        // Connect to MongoDB with proper timeout settings
        $client = new Client($mongoUrl, [
            'serverSelectionTimeoutMS' => 5000,
            'connectTimeoutMS' => 5000,
            'socketTimeoutMS' => 5000
        ]);
        $db = $client->amin;
        $collection = $db->logo;

        // Find the logo document first
        $doc = $collection->findOne(['type' => 'logo_container', 'slot' => $slot]);
        if (!$doc) {
            respond(false, 'Logo not found');
        }

        // Get the URL and verify it exists
        $url = (string)($doc['url'] ?? '');
        if (!$url) {
            respond(false, 'Logo URL is missing');
        }

        // Delete from BunnyCDN first
        $pathStart = strpos($url, '/Logo%20Container/');
        if ($pathStart === false) {
            respond(false, 'Invalid logo URL format');
        }

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

        // Check if BunnyCDN deletion was successful
        if ($httpCode !== 200 && $httpCode !== 404) {
            error_log("Failed to delete logo from BunnyCDN. HTTP $httpCode");
            respond(false, 'Failed to delete logo from storage');
        }

        // Only delete from MongoDB if BunnyCDN deletion was successful
        $result = $collection->deleteOne(['type' => 'logo_container', 'slot' => $slot]);
        
        if ($result->getDeletedCount() === 0) {
            respond(false, 'Failed to delete logo from database');
        }

        respond(true, 'Logo deleted successfully');
    } catch (Exception $e) {
        error_log('Logo deletion error: ' . $e->getMessage());
        respond(false, 'Failed to delete logo: ' . $e->getMessage());
    }
} catch (Exception $e) {
    respond(false, 'Failed to delete logo: ' . $e->getMessage());
}
