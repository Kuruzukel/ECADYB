<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: application/json');

// MongoDB connection
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

// ----------------------
// Read JSON POST data
// ----------------------
$data = json_decode(file_get_contents('php://input'), true);

$studentId = isset($data['student_id']) ? trim($data['student_id']) : null;
$collectionName = isset($data['collection']) ? trim($data['collection']) : null;

// ----------------------
// Validate parameters
// ----------------------
if (!$studentId || !$collectionName) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters.'
    ]);
    exit;
}

// ----------------------
// Validate collection exists
// ----------------------
$collections = iterator_to_array($departmentsDB->listCollectionNames());
if (!in_array($collectionName, $collections)) {
    echo json_encode([
        'success' => false,
        'message' => "Collection '$collectionName' does not exist."
    ]);
    exit;
}

$collection = $departmentsDB->$collectionName;

// ----------------------
// Attempt deletion
// ----------------------
try {
    // Use regex array for exact match ignoring case
    $studentIdRegex = [
        '$regex' => '^' . preg_quote($studentId) . '$',
        '$options' => 'i'
    ];

    // Find student by "student id" (space included)
    $student = $collection->findOne(['student id' => $studentIdRegex]);

    if (!$student) {
        // Debug: list all student IDs in collection (optional)
        $allStudents = $collection->find([], ['projection' => ['student id' => 1]]);
        $ids = [];
        foreach ($allStudents as $s) {
            $ids[] = $s['student id'];
        }
        error_log("Available student IDs in $collectionName: " . implode(', ', $ids));

        echo json_encode([
            'success' => false,
            'message' => "No student found with student_id='$studentId' in collection '$collectionName'."
        ]);
        exit;
    }

    // Delete the student
    $deleteResult = $collection->deleteOne(['student id' => $studentIdRegex]);

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
