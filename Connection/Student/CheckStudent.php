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
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

function respond($exists, $message = '', $data = [])
{
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(array_merge(['exists' => $exists, 'message' => $message], $data));
    exit;
}

try {
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    $client = new Client($mongoUrl);
} catch (Exception $e) {
    respond(false, "Failed to connect to MongoDB: " . $e->getMessage());
}

$data = json_decode(file_get_contents('php://input'), true);
error_log("CheckStudent - Received data: " . print_r($data, true));

if (!$data) {
    respond(false, 'No data received.');
}

$studentId = $data['student_id'] ?? null;
$collectionName = $data['collection'] ?? 'bsme';
$academicYear = $data['academic_year'] ?? '';

if (!$studentId || trim($studentId) === '') {
    respond(false, 'Missing student ID.');
}

$dbName = "ECADYB";
$db = $client->$dbName;

try {
    $collection = $db->{$collectionName};
    error_log("CheckStudent - Using database: " . $dbName . ", collection: " . $collectionName);

    // Search for student with multiple possible field names
    $filter = [
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ];

    error_log("CheckStudent - Search filter: " . print_r($filter, true));

    $student = $collection->findOne($filter);

    if ($student) {
        error_log("CheckStudent - Student found: " . print_r($student, true));
        
        // Convert MongoDB document to array for JSON response
        $studentArray = $student->toArray();
        
        respond(true, 'Student found in database.', ['student' => $studentArray]);
    } else {
        error_log("CheckStudent - Student not found with ID: " . $studentId);
        
        // Get sample documents to help debug
        $sampleDocs = $collection->find([], ['limit' => 3, 'projection' => ['student id' => 1, 'student_id' => 1, 'first name' => 1, 'last name' => 1]]);
        $samples = [];
        foreach ($sampleDocs as $doc) {
            $samples[] = [
                'student_id' => $doc['student id'] ?? $doc['student_id'] ?? 'N/A',
                'name' => ($doc['first name'] ?? '') . ' ' . ($doc['last name'] ?? ''),
                '_id' => (string)($doc['_id'] ?? 'N/A')
            ];
        }
        
        error_log("CheckStudent - Sample documents in collection: " . print_r($samples, true));
        
        respond(false, 'Student not found with ID: ' . $studentId . ' in collection: ' . $collectionName, ['samples' => $samples]);
    }

} catch (Exception $e) {
    error_log("CheckStudent - Error: " . $e->getMessage());
    respond(false, 'Failed to check student: ' . $e->getMessage());
}
?>