<?php
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

    // Return JSON response
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Invalid request method');
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    $db = $client->Departments;
    $collection = $db->AdminSettings;

    // Fetch the current admin logo setting
    $adminLogo = $collection->findOne(['setting_type' => 'admin_logo']);

    if ($adminLogo) {
        respond(true, 'Admin logo fetched successfully', [
            'logo_url' => $adminLogo['logo_url'] ?? '',
            'slot' => $adminLogo['slot'] ?? null,
            'updated_at' => $adminLogo['updated_at'] ?? null
        ]);
    } else {
        // Return default logo if none is set
        respond(true, 'No custom admin logo set, using default', [
            'logo_url' => 'https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png',
            'slot' => null,
            'updated_at' => null
        ]);
    }
} catch (Exception $e) {
    respond(false, 'Failed to fetch admin logo: ' . $e->getMessage());
}
