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
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Invalid request method');
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

    error_log("FetchCovers.php received template parameter: $template");

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    error_log("FetchCovers.php using MongoDB URL: $mongoUrl");

    // Ultra-fast MongoDB connection
    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 1000,  // Ultra-fast timeout
        'connectTimeoutMS' => 1000,          // Ultra-fast timeout
        'socketTimeoutMS' => 2000,           // Reduced timeout
        'retryReads' => true
    ]);

    $dbName = "BatchTemplate" . $template;
    $db = $client->$dbName;
    $collection = $db->YearbookCovers;

    error_log("FetchCovers.php using database: $dbName, collection: YearbookCovers");

    // Ultra-fast fetch with minimal projection including thumbnail fields
    $cursor = $collection->find(
        [], // Fetch all documents, not just those matching template
        [
            'projection' => [
                'slot' => 1,
                'front_url' => 1,
                'back_url' => 1,
                'front_thumb_url' => 1,
                'back_thumb_url' => 1,
                'background_url' => 1,
                'background_thumb_url' => 1,
                'template' => 1
            ],
            'limit' => 8  // We only have 8 slots maximum
        ]
    );
    
    $items = [];

    foreach ($cursor as $doc) {
        error_log("FetchCovers.php found document: " . json_encode($doc));
        $slot = (int)($doc['slot'] ?? 0);

        if ($slot >= 1 && $slot <= 7) {
            $items[] = [
                'slot' => $slot,
                'front_url' => isset($doc['front_url']) ? (string)$doc['front_url'] : '',
                'back_url' => isset($doc['back_url']) ? (string)$doc['back_url'] : '',
                'front_thumb_url' => isset($doc['front_thumb_url']) ? (string)$doc['front_thumb_url'] : '',
                'back_thumb_url' => isset($doc['back_thumb_url']) ? (string)$doc['back_thumb_url'] : ''
            ];
        } elseif ($slot === 8) {
            $backgroundUrl = isset($doc['background_url']) ? (string)$doc['background_url'] : '';
            $backgroundThumbUrl = isset($doc['background_thumb_url']) ? (string)$doc['background_thumb_url'] : '';
            $items[] = [
                'slot' => 8,
                'front_url' => $backgroundUrl,
                'background_url' => $backgroundUrl,
                'front_thumb_url' => $backgroundThumbUrl,
                'background_thumb_url' => $backgroundThumbUrl
            ];
        }
    }

    error_log("FetchCovers.php found " . count($items) . " items for template $template");

    respond(true, 'Covers fetched', ['items' => array_values($items)]);
} catch (Exception $e) {
    error_log("FetchCovers.php error: " . $e->getMessage());
    respond(false, 'Failed to fetch: ' . $e->getMessage());
}