<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
// Prevent caching to ensure fresh data after CSV upload
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
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    $mongoDbName = "BatchTemplate" . $template;
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl);

    $messageCollection = $mongoClient->$mongoDbName->top_management_message;
    
    // Check if there are any CSV messages - if not, return empty result
    $messageCount = $messageCollection->countDocuments([]);
    if ($messageCount === 0) {
        respond(true, 'Please upload CSV of the Top Management to the Batch Upload Section first.', ['data' => []]);
    }
    
    $messages = $messageCollection->find([], ['sort' => ['position' => 1]]);

    $result = [];

    foreach ($messages as $message) {
        $personData = [
            'id' => (string)$message['_id'],
            'name' => $message['name'] ?? '',
            'position' => $message['position'] ?? '',
            'message' => $message['message'] ?? '',
            'academicyear' => $message['academicyear'] ?? ''
        ];

        $result[] = $personData;
    }

    respond(true, 'Top management messages retrieved successfully', ['data' => $result]);
} catch (Exception $e) {
    error_log("FetchTopManagementMessages.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
