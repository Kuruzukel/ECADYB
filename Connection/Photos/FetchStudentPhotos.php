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
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    $mongoDbName = "BatchTemplate" . $template;
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl);

    $photosCollection = $mongoClient->$mongoDbName->StudentPhotos;
    
    // Build query filter
    $filter = ['template' => $template];
    if ($studentId) {
        $filter['student_id'] = $studentId;
    }

    $photos = $photosCollection->find($filter, ['sort' => ['upload_time' => -1]]);

    $result = [];

    foreach ($photos as $photo) {
        $studentData = [
            'id' => (string)$photo['_id'],
            'student_id' => $photo['student_id'] ?? '',
            'template' => $photo['template'] ?? $template,
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

    respond(true, 'Student photos retrieved successfully', ['data' => $result]);
} catch (Exception $e) {
    error_log("FetchStudentPhotos.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
