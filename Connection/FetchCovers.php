<?php
// Ensure no output before headers
ob_start();

// Set proper headers for Railway
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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

// Check if it's a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Invalid request method');
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);
    $db = $client->Departments;
    $collection = $db->YearbookCovers;

    $cursor = $collection->find(['template' => $template]);
    $items = [];

    foreach ($cursor as $doc) {
        $items[] = [
            'template'          => (int)($doc['template'] ?? 1),
            'slot'              => (int)($doc['slot'] ?? 0),
            'front_url'         => isset($doc['front_url']) ? (string)$doc['front_url'] : '',
            'back_url'          => isset($doc['back_url']) ? (string)$doc['back_url'] : '',
            'front_thumb_url'   => isset($doc['front_thumb_url']) ? (string)$doc['front_thumb_url'] : '',
            'back_thumb_url'    => isset($doc['back_thumb_url']) ? (string)$doc['back_thumb_url'] : ''
        ];
    }

    respond(true, 'Covers fetched successfully', ['items' => $items]);
} catch (Exception $e) {
    respond(false, 'Failed to fetch covers: ' . $e->getMessage());
}
