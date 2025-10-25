<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../Configuration/MongoConnect.php';
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to load dependencies: ' . $e->getMessage()]);
    exit;
}

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

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
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

set_exception_handler(function ($exception) {
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
    $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;

    error_log("FetchStudentPhotos.php called with student_id: " . ($studentId ?: 'null'));

    $mongoDbName = "ECADYB";
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    $mongoClient = new MongoDB\Client($mongoUrl);

    $photosCollection = $mongoClient->$mongoDbName->Student_Photos;

    $filter = [];
    if ($studentId) {
        $filter['student_id'] = $studentId;
    }

    error_log("Querying Student_Photos collection with filter: " . json_encode($filter));

    $photosCursor = $photosCollection->find($filter, ['sort' => ['upload_time' => -1]]);

    // Convert cursor to array to avoid rewind issues
    $photos = iterator_to_array($photosCursor);
    $photoCount = count($photos);

    error_log("Found " . $photoCount . " photo records for student_id: " . ($studentId ?: 'null'));

    $result = [];
    $processedCount = 0;
    foreach ($photos as $photo) {
        $processedCount++;
        error_log("Processing photo record #" . $processedCount . " for student_id: " . ($studentId ?: 'null'));

        $studentData = [
            'id' => (string)$photo['_id'],
            'student_id' => $photo['student_id'] ?? '',
            'template' => $photo['template'] ?? 1,
            'upload_time' => $photo['upload_time'] ?? '',
            'photos' => [
                'student_photo_1' => [
                    'type' => 'toga',
                    'url' => $photo['toga_url'] ?? '',
                    'filename' => $photo['toga_filename'] ?? '',
                    'original_name' => $photo['toga_original_name'] ?? ''
                ],
                'student_photo_2' => [
                    'type' => 'uniform',
                    'url' => $photo['uniform_url'] ?? '',
                    'filename' => $photo['uniform_filename'] ?? '',
                    'original_name' => $photo['uniform_original_name'] ?? ''
                ],
                'student_photo_3' => [
                    'type' => 'filipiniana',
                    'url' => $photo['filipiniana_url'] ?? '',
                    'filename' => $photo['filipiniana_filename'] ?? '',
                    'original_name' => $photo['filipiniana_original_name'] ?? ''
                ]
            ]
        ];

        $result[] = $studentData;
    }

    error_log("Returning " . count($result) . " photo records for student_id: " . ($studentId ?: 'null'));

    respond(true, 'Student photos retrieved successfully', ['data' => $result]);
} catch (Exception $e) {
    error_log("FetchStudentPhotos.php exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    respond(false, 'Server error: ' . $e->getMessage());
}
