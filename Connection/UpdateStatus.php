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

    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $studentId  = $input['student_id'] ?? null;
    $collection = $input['collection'] ?? null;
    $status     = $input['status'] ?? null;

    // Validation
    if (!$studentId || !$collection || !$status) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required data",
            "received" => $input
        ]);
        exit;
    }

    // Access the collection
    try {
        $coll = $db->$collection;

        // Filter by student_id or "student id"
        $filter = [
            '$or' => [
                ['student_id' => $studentId],
                ['student id' => $studentId]
            ]
        ];

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateOne($filter, $update);

        if ($result->getModifiedCount() > 0 || $result->getMatchedCount() > 0) {
            echo json_encode([
                "success"     => true,
                "message"     => "Status updated successfully",
                "lookup_id"   => $studentId,
                "new_status"  => $status,
                "collection"  => $collection,
                "matched"     => $result->getMatchedCount(),
                "modified"    => $result->getModifiedCount()
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "No matching student found",
                "lookup_id" => $studentId,
                "collection" => $collection
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

    exit;
}

// ----------------------
// Fallback for non-POST requests
// ----------------------
echo json_encode([
    "success" => false,
    "message" => "Invalid request method"
]);