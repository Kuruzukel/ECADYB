<?php
// Turn off error display for production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Start output buffering to catch any errors
ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/MongoConnect.php';

function respond($success, $message = '', $data = [])
{
    // Clean output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

// Global error handler to ensure JSON response even on errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error: $errstr in $errfile on line $errline");
    http_response_code(500);
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr
    ]);
    exit;
});

// Global exception handler
set_exception_handler(function($exception) {
    error_log("PHP Exception: " . $exception->getMessage());
    http_response_code(500);
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $exception->getMessage()
    ]);
    exit;
});

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    $mongoDbName = "BatchTemplate" . $template;
    // Use MONGO_URL or MONGODB_URI (Railway standard) with fallback
    $mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl);

    $messageCollection = $mongoClient->$mongoDbName->top_management_message;
    $messageCount = $messageCollection->countDocuments([]);
    if ($messageCount === 0) {
        respond(true, 'Please upload CSV of the Top Management to the Batch Upload Section first.', ['data' => []]);
    }

    $photosCollection = $mongoClient->$mongoDbName->top_management_photos;
    $photos = $photosCollection->find([], ['sort' => ['position' => 1]]);

    $result = [];

    foreach ($photos as $photo) {
        $personData = [
            'id' => (string)$photo['_id'],
            'name' => $photo['name'] ?? '',
            'position' => $photo['position'] ?? '',
            'photo_url' => $photo['url'] ?? '',
            'filename' => $photo['filename'] ?? '',
            'template' => $photo['template'] ?? $template
        ];

        $result[] = $personData;
    }

    respond(true, 'Top management photos retrieved successfully', ['data' => $result]);
} catch (Exception $e) {
    error_log("FetchTopManagementPhotos.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
