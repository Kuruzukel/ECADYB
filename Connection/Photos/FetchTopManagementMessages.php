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
    // Get template parameter (default to 1)
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    
    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }
    
    // MongoDB connection for the selected batch template
    $mongoDbName = "BatchTemplate" . $template;
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    
    $mongoClient = new MongoDB\Client($mongoUrl);
    
    // Get messages from top_management_message collection
    $messageCollection = $mongoClient->$mongoDbName->top_management_message;
    $messages = $messageCollection->find([], ['sort' => ['position' => 1]]);
    
    $result = [];
    
    // Process each message
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
