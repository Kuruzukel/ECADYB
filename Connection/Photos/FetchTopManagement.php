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

    // First check if there are any CSV messages - if not, return empty result
    $messageCount = $messageCollection->countDocuments([]);
    if ($messageCount === 0) {
        respond(true, 'Please upload CSV of the Top Management to the Batch Upload Section first.', ['data' => []]);
    }

    $photosCollection = $mongoClient->$mongoDbName->top_management_photos;
    $photos = $photosCollection->find([], ['sort' => ['position' => 1]]);

    $result = [];
    $photoMap = [];

    // Only process photos if there are CSV messages
    foreach ($photos as $photo) {
        $name = $photo['name'] ?? '';
        $photoMap[$name] = [
            'id' => (string)$photo['_id'],
            'name' => $name,
            'position' => $photo['position'] ?? '',
            'photo_url' => $photo['url'] ?? '',
            'filename' => $photo['filename'] ?? '',
            'template' => $photo['template'] ?? $template,
            'message' => '',
            'academicyear' => ''
        ];
    }

    $messages = $messageCollection->find([], ['sort' => ['position' => 1]]);
    foreach ($messages as $message) {
        $name = $message['name'] ?? '';

        if (isset($photoMap[$name])) {
            $photoMap[$name]['message'] = $message['message'] ?? '';
            $photoMap[$name]['academicyear'] = $message['academicyear'] ?? '';
        } else {
            $photoMap[$name] = [
                'id' => (string)$message['_id'],
                'name' => $name,
                'position' => $message['position'] ?? '',
                'photo_url' => '',
                'message' => $message['message'] ?? '',
                'academicyear' => $message['academicyear'] ?? '',
                'template' => $template
            ];
        }
    }

    $result = array_values($photoMap);

    usort($result, function ($a, $b) {
        $posA = isset($a['position']) ? $a['position'] : '';
        $posB = isset($b['position']) ? $b['position'] : '';

        if ($posA == $posB) {
            return 0;
        }

        if (is_numeric($posA) && is_numeric($posB)) {
            return $posA - $posB;
        }

        if ($posA == "President") return -1;
        if ($posB == "President") return 1;

        return strcmp($posA, $posB);
    });

    if (empty($result)) {
        respond(true, 'Please upload CSV of the Top Management to the Batch Upload Section first.', ['data' => $result]);
    } else {
        respond(true, 'Top management data retrieved successfully', ['data' => $result]);
    }
} catch (Exception $e) {
    error_log("FetchTopManagement.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
