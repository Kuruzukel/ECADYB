<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

// ----------------------
// Helper: JSON Response
// ----------------------
function respond($success, $message = '', $data = []) {
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
// Require "student id"
// ----------------------
if (!isset($data['student id']) || trim($data['student id']) === '') {
    respond(false, 'Missing student id.');
}
$studentId = $data['student id'];
unset($data['student id']); // do NOT allow overwriting ID

// Determine collection
$collectionName = $data['collection'] ?? 'students';
unset($data['collection']);

// ----------------------
// Update MongoDB document
// ----------------------
try {
    $collection = $db->{$collectionName};

    // Clean update fields (remove nulls & empties if needed)
    $updateFields = array_filter($data, function($val) {
        return $val !== null; // keep empty string if you want to clear values
    });

    // Try to find the document first to ensure it exists
    $existingDoc = $collection->findOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId] // Also check underscore version
        ]
    ]);

    if (!$existingDoc) {
        respond(false, 'Student not found with ID: ' . $studentId);
    }

    // Use the correct field name that exists in the document
    $queryField = isset($existingDoc['student id']) ? 'student id' : 'student_id';
    
    $result = $collection->updateOne(
        [$queryField => $studentId], // Use the correct field name
        ['$set' => $updateFields],
        ['upsert' => false] // Never create new documents
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