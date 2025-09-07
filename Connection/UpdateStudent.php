<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

// Helper: JSON Response
function respond($success, $message = '', $data = [])
{
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// ----------------------
// MongoDB connection
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
} catch (Exception $e) {
    respond(false, "Failed to connect to MongoDB: " . $e->getMessage());
}

// ----------------------
// Get JSON POST data
// ----------------------
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    respond(false, 'No data received.');
}

// ----------------------
// IDs: original for lookup, optional new for update
// ----------------------
$originalId = $data['original_student_id'] ?? null;
if (!$originalId || trim($originalId) === '') {
    respond(false, 'Missing original student id.');
}
unset($data['original_student_id']);

// Optional new ID provided from the form
$newStudentId = null;
if (isset($data['student id'])) {
    $newStudentId = trim($data['student id']) !== '' ? $data['student id'] : null;
}

// Determine collection
$collectionName = $data['collection'] ?? 'students';
unset($data['collection']);

// ----------------------
// Update MongoDB document
// ----------------------
try {
    $collection = $db->{$collectionName};

    // Clean update fields (remove nulls & empties if needed)
    $updateFields = array_filter($data, function ($val) {
        return $val !== null; // keep empty string if you want to clear values
    });

    // Try to find the document first to ensure it exists
    $existingDoc = $collection->findOne([
        '$or' => [
            ['student id' => $originalId],
            ['student_id' => $originalId] // Also check underscore version
        ]
    ]);

    if (!$existingDoc) {
        respond(false, 'Student not found with ID: ' . $studentId);
    }

    // Use the correct field name that exists in the document
    $queryField = isset($existingDoc['student id']) ? 'student id' : 'student_id';

    // If a new student id was provided, include it in the update set using the same field name
    if ($newStudentId !== null && $newStudentId !== $originalId) {
        $updateFields[$queryField] = $newStudentId;
    }

    $result = $collection->updateOne(
        [$queryField => $originalId], // lookup by original id
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
