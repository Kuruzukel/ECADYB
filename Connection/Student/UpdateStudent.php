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

$mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
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
$template = $data['template'] ?? '1';
unset($data['collection']);
unset($data['template']);

$dbName = "BatchTemplate" . $template;
$db = $client->$dbName;

try {
    $collection = $db->{$collectionName};
    error_log("Using database: " . $dbName . ", collection: " . $collectionName);

    $updateFields = array_filter($data, function ($val) {
        return $val !== null;
    });

    error_log("Update fields: " . print_r($updateFields, true));

    $existingDoc = $collection->findOne([
        '$or' => [
            ['student id' => $originalId],
            ['student_id' => $originalId]
        ]
    ]);

    if (!$existingDoc) {
        $allDocs = $collection->find([], ['limit' => 5]);
        $docList = [];
        foreach ($allDocs as $doc) {
            $docList[] = [
                'student_id' => $doc['student id'] ?? $doc['student_id'] ?? 'N/A',
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
        [$queryField => $originalId],
        ['$set' => $updateFields],
        ['upsert' => false]
    );

    error_log("Update result - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());

    if ($result->getModifiedCount() > 0) {
        respond(true, 'Student details saved successfully.');
    } elseif ($result->getMatchedCount() > 0) {
        respond(true, 'No changes detected, student record remains the same.');
    } else {
        respond(false, 'Update failed - no documents matched or modified.');
    }
} catch (Exception $e) {
    error_log("[UpdateStudent.php] Error: " . $e->getMessage());
    respond(false, 'Failed to update student: ' . $e->getMessage());
}
