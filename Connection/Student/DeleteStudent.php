<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error in DeleteStudent: [$errno] $errstr in $errfile:$errline");
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr,
        'error_code' => $errno
    ]);
    exit;
});

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    $client = new Client($mongoUrl);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'MongoDB connection failed: ' . $e->getMessage()
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
error_log("DeleteStudent raw input: " . $rawInput);

$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input: ' . json_last_error_msg(),
        'raw_input' => $rawInput
    ]);
    exit;
}

error_log("DeleteStudent parsed data: " . print_r($data, true));

$studentId = isset($data['student_id']) ? trim($data['student_id']) : null;
$collectionName = isset($data['collection']) ? trim($data['collection']) : null;
$academicYear = isset($data['academic_year']) ? trim($data['academic_year']) : '';

error_log("DeleteStudent - student_id: $studentId, collection: $collectionName, academic_year: $academicYear");

if (!$studentId || !$collectionName) {
    error_log("DeleteStudent - Missing required parameters: student_id=" . ($studentId ? 'OK' : 'MISSING') . ", collection=" . ($collectionName ? 'OK' : 'MISSING'));
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters.'
    ]);
    exit;
}

$dbName = "ECADYB";
$db = $client->$dbName;

try {
    error_log("DeleteStudent attempting to delete student_id=$studentId from collection=$collectionName in database=$dbName");

    $collections = iterator_to_array($db->listCollectionNames());
    if (!in_array($collectionName, $collections)) {
        error_log("DeleteStudent collection not found: $collectionName");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "Collection '$collectionName' does not exist in database '$dbName'."
        ]);
        exit;
    }

    $collection = $db->$collectionName;

    $filter = [
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ];

    if (!empty($academicYear)) {
        $filter['academic year'] = $academicYear;
    }

    $student = $collection->findOne($filter);

    if (!$student && !empty($academicYear)) {
        error_log("DeleteStudent - Student not found with academic year, trying without it");
        $filterWithoutYear = [
            '$or' => [
                ['student id' => $studentId],
                ['student_id' => $studentId]
            ]
        ];
        $student = $collection->findOne($filterWithoutYear);
        if ($student) {
            $filter = $filterWithoutYear;
            error_log("DeleteStudent - Student found without academic year filter");
        }
    }

    error_log("DeleteStudent - Student found: " . ($student ? "YES" : "NO"));
    if ($student) {
        error_log("DeleteStudent - Student data: " . print_r($student, true));
        error_log("DeleteStudent - Using filter: " . print_r($filter, true));
    }

    if (!$student) {
        $allStudents = $collection->find([], [
            'projection' => ['student id' => 1, 'student_id' => 1, 'academic year' => 1],
            'limit' => 10
        ]);

        $ids = [];
        foreach ($allStudents as $s) {
            $year = $s['academic year'] ?? 'N/A';
            if (isset($s['student id'])) {
                $ids[] = "[space] " . $s['student id'] . " (year: $year)";
            }
            if (isset($s['student_id'])) {
                $ids[] = "[underscore] " . $s['student_id'] . " (year: $year)";
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

    $deleteResult = $collection->deleteOne($filter);

    error_log("DeleteStudent result - Deleted count: " . $deleteResult->getDeletedCount());

    if ($deleteResult->getDeletedCount() > 0) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Student deleted successfully!'
        ]);
    } else {
        error_log("DeleteStudent failed - student found but not deleted");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Student could not be deleted. Deleted count: ' . $deleteResult->getDeletedCount()
        ]);
    }
} catch (Exception $e) {
    error_log("DeleteStudent Exception: " . $e->getMessage());
    error_log("DeleteStudent Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting student: ' . $e->getMessage(),
        'error_type' => get_class($e)
    ]);
}
