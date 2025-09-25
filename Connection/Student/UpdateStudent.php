<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

function respond($success, $message = '', $data = [])
{
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
} catch (Exception $e) {
    respond(false, "Failed to connect to MongoDB: " . $e->getMessage());
}

$data = json_decode(file_get_contents('php://input'), true);
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
unset($data['collection']);

try {
    $collection = $db->{$collectionName};

    $updateFields = array_filter($data, function ($val) {});

    $existingDoc = $collection->findOne([
        '$or' => [
            ['student id' => $originalId],
            ['student_id' => $originalId]
        ]
    ]);

    if (!$existingDoc) {
        respond(false, 'Student not found with ID: ' . $studentId);
    }

    $queryField = isset($existingDoc['student id']) ? 'student id' : 'student_id';

    if ($newStudentId !== null && $newStudentId !== $originalId) {
        $updateFields[$queryField] = $newStudentId;
    }

    $result = $collection->updateOne(
        [$queryField => $originalId],
        ['$set' => $updateFields],
        ['upsert' => false]
    );

    if ($result->getModifiedCount() > 0) {
        respond(true, 'Student details saved successfully.');
    } elseif ($result->getMatchedCount() > 0) {
        respond(true, 'No changes detected, student record remains the same.');
    } else {
        respond(false, 'Update failed - no documents matched or modified.');
    }
} catch (Exception $e) {
    error_log("[UpdateStudent.php] " . $e->getMessage());
    respond(false, 'Failed to update student. Check server logs.');
}
