<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

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

try {
    $mongoDbName = "ECADYB";
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    $mongoClient = new MongoDB\Client($mongoUrl);

    $messageCollection = $mongoClient->$mongoDbName->Top_Management_Messages;

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
