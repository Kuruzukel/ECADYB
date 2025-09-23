<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: application/json');

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

try {
    $client = new Client($mongoUrl);
    $departmentsDB = $client->Departments;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'MongoDB connection failed: ' . $e->getMessage()
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$studentId = isset($data['student_id']) ? trim($data['student_id']) : null;
$collectionName = isset($data['collection']) ? trim($data['collection']) : null;

if (!$studentId || !$collectionName) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters.'
    ]);
    exit;
}

$collections = iterator_to_array($departmentsDB->listCollectionNames());
if (!in_array($collectionName, $collections)) {
    echo json_encode([
        'success' => false,
        'message' => "Collection '$collectionName' does not exist."
    ]);
    exit;
}

$collection = $departmentsDB->$collectionName;

try {
    $studentIdRegex = [
        '$regex' => '^' . preg_quote($studentId) . '$',
        '$options' => 'i'
    ];

    $student = $collection->findOne([
        '$or' => [
            ['student id' => $studentIdRegex],
            ['student_id' => $studentIdRegex],
        ]
    ]);

    if (!$student) {
        $allStudents = $collection->find([], [
            'projection' => ['student id' => 1, 'student_id' => 1]
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

        echo json_encode([
            'success' => false,
            'message' => "No student found with student_id='$studentId' in collection '$collectionName'.",
            'debug_ids' => $ids
        ]);
        exit;
    }

    $deleteResult = $collection->deleteOne([
        '$or' => [
            ['student id' => $studentIdRegex],
            ['student_id' => $studentIdRegex],
        ]
    ]);

    if ($deleteResult->getDeletedCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Student deleted successfully!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Student could not be deleted.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting student: ' . $e->getMessage()
    ]);
}
