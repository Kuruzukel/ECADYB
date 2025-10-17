<?php
// Set headers first to allow CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Turn off error display for production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Global error handler to ensure JSON response
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr
    ]);
    exit;
});

session_start();
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

// Use MONGO_URL or MONGODB_URI (Railway standard) with fallback
$mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

try {
    $client = new Client($mongoUrl);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'MongoDB connection failed: ' . $e->getMessage()
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$studentId = isset($data['student_id']) ? trim($data['student_id']) : null;
$collectionName = isset($data['collection']) ? trim($data['collection']) : null;
$template = isset($data['template']) ? trim($data['template']) : '1';

if (!$studentId || !$collectionName) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters.'
    ]);
    exit;
}

$dbName = "BatchTemplate" . $template;
$db = $client->$dbName;

try {
    $collections = iterator_to_array($db->listCollectionNames());
    if (!in_array($collectionName, $collections)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "Collection '$collectionName' does not exist in database '$dbName'."
        ]);
        exit;
    }

    $collection = $db->$collectionName;

    $student = $collection->findOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ]);

    if (!$student) {
        $allStudents = $collection->find([], [
            'projection' => ['student id' => 1, 'student_id' => 1],
            'limit' => 10
        ]);

        $ids = [];
        foreach ($allStudents as $s) {
            if (isset($s['student id'])) {
                $ids[] = "[space] " . $s['student id'];
            }
            if (isset($s['student_id'])) {
                $ids[] = "[underscore] " . $s['student_id'];
            }
        }

        error_log("Available IDs in $collectionName: " . implode(', ', $ids));

        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "No student found with student_id='$studentId' in collection '$collectionName' in database '$dbName'.",
            'debug_ids' => $ids
        ]);
        exit;
    }

    $deleteResult = $collection->deleteOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ]);

    if ($deleteResult->getDeletedCount() > 0) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Student deleted successfully!'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Student could not be deleted.'
        ]);
    }
} catch (Exception $e) {
    error_log("DeleteStudent Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting student: ' . $e->getMessage()
    ]);
}
