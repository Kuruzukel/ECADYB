<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $slot = isset($_GET['slot']) ? (int)$_GET['slot'] : 1;

    if ($template < 1 || $template > 3) {
        throw new Exception('Invalid template parameter. Must be 1, 2, or 3.');
    }

    if ($slot < 1 || $slot > 8) {
        throw new Exception('Invalid slot parameter. Must be between 1 and 8.');
    }

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    $dbName = "BatchTemplate" . $template;
    $db = $client->$dbName;
    $collection = $db->YearbookCovers;

    // Find the specific cover
    $cover = $collection->findOne(['template' => $template, 'slot' => $slot]);

    if (!$cover) {
        throw new Exception('Cover not found for template ' . $template . ' and slot ' . $slot);
    }

    // Format the response
    $response = [
        '_id' => (string)$cover['_id'],
        'slot' => (int)$cover['slot'],
        'template' => (int)$cover['template'],
        'front_url' => isset($cover['front_url']) ? (string)$cover['front_url'] : '',
        'back_url' => isset($cover['back_url']) ? (string)$cover['back_url'] : '',
        'front_thumb_url' => isset($cover['front_thumb_url']) ? (string)$cover['front_thumb_url'] : '',
        'back_thumb_url' => isset($cover['back_thumb_url']) ? (string)$cover['back_thumb_url'] : '',
        'background_url' => isset($cover['background_url']) ? (string)$cover['background_url'] : '',
        'background_thumb_url' => isset($cover['background_thumb_url']) ? (string)$cover['background_thumb_url'] : '',
        'created_at' => isset($cover['created_at']) ? $cover['created_at']->toDateTime()->format('c') : null,
        'updated_at' => isset($cover['updated_at']) ? $cover['updated_at']->toDateTime()->format('c') : null
    ];

    // Debug: Print the actual data being returned
    error_log("FetchCoverData response: " . json_encode($response));

    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
} catch (Exception $e) {
    error_log("FetchCoverData error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
