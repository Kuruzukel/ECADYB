<?php
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

header('Content-Type: application/json');

// ----------------------
// MongoDB connection
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$client   = new Client($mongoUrl);
$db       = $client->Departments;

// ----------------------
// Handle POST request
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect values
    $studentId   = $_POST['student_id'] ?? null;
    $originalId  = $_POST['original_student_id'] ?? null; // if form uses hidden ID
    $collection  = $_POST['collection'] ?? null;
    $status      = $_POST['status'] ?? null;

    // Validation
    if ($collection && $status && ($studentId || $originalId)) {
        $coll = $db->$collection;

        // Use original ID for lookup if provided, else fall back to student_id
        $lookupId = $originalId ?: $studentId;

        // Match either "student_id" or "student id" (in case schema differs)
        $filter = [
            '$or' => [
                ['student_id' => $lookupId],
                ['student id' => $lookupId]
            ]
        ];

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateOne($filter, $update);

        echo json_encode([
            "success"     => true,
            "lookup_id"   => $lookupId,
            "new_status"  => $status,
            "collection"  => $collection,
            "matched"     => $result->getMatchedCount(),
            "modified"    => $result->getModifiedCount()
        ]);
        exit;
    }

    // If missing data
    echo json_encode([
        "success"  => false,
        "message"  => "Missing required data",
        "received" => $_POST // debug helper
    ]);
    exit;
}

// ----------------------
// Fallback for non-POST
// ----------------------
echo json_encode([
    "success" => false,
    "message" => "Invalid request method"
]);