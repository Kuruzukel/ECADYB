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

// Use the correct BatchTemplate database
$dbName = "BatchTemplate" . $template;
$db = $client->$dbName;

try {
    $collection = $db->{$collectionName};
    error_log("Using database: " . $dbName . ", collection: " . $collectionName);

    // Filter out null values but keep empty strings
    $updateFields = array_filter($data, function ($val) {
        return $val !== null;
    });
    
    error_log("Update fields: " . print_r($updateFields, true));

    // Find the existing document
    $existingDoc = $collection->findOne([
        '$or' => [
            ['student id' => $originalId],
            ['student_id' => $originalId]
        ]
    ]);

    if (!$existingDoc) {
        // Let's also check what documents exist in this collection
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

    // Determine which field name is used for student ID in this document
    $queryField = isset($existingDoc['student id']) ? 'student id' : 'student_id';
    error_log("Using query field: " . $queryField . " with value: " . $originalId);

    // If student ID is being changed, update that field as well
    if ($newStudentId !== null && $newStudentId !== $originalId) {
        $updateFields[$queryField] = $newStudentId;
        error_log("Student ID will be changed from " . $originalId . " to " . $newStudentId);
    }

    // Remove fields that haven't actually changed
    foreach ($updateFields as $field => $value) {
        // Skip the student ID field as it's used for querying
        if ($field === $queryField) {
            continue;
        }
        
        // If the field exists in the existing document and has the same value, remove it
        if (isset($existingDoc[$field]) && $existingDoc[$field] === $value) {
            unset($updateFields[$field]);
            error_log("Field " . $field . " unchanged, removing from update");
        }
        
        // If the field doesn't exist in the document and the new value is empty, remove it
        if (!isset($existingDoc[$field]) && ($value === '' || $value === null)) {
            unset($updateFields[$field]);
            error_log("Field " . $field . " is empty and didn't exist, removing from update");
        }
    }

    // If no fields have actually changed, return success but indicate no changes
    if (empty($updateFields)) {
        respond(true, 'No changes detected, student record remains the same.');
    }

    error_log("Final update fields: " . print_r($updateFields, true));

    // Perform the update operation
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