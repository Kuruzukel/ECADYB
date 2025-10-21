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
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $batchYear = isset($_GET['batch_year']) ? trim($_GET['batch_year']) : null;
    
    error_log("FetchTopManagement Request: " . json_encode($_GET));
    error_log("Parsed parameters - Template: $template, BatchYear: $batchYear");

    $mongoDbName = "ECADYB";
    $mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl);

    $messageCollection = $mongoClient->$mongoDbName->Top_Management_Messages;

    // Build filter for academic year if batch year is provided
    $academicYearFilter = [];
    if ($batchYear) {
        // Convert "Batch Year 2024-2025" to "2024-2025"
        $academicYear = str_replace('Batch Year ', '', $batchYear);
        $academicYearFilter = ['academicyear' => $academicYear];
        error_log("Filtering top management by academic year: $academicYear");
    }

    $messageCount = $messageCollection->countDocuments($academicYearFilter);
    if ($messageCount === 0) {
        $message = $batchYear ? 
            "No top management data found for academic year " . str_replace('Batch Year ', '', $batchYear) . ". Please upload CSV of the Top Management to the Batch Upload Section first." :
            'Please upload CSV of the Top Management to the Batch Upload Section first.';
        respond(true, $message, ['data' => []]);
    }

    $photosCollection = $mongoClient->$mongoDbName->Top_Management_Photos;
    $photos = $photosCollection->find($academicYearFilter, ['sort' => ['position' => 1]]);

    $result = [];
    $photoMap = [];

    foreach ($photos as $photo) {
        $name = $photo['name'] ?? '';
        $photoMap[$name] = [
            'id' => (string)$photo['_id'],
            'name' => $name,
            'position' => $photo['position'] ?? '',
            'photo_url' => $photo['url'] ?? '',
            'filename' => $photo['filename'] ?? '',
            'message' => '',
            'academicyear' => ''
        ];
    }

    $messages = $messageCollection->find($academicYearFilter, ['sort' => ['position' => 1]]);
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
                'academicyear' => $message['academicyear'] ?? ''
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
