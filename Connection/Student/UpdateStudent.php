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

function respond($success, $message = '', $data = [])
{
    header('Content-Type: application/json');
    http_response_code($success ? 200 : 400);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

require_once __DIR__ . '/../Configuration/EnvLoader.php';
$mongoUrl = getMongoUrl();
try {
    $client = new Client($mongoUrl);
} catch (Exception $e) {
    respond(false, "Failed to connect to MongoDB: " . $e->getMessage());
}

$data = json_decode(file_get_contents('php://input'), true);
error_log("Received data: " . print_r($data, true));

if (!$data) {
    respond(false, 'No data received.');
}

$originalId = $data['original_student_id'] ?? null;
if (!$originalId || trim($originalId) === '') {
    respond(false, 'Missing original student id.');
}
unset($data['original_student_id']);

$newStudentId = null;
if (isset($data['student id'])) {
    $newStudentId = trim($data['student id']) !== '' ? $data['student id'] : null;
}

$collectionName = $data['collection'] ?? 'students';
$academicYear = $data['academic_year'] ?? '';
unset($data['collection']);
unset($data['academic_year']);

$dbName = "ECADYB";
$db = $client->$dbName;

try {
    $collection = $db->{$collectionName};
    error_log("Using database: " . $dbName . ", collection: " . $collectionName);

    $updateFields = array_filter($data, function ($val) {
        return $val !== null;
    });

    error_log("Update fields: " . print_r($updateFields, true));

    $filter = [
        '$or' => [
            ['student id' => $originalId],
            ['student_id' => $originalId]
        ]
    ];

    if (!empty($academicYear)) {
        $filter['academic year'] = $academicYear;
    }

    $existingDoc = $collection->findOne($filter);

    if (!$existingDoc && !empty($academicYear)) {
        $filterWithoutYear = [
            '$or' => [
                ['student id' => $originalId],
                ['student_id' => $originalId]
            ]
        ];
        $existingDoc = $collection->findOne($filterWithoutYear);
        if ($existingDoc) {
            $filter = $filterWithoutYear;
        }
    }

    if (!$existingDoc) {
        $allDocs = $collection->find([], ['limit' => 5, 'projection' => ['student id' => 1, 'student_id' => 1, 'academic year' => 1]]);
        $docList = [];
        foreach ($allDocs as $doc) {
            $year = $doc['academic year'] ?? 'N/A';
            $docList[] = [
                'student_id' => $doc['student id'] ?? $doc['student_id'] ?? 'N/A',
                'academic_year' => $year,
                '_id' => (string)($doc['_id'] ?? 'N/A')
            ];
        }
        error_log("Student not found with ID: " . $originalId . " in collection: " . $collectionName);
        error_log("Sample documents in collection: " . print_r($docList, true));
        respond(false, 'Student not found with ID: ' . $originalId . ' in collection: ' . $collectionName);
    }

    error_log("Found existing document: " . print_r($existingDoc, true));

    $queryField = isset($existingDoc['student id']) ? 'student id' : 'student_id';
    error_log("Using query field: " . $queryField . " with value: " . $originalId);

    if ($newStudentId !== null && $newStudentId !== $originalId) {
        $updateFields[$queryField] = $newStudentId;
        error_log("Student ID will be changed from " . $originalId . " to " . $newStudentId);
    }

    foreach ($updateFields as $field => $value) {
        if ($field === $queryField) {
            continue;
        }

        if (isset($existingDoc[$field]) && $existingDoc[$field] === $value) {
            unset($updateFields[$field]);
            error_log("Field " . $field . " unchanged, removing from update");
        }

        if (!isset($existingDoc[$field]) && ($value === '' || $value === null)) {
            unset($updateFields[$field]);
            error_log("Field " . $field . " is empty and didn't exist, removing from update");
        }
    }

    if (empty($updateFields)) {
        respond(true, 'No changes detected, student record remains the same.');
    }

    error_log("Final update fields: " . print_r($updateFields, true));

    $result = $collection->updateOne(
        $filter,
        ['$set' => $updateFields],
        ['upsert' => false]
    );

    error_log("Update result - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());
    error_log("Update filter used: " . print_r($filter, true));

    if ($result->getModifiedCount() > 0) {
        respond(true, 'Student details saved successfully.');
    } elseif ($result->getMatchedCount() > 0) {
        respond(true, 'No changes detected, student record remains the same.');
    } else {
        error_log("Update failed - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());
        respond(false, 'Update failed - no documents matched or modified. Matched: ' . $result->getMatchedCount() . ', Modified: ' . $result->getModifiedCount());
    }
} catch (Exception $e) {
    error_log("[UpdateStudent.php] Error: " . $e->getMessage());
    respond(false, 'Failed to update student: ' . $e->getMessage());
}
