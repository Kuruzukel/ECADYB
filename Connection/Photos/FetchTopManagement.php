<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Increase limits for large datasets
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '60');
set_time_limit(60);

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

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        error_log("FetchTopManagement Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);
        
        $response = [
            'success' => false,
            'message' => 'Server error occurred while fetching top management data',
            'error_details' => $error['message']
        ];
        
        $jsonOutput = json_encode($response);
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($jsonOutput));
        echo $jsonOutput;
        exit;
    }
});

function respond($success, $message = '', $data = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }

    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);
    
    $jsonOutput = json_encode($response);

    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($jsonOutput));
    echo $jsonOutput;
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
    error_log("Found $messageCount top management messages with filter: " . json_encode($academicYearFilter));
    
    if ($messageCount === 0) {
        $message = $batchYear ? 
            "No top management data found for academic year " . str_replace('Batch Year ', '', $batchYear) . ". Please upload CSV of the Top Management to the Batch Upload Section first." :
            'Please upload CSV of the Top Management to the Batch Upload Section first.';
        error_log("No top management messages found - responding with message: $message");
        respond(true, $message, ['data' => []]);
    }

    $photosCollection = $mongoClient->$mongoDbName->Top_Management_Photos;
    error_log("Querying Top_Management_Photos collection with filter: " . json_encode($academicYearFilter));
    $photos = $photosCollection->find($academicYearFilter, ['sort' => ['position' => 1]]);
    
    $photoCount = count(iterator_to_array($photos));
    error_log("Found $photoCount top management photos");

    $result = [];
    $photoMap = [];

    $photoIndex = 0;
    foreach ($photos as $photo) {
        $photoIndex++;
        error_log("Processing photo record #$photoIndex: " . json_encode($photo));
        
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

    error_log("Querying Top_Management_Messages collection with filter: " . json_encode($academicYearFilter));
    $messages = $messageCollection->find($academicYearFilter, ['sort' => ['position' => 1]]);
    
    $messageCount = count(iterator_to_array($messages));
    error_log("Found $messageCount top management messages");
    
    $messageIndex = 0;
    foreach ($messages as $message) {
        $messageIndex++;
        error_log("Processing message record #$messageIndex: " . json_encode($message));
        
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
    error_log("Combined result has " . count($result) . " items");

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
    
    error_log("Final sorted result has " . count($result) . " items");
    
    if (empty($result)) {
        respond(true, 'Please upload CSV of the Top Management to the Batch Upload Section first.', ['data' => $result]);
    } else {
        respond(true, 'Top management data retrieved successfully', ['data' => $result]);
    }
} catch (Exception $e) {
    error_log("FetchTopManagement.php exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    respond(false, 'Server error: ' . $e->getMessage());
}
