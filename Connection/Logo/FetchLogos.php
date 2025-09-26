<?php
ob_start();

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
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);
    $db = $client->admin;
    $collection = $db->logo;

    $cursor = $collection->find(
        ['type' => 'logo_container'],
        [
            'projection' => ['slot' => 1, 'url' => 1],
            'sort' => ['slot' => 1]
        ]
    );

    $items = [];
    foreach ($cursor as $doc) {
        $items[] = [
            'slot' => isset($doc['slot']) ? (int)$doc['slot'] : 0,
            'url'  => isset($doc['url']) ? (string)$doc['url'] : ''
        ];
    }

    respond(true, 'Logos fetched successfully', ['items' => $items]);
} catch (Exception $e) {
    respond(false, 'Failed to fetch logos: ' . $e->getMessage());
}