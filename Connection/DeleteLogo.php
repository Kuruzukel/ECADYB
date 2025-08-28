<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/BunnyConfig.php')) {
    require __DIR__ . '/BunnyConfig.php';
}
use MongoDB\Client;

// ----------------------
// Helper: Always return JSON
// ----------------------
function respond($success, $message = '', $data = []) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// ----------------------
// Validate input
// ----------------------
$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;
if (!$slot || $slot < 1 || $slot > 9) {
    respond(false, 'Invalid slot.');
}

// ----------------------
// Environment variables
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
$bunnyStorageZone = getenv('BUNNY_STORAGE_ZONE') ?: (defined('BUNNY_STORAGE_ZONE') ? BUNNY_STORAGE_ZONE : 'ecadyb');
$bunnyAccessKey   = getenv('BUNNY_ACCESS_KEY') ?: (defined('BUNNY_ACCESS_KEY') ? BUNNY_ACCESS_KEY : null);

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    $collection = $db->DashboardAssets;

    // ----------------------
    // Find document
    // ----------------------
    $doc = $collection->findOne(['type' => 'logo_container', 'slot' => $slot]);
    if (!$doc) {
        respond(false, 'Logo not found in MongoDB');
    }

    $url = (string)($doc['url'] ?? '');

    // ----------------------
    // Delete from BunnyCDN
    // ----------------------
    if ($url && $bunnyStorageZone && $bunnyAccessKey) {
        $pathStart = strpos($url, '/Logo%20Container/');
        if ($pathStart !== false) {
            $relative = substr($url, $pathStart + 1);
            $storageUrl = 'https://storage.bunnycdn.com/' . $bunnyStorageZone . '/' . $relative;

            $ch = curl_init($storageUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'AccessKey: ' . $bunnyAccessKey,
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                respond(false, "cURL error while deleting from BunnyCDN: $error");
            }

            curl_close($ch);

            if ($httpCode >= 400) {
                respond(false, "BunnyCDN deletion failed (HTTP $httpCode): " . $response);
            }
        }
    }

    // ----------------------
    // Delete from MongoDB
    // ----------------------
    $collection->deleteOne(['type' => 'logo_container', 'slot' => $slot]);

    respond(true, 'Logo deleted successfully');
} catch (Exception $e) {
    respond(false, 'Failed to delete logo: ' . $e->getMessage());
}