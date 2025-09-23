<?php
// Ensure no output before headers
ob_start();

// Set proper headers for Railway
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
function respond($success, $message = '', $data = []) {
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
require __DIR__ . '/../../vendor/autoload.php';
use MongoDB\Client;

try {

    $logoUrl = isset($_POST['logo_url']) ? trim($_POST['logo_url']) : '';
    $slot = isset($_POST['slot']) ? (int)$_POST['slot'] : null;

    if (empty($logoUrl)) {
        respond(false, 'Logo URL is required');
    }

    if (!$slot || $slot < 1 || $slot > 9) {
        respond(false, 'Invalid slot number');
    }

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);
    
    $db = $client->admin;
    $collection = $db->selectedlogo;

    // Update or insert the admin logo setting
    $result = $collection->updateOne(
        ['setting_type' => 'admin_logo'],
        [
            '$set' => [
                'setting_type' => 'admin_logo',
                'logo_url' => $logoUrl,
                'slot' => $slot,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ],
        ['upsert' => true]
    );

    if ($result->getModifiedCount() > 0 || $result->getUpsertedCount() > 0) {
        respond(true, 'Admin dashboard logo updated successfully', [
            'logo_url' => $logoUrl,
            'slot' => $slot
        ]);
    } else {
        respond(false, 'Failed to update admin logo');
    }

} catch (Exception $e) {
    respond(false, 'Failed to update admin logo: ' . $e->getMessage());
}
